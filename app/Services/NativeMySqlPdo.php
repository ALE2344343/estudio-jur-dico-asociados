<?php

namespace App\Services;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Puente PDO emulado sobre el protocolo TCP binario de MySQL (3306) o mysqli
 * cuando la extensión pdo_mysql no está cargada en el proceso PHP en ejecución.
 */
class NativeMySqlPdo extends PDO
{
    private $mysqli = null;
    private $socket = null;
    private string $host;
    private int $port;
    private string $database;
    private string $username;
    private string $password;
    private int $defaultFetchMode = PDO::FETCH_OBJ;
    private $lastId = 0;
    private bool $inTx = false;

    public function __construct(string $host, int $port, string $database, string $username, string $password)
    {
        $this->host = $host ?: '127.0.0.1';
        $this->port = $port ?: 3306;
        $this->database = $database;
        $this->username = $username ?: 'root';
        $this->password = $password ?? '';

        if (extension_loaded('mysqli')) {
            mysqli_report(MYSQLI_REPORT_OFF);
            $this->mysqli = @mysqli_init();
            if (!@mysqli_real_connect($this->mysqli, $this->host, $this->username, $this->password, $this->database, $this->port)) {
                throw new PDOException('MySQL connection failed: ' . mysqli_connect_error());
            }
            @mysqli_set_charset($this->mysqli, 'utf8mb4');
        } else {
            $this->connectViaSocket();
        }
    }

    private function connectViaSocket(): void
    {
        $fp = @fsockopen($this->host, $this->port, $errno, $errstr, 5);
        if (!$fp) {
            throw new PDOException("No se pudo conectar a MySQL en {$this->host}:{$this->port} ({$errstr})");
        }
        stream_set_timeout($fp, 15);
        $this->socket = $fp;

        // 1. Leer paquete de saludo inicial (HandshakeV10)
        [$seq, $payload] = $this->readPacket();
        $pos = 1; // protocol version (10)
        $endVer = strpos($payload, "\0", $pos);
        $pos = $endVer + 1 + 4; // thread id (4 bytes)
        $authData1 = substr($payload, $pos, 8);
        $pos += 8 + 1; // filler
        $capLower = unpack('v', substr($payload, $pos, 2))[1];
        $pos += 2 + 1 + 2; // charset(1) + status(2)
        $capUpper = unpack('v', substr($payload, $pos, 2))[1];
        $pos += 2;
        $authLen = ord($payload[$pos]);
        $pos += 1 + 10; // reserved 10 bytes
        $restLen = max(13, $authLen - 8);
        $authData2 = rtrim(substr($payload, $pos, $restLen), "\0");
        $scramble = $authData1 . $authData2;

        // 2. Construir HandshakeResponse41
        // CLIENT_PROTOCOL_41 (512) | CLIENT_SECURE_CONNECTION (32768) | CLIENT_CONNECT_WITH_DB (8) | CLIENT_TRANSACTIONS (8192) | CLIENT_MULTI_RESULTS (131072)
        $clientFlags = 512 | 32768 | 8 | 8192 | 131072 | 4;
        $maxPacket = 16777216;
        $charset = 45; // utf8mb4_general_ci

        $authResp = '';
        if ($this->password !== '') {
            $stage1 = sha1($this->password, true);
            $stage2 = sha1($stage1, true);
            $authResp = $stage1 ^ sha1($scramble . $stage2, true);
        }

        $resp = pack('VVc', $clientFlags, $maxPacket, $charset)
              . str_repeat("\0", 23)
              . $this->username . "\0"
              . chr(strlen($authResp)) . $authResp
              . $this->database . "\0";

        $this->writePacket($resp, $seq + 1);
        [, $resPayload] = $this->readPacket();
        $firstByte = ord($resPayload[0]);
        if ($firstByte === 0xFF) {
            $errMsg = substr($resPayload, 9);
            throw new PDOException("Error de autenticación MySQL: {$errMsg}");
        }
    }

    private function readPacket(): array
    {
        $header = $this->readBytes(4);
        $len = ord($header[0]) | (ord($header[1]) << 8) | (ord($header[2]) << 16);
        $seq = ord($header[3]);
        $body = $len > 0 ? $this->readBytes($len) : '';
        return [$seq, $body];
    }

    private function readBytes(int $length): string
    {
        $buf = '';
        while (strlen($buf) < $length) {
            $chunk = fread($this->socket, $length - strlen($buf));
            if ($chunk === false || $chunk === '') {
                throw new PDOException('Conexión MySQL cerrada inesperadamente.');
            }
            $buf .= $chunk;
        }
        return $buf;
    }

    private function writePacket(string $payload, int $seq = 0): void
    {
        $len = strlen($payload);
        $header = chr($len & 0xFF) . chr(($len >> 8) & 0xFF) . chr(($len >> 16) & 0xFF) . chr($seq & 0xFF);
        fwrite($this->socket, $header . $payload);
    }

