<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Usuario;
use App\Models\HistorialActividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ClienteController extends Controller
{
    private function checkAuth()
    {
        if (!Session::has('usuario_id')) {
            return redirect()->route('inicio');
        }
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

        $rol      = Session::get('usuario_rol');
        $userId   = Session::get('usuario_id');
        $query    = Cliente::with('abogado');

        if ($rol !== 'administrador') {
            $query->where('usuario_id', $userId);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%{$search}%"])
                  ->orWhere('ci', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhere('celular', 'like', "%{$search}%")
                  ->orWhere('ciudad', 'like', "%{$search}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $clientes  = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $abogados  = Usuario::where('rol', 'abogado')->where('activo', true)->get();
        return view('clientes.index', compact('clientes', 'abogados'));
    }

    public function create()
    {
        if ($r = $this->checkAdmin()) return $r;
        $abogados = Usuario::where('rol', 'abogado')->where('activo', true)->get();
        return view('clientes.create', compact('abogados'));
    }

    public function store(Request $request)
    {
        if ($r = $this->checkAdmin()) return $r;

        $request->validate([
            'nombre'           => 'required|min:2|max:80|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido'         => 'required|min:2|max:80|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'ci'               => 'required|regex:/^[1-9]\d{4,8}$/|unique:clientes,ci',
            'telefono'         => 'required|regex:/^[23467]\d{6,7}$/',
            'celular'          => 'nullable|regex:/^[67]\d{7}$/',
            'email'            => 'nullable|email|max:100',
            'fecha_nacimiento' => 'nullable|date|after_or_equal:1925-01-01|before:today',
        ], [
            'nombre.min'                     => 'El nombre debe tener al menos 2 letras reales.',
            'nombre.regex'                   => 'El nombre debe contener solo letras, sin números ni símbolos.',
            'apellido.min'                   => 'El apellido debe tener al menos 2 letras reales.',
            'apellido.regex'                 => 'El apellido debe contener solo letras, sin números ni símbolos.',
            'ci.regex'                       => 'El Carnet de Identidad (CI) no es creíble (debe tener entre 5 y 9 dígitos reales, ej: 7891234).',
            'ci.unique'                      => 'El Carnet de Identidad (CI) ya se encuentra registrado con otro cliente.',
            'telefono.regex'                 => 'El teléfono debe ser un número boliviano real (7 u 8 dígitos, ej: 22441234 o 72000001).',
            'celular.regex'                  => 'El celular debe ser un número móvil boliviano real (exactamente 8 dígitos iniciando en 6 o 7, ej: 71234567 o 60045788).',
            'email.email'                    => 'El correo electrónico ingresado no tiene un formato válido.',
            'fecha_nacimiento.after_or_equal'=> 'La fecha de nacimiento indica una edad irreal (debe ser posterior al año 1925).',
            'fecha_nacimiento.before'        => 'La fecha de nacimiento no puede ser hoy ni una fecha futura.'
        ]);

        $data           = $request->all();
        $data['nombre'] = mb_convert_case(trim($request->nombre), MB_CASE_TITLE, 'UTF-8');
        $data['apellido'] = mb_convert_case(trim($request->apellido), MB_CASE_TITLE, 'UTF-8');
        $data['codigo'] = Cliente::generarCodigo();
        if (!isset($data['usuario_id'])) {
            $data['usuario_id'] = Session::get('usuario_id');
        }

        $cliente = Cliente::create($data);
        HistorialActividad::registrar('Clientes', 'Creación', "Registró al cliente {$cliente->nombre_completo} (Cód: {$cliente->codigo})", $cliente->id);
        return redirect()->route('clientes.index')->with('success', 'Cliente registrado exitosamente.');
    }

    public function show(int $id)
    {
        if ($r = $this->checkAuth()) return $r;
        $cliente = Cliente::with(['abogado', 'casos', 'documentos'])->findOrFail($id);
        return view('clientes.show', compact('cliente'));
    }

    public function edit(int $id)
    {
        if ($r = $this->checkAdmin()) return $r;
        $cliente  = Cliente::findOrFail($id);
        $abogados = Usuario::where('rol', 'abogado')->where('activo', true)->get();
        return view('clientes.edit', compact('cliente', 'abogados'));
    }

    public function update(Request $request, int $id)
    {
        if ($r = $this->checkAdmin()) return $r;

        $cliente = Cliente::findOrFail($id);
        $request->validate([
            'nombre'           => 'required|min:2|max:80|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido'         => 'required|min:2|max:80|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'ci'               => 'required|regex:/^[1-9]\d{4,8}$/|unique:clientes,ci,' . $id,
            'telefono'         => 'required|regex:/^[23467]\d{6,7}$/',
            'celular'          => 'nullable|regex:/^[67]\d{7}$/',
            'email'            => 'nullable|email|max:100',
            'fecha_nacimiento' => 'nullable|date|after_or_equal:1925-01-01|before:today',
        ], [
            'nombre.min'                     => 'El nombre debe tener al menos 2 letras reales.',
            'nombre.regex'                   => 'El nombre debe contener solo letras, sin números ni símbolos.',
            'apellido.min'                   => 'El apellido debe tener al menos 2 letras reales.',
            'apellido.regex'                 => 'El apellido debe contener solo letras, sin números ni símbolos.',
            'ci.regex'                       => 'El Carnet de Identidad (CI) no es creíble (debe tener entre 5 y 9 dígitos reales, ej: 7891234).',
            'ci.unique'                      => 'El Carnet de Identidad (CI) ya se encuentra registrado con otro cliente.',
            'telefono.regex'                 => 'El teléfono debe ser un número boliviano real (7 u 8 dígitos, ej: 22441234 o 72000001).',
            'celular.regex'                  => 'El celular debe ser un número móvil boliviano real (exactamente 8 dígitos iniciando en 6 o 7, ej: 71234567 o 60045788).',
            'email.email'                    => 'El correo electrónico ingresado no tiene un formato válido.',
            'fecha_nacimiento.after_or_equal'=> 'La fecha de nacimiento indica una edad irreal (debe ser posterior al año 1925).',
            'fecha_nacimiento.before'        => 'La fecha de nacimiento no puede ser hoy ni una fecha futura.'
        ]);

        $dataCliente = $request->except(['_token', '_method']);
        $dataCliente['nombre'] = mb_convert_case(trim($request->nombre), MB_CASE_TITLE, 'UTF-8');
        $dataCliente['apellido'] = mb_convert_case(trim($request->apellido), MB_CASE_TITLE, 'UTF-8');
        HistorialActividad::registrarCambios('Clientes', "cliente {$cliente->nombre_completo} (Cód: {$cliente->codigo})", $cliente, $dataCliente, [
            'nombre'           => 'Nombre',
            'apellido'         => 'Apellido',
            'ci'               => 'Cédula de Identidad (CI)',
            'telefono'         => 'Teléfono',
            'celular'          => 'Celular',
            'email'            => 'Correo Electrónico',
            'direccion'        => 'Dirección',
            'ciudad'           => 'Ciudad',
            'fecha_nacimiento' => 'Fecha de Nacimiento',
            'abogado_id'       => 'Abogado Asignado',
        ]);
        $cliente->update($dataCliente);
        return redirect()->route('clientes.show', $id)->with('success', 'Cliente actualizado.');
    }

    public function destroy(int $id)
    {
        if ($r = $this->checkAdmin()) return $r;
        $cliente = Cliente::findOrFail($id);
        $info = "{$cliente->nombre_completo} (Cód: {$cliente->codigo})";
        $cliente->delete();
        HistorialActividad::registrar('Clientes', 'Eliminación', "Eliminó al cliente {$info}", $id);
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado.');
    }
}









