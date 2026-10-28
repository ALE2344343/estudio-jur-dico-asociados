<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Caso;
use App\Models\Cliente;
use App\Models\HistorialActividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    private function checkAuth()
    {
        if (!Session::has('usuario_id')) return redirect()->route('inicio');
        return null;
    }

    public function index(Request $request)
    {
        if ($r = $this->checkAuth()) return $r;
        $rol    = Session::get('usuario_rol');
        $userId = Session::get('usuario_id');
        $query  = Documento::with(['caso','cliente','autor'])->where('estado','activo');

        if ($rol !== 'administrador') $query->where('subido_por', $userId);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('codigo', 'like', "%{$s}%")
                  ->orWhere('descripcion', 'like', "%{$s}%")
                  ->orWhere('tipo', 'like', "%{$s}%")
                  ->orWhereHas('caso', function($qc) use ($s) {
                      $qc->where('numero_caso', 'like', "%{$s}%")
                         ->orWhere('titulo', 'like', "%{$s}%");
                  })
                  ->orWhereHas('cliente', function($qcl) use ($s) {
                      $qcl->where('nombre', 'like', "%{$s}%")
                          ->orWhere('apellido', 'like', "%{$s}%")
                          ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%{$s}%"]);
                  });
            });
        }
        if ($request->filled('tipo')) $query->where('tipo', $request->tipo);

        $documentos = $query->orderBy('created_at','desc')->paginate(20)->withQueryString();
        $casos      = ($rol === 'administrador') ? Caso::all() : Caso::where('abogado_id',$userId)->get();
        $clientes   = ($rol === 'administrador') ? Cliente::all() : Cliente::where('usuario_id',$userId)->get();

        // Link de Google Drive de la biblioteca
        $driveLink = 'https://drive.google.com/drive/folders/1m6zAmvyumCIE_eChPpWaKxUjk-lFe89J';

        return view('documentos.index', compact('documentos','casos','clientes','driveLink'));
    }

    public function store(Request $request)
    {
        if ($r = $this->checkAuth()) return $r;

        $request->validate([
            'nombre' => 'required|max:200',
            'tipo'   => 'required',
        ]);

        $data = $request->only(['nombre','descripcion','tipo','notas']);
        if ($data['tipo'] === 'Poder') {
            $data['tipo'] = 'Poder Notarial';
        }
        $data['caso_id']    = $request->caso_id ?: null;
        $data['cliente_id'] = $request->cliente_id ?: null;
        $data['codigo']     = Documento::generarCodigo();
        $data['subido_por'] = Session::get('usuario_id');

        if ($request->es_drive) {
            $data['es_drive']   = true;
            $data['drive_link'] = $request->drive_link;
        } elseif ($request->hasFile('archivo')) {
            $file             = $request->file('archivo');
            $path             = $file->store('documentos', 'public');
            $data['archivo']  = $path;
            $data['extension']= strtolower($file->getClientOriginalExtension());
            $data['tamano']   = $file->getSize();
        }

        $doc = Documento::create($data);
        $casoTxt = $doc->caso ? " (Caso: {$doc->caso->numero_caso})" : "";
        HistorialActividad::registrar('Documentos', 'Creación', "Subió el documento '{$doc->nombre}'{$casoTxt}", $doc->id);
        return back()->with('success','Documento registrado exitosamente.');
    }

    public function show(int $id)
    {
        if ($r = $this->checkAuth()) return $r;
        $documento = Documento::with(['caso','cliente','autor'])->findOrFail($id);
        return view('documentos.show', compact('documento'));
    }

    public function edit(int $id)
    {
        if ($r = $this->checkAuth()) return $r;
        $documento = Documento::findOrFail($id);
        $casos     = Caso::where('estado','activo')->get();
        return view('documentos.edit', compact('documento','casos'));
    }

    public function update(Request $request, int $id)
    {
        if ($r = $this->checkAuth()) return $r;

        $doc = Documento::findOrFail($id);

        $request->validate([
            'nombre' => 'required|max:200',
            'tipo'   => 'required',
        ]);

        $data = $request->only(['nombre','descripcion','tipo','notas']);
        $data['caso_id']    = $request->caso_id ?: null;
        $data['cliente_id'] = $request->cliente_id ?: null;

        // Allow replacing the file or link
        if ($request->es_drive) {
            $data['es_drive']   = true;
            $data['drive_link'] = $request->drive_link;
        } elseif ($request->hasFile('archivo')) {
            if ($doc->archivo && !$doc->es_drive) {
                Storage::disk('public')->delete($doc->archivo);
            }
            $file             = $request->file('archivo');
            $path             = $file->store('documentos', 'public');
            $data['archivo']  = $path;
            $data['extension']= strtolower($file->getClientOriginalExtension());
            $data['tamano']   = $file->getSize();
            $data['es_drive'] = false;
            $data['drive_link'] = null;
        }

        HistorialActividad::registrarCambios('Documentos', "documento '{$doc->nombre}' (Cód: {$doc->codigo})", $doc, $data, [
            'nombre'     => 'Nombre del Documento',
            'categoria'  => 'Categoría',
            'caso_id'    => 'Caso Vinculado',
            'es_drive'   => 'Almacenamiento Google Drive',
            'drive_link' => 'Enlace de Google Drive',
            'archivo'    => 'Archivo Digital',
        ]);
        $doc->update($data);
        return redirect()->route('documentos.index')->with('success','Documento actualizado.');
    }

    public function destroy(int $id)
    {
        if ($r = $this->checkAuth()) return $r;
        $doc = Documento::findOrFail($id);
        if ($doc->archivo) Storage::disk('public')->delete($doc->archivo);
        $nomDoc = $doc->nombre;
        $doc->update(['estado' => 'eliminado']);
        HistorialActividad::registrar('Documentos', 'Eliminación', "Eliminó el documento '{$nomDoc}'", $id);
        return redirect()->route('documentos.index')->with('success','Documento eliminado.');
    }
}
