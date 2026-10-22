<?php

namespace App\Http\Controllers;

use App\Models\Ley;
use App\Models\HistorialActividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LeyController extends Controller
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
        $query = Ley::query();
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('numero', 'like', "%{$s}%")
                  ->orWhere('titulo', 'like', "%{$s}%")
                  ->orWhere('descripcion', 'like', "%{$s}%")
                  ->orWhere('articulos', 'like', "%{$s}%")
                  ->orWhere('notas', 'like', "%{$s}%")
                  ->orWhere('categoria', 'like', "%{$s}%");
            });
        }
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }
        $leyes = $query->orderBy('categoria')->orderBy('numero')->paginate(30)->withQueryString();
        return view('leyes.index', compact('leyes'));
    }

    public function create()
    {
        if ($r = $this->checkAuth()) return $r;
        return view('leyes.create');
    }

    public function store(Request $request)
    {
        if ($r = $this->checkAuth()) return $r;
        $request->validate([
            'numero'      => 'required|max:100',
            'titulo'      => 'required|max:300',
            'descripcion' => 'required',
            'categoria'   => 'required',
            'archivo_pdf' => 'nullable|file|mimes:pdf|max:1048576',
        ], [
            'numero.required'      => 'El número o identificador de la ley es obligatorio.',
            'titulo.required'      => 'El título completo de la ley es obligatorio.',
            'descripcion.required' => 'La descripción o contenido principal es obligatoria.',
            'categoria.required'   => 'La categoría es obligatoria.',
            'archivo_pdf.mimes'    => 'El archivo adjunto debe ser de formato PDF.',
            'archivo_pdf.max'      => 'El archivo PDF es demasiado pesado.',
        ]);
        $data = $request->only([
            'numero','titulo','descripcion','categoria',
            'gaceta_oficial','link_oficial','notas','fecha_promulgacion','articulos'
        ]);
        $data['vigente'] = $request->input('vigente') == '1';

        if ($request->hasFile('archivo_pdf')) {
            $data['archivo_pdf'] = $request->file('archivo_pdf')->store('leyes_pdf', 'public');
        }

        $ley = Ley::create($data);
        if (!empty($data['archivo_pdf'])) {
            $ley->archivo_pdf = $data['archivo_pdf'];
            $ley->save();
        }

        HistorialActividad::registrar('Leyes', 'Creación', "Registró la norma {$ley->numero}: '{$ley->titulo}' ({$ley->categoria})", $ley->id);

        return redirect()->route('leyes.index')->with('success', 'Ley registrada correctamente.');
    }

    public function show(int $id)
    {
        if ($r = $this->checkAuth()) return $r;
        $ley = Ley::findOrFail($id);
        return view('leyes.show', compact('ley'));
    }

    public function edit(int $id)
    {
        if ($r = $this->checkAuth()) return $r;
        $ley = Ley::findOrFail($id);
        return view('leyes.edit', compact('ley'));
    }

    public function update(Request $request, int $id)
    {
        if ($r = $this->checkAuth()) return $r;
        $ley = Ley::findOrFail($id);
        $request->validate([
            'numero'      => 'required|max:100',
            'titulo'      => 'required|max:300',
            'descripcion' => 'required',
            'categoria'   => 'required',
            'archivo_pdf' => 'nullable|file|mimes:pdf|max:1048576',
        ], [
            'numero.required'      => 'El número o identificador es obligatorio.',
            'titulo.required'      => 'El título es obligatorio.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'categoria.required'   => 'La categoría es obligatoria.',
            'archivo_pdf.mimes'    => 'El archivo adjunto debe ser de formato PDF.',
        ]);
        $data = $request->only([
            'numero','titulo','descripcion','categoria',
            'gaceta_oficial','link_oficial','notas','fecha_promulgacion','articulos'
        ]);
        $data['vigente'] = $request->input('vigente') == '1';

        if ($request->hasFile('archivo_pdf')) {
            if ($ley->archivo_pdf) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($ley->archivo_pdf);
            }
            $data['archivo_pdf'] = $request->file('archivo_pdf')->store('leyes_pdf', 'public');
        }

        HistorialActividad::registrarCambios('Leyes', "norma {$ley->numero} ({$ley->titulo})", $ley, $data, [
            'numero'             => 'Número de Ley',
            'titulo'             => 'Título',
            'descripcion'        => 'Descripción',
            'categoria'          => 'Categoría',
            'gaceta_oficial'     => 'Gaceta Oficial',
            'link_oficial'       => 'Enlace Oficial',
            'fecha_promulgacion' => 'Fecha de Promulgación',
            'vigente'            => 'Vigencia',
            'archivo_pdf'        => 'Archivo PDF',
        ]);

        $ley->update($data);
        if (isset($data['archivo_pdf'])) {
            $ley->archivo_pdf = $data['archivo_pdf'];
            $ley->save();
        }

        return redirect()->route('leyes.index')->with('success', 'Ley actualizada correctamente.');
    }

    public function destroy(int $id)
    {
        if ($r = $this->checkAdmin()) return $r;
        $ley = Ley::findOrFail($id);
        $info = "{$ley->numero} ({$ley->titulo})";
        if ($ley->archivo_pdf) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($ley->archivo_pdf);
        }
        $ley->delete();
        HistorialActividad::registrar('Leyes', 'Eliminación', "Eliminó la norma {$info}", $id);
        return redirect()->route('leyes.index')->with('success', 'Ley eliminada.');
    }

    public function uploadPdf(Request $request, int $id)
    {
        if ($r = $this->checkAuth()) return $r;
        $ley = Ley::findOrFail($id);
        $request->validate([
            'archivo_pdf' => 'required|file|mimes:pdf|max:1048576',
        ], [
            'archivo_pdf.required' => 'Debe seleccionar un archivo PDF.',
            'archivo_pdf.mimes'    => 'El archivo debe estar en formato PDF.',
            'archivo_pdf.max'      => 'El archivo PDF es demasiado pesado.',
        ]);

        if ($ley->archivo_pdf) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($ley->archivo_pdf);
        }

        $path = $request->file('archivo_pdf')->store('leyes_pdf', 'public');
        $ley->archivo_pdf = $path;
        $ley->save();

        HistorialActividad::registrar('Leyes', 'Modificación de Archivo PDF', "Subió o actualizó el documento PDF oficial de {$ley->numero} ({$ley->titulo})", $ley->id);

        return redirect()->route('leyes.index')->with('success', 'PDF subido correctamente para ' . $ley->numero . '.');
    }
}
