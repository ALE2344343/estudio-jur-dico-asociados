<?php

namespace App\Http\Controllers;

use App\Models\HistorialActividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HistorialController extends Controller
{
    private function checkAdmin()
    {
        if (!Session::has('usuario_id')) return redirect()->route('inicio');
        if (Session::get('usuario_rol') !== 'administrador') {
            abort(403, 'Acceso restringido a administradores.');
        }
        return null;
    }

    /**
     * Vista de solo lectura del historial de auditoría
     */
    public function index(Request $request)
    {
        if ($r = $this->checkAdmin()) return $r;

        $query = HistorialActividad::query();

        // Filtro por búsqueda general
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('descripcion', 'like', "%{$s}%")
                  ->orWhere('usuario_nombre', 'like', "%{$s}%");
            });
        }

        // Filtro por módulo
        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        // Filtro por tipo de acción
        if ($request->filled('accion')) {
            $query->where('accion', 'like', $request->accion . '%');
        }

        // Filtro por fecha
        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();

        // Lista completa de módulos y acciones para el selector de filtros
        $modulos = [
            'Clientes',
            'Casos',
            'Audiencias',
            'Documentos',
            'Leyes',
            'Calculadora',
            'Calendario',
            'Usuarios',
            'Control y Asignaciones',
            'Notificaciones Judiciales',
        ];
        $acciones = ['Creación', 'Modificación', 'Actualización', 'Eliminación', 'Reasignación', 'Cambio de Estado'];

        return view('historial.index', compact('logs', 'modulos', 'acciones'));
    }
}
