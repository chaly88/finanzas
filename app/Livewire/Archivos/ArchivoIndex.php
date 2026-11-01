<?php

namespace App\Livewire\Archivos;

use App\Models\Archivo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Archivos')]
class ArchivoIndex extends Component
{
    use WithFileUploads, WithPagination;

    public ?int $carpetaActualId = null;
    public string $busqueda = '';
    public string $tipo = 'todos';
    public string $orden = 'nombre';
    public string $vista = 'cuadricula';
    public array $uploads = [];
    public array $seleccionados = [];
    public string $dialogo = '';
    public string $nombreCarpeta = '';
    public string $nuevoNombre = '';
    public ?int $archivoId = null;
    public ?int $destinoId = null;

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingTipo(): void
    {
        $this->resetPage();
    }

    public function abrirCarpeta(?int $id): void
    {
        if ($id !== null) {
            $this->carpeta($id);
        }

        $this->carpetaActualId = $id;
        $this->busqueda = '';
        $this->seleccionados = [];
        $this->resetPage();
    }

    public function crearCarpeta(): void
    {
        $this->validate(['nombreCarpeta' => ['required', 'string', 'max:120']]);
        $nombre = $this->limpiarNombre($this->nombreCarpeta);
        if (! $this->validarNombre($nombre, 'nombreCarpeta')) {
            return;
        }

        Archivo::create([
            'user_id' => auth()->id(),
            'parent_id' => $this->carpetaActualId,
            'name' => $nombre,
            'is_folder' => true,
        ]);

        $this->cerrarDialogo();
        session()->flash('mensaje', 'La carpeta se creó correctamente.');
    }

    public function subirArchivos(): void
    {
        $this->validate([
            'uploads' => ['required', 'array', 'min:1', 'max:20'],
            'uploads.*' => ['file', 'max:51200', 'mimes:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,gif,webp,zip,ppt,pptx', 'extensions:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,gif,webp,zip,ppt,pptx'],
        ], [
            'uploads.required' => 'Selecciona al menos un archivo.',
            'uploads.*.max' => 'Cada archivo puede pesar hasta 50 MB.',
            'uploads.*.mimes' => 'Este tipo de archivo no está permitido.',
            'uploads.max' => 'Puedes subir hasta 20 archivos a la vez.',
        ]);

        $parentId = $this->carpetaActualId ? $this->carpeta($this->carpetaActualId)->id : null;
        $disk = Storage::disk('local');

        foreach ($this->uploads as $upload) {
            $nombre = mb_substr($this->limpiarNombre($upload->getClientOriginalName()), 0, 180);
            $extension = strtolower($upload->extension() ?: pathinfo($nombre, PATHINFO_EXTENSION));
            $path = $upload->storeAs('archivos/'.auth()->id(), Str::uuid().($extension ? '.'.$extension : ''), 'local');

            Archivo::create([
                'user_id' => auth()->id(),
                'parent_id' => $parentId,
                'name' => $nombre,
                'path' => $path,
                'mime_type' => $upload->getMimeType() ?: 'application/octet-stream',
                'extension' => $extension,
                'size' => $disk->size($path),
                'is_folder' => false,
            ]);
        }

        $total = count($this->uploads);
        $this->reset('uploads');
        $this->resetValidation();
        session()->flash('mensaje', $total === 1 ? 'El archivo se subió correctamente.' : "Se subieron {$total} archivos correctamente.");
    }

    public function descargar(int $id)
    {
        $archivo = $this->archivo($id);
        abort_if($archivo->is_folder || ! $archivo->path, 404);

        return Storage::disk('local')->download($archivo->path, $archivo->name);
    }

    public function prepararRenombrar(int $id): void
    {
        $archivo = $this->archivo($id);
        $this->archivoId = $archivo->id;
        $this->nuevoNombre = $archivo->name;
        $this->dialogo = 'renombrar';
        $this->resetValidation();
    }

    public function renombrar(): void
    {
        $this->validate(['nuevoNombre' => ['required', 'string', 'max:180']]);
        $archivo = $this->archivo($this->archivoId);
        $nombre = $this->limpiarNombre($this->nuevoNombre);
        if (! $this->validarNombre($nombre, 'nuevoNombre')) {
            return;
        }

        $archivo->update(['name' => $nombre]);
        $this->cerrarDialogo();
        session()->flash('mensaje', 'El elemento se renombró correctamente.');
    }

    public function prepararMover(int $id): void
    {
        $archivo = $this->archivo($id);
        $this->archivoId = $archivo->id;
        $this->destinoId = $archivo->parent_id;
        $this->dialogo = 'mover';
        $this->resetValidation();
    }

    public function mover(): void
    {
        $archivo = $this->archivo($this->archivoId);

        if ($this->destinoId !== null) {
            $destino = $this->carpeta($this->destinoId);
            abort_if($destino->id === $archivo->id || $this->esDescendiente($destino, $archivo->id), 422);
        }

        $archivo->update(['parent_id' => $this->destinoId]);
        if ($this->carpetaActualId === $archivo->id) {
            $this->carpetaActualId = $this->destinoId;
        }
        $this->cerrarDialogo();
        session()->flash('mensaje', 'El elemento se movió correctamente.');
    }

    public function confirmarEliminar(int $id): void
    {
        $this->archivo($id);
        $this->archivoId = $id;
        $this->dialogo = 'eliminar';
    }

    public function eliminar(): void
    {
        $archivo = $this->archivo($this->archivoId);
        $ids = $this->idsIncluyendoDescendientes($archivo->id);

        foreach (Archivo::query()->where('user_id', auth()->id())->whereIn('id', $ids)->where('is_folder', false)->pluck('path') as $path) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
        }

