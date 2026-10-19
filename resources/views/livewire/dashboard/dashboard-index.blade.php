<div>
    @section('page_title', 'Dashboard')
    @section('page_description', 'Resumen de tus finanzas personales.')

    <div class="container-fluid">

        {{-- Filtros del dashboard --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">

                    <div class="col-12 col-md-4">
                        <label for="periodo" class="form-label">
                            Período
                        </label>

                        <select id="periodo" class="form-select" wire:model.live="periodo">
                            <option value="mes_actual">Este mes</option>
                            <option value="mes_anterior">Mes anterior</option>
                            <option value="este_anio">Este año</option>
                            <option value="personalizado">Personalizado</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="fechaDesde" class="form-label">
                            Fecha inicial
                        </label>

                        <input type="date" id="fechaDesde" class="form-control" wire:model.live="fechaDesde"
                            @disabled($periodo !== 'personalizado')>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="fechaHasta" class="form-label">
                            Fecha final
                        </label>

                        <input type="date" id="fechaHasta" class="form-control" wire:model.live="fechaHasta"
                            @disabled($periodo !== 'personalizado')>
                    </div>

                </div>
            </div>
        </div>

        {{-- Tarjetas principales --}}
        <div class="row g-3 mb-4">
            {{-- Dinero disponible --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="flex-grow-1">
                                <p class="text-body-secondary mb-2">
                                    Dinero disponible
                                </p>
                                <h3 class="fw-bold mb-1 text-break">
                                    ${{ number_format($dineroDisponible, 0, ',', '.') }}
                                </h3>
                                <span class="small text-body-secondary">
                                    En todas las cuentas
                                </span>
                            </div>
                            <div class="rounded-3 p-3 bg-info-subtle text-info">
                                <i class="bi bi-wallet2 fs-4"></i>
                            </div>
                        </div>
                    </div>
                    <div class="border-top border-info border-3"></div>
                </div>
            </div>

            {{-- Ingresos --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="flex-grow-1">
                                <p class="text-body-secondary mb-2">
                                    Ingresos del mes
                                </p>
                                <h3 class="fw-bold mb-1 text-break text-success">
                                    ${{ number_format($ingresos, 0, ',', '.') }}
                                </h3>
                                <span class="small text-body-secondary">
                                    Dinero recibido
                                </span>
                            </div>
                            <div class="rounded-3 p-3 bg-success-subtle text-success">
                                <i class="bi bi-arrow-down-left-circle fs-4"></i>
                            </div>
                        </div>
                    </div>
                    <div class="border-top border-success border-3"></div>
                </div>
            </div>

            {{-- Gastos --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="flex-grow-1">
                                <p class="text-body-secondary mb-2">
                                    Gastos del mes
                                </p>
                                <h3 class="fw-bold mb-1 text-danger text-break">
                                    ${{ number_format($gastos, 0, ',', '.') }}
                                </h3>
                                <span class="small text-body-secondary">
                                    Dinero gastado
                                </span>
                            </div>
                            <div class="rounded-3 p-3 bg-danger-subtle text-danger">
                                <i class="bi bi-arrow-up-right-circle fs-4"></i>
                            </div>
                        </div>
                    </div>
                    <div class="border-top border-danger border-3"></div>
                </div>
            </div>

            {{-- Balance --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="flex-grow-1">
                                <p class="text-body-secondary mb-2">
                                    Balance del mes
                                </p>
                                <h3
                                    class="fw-bold mb-1 text-break {{ $balanceMes >= 0 ? 'text-primary' : 'text-danger' }}">
                                    ${{ number_format($balanceMes, 0, ',', '.') }}
                                </h3>
                                <span class="small text-body-secondary">
                                    Ingresos menos gastos
                                </span>
                            </div>
                            <div class="rounded-3 p-3 bg-primary-subtle text-primary">
                                <i class="bi bi-calculator fs-4"></i>
                            </div>
                        </div>
                    </div>
                    <div class="border-top border-primary border-3"></div>
                </div>
            </div>

        </div>

        {{-- Resumen de ahorros --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <p class="text-body-secondary mb-2">
                                    Ahorro registrado en metas activas
                                </p>

                                <h3 class="fw-bold text-primary text-break">
                                    ${{ number_format($saldoMetasActivas, 2, ',', '.') }}
                                </h3>

                                <p class="text-body-secondary small mb-1">
                                    {{ $metasActivas }}
                                    {{ $metasActivas === 1 ? 'meta activa' : 'metas activas' }}
                                </p>

                                <p class="text-body-secondary small mb-0">
                                    Objetivo total:
                                    ${{ number_format($objetivoMetasActivas, 0, ',', '.') }}
                                </p>
                            </div>

                            <div class="rounded-3 p-3 bg-primary-subtle text-primary">
                                <i class="bi bi-piggy-bank fs-4"></i>
                            </div>
                        </div>
                    </div>

                    <div class="border-top border-primary border-3"></div>
                </div>
            </div>
        </div>

@if ($movimientosAhorroSinVinculo > 0)
    <div class="alert alert-info d-flex align-items-start gap-2" role="alert">
        <i class="bi bi-info-circle-fill mt-1"></i>

        <div>
            <strong>Historial anterior a la integración.</strong>

            Hay {{ $movimientosAhorroSinVinculo }}
            {{ $movimientosAhorroSinVinculo === 1 ? 'movimiento' : 'movimientos' }}
            de ahorro registrados antes de habilitar la vinculación con las cuentas.

            Estos registros se conservan como historial y no se han generado
            transferencias retroactivas.

            <a href="{{ route('ahorros.index') }}" class="alert-link ms-1">
                Ver ahorros y metas
            </a>
        </div>
    </div>
@endif

        <div class="row">

            {{-- Cuentas --}}
            <div class="col-12 col-xl-5 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-wallet2 me-1"></i>
                            Mis cuentas
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('cuentas.index') }}" class="btn btn-sm btn-outline-primary">
                                Ver cuentas
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Cuenta</th>
                                        <th class="text-end">Saldo actual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($cuentas->where('activa', true) as $cuenta)
                                        <tr>
                                            <td>
                                                <strong>{{ $cuenta->nombre }}</strong>
                                                <div class="text-muted small">
                                                    {{ ucfirst($cuenta->tipo) }}
                                                </div>
                                            </td>
                                            <td class="text-end fw-bold">
                                                ${{ number_format($cuenta->saldo_actual, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-4">
                                                No hay cuentas activas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gastos por categoría --}}
            <div class="col-12 col-xl-7 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-pie-chart me-1"></i>
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
                                $porcentaje = $gastos > 0
                                    ? min(100, round(($categoria->total_gastado / $gastos) * 100))
                                    : 0;
                            @endphp

                            <div class="mb-3">
                                <div class="d-flex justify-content-between gap-2 mb-1">
                                    <span>{{ $categoria->nombre }}</span>
                                    <span class="fw-bold text-nowrap">
                                        ${{ number_format($categoria->total_gastado, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ $porcentaje }}%; background-color: {{ $categoria->color ?: '#0d6efd' }};"
                                        aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <div class="text-muted small text-end">
                                    {{ $porcentaje }} %
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-pie-chart fs-1 d-block mb-2"></i>
                                Todavía no hay gastos categorizados este mes.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- Últimos movimientos --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-clock-history me-1"></i>
                    Últimos movimientos
                </h3>

                <div class="card-tools">
                    <a href="{{ route('movimientos.index') }}" class="btn btn-sm btn-outline-primary">
                        Ver movimientos
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
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
                            @forelse ($ultimosMovimientos as $movimiento)
                                <tr>
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
                                            <div class="text-muted small">
                                                {{ $movimiento->categoria->nombre }}
                                            </div>
                                        @endif
                                        @if ($movimiento->tipo === 'transferencia' && $movimiento->cuentaDestino)
                                            <div class="text-muted small">
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
                                    <td colspan="5" class="text-center py-4">
                                        Todavía no hay movimientos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- Gráfico de ingresos y gastos --}}
        <div class="card mt-4">
            <div class="card-header bg-transparent">
                <h3 class="card-title">
                    <i class="bi bi-bar-chart-line me-2"></i>
                    Comparación de ingresos y gastos
                </h3>
            </div>

            <div class="card-body">
                <div wire:ignore style="position: relative; height: 300px;">
                    <canvas id="graficoFinanciero"></canvas>
                </div>
            </div>
        </div>
    </div>




    @script
    <script>
        const canvas = document.getElementById('graficoFinanciero');

        if (canvas) {
            const datos = @js($datosGrafico);

            if (canvas.chartInstance) {
                canvas.chartInstance.destroy();
            }

            canvas.chartInstance = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: ['Ingresos', 'Gastos'],
                    datasets: [{
                        label: 'Valor en COP',
                        data: [datos.ingresos, datos.gastos],
                        backgroundColor: ['#198754', '#dc3545'],
                        borderRadius: 6,
                        maxBarThickness: 90
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return '$' + new Intl.NumberFormat('es-CO')
                                        .format(context.raw);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return '$' + new Intl.NumberFormat('es-CO')
                                        .format(value);
                                }
                            }
                        }
                    }
                }
            });
        }


        Livewire.on('dashboard-grafico-actualizado', (event) => {
            const datos = event.detail ?? event;

            if (!canvas.chartInstance) {
                return;
            }

            canvas.chartInstance.data.datasets[0].data = [
                datos.ingresos,
                datos.gastos
            ];

            canvas.chartInstance.update();
        });

    </script>
    @endscript

</div>