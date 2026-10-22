<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\HistorialActividad;
use App\Services\NotificadorExternoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UsuarioController extends Controller
{
    private function checkAdmin()
    {
        if (!Session::has('usuario_id')) return redirect()->route('inicio');
        if (Session::get('usuario_rol') !== 'administrador') abort(403, 'Acceso restringido a administradores.');
        return null;
    }

    public function index(Request $request)
    {
        if ($r = $this->checkAdmin()) return $r;

        $queryAbogados = Usuario::where('rol', 'abogado');
        $queryAdmins   = Usuario::where('rol', 'administrador');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $filtro = function ($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('apellido', 'like', "%{$s}%")
                  ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%{$s}%"])
                  ->orWhere('username', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('especialidad', 'like', "%{$s}%")
                  ->orWhere('matricula', 'like', "%{$s}%")
                  ->orWhere('telefono', 'like', "%{$s}%");
            };
            $queryAbogados->where($filtro);
            $queryAdmins->where($filtro);
        }

        $abogados = $queryAbogados->orderBy('nombre')->get();
        $administradores = $queryAdmins->orderBy('nombre')->get();
        return view('usuarios.index', compact('abogados', 'administradores'));
    }

    public function create()
    {
        if ($r = $this->checkAdmin()) return $r;
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        if ($r = $this->checkAdmin()) return $r;
        $request->validate([
            'nombre'      => 'required|min:2|max:80|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido'    => 'required|min:2|max:80|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'username'    => 'required|min:3|max:30|alpha_dash|unique:usuarios,username',
            'email'       => 'required|email|max:100|unique:usuarios,email',
            'telefono'    => 'nullable|regex:/^[23467]\d{6,7}$/',
            'matricula'   => 'nullable|string|min:3|max:30',
            'password'    => ['required', 'min:6', 'regex:/[a-zA-Z]/', 'regex:/[0-9@$!%*#?&_\-\.]/', 'confirmed'],
            'rol'         => 'required|in:administrador,abogado',
        ], [
            'nombre.min'          => 'El nombre debe tener al menos 2 letras reales.',
            'nombre.regex'        => 'El nombre debe contener solo letras reales, sin números ni símbolos.',
            'apellido.min'        => 'El apellido debe tener al menos 2 letras reales.',
            'apellido.regex'      => 'El apellido debe contener solo letras reales, sin números ni símbolos.',
            'username.min'        => 'El nombre de usuario debe tener al menos 3 caracteres.',
            'username.alpha_dash' => 'El nombre de usuario solo puede contener letras, números y guiones.',
            'username.unique'     => 'Este nombre de usuario ya se encuentra en uso.',
            'email.email'         => 'El correo electrónico ingresado no tiene un formato válido.',
            'email.unique'        => 'Este correo electrónico ya está registrado con otro usuario.',
            'telefono.regex'      => 'El teléfono debe ser un número boliviano real (7 u 8 dígitos, ej: 72000001).',
            'matricula.min'       => 'La matrícula profesional debe contener al menos 3 caracteres válidos.',
            'password.min'        => 'La contraseña debe tener al menos 6 caracteres.',
            'password.regex'      => 'La contraseña debe combinar letras con números o símbolos para mayor seguridad.',
            'password.confirmed'  => 'La confirmación de la contraseña no coincide.',
        ]);

        $data = $request->only(['username','email','rol','telefono','especialidad','matricula']);
        $data['nombre'] = mb_convert_case(trim($request->nombre), MB_CASE_TITLE, 'UTF-8');
        $data['apellido'] = mb_convert_case(trim($request->apellido), MB_CASE_TITLE, 'UTF-8');
        $data['password'] = Hash::make($request->password);

        $usuario = Usuario::create($data);
        if ($request->filled('telegram_chat_id')) {
            NotificadorExternoService::guardarTelegramIdUsuario($usuario->id, $request->telegram_chat_id);
        }
        HistorialActividad::registrar('Usuarios', 'Creación', "Registró nuevo usuario: {$usuario->nombre_completo} (Rol: " . ucfirst($usuario->rol) . ")", $usuario->id);
        return redirect()->route('usuarios.index')->with('success','Usuario creado exitosamente.');
    }

    public function edit(int $id)
    {
        if ($r = $this->checkAdmin()) return $r;
        $usuario = Usuario::findOrFail($id);
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, int $id)
    {
        if ($r = $this->checkAdmin()) return $r;
        $usuario = Usuario::findOrFail($id);
        $request->validate([
            'nombre'    => 'required|min:2|max:80|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellido'  => 'required|min:2|max:80|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'email'     => 'required|email|max:100|unique:usuarios,email,'.$id,
            'username'  => 'required|min:3|max:30|alpha_dash|unique:usuarios,username,'.$id,
            'telefono'  => 'nullable|regex:/^[23467]\d{6,7}$/',
            'matricula' => 'nullable|string|min:3|max:30',
            'rol'       => 'required|in:administrador,abogado',
        ], [
            'nombre.min'          => 'El nombre debe tener al menos 2 letras ',
            'nombre.regex'        => 'El nombre debe contener solo letras reales, sin números ni símbolos',
            'apellido.min'        => 'El apellido debe tener al menos 2 letras',
            'apellido.regex'      => 'El apellido debe contener solo letras reales, sin números ni símbolos',
            'username.min'        => 'El nombre de usuario debe tener al menos 3 caracteres',
            'username.alpha_dash' => 'El nombre de usuario solo puede contener letras, números y guiones',
            'username.unique'     => 'Este nombre de usuario ya se encuentra en uso',
            'email.email'         => 'El correo electrónico ingresado no tiene un formato válido',
            'email.unique'        => 'Este correo electrónico ya está registrado con otro usuario',
            'telefono.regex'      => 'El teléfono debe ser un número boliviano real (7 u 8 dígitos, ej: 72000001)',
            'matricula.min'       => 'La matrícula profesional debe contener al menos 3 caracteres válidos',
        ]);
        $data = $request->only(['username','email','rol','telefono','especialidad','matricula','activo']);
        $data['nombre'] = mb_convert_case(trim($request->nombre), MB_CASE_TITLE, 'UTF-8');
        $data['apellido'] = mb_convert_case(trim($request->apellido), MB_CASE_TITLE, 'UTF-8');

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['min:6', 'regex:/[a-zA-Z]/', 'regex:/[0-9@$!%*#?&_\-\.]/', 'confirmed'],
            ], [
                'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
                'password.regex'     => 'La contraseña debe combinar letras con números o símbolos para mayor seguridad.',
                'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            ]);
            $data['password'] = Hash::make($request->password);
        }
        HistorialActividad::registrarCambios('Usuarios', "usuario {$usuario->nombre_completo}", $usuario, $data, [
            'nombre'       => 'Nombre',
            'apellido'     => 'Apellido',
            'username'     => 'Usuario de Acceso',
            'email'        => 'Correo Electrónico (Gmail)',
            'rol'          => 'Rol',
            'telefono'     => 'Teléfono',
            'especialidad' => 'Especialidad',
            'matricula'    => 'Matrícula',
            'activo'       => 'Estado de Cuenta',
        ]);
        $usuario->update($data);
        if ($request->has('telegram_chat_id')) {
            NotificadorExternoService::guardarTelegramIdUsuario($usuario->id, $request->telegram_chat_id);
        }
        $mensaje = $request->filled('password')
            ? "Usuario {$usuario->nombre_completo} actualizado y nueva contraseña guardada con éxito."
            : "Usuario {$usuario->nombre_completo} actualizado correctamente.";
        return redirect()->route('usuarios.index')->with('success', $mensaje);
    }

    public function destroy(int $id)
    {
        if ($r = $this->checkAdmin()) return $r;
        if ($id == Session::get('usuario_id')) {
            return back()->with('error','No puede eliminar su propia cuenta.');
        }
        $usr = Usuario::findOrFail($id);
        $info = "{$usr->nombre_completo} (Rol: " . ucfirst($usr->rol) . ")";
        $usr->delete();
        HistorialActividad::registrar('Usuarios', 'Eliminación', "Eliminó la cuenta del usuario {$info}", $id);
        return redirect()->route('usuarios.index')->with('success','Usuario eliminado.');
    }
}
