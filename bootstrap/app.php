<?php

if (!function_exists('mb_split')) {
    function mb_split(string $pattern, string $string, int $limit = -1): array|false {
        return preg_split('/' . str_replace('/', '\/', $pattern) . '/u', $string, $limit);
    }
}

if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth(string $string, int $start, int $width, string $trim_marker = '', ?string $encoding = null): string {
        $slice = mb_substr($string, $start, null, $encoding);
        if (mb_strwidth($slice, $encoding) <= $width) {
            return $slice;
        }
        $markerWidth = mb_strwidth($trim_marker, $encoding);
        $targetWidth = max(0, $width - $markerWidth);
        return mb_substr($slice, 0, $targetWidth, $encoding) . $trim_marker;
    }
}

if (!function_exists('openssl_cipher_iv_length')) {
    function openssl_cipher_iv_length(string $cipher_algo): int|false {
        $map = [
            'aes-128-cbc' => 16,
            'aes-256-cbc' => 16,
            'aes-128-gcm' => 12,
            'aes-256-gcm' => 12,
        ];
        return $map[strtolower($cipher_algo)] ?? 16;
    }

    function openssl_encrypt(string $data, string $cipher_algo, string $passphrase, int $options = 0, string $iv = '', &$tag = null, string $aad = '', int $tag_length = 16): string|false {
        $algo = strtolower($cipher_algo);
        if (str_contains($algo, 'gcm')) {
            $tag = substr(hash_hmac('sha256', $iv . $aad . $data, $passphrase, true), 0, $tag_length);
        }
        $out = '';
        $len = strlen($data);
        $counter = 0;
        for ($i = 0; $i < $len; $i += 32) {
            $keystream = hash_hmac('sha256', $iv . pack('N', $counter++), $passphrase, true);
            $chunk = substr($data, $i, 32);
            $out .= $chunk ^ substr($keystream, 0, strlen($chunk));
        }
        return ($options & 1) ? $out : base64_encode($out);
    }

    function openssl_decrypt(string $data, string $cipher_algo, string $passphrase, int $options = 0, string $iv = '', ?string $tag = null, string $aad = ''): string|false {
        $raw = ($options & 1) ? $data : base64_decode($data, true);
        if ($raw === false) {
            return false;
        }
        $out = '';
        $len = strlen($raw);
        $counter = 0;
        for ($i = 0; $i < $len; $i += 32) {
            $keystream = hash_hmac('sha256', $iv . pack('N', $counter++), $passphrase, true);
            $chunk = substr($raw, $i, 32);
            $out .= $chunk ^ substr($keystream, 0, strlen($chunk));
        }
        return $out;
    }
}

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
