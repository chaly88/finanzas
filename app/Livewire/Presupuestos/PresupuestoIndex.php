<?php

namespace App\Livewire\Presupuestos;

use App\Models\Categoria;
use App\Models\Presupuesto;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Movimiento;

#[Layout('layouts.app')]
#[Title('Presupuestos')]
class PresupuestoIndex extends Component
{
    public ?int $presupuestoId = null;

    public ?int $categoria_id = null;

    public int $mes;

    public int $anio;

    public int $valor = 0;

    public string $observacion = '';

    public function mount(): void
    {
        $this->mes = (int) now()->month;
        $this->anio = (int) now()->year;
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'categoria_id' => [
                'required',
                'integer',
                'exists:categorias,id',
            ],
            'mes' => ['required', 'integer', 'between:1,12'],
            'anio' => ['required', 'integer', 'between:2000,2100'],
            'valor' => ['required', 'integer', 'min:1'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ]);

        $categoria = Categoria::query()
            ->whereKey($datos['categoria_id'])
            ->where('tipo', 'gasto')
            ->where('activa', true)
            ->first();

        if (!$categoria) {
            $this->addError(
                'categoria_id',
                'Selecciona una categoría de gasto activa.'
            );

            return;
        }

        $duplicado = Presupuesto::query()
            ->where('categoria_id', $datos['categoria_id'])
            ->where('mes', $datos['mes'])
            ->where('anio', $datos['anio'])
            ->when(
                $this->presupuestoId,
                fn($query) => $query->where('id', '!=', $this->presupuestoId)
            )
            ->exists();

        if ($duplicado) {
            $this->addError(
                'categoria_id',
                'Ya existe un presupuesto para esa categoría en ese período.'
            );

            return;
        }

        if ($this->presupuestoId) {
            $presupuesto = Presupuesto::findOrFail($this->presupuestoId);
            $presupuesto->update($datos);

            session()->flash('mensaje', 'Presupuesto actualizado correctamente.');
        } else {
            Presupuesto::create($datos);

            session()->flash('mensaje', 'Presupuesto creado correctamente.');
        }

        $this->cancelar();
    }

    public function editar(int $id): void
    {
        $presupuesto = Presupuesto::findOrFail($id);

        $this->presupuestoId = $presupuesto->id;
        $this->categoria_id = $presupuesto->categoria_id;
        $this->mes = $presupuesto->mes;
        $this->anio = $presupuesto->anio;
        $this->valor = $presupuesto->valor;
        $this->observacion = $presupuesto->observacion ?? '';

        $this->resetValidation();
    }

    public function eliminar(int $id): void
    {
        Presupuesto::findOrFail($id)->delete();

        if ($this->presupuestoId === $id) {
            $this->cancelar();
        }

        session()->flash('mensaje', 'Presupuesto eliminado correctamente.');
    }

    public function cancelar(): void
    {
        $this->reset([
            'presupuestoId',
            'categoria_id',
            'valor',
            'observacion',
        ]);

        $this->mes = (int) now()->month;
        $this->anio = (int) now()->year;

        $this->resetValidation();
    }

    public function render()
    {
        $presupuestos = Presupuesto::query()
            ->with('categoria')
            ->where('mes', $this->mes)
            ->where('anio', $this->anio)
            ->orderBy('categoria_id')
            ->get();

        $categorias = Categoria::query()
            ->where('tipo', 'gasto')
            ->where('activa', true)
            ->orderBy('nombre')
            ->get();

        $totalesPorCategoria = \App\Models\Movimiento::query()
            ->selectRaw('categoria_id, SUM(valor) as total_gastado')
            ->where('tipo', 'gasto')
            ->whereMonth('fecha', $this->mes)
            ->whereYear('fecha', $this->anio)
            ->groupBy('categoria_id')
            ->pluck('total_gastado', 'categoria_id');
            
        $totalGastadoGeneral = Movimiento::query()
            ->where('tipo', 'gasto')
            ->whereMonth('fecha', $this->mes)
            ->whereYear('fecha', $this->anio)
            ->sum('valor');

        return view('livewire.presupuestos.presupuesto-index', [
            'presupuestos' => $presupuestos,
            'categorias' => $categorias,
            'totalesPorCategoria' => $totalesPorCategoria,
            'totalGastadoGeneral' => $totalGastadoGeneral,
        ]);
    }
}

