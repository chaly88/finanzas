<?php

namespace App\Exports;

use App\Models\Movimiento;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MovimientosExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, WithTitle
{
    public function __construct(
        private string $fechaDesde,
        private string $fechaHasta,
    ) {}

    public function query(): Builder
    {
        return Movimiento::query()
            ->with(['cuenta', 'cuentaDestino', 'categoria'])
            ->whereDate('fecha', '>=', $this->fechaDesde)
            ->whereDate('fecha', '<=', $this->fechaHasta)
            ->orderBy('fecha')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Tipo',
            'Concepto',
            'Categoría',
            'Cuenta de origen',
            'Cuenta de destino',
            'Medio de pago',
            'Valor (COP)',
            'Observación',
        ];
    }

    public function map($movimiento): array
    {
        return [
            $movimiento->fecha->format('d/m/Y'),
            ucfirst($movimiento->tipo),
            $movimiento->concepto,
            $movimiento->categoria?->nombre ?? '',
            $movimiento->cuenta?->nombre ?? '',
            $movimiento->cuentaDestino?->nombre ?? '',
            $movimiento->medio_pago ?? '',
            $movimiento->valor,
            $movimiento->observacion ?? '',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function title(): string
    {
        return 'Movimientos';
    }
}