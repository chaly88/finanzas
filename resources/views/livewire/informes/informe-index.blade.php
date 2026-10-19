<div>
    @section('page_title', 'Informes financieros')
    @section('page_description', 'Analiza tus ingresos, gastos y balance.')

    <div class="container-fluid">

        {{-- Filtros --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">

                    <div class="col-12 col-md-4">
                        <label for="periodo" class="form-label">Período</label>
                        <select id="periodo" class="form-select" wire:model.live="periodo">
                            <option value="mes_actual">Este mes</option>
                            <option value="mes_anterior">Mes anterior</option>
                            <option value="este_anio">Este año</option>
                            <option value="personalizado">Personalizado</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="fechaDesde" class="form-label">Fecha inicial</label>
                        <input id="fechaDesde" type="date" class="form-control" wire:model.live="fechaDesde"
                            @disabled($periodo !== 'personalizado')>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="fechaHasta" class="form-label">Fecha final</label>
                        <input id="fechaHasta" type="date" class="form-control" wire:model.live="fechaHasta"
                            @disabled($periodo !== 'personalizado')>
                    </div>

                </div>
                <div class="d-flex justify-content-end mt-3 mb-0">
                    <button type="button" class="btn btn-success" wire:click="exportar" wire:loading.attr="disabled"
                        wire:target="exportar">
                        <span wire:loading.remove wire:target="exportar">
                            <i class="bi bi-file-earmark-excel me-1"></i>
                            Exportar a Excel
                        </span>

                        <span wire:loading wire:target="exportar">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                            Generando archivo...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Resumen --}}
        <div class="row g-3 mb-4">

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-body-secondary mb-2">Total de ingresos</div>
                        <h3 class="fw-bold text-success mb-0">
                            ${{ number_format($ingresos, 0, ',', '.') }}
                        </h3>
                        <div class="small text-body-secondary mt-2">
                            Dinero recibido en el período
                        </div>
                    </div>
                    <div class="border-top border-success border-3"></div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-body-secondary mb-2">Total de gastos</div>
                        <h3 class="fw-bold text-danger mb-0">
                            ${{ number_format($gastos, 0, ',', '.') }}
                        </h3>
                        <div class="small text-body-secondary mt-2">
                            Dinero gastado en el período
                        </div>
                    </div>
                    <div class="border-top border-danger border-3"></div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-body-secondary mb-2">Balance del período</div>
                        <h3 class="fw-bold mb-0 {{ $balance >= 0 ? 'text-primary' : 'text-danger' }}">
                            ${{ number_format($balance, 0, ',', '.') }}
                        </h3>
                        <div class="small text-body-secondary mt-2">
                            Ingresos menos gastos
                        </div>
                    </div>
                    <div class="border-top border-primary border-3"></div>
                </div>
            </div>

        </div>

        <div class="row g-3 mb-4">

            {{-- Gastos por categoría --}}
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <h3 class="card-title">
                            <i class="bi bi-pie-chart me-2"></i>
                            Gastos por categoría
                        </h3>
                    </div>

                    <div class="card-body">
                        @php
                            $categoriasConGastos = $gastosPorCategoria
                                ->filter(fn($categoria) => ($categoria->total_gastado ?? 0) > 0);
                        @endphp

                        @forelse ($categoriasConGastos as $categoria)
                            @php
                                $totalCategoria = (int) $categoria->total_gastado;
                                $porcentaje = $gastos > 0
                                    ? round(($totalCategoria / $gastos) * 100)
                                    : 0;
                            @endphp

                            <div class="mb-3">
                                <div class="d-flex justify-content-between gap-2 mb-1">
                                    <span>{{ $categoria->nombre }}</span>
                                    <strong class="text-nowrap">
                                        ${{ number_format($totalCategoria, 0, ',', '.') }}
                                    </strong>
                                </div>

                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ min(100, $porcentaje) }}%; background-color: {{ $categoria->color ?: '#0d6efd' }};"
                                        aria-valuenow="{{ min(100, $porcentaje) }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>

                                <div class="text-body-secondary small text-end mt-1">
                                    {{ $porcentaje }} %
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-body-secondary py-4">
                                <i class="bi bi-pie-chart fs-1 d-block mb-2"></i>
                                No hay gastos categorizados en este período.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Resumen por tipo --}}
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <h3 class="card-title">
                            <i class="bi bi-info-circle me-2"></i>
                            Resumen del período
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between py-3 border-bottom">
                            <span>Ingresos registrados</span>
                            <strong class="text-success">
                                ${{ number_format($ingresos, 0, ',', '.') }}
                            </strong>
                        </div>

                        <div class="d-flex justify-content-between py-3 border-bottom">
                            <span>Gastos registrados</span>
                            <strong class="text-danger">
                                ${{ number_format($gastos, 0, ',', '.') }}
                            </strong>
                        </div>

                        <div class="d-flex justify-content-between py-3">
                            <span>Resultado neto</span>
                            <strong class="{{ $balance >= 0 ? 'text-primary' : 'text-danger' }}">
                                ${{ number_format($balance, 0, ',', '.') }}
                            </strong>
                        </div>

                        <div class="alert alert-light border mb-0">
                            <i class="bi bi-lightbulb me-2"></i>
                            Las transferencias entre tus cuentas no se consideran ingresos ni gastos.
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Detalle de movimientos --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h3 class="card-title">
                    <i class="bi bi-list-ul me-2"></i>
                    Detalle de movimientos
                </h3>
                <span class="badge text-bg-secondary">
                    {{ $movimientos->count() }} registros
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Concepto</th>
                                <th>Cuenta</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movimientos as $movimiento)
                                <tr wire:key="informe-movimiento-{{ $movimiento->id }}">
                                    <td class="text-nowrap">
                                        {{ $movimiento->fecha->format('d/m/Y') }}
                                    </td>

                                    <td>
                                        @if ($movimiento->tipo === 'ingreso')
                                            <span class="badge text-bg-success">Ingreso</span>
                                        @elseif ($movimiento->tipo === 'gasto')
                                            <span class="badge text-bg-danger">Gasto</span>
                                        @else
                                            <span class="badge text-bg-primary">Transferencia</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $movimiento->concepto }}
                                        @if ($movimiento->categoria)
                                            <div class="small text-body-secondary">
                                                {{ $movimiento->categoria->nombre }}
                                            </div>
                                        @endif

                                        @if ($movimiento->tipo === 'transferencia' && $movimiento->cuentaDestino)
                                            <div class="small text-body-secondary">
                                                Destino: {{ $movimiento->cuentaDestino->nombre }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>{{ $movimiento->cuenta?->nombre ?? '—' }}</td>

                                    <td class="text-end text-nowrap fw-bold">
                                        @if ($movimiento->tipo === 'ingreso')
                                            <span class="text-success">+</span>
                                        @elseif ($movimiento->tipo === 'gasto')
                                            <span class="text-danger">−</span>
                                        @endif
                                        ${{ number_format($movimiento->valor, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-body-secondary py-5">
                                        No hay movimientos en este período.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>