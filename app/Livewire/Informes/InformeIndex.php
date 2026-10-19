<?php

namespace App\Livewire\Informes;

use App\Models\Categoria;
use App\Models\Movimiento;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InformeFinancieroExport;

#[Layout('layouts.app')]
#[Title('Informes financieros')]
class InformeIndex extends Component
{
    public string $periodo = 'mes_actual';

    public string $fechaDesde = '';

    public string $fechaHasta = '';

    public function mount(): void
    {
        $this->fechaDesde = now()->startOfMonth()->format('Y-m-d');
        $this->fechaHasta = now()->format('Y-m-d');
    }

    public function updatedPeriodo(): void
    {
        if ($this->periodo === 'mes_actual') {
            $this->fechaDesde = now()->startOfMonth()->format('Y-m-d');
            $this->fechaHasta = now()->format('Y-m-d');
        } elseif ($this->periodo === 'mes_anterior') {
            $mesAnterior = now()->subMonthNoOverflow();

            $this->fechaDesde = $mesAnterior->copy()
                ->startOfMonth()->format('Y-m-d');

            $this->fechaHasta = $mesAnterior->copy()
                ->endOfMonth()->format('Y-m-d');
        } elseif ($this->periodo === 'este_anio') {
            $this->fechaDesde = now()->startOfYear()->format('Y-m-d');
            $this->fechaHasta = now()->format('Y-m-d');
        }
    }

public function exportar()
{
    $this->validate([
        'fechaDesde' => 'required|date',
        'fechaHasta' => 'required|date|after_or_equal:fechaDesde',
    ]);

    $nombreArchivo = 'informe-financiero-'
        . $this->fechaDesde
        . '-'
        . $this->fechaHasta
        . '.xlsx';

    return Excel::download(
        new InformeFinancieroExport(
            $this->fechaDesde,
            $this->fechaHasta
        ),
        $nombreArchivo
    );
}

    public function render()
    {
        $inicio = $this->fechaDesde ?: now()->startOfMonth()->toDateString();
        $fin = $this->fechaHasta ?: now()->toDateString();

        if ($inicio > $fin) {
            $inicio = $fin;
        }

        $movimientos = Movimiento::query()
            ->with(['cuenta', 'cuentaDestino', 'categoria'])
            ->whereDate('fecha', '>=', $inicio)
            ->whereDate('fecha', '<=', $fin)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        $ingresos = (int) $movimientos
            ->where('tipo', 'ingreso')
            ->sum('valor');

        $gastos = (int) $movimientos
            ->where('tipo', 'gasto')
            ->sum('valor');

        $gastosPorCategoria = Categoria::query()
            ->where('tipo', 'gasto')
            ->where('activa', true)
            ->withSum([
                'movimientos as total_gastado' => function ($query) use ($inicio, $fin) {
                    $query->where('tipo', 'gasto')
                        ->whereDate('fecha', '>=', $inicio)
                        ->whereDate('fecha', '<=', $fin);
                },
            ], 'valor')
            ->orderByDesc('total_gastado')
            ->get();

        return view('livewire.informes.informe-index', [
            'movimientos' => $movimientos,
            'ingresos' => $ingresos,
            'gastos' => $gastos,
            'balance' => $ingresos - $gastos,
            'gastosPorCategoria' => $gastosPorCategoria,
        ]);
    }
}
