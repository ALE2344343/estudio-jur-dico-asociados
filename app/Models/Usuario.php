<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Usuario extends Model
{
    protected $table = 'usuarios';

    protected $fillable = [
        'nombre', 'apellido', 'username', 'email', 'password',
        'rol', 'telefono', 'especialidad', 'matricula', 'activo',
        'ultimo_acceso', 'avatar'
    ];

    protected $hidden = ['password'];

    public function getTelegramChatIdAttribute(): string
    {
        return \App\Services\NotificadorExternoService::obtenerTelegramIdUsuario($this->id);
    }

    public function getNombreCompletoAttribute(): string
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    public function getInicialAttribute(): string
    {
        return strtoupper(substr($this->nombre, 0, 1) . substr($this->apellido, 0, 1));
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'usuario_id');
    }

    public function casos()
    {
        return $this->hasMany(Caso::class, 'abogado_id');
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'subido_por');
    }

    public function calculos()
    {
        return $this->hasMany(CalculoFiniquito::class, 'usuario_id');
    }

    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'administrador';
    }
}