    private function readLenEncInt(string $data, int &$offset): ?int
    {
        if ($offset >= strlen($data)) return null;
        $first = ord($data[$offset++]);
        if ($first < 0xFB) return $first;
        if ($first === 0xFB) return null; // NULL
        if ($first === 0xFC) {
            $val = unpack('v', substr($data, $offset, 2))[1];
            $offset += 2;
            return $val;
        }
        if ($first === 0xFD) {
            $b = substr($data, $offset, 3);
            $offset += 3;
            return ord($b[0]) | (ord($b[1]) << 8) | (ord($b[2]) << 16);
        }
        $val = unpack('P', substr($data, $offset, 8))[1];
        $offset += 8;
        return $val;
    }

    private function readLenEncStr(string $data, int &$offset): ?string
    {
        $len = $this->readLenEncInt($data, $offset);
        if ($len === null) return null;
        $str = substr($data, $offset, $len);
        $offset += $len;
        return $str;
    }

    public function executeRawSql(string $sql): array
    {
        if ($this->mysqli) {
            $res = @mysqli_query($this->mysqli, $sql);
            if ($res === false) {
                throw new PDOException(mysqli_error($this->mysqli), mysqli_errno($this->mysqli));
            }
            $this->lastId = mysqli_insert_id($this->mysqli);
            $affected = mysqli_affected_rows($this->mysqli);
            $rows = [];
            if ($res instanceof \mysqli_result) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $rows[] = $row;
                }
                mysqli_free_result($res);
            }
            return ['rows' => $rows, 'affected' => $affected, 'insert_id' => $this->lastId];
        }

        // COM_QUERY (0x03)
        $this->writePacket(chr(0x03) . $sql, 0);
        [, $pkt] = $this->readPacket();
        $first = ord($pkt[0]);

        if ($first === 0xFF) {
            $errno = unpack('v', substr($pkt, 1, 2))[1];
            $msg = substr($pkt, 9);
            throw new PDOException("SQL Error [{$errno}]: {$msg}", $errno);
        }

        if ($first === 0x00) {
            $off = 1;
            $affected = $this->readLenEncInt($pkt, $off) ?? 0;
            $insertId = $this->readLenEncInt($pkt, $off) ?? 0;
            if ($insertId > 0) {
                $this->lastId = $insertId;
            }
            return ['rows' => [], 'affected' => $affected, 'insert_id' => $this->lastId];
        }

        // ResultSet: número de columnas
        $off = 0;
        $colCount = $this->readLenEncInt($pkt, $off);
        $columns = [];
        for ($i = 0; $i < $colCount; $i++) {
            [, $colPkt] = $this->readPacket();
            $cOff = 0;
            $this->readLenEncStr($colPkt, $cOff); // catalog
            $this->readLenEncStr($colPkt, $cOff); // schema
            $this->readLenEncStr($colPkt, $cOff); // table
            $this->readLenEncStr($colPkt, $cOff); // org_table
            $colName = $this->readLenEncStr($colPkt, $cOff); // name
            $columns[] = $colName;
        }

        // Paquete EOF tras columnas
        [, $eofPkt] = $this->readPacket();

        $rows = [];
        while (true) {
            [, $rowPkt] = $this->readPacket();
            if (ord($rowPkt[0]) === 0xFE && strlen($rowPkt) < 9) {
                break; // EOF
            }
            if (ord($rowPkt[0]) === 0xFF) {
                $msg = substr($rowPkt, 9);
                throw new PDOException("SQL Row Error: {$msg}");
            }
            $rOff = 0;
            $assoc = [];
            foreach ($columns as $colName) {
                $assoc[$colName] = $this->readLenEncStr($rowPkt, $rOff);
            }
            $rows[] = $assoc;
        }

        return ['rows' => $rows, 'affected' => count($rows), 'insert_id' => $this->lastId];
    }

    #[\ReturnTypeWillChange]
    public function prepare($query, $options = [])
    {
        return new NativeMySqlStatement($this, $query, $this->defaultFetchMode);
    }

    #[\ReturnTypeWillChange]
    public function exec($statement)
    {
        $res = $this->executeRawSql($statement);
        return $res['affected'];
    }

    #[\ReturnTypeWillChange]
    public function query($query, $fetchMode = null, ...$fetchModeArgs)
    {
        $stmt = $this->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    #[\ReturnTypeWillChange]
    public function lastInsertId($name = null)
    {
        return (string) $this->lastId;
    }

    #[\ReturnTypeWillChange]
    public function beginTransaction()
    {
        $this->executeRawSql('START TRANSACTION');
        $this->inTx = true;
        return true;
    }

    #[\ReturnTypeWillChange]
    public function commit()
    {
        $this->executeRawSql('COMMIT');
        $this->inTx = false;
        return true;
    }

    #[\ReturnTypeWillChange]
    public function rollBack()
    {
        $this->executeRawSql('ROLLBACK');
        $this->inTx = false;
        return true;
    }

    #[\ReturnTypeWillChange]
    public function inTransaction()
    {
        return $this->inTx;
    }

    #[\ReturnTypeWillChange]
    public function getAttribute($attribute)
    {
        if ($attribute === PDO::ATTR_SERVER_VERSION) {
            return '8.0.30-MariaDB';
        }
        if ($attribute === PDO::ATTR_DRIVER_NAME) {
            return 'mysql';
        }
        return null;
    }

    #[\ReturnTypeWillChange]
    public function setAttribute($attribute, $value)
    {
        if ($attribute === PDO::ATTR_DEFAULT_FETCH_MODE) {
            $this->defaultFetchMode = (int) $value;
        }
        return true;
    }

    #[\ReturnTypeWillChange]
    public function quote($string, $type = PDO::PARAM_STR)
    {
        if ($this->mysqli) {
            return "'" . mysqli_real_escape_string($this->mysqli, (string) $string) . "'";
        }
        return "'" . addslashes((string) $string) . "'";
    }
}

