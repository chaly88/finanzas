<?php

namespace App\Exports;

use App\Models\Movimiento;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;

class ResumenFinancieroExport implements FromCollection, WithTitle
{
    public function __construct(
        private string $fechaDesde,
        private string $fechaHasta,
    ) {}

    public function collection(): Collection
    {
        $movimientos = Movimiento::query()
            ->with('categoria')
            ->whereDate('fecha', '>=', $this->fechaDesde)
            ->whereDate('fecha', '<=', $this->fechaHasta)
            ->get();

        $ingresos = $movimientos
            ->where('tipo', 'ingreso')
            ->sum('valor');

        $gastos = $movimientos
            ->where('tipo', 'gasto')
            ->sum('valor');

        $filas = collect([
            ['RESUMEN FINANCIERO'],
            ['Fecha inicial', $this->fechaDesde],
            ['Fecha final', $this->fechaHasta],
            [''],
            ['Concepto', 'Valor (COP)'],
            ['Total de ingresos', $ingresos],
            ['Total de gastos', $gastos],
            ['Balance del período', $ingresos - $gastos],
            [''],
            ['GASTOS POR CATEGORÍA'],
            ['Categoría', 'Valor (COP)'],
        ]);

        $movimientos
            ->where('tipo', 'gasto')
            ->groupBy(
                fn ($movimiento) => $movimiento->categoria?->nombre ?? 'Sin categoría'
            )
            ->each(function ($grupo, $categoria) use ($filas) {
                $filas->push([
                    $categoria,
                    $grupo->sum('valor'),
                ]);
            });

        return $filas;
    }

    public function title(): string
    {
        return 'Resumen financiero';
    }
}