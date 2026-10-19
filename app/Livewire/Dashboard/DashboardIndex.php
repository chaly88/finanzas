<?php

namespace App\Livewire\Dashboard;

use App\Models\Categoria;
use App\Models\Cuenta;
use App\Models\Movimiento;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\MetaAhorro;
use App\Models\MovimientoAhorro;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class DashboardIndex extends Component
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
        $this->actualizarGrafico();
    }
    public function diagnostico(): array
    {
        return [
            'periodo' => $this->periodo,
            'fechaDesde' => $this->fechaDesde,
            'fechaHasta' => $this->fechaHasta,
            'totalMovimientos' => Movimiento::count(),
            'ingresos' => Movimiento::where('tipo', 'ingreso')->sum('valor'),
            'gastos' => Movimiento::where('tipo', 'gasto')->sum('valor'),
            'transferencias' => Movimiento::where('tipo', 'transferencia')->sum('valor'),
        ];
    }
    public function render()
    {
        $inicio = $this->fechaDesde ?: now()->startOfMonth()->toDateString();
        $fin = $this->fechaHasta ?: now()->toDateString();

        // Evita consultar un rango de fechas invertido.
        if ($inicio > $fin) {
            $inicio = $fin;
        }

        $ingresos = Movimiento::query()
            ->where('tipo', 'ingreso')
            ->whereDate('fecha', '>=', $inicio)
            ->whereDate('fecha', '<=', $fin)
            ->sum('valor');

        $gastos = Movimiento::query()
            ->where('tipo', 'gasto')
            ->whereDate('fecha', '>=', $inicio)
            ->whereDate('fecha', '<=', $fin)
            ->sum('valor');

        $cuentas = Cuenta::query()
            ->orderBy('nombre')
            ->get();

        $dineroDisponible = $cuentas->sum(
            fn(Cuenta $cuenta) => $cuenta->saldo_actual
        );
        // Metas que continúan activas.
        $metasActivas = MetaAhorro::where('estado', 'activa')->count();

        $objetivoMetasActivas = (float) MetaAhorro::where('estado', 'activa')
            ->sum('monto_objetivo');

        // Saldo registrado en las metas activas.
// No representa necesariamente dinero respaldado en una cuenta.
        $saldoMetasActivas = (float) MovimientoAhorro::query()
            ->join(
                'metas_ahorro',
                'metas_ahorro.id',
                '=',
                'movimientos_ahorro.meta_ahorro_id'
            )
            ->where('metas_ahorro.estado', 'activa')
            ->selectRaw("
        COALESCE(
            SUM(
                CASE
                    WHEN movimientos_ahorro.tipo = 'aporte'
                        THEN movimientos_ahorro.monto
                    WHEN movimientos_ahorro.tipo = 'retiro'
                        THEN -movimientos_ahorro.monto
                    ELSE 0
                END
            ),
            0
        ) AS saldo
    ")
            ->value('saldo');

        // Movimientos históricos que todavía no tienen transferencia asociada.
        $movimientosAhorroSinVinculo = MovimientoAhorro::whereNull('movimiento_id')
            ->count();
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

        $ultimosMovimientos = Movimiento::query()
            ->with(['cuenta', 'cuentaDestino', 'categoria'])
            ->whereDate('fecha', '>=', $inicio)
            ->whereDate('fecha', '<=', $fin)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(10)
            ->get();
        $datosGrafico = [
            'ingresos' => (int) $ingresos,
            'gastos' => (int) $gastos,
        ];
        return view('livewire.dashboard.dashboard-index', [
            'dineroDisponible' => $dineroDisponible,
            'ingresos' => $ingresos,
            'gastos' => $gastos,
            'balanceMes' => $ingresos - $gastos,
            'cuentas' => $cuentas,
            'gastosPorCategoria' => $gastosPorCategoria,
            'ultimosMovimientos' => $ultimosMovimientos,
            'datosGrafico' => $datosGrafico,
            'metasActivas' => $metasActivas,
            'objetivoMetasActivas' => $objetivoMetasActivas,
            'saldoMetasActivas' => $saldoMetasActivas,
            'movimientosAhorroSinVinculo' => $movimientosAhorroSinVinculo,
        ]);
    }



    public function updatedFechaDesde(): void
    {
        $this->actualizarGrafico();
    }

    public function updatedFechaHasta(): void
    {
        $this->actualizarGrafico();
    }

    private function actualizarGrafico(): void
    {
        $inicio = $this->fechaDesde ?: now()->startOfMonth()->toDateString();
        $fin = $this->fechaHasta ?: now()->toDateString();

        if ($inicio > $fin) {
            return;
        }

        $ingresos = Movimiento::query()
            ->where('tipo', 'ingreso')
            ->whereDate('fecha', '>=', $inicio)
            ->whereDate('fecha', '<=', $fin)
            ->sum('valor');

        $gastos = Movimiento::query()
            ->where('tipo', 'gasto')
            ->whereDate('fecha', '>=', $inicio)
            ->whereDate('fecha', '<=', $fin)
            ->sum('valor');

        $this->dispatch(
            'dashboard-grafico-actualizado',
            ingresos: (int) $ingresos,
            gastos: (int) $gastos,
        );
    }



}
