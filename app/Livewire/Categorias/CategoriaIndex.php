<?php

namespace App\Livewire\Categorias;

use App\Models\Categoria;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Categorías')]
class CategoriaIndex extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public string $filtroTipo = 'todos';
    public string $filtroEstado = 'todas';

    public bool $mostrarFormulario = false;
    public ?int $categoriaId = null;

    public string $nombre = '';
    public string $tipo = 'gasto';
    public string $icono = '';
    public string $color = '#6c757d';
    public bool $activa = true;

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function crear(): void
    {
        $this->limpiarFormulario();
        $this->mostrarFormulario = true;
    }

    public function editar(int $id): void
    {
        $categoria = Categoria::findOrFail($id);

        $this->categoriaId = $categoria->id;
        $this->nombre = $categoria->nombre;
        $this->tipo = $categoria->tipo;
        $this->icono = $categoria->icono ?? '';
        $this->color = $categoria->color ?: '#6c757d';
        $this->activa = $categoria->activa;

        $this->resetValidation();
        $this->mostrarFormulario = true;
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categorias', 'nombre')
                    ->where(fn ($query) => $query->where('tipo', $this->tipo))
                    ->ignore($this->categoriaId),
            ],
            'tipo' => ['required', Rule::in(['ingreso', 'gasto'])],
            'icono' => ['nullable', 'string', 'max:50'],
            'color' => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'activa' => ['required', 'boolean'],
        ], [
            'nombre.required' => 'Escribe el nombre de la categoría.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre y tipo.',
            'tipo.in' => 'Selecciona un tipo válido.',
            'color.regex' => 'El color debe ser un código hexadecimal, por ejemplo #198754.',
        ]);

        $datos['nombre'] = trim($datos['nombre']);
        $datos['icono'] = trim($datos['icono'] ?? '') ?: null;

        if ($this->categoriaId) {
            Categoria::findOrFail($this->categoriaId)->update($datos);
            $mensaje = 'La categoría se actualizó correctamente.';
        } else {
            Categoria::create($datos);
            $mensaje = 'La categoría se creó correctamente.';
        }

        $this->cancelar();

        session()->flash('mensaje', $mensaje);
    }

    public function cambiarEstado(int $id): void
    {
        $categoria = Categoria::findOrFail($id);

        $categoria->update([
            'activa' => ! $categoria->activa,
        ]);

        session()->flash(
            'mensaje',
            $categoria->activa
                ? 'La categoría se activó correctamente.'
                : 'La categoría se desactivó correctamente.'
        );
    }

    public function cancelar(): void
    {
        $this->limpiarFormulario();
        $this->mostrarFormulario = false;
        $this->resetValidation();
    }

    private function limpiarFormulario(): void
    {
        $this->reset([
            'categoriaId',
            'nombre',
            'icono',
        ]);

        $this->tipo = 'gasto';
        $this->color = '#6c757d';
        $this->activa = true;

        $this->resetValidation();
    }

    public function render()
    {
        $categorias = Categoria::query()
            ->when(
                trim($this->busqueda) !== '',
                fn ($query) => $query->where(
                    'nombre',
                    'like',
                    '%' . trim($this->busqueda) . '%'
                )
            )
            ->when(
                $this->filtroTipo !== 'todos',
                fn ($query) => $query->where('tipo', $this->filtroTipo)
            )
            ->when(
                $this->filtroEstado !== 'todas',
                fn ($query) => $query->where(
                    'activa',
                    $this->filtroEstado === 'activas'
                )
            )
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->paginate(10);

        return view('livewire.categorias.categoria-index', [
            'categorias' => $categorias,
        ]);
    }
}