        Archivo::query()->where('user_id', auth()->id())->whereIn('id', $ids)->delete();
        if (in_array($this->carpetaActualId, $ids, true)) {
            $this->carpetaActualId = $archivo->parent_id;
        }
        $this->seleccionados = array_values(array_diff($this->seleccionados, $ids));
        $this->cerrarDialogo();
        session()->flash('mensaje', 'El elemento y su contenido se eliminaron correctamente.');
    }

    public function eliminarSeleccionados(): void
    {
        $ids = collect($this->seleccionados)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => Archivo::query()->where('user_id', auth()->id())->whereKey($id)->exists())
            ->flatMap(fn ($id) => $this->idsIncluyendoDescendientes($id))
            ->unique()
            ->values();

        foreach (Archivo::query()->where('user_id', auth()->id())->whereIn('id', $ids)->where('is_folder', false)->pluck('path') as $path) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
        }

        Archivo::query()->where('user_id', auth()->id())->whereIn('id', $ids)->delete();
        $this->seleccionados = [];
        session()->flash('mensaje', 'Se eliminaron los elementos seleccionados.');
    }

    public function cerrarDialogo(): void
    {
        $this->dialogo = '';
        $this->archivoId = null;
        $this->destinoId = null;
        $this->nombreCarpeta = '';
        $this->nuevoNombre = '';
        $this->resetValidation();
    }

    public function render()
    {
        $usuarioId = auth()->id();
        $carpetasBloqueadas = $this->archivoId
            ? $this->idsIncluyendoDescendientes($this->archivoId)
            : [];
        $consulta = Archivo::query()
            ->where('user_id', $usuarioId)
            ->where('parent_id', $this->carpetaActualId);

        if (trim($this->busqueda) !== '') {
            $consulta->where('name', 'like', '%'.trim($this->busqueda).'%');
        }

        if ($this->tipo === 'carpetas') {
            $consulta->where('is_folder', true);
        } elseif ($this->tipo === 'archivos') {
            $consulta->where('is_folder', false);
        } elseif (in_array($this->tipo, ['imagenes', 'documentos', 'pdf', 'hojas'], true)) {
            $consulta->where('is_folder', false)->whereIn('extension', match ($this->tipo) {
                'imagenes' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'documentos' => ['doc', 'docx', 'txt', 'ppt', 'pptx'],
                'pdf' => ['pdf'],
                'hojas' => ['xls', 'xlsx', 'csv'],
            });
        }

        $consulta->withCount('children')->orderByDesc('is_folder');
        match ($this->orden) {
            'recientes' => $consulta->orderByDesc('created_at'),
            'grandes' => $consulta->orderByDesc('size'),
            'tipo' => $consulta->orderBy('extension')->orderBy('name'),
            default => $consulta->orderBy('name'),
        };

        $baseUsuario = Archivo::query()->where('user_id', $usuarioId);
        $carpeta = $this->carpetaActualId ? $this->carpeta($this->carpetaActualId) : null;

        return view('livewire.archivos.archivo-index', [
            'elementos' => $consulta->paginate(30),
            'carpeta' => $carpeta,
            'migas' => $this->migas($carpeta),
            'carpetasDestino' => (clone $baseUsuario)->where('is_folder', true)->orderBy('name')->get()
                ->reject(fn (Archivo $folder) => in_array($folder->id, $carpetasBloqueadas, true)),
            'totalArchivos' => (clone $baseUsuario)->where('is_folder', false)->count(),
            'totalCarpetas' => (clone $baseUsuario)->where('is_folder', true)->count(),
            'espacioUsado' => (int) (clone $baseUsuario)->where('is_folder', false)->sum('size'),
        ]);
    }

    private function archivo(?int $id): Archivo
    {
        abort_unless($id, 404);

        return Archivo::query()->where('user_id', auth()->id())->findOrFail($id);
    }

    private function carpeta(int $id): Archivo
    {
        $folder = $this->archivo($id);
        abort_unless($folder->is_folder, 404);

        return $folder;
    }

    private function limpiarNombre(string $nombre): string
    {
        return trim(str_replace("\0", '', basename(str_replace('\\', '/', $nombre))));
    }

    private function validarNombre(string $nombre, string $campo): bool
    {
        if ($nombre === '' || in_array($nombre, ['.', '..'], true)) {
            $this->addError($campo, 'Escribe un nombre válido.');
            return false;
        }

        return true;
    }

    private function idsIncluyendoDescendientes(int $id): array
    {
        $ids = [$id];
        for ($i = 0; $i < count($ids); $i++) {
            $hijos = Archivo::query()->where('user_id', auth()->id())->where('parent_id', $ids[$i])->pluck('id')->all();
            $ids = array_merge($ids, array_diff($hijos, $ids));
        }

        return array_values(array_unique($ids));
    }

    private function esDescendiente(Archivo $folder, int $ancestorId): bool
    {
        $parentId = $folder->parent_id;
        while ($parentId !== null) {
            if ($parentId === $ancestorId) {
                return true;
            }

            $parentId = Archivo::query()->where('user_id', auth()->id())->whereKey($parentId)->value('parent_id');
        }

        return false;
    }

    private function migas(?Archivo $carpeta): array
    {
        $migas = [];
        while ($carpeta) {
            array_unshift($migas, ['id' => $carpeta->id, 'name' => $carpeta->name]);
            $carpeta = $carpeta->parent_id ? Archivo::query()->where('user_id', auth()->id())->find($carpeta->parent_id) : null;
        }

        return $migas;
    }
}
