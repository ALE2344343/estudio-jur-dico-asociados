<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function inicio()
    {
        if (Session::has('usuario_id')) {
            return redirect()->route('dashboard');
        }
        return view('auth.inicio');
    }

    public function showLoginAdmin()
    {
        return view('auth.login', ['rol' => 'administrador']);
    }

    public function showLoginAbogado()
    {
        return view('auth.login', ['rol' => 'abogado']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'rol'      => 'required|in:administrador,abogado',
        ]);

        $usuario = Usuario::where('username', $request->username)
                          ->where('rol', $request->rol)
                          ->where('activo', true)
                          ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return back()->with('error', 'Usuario o contraseña incorrectos.')->withInput();
        }

        // Guardar en sesión
        Session::put('usuario_id', $usuario->id);
        Session::put('usuario_nombre', $usuario->nombre_completo);
        Session::put('usuario_rol', $usuario->rol);
        Session::put('usuario_inicial', $usuario->inicial);

        // Actualizar último acceso
        $usuario->update(['ultimo_acceso' => now()]);

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('inicio');
    }
}