class NativeMySqlStatement extends PDOStatement
{
    private NativeMySqlPdo $pdo;
    private string $sql;
    private array $bindings = [];
    private array $rows = [];
    private int $cursor = 0;
    private int $affected = 0;
    private int $fetchMode;

    public function __construct(NativeMySqlPdo $pdo, string $sql, int $fetchMode = PDO::FETCH_OBJ)
    {
        $this->pdo = $pdo;
        $this->sql = $sql;
        $this->fetchMode = $fetchMode;
    }

    #[\ReturnTypeWillChange]
    public function setFetchMode($mode, ...$args)
    {
        $this->fetchMode = (int) $mode;
        return true;
    }

    #[\ReturnTypeWillChange]
    public function bindValue($param, $value, $type = PDO::PARAM_STR)
    {
        $this->bindings[$param] = ['value' => $value, 'type' => $type];
        return true;
    }

    #[\ReturnTypeWillChange]
    public function execute($params = null)
    {
        $sql = $this->sql;
        if (is_array($params)) {
            foreach ($params as $k => $v) {
                $key = is_int($k) ? $k + 1 : $k;
                $this->bindings[$key] = ['value' => $v, 'type' => is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR];
            }
        }

        if (!empty($this->bindings)) {
            $idx = 1;
            $sql = preg_replace_callback('/\?/', function () use (&$idx) {
                $b = $this->bindings[$idx] ?? $this->bindings[$idx - 1] ?? null;
                $idx++;
                if ($b === null || $b['value'] === null) {
                    return 'NULL';
                }
                $val = $b['value'];
                if (is_bool($val)) {
                    return $val ? '1' : '0';
                }
                if (is_int($val) || is_float($val) || $b['type'] === PDO::PARAM_INT) {
                    return (string) $val;
                }
                return $this->pdo->quote((string) $val);
            }, $sql);
        }

        $res = $this->pdo->executeRawSql($sql);
        $this->rows = $res['rows'];
        $this->affected = $res['affected'];
        $this->cursor = 0;
        return true;
    }

    private function formatRow(array $row)
    {
        if ($this->fetchMode === PDO::FETCH_ASSOC) {
            return $row;
        }
        if ($this->fetchMode === PDO::FETCH_NUM) {
            return array_values($row);
        }
        if ($this->fetchMode === PDO::FETCH_COLUMN) {
            return reset($row);
        }
        return (object) $row;
    }

    #[\ReturnTypeWillChange]
    public function fetch($mode = PDO::FETCH_DEFAULT, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        if (!isset($this->rows[$this->cursor])) {
            return false;
        }
        $row = $this->rows[$this->cursor++];
        return $this->formatRow($row);
    }

    #[\ReturnTypeWillChange]
    public function fetchAll($mode = PDO::FETCH_DEFAULT, ...$args)
    {
        $useMode = ($mode === PDO::FETCH_DEFAULT || $mode === 0) ? $this->fetchMode : $mode;
        $out = [];
        foreach ($this->rows as $r) {
            if ($useMode === PDO::FETCH_ASSOC) {
                $out[] = $r;
            } elseif ($useMode === PDO::FETCH_NUM) {
                $out[] = array_values($r);
            } elseif ($useMode === PDO::FETCH_COLUMN) {
                $out[] = reset($r);
            } else {
                $out[] = (object) $r;
            }
        }
        return $out;
    }

    #[\ReturnTypeWillChange]
    public function fetchColumn($column = 0)
    {
        if (!isset($this->rows[$this->cursor])) {
            return false;
        }
        $vals = array_values($this->rows[$this->cursor++]);
        return $vals[$column] ?? false;
    }

    #[\ReturnTypeWillChange]
    public function rowCount()
    {
        return $this->affected;
    }

    #[\ReturnTypeWillChange]
    public function nextRowset()
    {
        return false;
    }
}
