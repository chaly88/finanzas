<?php

namespace App\Livewire\Enlaces;

use App\Models\Enlace;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Enlaces')]
class EnlaceIndex extends Component
{
    use WithPagination;

    public ?int $carpetaActualId = null;
    public string $busqueda = '';
    public string $filtro = 'todos';
    public string $orden = 'recientes';
    public string $vista = 'cuadricula';
    public string $dialogo = '';
    public ?int $enlaceId = null;
    public string $titulo = '';
    public string $url = '';
    public string $descripcion = '';
    public string $etiquetasTexto = '';
    public ?int $destinoId = null;
    public bool $favorito = false;

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingFiltro(): void
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
        $this->filtro = 'todos';
        $this->resetPage();
    }

    public function nuevoEnlace(): void
    {
        $this->limpiarFormulario();
        $this->destinoId = $this->carpetaActualId;
        $this->dialogo = 'enlace';
    }

    public function editar(int $id): void
    {
        $enlace = $this->registro($id);
        abort_if($enlace->is_folder, 404);

        $this->enlaceId = $enlace->id;
        $this->titulo = $enlace->title;
        $this->url = $enlace->url;
        $this->descripcion = $enlace->description ?? '';
        $this->etiquetasTexto = implode(', ', $enlace->tags ?? []);
        $this->destinoId = $enlace->folder_id;
        $this->favorito = $enlace->is_favorite;
        $this->dialogo = 'enlace';
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $editando = $this->enlaceId !== null;
        $this->url = $this->normalizarUrl($this->url);
        if (trim($this->titulo) === '' && filter_var($this->url, FILTER_VALIDATE_URL)) {
            $host = parse_url($this->url, PHP_URL_HOST);
            $this->titulo = preg_replace('/^www\./i', '', (string) $host) ?: '';
        }

        $datos = $this->validate([
            'titulo' => ['required', 'string', 'max:180'],
            'url' => ['required', 'string', 'max:2048', 'url:http,https'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'etiquetasTexto' => ['nullable', 'string', 'max:500'],
            'destinoId' => ['nullable', 'integer'],
            'favorito' => ['boolean'],
        ], [
            'titulo.required' => 'Escribe un nombre para el enlace.',
            'url.required' => 'Pega la dirección del sitio web.',
            'url.url' => 'Usa una dirección válida que empiece con http:// o https://.',
            'destinoId.integer' => 'Selecciona una carpeta válida.',
        ]);

        if ($this->destinoId !== null) {
            $this->carpeta($this->destinoId);
        }
        if ($this->carpetaActualId !== null) {
            $this->carpeta($this->carpetaActualId);
        }

        $tags = collect(explode(',', $this->etiquetasTexto))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->unique(fn (string $tag) => mb_strtolower($tag))
            ->take(15)
            ->map(fn (string $tag) => mb_substr($tag, 0, 30))
            ->values()
            ->all();

        $registro = $this->enlaceId ? $this->registro($this->enlaceId) : new Enlace;
        abort_if($registro->exists && $registro->is_folder, 404);
        $registro->fill([
            'user_id' => auth()->id(),
            'folder_id' => $this->destinoId,
            'title' => trim($datos['titulo']),
            'url' => trim($datos['url']),
            'description' => trim($datos['descripcion'] ?? '') ?: null,
            'tags' => $tags,
            'is_folder' => false,
            'is_favorite' => $datos['favorito'],
        ])->save();

        $this->cerrarDialogo();
        session()->flash('mensaje', $editando ? 'El enlace se actualizó correctamente.' : 'El enlace se guardó correctamente.');
    }

    public function nuevaCarpeta(): void
    {
        $this->limpiarFormulario();
        $this->dialogo = 'carpeta';
    }

    public function crearCarpeta(): void
    {
        if ($this->carpetaActualId !== null) {
            $this->carpeta($this->carpetaActualId);
        }

        $datos = $this->validate([
            'titulo' => ['required', 'string', 'max:120'],
        ], ['titulo.required' => 'Escribe el nombre de la carpeta.']);

        Enlace::create([
            'user_id' => auth()->id(),
            'folder_id' => $this->carpetaActualId,
            'title' => trim($datos['titulo']),
            'is_folder' => true,
        ]);

        $this->cerrarDialogo();
        session()->flash('mensaje', 'La carpeta se creó correctamente.');
    }

    public function prepararRenombrarCarpeta(int $id): void
    {
        $folder = $this->carpeta($id);
        $this->enlaceId = $folder->id;
        $this->titulo = $folder->title;
        $this->dialogo = 'renombrar-carpeta';
        $this->resetValidation();
    }

    public function renombrarCarpeta(): void
    {
        $datos = $this->validate(['titulo' => ['required', 'string', 'max:120']]);
        $this->carpeta($this->enlaceId)->update(['title' => trim($datos['titulo'])]);
        $this->cerrarDialogo();
        session()->flash('mensaje', 'La carpeta se renombró correctamente.');
    }

    public function eliminarCarpeta(int $id): void
    {
        $folder = $this->carpeta($id);
        if ($folder->children()->exists()) {
            session()->flash('error', 'La carpeta debe estar vacía para poder eliminarla. Mueve o elimina primero su contenido.');
            return;
        }

        $folder->delete();
        session()->flash('mensaje', 'La carpeta se eliminó correctamente.');
    }

    public function alternarFavorito(int $id): void
    {
        $enlace = $this->registro($id);
        abort_if($enlace->is_folder, 404);
        $enlace->update(['is_favorite' => ! $enlace->is_favorite]);
    }

    public function confirmarEliminar(int $id): void
    {
        $enlace = $this->registro($id);
        abort_if($enlace->is_folder, 404);
        $this->enlaceId = $id;
        $this->dialogo = 'eliminar';
    }

    public function eliminar(): void
    {
        $enlace = $this->registro($this->enlaceId);
        abort_if($enlace->is_folder, 404);
        $enlace->delete();
        $this->cerrarDialogo();
        session()->flash('mensaje', 'El enlace se eliminó correctamente.');
    }

    public function cerrarDialogo(): void
    {
        $this->dialogo = '';
        $this->limpiarFormulario();
        $this->resetValidation();
    }

    public function render()
    {
        $usuarioId = auth()->id();
        $consulta = Enlace::query()
            ->where('user_id', $usuarioId)
            ->where('folder_id', $this->carpetaActualId);

        if (trim($this->busqueda) !== '') {
            $termino = '%'.trim($this->busqueda).'%';
            $consulta->where(function ($query) use ($termino) {
                $query->where('title', 'like', $termino)
                    ->orWhere('url', 'like', $termino)
                    ->orWhere('description', 'like', $termino)
                    ->orWhere('tags', 'like', $termino);
            });
        }

        if ($this->filtro === 'favoritos') {
            $consulta->where('is_folder', false)->where('is_favorite', true);
        } elseif ($this->filtro === 'enlaces') {
            $consulta->where('is_folder', false);
        } elseif ($this->filtro === 'carpetas') {
            $consulta->where('is_folder', true);
        }

        $consulta->withCount('children')->orderByDesc('is_folder');
        match ($this->orden) {
            'alfabetico' => $consulta->orderBy('title'),
            'visitas' => $consulta->orderByDesc('visits')->orderBy('title'),
            'dominio' => $consulta->orderBy('url'),
            default => $consulta->orderByDesc('updated_at'),
        };

        $baseUsuario = Enlace::query()->where('user_id', $usuarioId);
        $carpeta = $this->carpetaActualId ? $this->carpeta($this->carpetaActualId) : null;

        return view('livewire.enlaces.enlace-index', [
            'elementos' => $consulta->paginate(24),
            'carpeta' => $carpeta,
            'migas' => $this->migas($carpeta),
            'carpetas' => (clone $baseUsuario)->where('is_folder', true)->orderBy('title')->get(),
            'totalEnlaces' => (clone $baseUsuario)->where('is_folder', false)->count(),
            'totalFavoritos' => (clone $baseUsuario)->where('is_folder', false)->where('is_favorite', true)->count(),
            'totalCarpetas' => (clone $baseUsuario)->where('is_folder', true)->count(),
            'totalVisitas' => (int) (clone $baseUsuario)->where('is_folder', false)->sum('visits'),
        ]);
    }

    private function normalizarUrl(string $url): string
    {
        $url = trim($url);

        return $url !== '' && ! preg_match('/^https?:\/\//i', $url) ? 'https://'.$url : $url;
    }

    private function limpiarFormulario(): void
    {
        $this->reset(['enlaceId', 'titulo', 'url', 'descripcion', 'etiquetasTexto', 'destinoId', 'favorito']);
        $this->favorito = false;
    }

    private function registro(?int $id): Enlace
    {
        abort_unless($id, 404);

        return Enlace::query()->where('user_id', auth()->id())->findOrFail($id);
    }

    private function carpeta(int $id): Enlace
    {
        $folder = $this->registro($id);
        abort_unless($folder->is_folder, 404);

        return $folder;
    }

    private function migas(?Enlace $carpeta): array
    {
        $migas = [];
        while ($carpeta) {
            array_unshift($migas, ['id' => $carpeta->id, 'title' => $carpeta->title]);
            $carpeta = $carpeta->folder_id
                ? Enlace::query()->where('user_id', auth()->id())->find($carpeta->folder_id)
                : null;
        }

        return $migas;
    }
}
