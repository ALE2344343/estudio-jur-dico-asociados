<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Models\Audiencia;
use App\Models\HistorialActividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CasoController extends Controller
{
    private function checkAuth()
    {
        if (!Session::has('usuario_id')) return redirect()->route('inicio');
        return null;
    }

    private function checkAdmin()
    {
        if (!Session::has('usuario_id')) return redirect()->route('inicio');
        if (Session::get('usuario_rol') !== 'administrador') abort(403, 'Solo administradores pueden realizar esta acción.');
        return null;
    }

    public function index(Request $request)
    {
        if ($r = $this->checkAuth()) return $r;
        $rol    = Session::get('usuario_rol');
        $userId = Session::get('usuario_id');
        $query  = Caso::with(['cliente', 'abogado']);

        if ($rol !== 'administrador') $query->where('abogado_id', $userId);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('titulo', 'like', "%{$s}%")
                  ->orWhere('numero_caso', 'like', "%{$s}%")
                  ->orWhere('numero_expediente', 'like', "%{$s}%")
                  ->orWhere('juzgado', 'like', "%{$s}%")
                  ->orWhereHas('cliente', function($qc) use ($s) {
                      $qc->where('nombre', 'like', "%{$s}%")
                         ->orWhere('apellido', 'like', "%{$s}%")
                         ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%{$s}%"])
                         ->orWhere('ci', 'like', "%{$s}%");
                  })
                  ->orWhereHas('abogado', function($qa) use ($s) {
                      $qa->where('nombre', 'like', "%{$s}%")
                         ->orWhere('apellido', 'like', "%{$s}%")
                         ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%{$s}%"]);
                  });
            });
        }
        if ($request->filled('tipo'))      $query->where('tipo', $request->tipo);
        if ($request->filled('estado'))    $query->where('estado', $request->estado);
        if ($request->filled('prioridad')) $query->where('prioridad', $request->prioridad);

        $casos    = $query->orderBy('created_at','desc')->paginate(15)->withQueryString();
        $clientes = ($rol === 'administrador')
            ? Cliente::all()
            : Cliente::where('usuario_id', $userId)->get();
        $abogados = Usuario::where('rol','abogado')->where('activo',true)->get();

        return view('casos.index', compact('casos','clientes','abogados'));
    }

    public function create()
    {
        if ($r = $this->checkAdmin()) return $r;
        $rol      = Session::get('usuario_rol');
        $userId   = Session::get('usuario_id');
        $clientes = ($rol === 'administrador') ? Cliente::all() : Cliente::where('usuario_id', $userId)->get();
        $abogados = Usuario::where('rol','abogado')->where('activo',true)->get();
        return view('casos.create', compact('clientes','abogados'));
    }

    public function store(Request $request)
    {
        if ($r = $this->checkAdmin()) return $r;
        $request->validate([
            'titulo'            => 'required|min:4|max:200',
            'tipo'              => 'required',
            'cliente_id'        => 'required|exists:clientes,id',
            'abogado_id'        => 'required|exists:usuarios,id',
            'fecha_inicio'      => 'required|date',
            'numero_expediente' => 'nullable|regex:/^\d{5,15}$/',
        ], [
            'titulo.min'                => 'El título del caso debe ser descriptivo (mínimo 4 caracteres).',
            'numero_expediente.regex'   => 'El NUREJ (Número de Expediente) no es válido (debe tener entre 5 y 15 dígitos numéricos).',
        ]);
        $data = $request->all();
        $data['numero_caso'] = Caso::generarNumero();
        $caso = Caso::create($data);
        HistorialActividad::registrar('Casos', 'Creación', "Creó el caso {$caso->numero_caso} ({$caso->titulo})", $caso->id);
        return redirect()->route('casos.index')->with('success', 'Caso registrado exitosamente.');
    }

    public function show(int $id)
    {
        if ($r = $this->checkAuth()) return $r;
        $caso = Caso::with(['cliente','abogado','documentos'])->findOrFail($id);
        return view('casos.show', compact('caso'));
    }

    public function edit(int $id)
    {
        if ($r = $this->checkAdmin()) return $r;
        $caso     = Caso::findOrFail($id);
        $rol      = Session::get('usuario_rol');
        $userId   = Session::get('usuario_id');
        $clientes = ($rol === 'administrador') ? Cliente::all() : Cliente::where('usuario_id', $userId)->get();
        $abogados = Usuario::where('rol','abogado')->where('activo',true)->get();
        return view('casos.edit', compact('caso','clientes','abogados'));
    }

    public function update(Request $request, int $id)
    {
        if ($r = $this->checkAdmin()) return $r;
        $caso = Caso::findOrFail($id);
        $request->validate([
            'titulo'            => 'required|min:4|max:200',
            'tipo'              => 'required',
            'cliente_id'        => 'required|exists:clientes,id',
            'abogado_id'        => 'required|exists:usuarios,id',
            'numero_expediente' => 'nullable|regex:/^\d{5,15}$/',
        ], [
            'titulo.min'                => 'El título del caso debe ser descriptivo (mínimo 4 caracteres).',
            'numero_expediente.regex'   => 'El NUREJ (Número de Expediente) no es válido (debe tener entre 5 y 15 dígitos numéricos reales).',
        ]);
        $dataCaso = $request->except(['_token', '_method']);
        HistorialActividad::registrarCambios('Casos', "caso {$caso->numero_caso} ({$caso->titulo})", $caso, $dataCaso, [
            'titulo'            => 'Título del Caso',
            'tipo'              => 'Materia / Tipo',
            'estado'            => 'Estado Procesal',
            'numero_expediente' => 'NUREJ / Expediente',
            'juzgado'           => 'Juzgado',
            'abogado_id'        => 'Abogado Asignado',
            'cliente_id'        => 'Cliente',
            'fecha_inicio'      => 'Fecha de Inicio',
            'descripcion'       => 'Descripción',
        ]);
        $caso->update($dataCaso);
        return redirect()->route('casos.show',$id)->with('success','Caso actualizado.');
    }

    public function storeAudiencia(Request $request, int $id)
    {
        if ($r = $this->checkAuth()) return $r;
        $request->validate([
            'titulo'               => 'required',
            'fecha_hora'           => 'required|date',
            'recordatorio_minutos' => 'nullable|integer',
        ]);
        $caso = Caso::with(['abogado', 'cliente'])->findOrFail($id);
        $aud = Audiencia::create([
            'caso_id'              => $id,
            'titulo'               => $request->titulo,
            'fecha_hora'           => $request->fecha_hora,
            'lugar'                => $request->lugar,
            'recordatorio_minutos' => $request->recordatorio_minutos ?? 15,
        ]);
        $num = $caso->numero_caso;
        HistorialActividad::registrar('Audiencias', 'Creación', "Programó audiencia '{$request->titulo}' para el caso {$num} ({$request->fecha_hora})", $aud->id);

        // Enviar alerta automática por Telegram y Gmail al abogado asignado (y al usuario actual si es distinto)
        $fechaFmt = \Carbon\Carbon::parse($aud->fecha_hora)->format('d/m/Y H:i');
        $clienteNom = $caso->cliente ? $caso->cliente->nombre_completo : 'N/A';
        $lugarTxt = $aud->lugar ?: 'Juzgado correspondiente';
        $msg = "Se ha programado una nueva audiencia judicial:\n"
             . "• Audiencia: {$aud->titulo}\n"
             . "• Expediente: {$num} ({$caso->titulo})\n"
             . "• Cliente: {$clienteNom}\n"
             . "• Fecha y Hora: {$fechaFmt}\n"
             . "• Lugar / Enlace: {$lugarTxt}";

        if ($caso->abogado) {
            \App\Services\NotificadorExternoService::notificarUsuario(
                $caso->abogado,
                "Nueva Audiencia Programada ({$num})",
                $msg,
                "Recordatorio programado {$aud->recordatorio_minutos} minutos antes."
            );
        }

        return redirect()->back()->with('success','Audiencia programada correctamente (se envió el aviso a Telegram).');
    }

    public function destroy(int $id)
    {
        if ($r = $this->checkAdmin()) return $r;
        $caso = Caso::findOrFail($id);
        $info = "{$caso->numero_caso} ({$caso->titulo})";
        $caso->delete();
        HistorialActividad::registrar('Casos', 'Eliminación', "Eliminó el caso {$info}", $id);
        return redirect()->route('casos.index')->with('success','Caso eliminado.');
    }
}


