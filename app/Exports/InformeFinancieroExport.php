<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InformeFinancieroExport implements Export, WithMultipleSheets
{
    public function __construct(
        private string $fechaDesde,
        private string $fechaHasta,
    ) {}

    public function sheets(): array
    {
        return [
            new ResumenFinancieroExport(
                $this->fechaDesde,
                $this->fechaHasta
            ),
            new MovimientosExport(
                $this->fechaDesde,
                $this->fechaHasta
            ),
        ];
    }
}