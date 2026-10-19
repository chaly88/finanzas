
<div>
    @section('page_title', 'Presupuestos')
    @section('page_description', 'Planifica y controla tus gastos mensuales.')

    <div class="container-fluid">

        @if (session()->has('mensaje'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('mensaje') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <div class="row g-3">

            {{-- Formulario --}}
            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent">
                        <h3 class="card-title">
                            <i class="bi bi-wallet2 me-2"></i>
                            {{ $presupuestoId ? 'Editar presupuesto' : 'Nuevo presupuesto' }}
                        </h3>
                    </div>

                    <div class="card-body">
                        <form wire:submit="guardar">

                            <div class="mb-3">
                                <label for="categoria_id" class="form-label">
                                    Categoría de gasto
                                </label>

                                <select id="categoria_id" class="form-select"
                                    wire:model="categoria_id" required>
                                    <option value="">Selecciona una categoría</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}">
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('categoria_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-6">
                                    <label for="mes" class="form-label">Mes</label>
                                    <select id="mes" class="form-select" wire:model="mes" required>
                                        @foreach ([
                                            1 => 'Enero',
                                            2 => 'Febrero',
                                            3 => 'Marzo',
                                            4 => 'Abril',
                                            5 => 'Mayo',
                                            6 => 'Junio',
                                            7 => 'Julio',
                                            8 => 'Agosto',
                                            9 => 'Septiembre',
                                            10 => 'Octubre',
                                            11 => 'Noviembre',
                                            12 => 'Diciembre',
                                        ] as $numero => $nombre)
                                            <option value="{{ $numero }}">{{ $nombre }}</option>
                                        @endforeach
                                    </select>

                                    @error('mes')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-6">
                                    <label for="anio" class="form-label">Año</label>
                                    <input id="anio" type="number" class="form-control"
                                        wire:model="anio" min="2000" max="2100" required>

                                    @error('anio')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-3 mb-3">
                                <label for="valor" class="form-label">
                                    Límite de gasto (COP)
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input id="valor" type="number" class="form-control"
                                        wire:model="valor" min="1" step="1"
                                        placeholder="Ej. 300000" required>
                                </div>

                                @error('valor')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="observacion" class="form-label">
                                    Observación (opcional)
                                </label>

                                <textarea id="observacion" class="form-control"
                                    wire:model="observacion" rows="3"
                                    placeholder="Notas sobre este presupuesto"></textarea>

                                @error('observacion')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary"
                                    wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="guardar">
                                        <i class="bi bi-save me-1"></i>
                                        {{ $presupuestoId ? 'Actualizar' : 'Guardar presupuesto' }}
                                    </span>

                                    <span wire:loading wire:target="guardar">
                                        Guardando...
                                    </span>
                                </button>

                                @if ($presupuestoId)
                                    <button type="button" class="btn btn-outline-secondary"
                                        wire:click="cancelar">
                                        Cancelar
                                    </button>
                                @endif
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            {{-- Seguimiento de presupuestos --}}
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent">
                        <h3 class="card-title">
                            <i class="bi bi-bar-chart-line me-2"></i>
                            Seguimiento de presupuestos
                        </h3>
                    </div>

                    <div class="card-body">
                        @php
                            $totalPresupuestado = $presupuestos->sum('valor');

                            // Gasto correspondiente a las categorías presupuestadas.
                            $totalGastadoPresupuestado = $presupuestos->sum(
                                fn ($presupuesto) =>
                                    (float) ($totalesPorCategoria[$presupuesto->categoria_id] ?? 0)
                            );

                            // Disponible dentro de los presupuestos definidos.
                            $totalDisponible = $totalPresupuestado - $totalGastadoPresupuestado;
                        @endphp

                        {{-- Resumen --}}
                        <div class="row g-3 mb-4">

                            <div class="col-12 col-md-6">
                                <div class="rounded-3 bg-body-tertiary p-3 h-100">
                                    <div class="small text-body-secondary">Presupuestado</div>
                                    <div class="fs-5 fw-bold">
                                        ${{ number_format($totalPresupuestado, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="rounded-3 bg-body-tertiary p-3 h-100">
                                    <div class="small text-body-secondary">Gasto total del mes</div>
                                    <div class="fs-5 fw-bold text-danger">
                                        ${{ number_format($totalGastadoGeneral, 0, ',', '.') }}
                                    </div>
                                    <div class="small text-body-secondary">
                                        Incluye gastos sin presupuesto o sin categoría.
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="rounded-3 bg-body-tertiary p-3 h-100">
                                    <div class="small text-body-secondary">
                                        Gastado en categorías presupuestadas
                                    </div>
                                    <div class="fs-5 fw-bold">
                                        ${{ number_format($totalGastadoPresupuestado, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="rounded-3 bg-body-tertiary p-3 h-100">
                                    <div class="small text-body-secondary">
                                        Disponible en presupuestos
                                    </div>
                                    <div class="fs-5 fw-bold {{ $totalDisponible < 0 ? 'text-danger' : 'text-success' }}">
                                        ${{ number_format($totalDisponible, 0, ',', '.') }}
                                    </div>
                                    <div class="small text-body-secondary">
                                        Presupuesto menos gasto de sus categorías.
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- Tabla de presupuestos --}}
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Categoría</th>
                                        <th>Presupuesto</th>
                                        <th>Gastado</th>
                                        <th>Progreso</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($presupuestos as $presupuesto)
                                        @php
                                            $gastado = (float) (
                                                $totalesPorCategoria[$presupuesto->categoria_id] ?? 0
                                            );

                                            $limite = (float) $presupuesto->valor;

                                            $porcentaje = $limite > 0
                                                ? round(($gastado / $limite) * 100)
                                                : 0;

                                            $ancho = min(100, $porcentaje);

                                            $excedido = $gastado > $limite;
                                            $agotado = $gastado >= $limite;
                                            $alerta = $porcentaje >= 100;
                                            $proximoAgotarse = $porcentaje >= 80 && $porcentaje < 100;

                                            $color = $alerta
                                                ? 'bg-danger'
                                                : ($proximoAgotarse ? 'bg-warning' : 'bg-success');
                                        @endphp

                                        <tr wire:key="presupuesto-{{ $presupuesto->id }}">
                                            <td>
                                                <strong>{{ $presupuesto->categoria->nombre }}</strong>

                                                @if ($presupuesto->observacion)
                                                    <div class="small text-body-secondary">
                                                        {{ $presupuesto->observacion }}
                                                    </div>
                                                @endif
                                            </td>

                                            <td class="text-nowrap">
                                                ${{ number_format($limite, 0, ',', '.') }}
                                            </td>

                                            <td class="text-nowrap">
                                                <span class="{{ $excedido ? 'text-danger fw-bold' : '' }}">
                                                    ${{ number_format($gastado, 0, ',', '.') }}
                                                </span>

                                                @if ($excedido)
                                                    <div class="small text-danger">
                                                        Exceso:
                                                        ${{ number_format($gastado - $limite, 0, ',', '.') }}
                                                    </div>
                                                @endif
                                            </td>

                                            <td style="min-width: 150px;">
                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span class="{{ $alerta ? 'text-danger fw-bold' : ($proximoAgotarse ? 'text-warning-emphasis fw-bold' : '') }}">
                                                        {{ $porcentaje }} %
                                                    </span>

                                                    @if ($excedido)
                                                        <span class="text-danger">Excedido</span>
                                                    @elseif ($agotado)
                                                        <span class="text-danger">Agotado</span>
                                                    @endif
                                                </div>

                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar {{ $color }}"
                                                        role="progressbar"
                                                        style="width: {{ $ancho }}%"
                                                        aria-valuenow="{{ $ancho }}"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="text-end text-nowrap">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    wire:click="editar({{ $presupuesto->id }})"
                                                    title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    wire:click="eliminar({{ $presupuesto->id }})"
                                                    wire:confirm="¿Deseas eliminar este presupuesto?"
                                                    title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        {{-- Alertas según el consumo del presupuesto --}}
                                        @if ($excedido)
                                            <tr wire:key="alerta-presupuesto-{{ $presupuesto->id }}">
                                                <td colspan="5" class="pt-0 border-top-0">
                                                    <div class="alert alert-danger py-2 mb-2" role="alert">
                                                        <i class="bi bi-exclamation-octagon-fill me-2"></i>
                                                        <strong>Presupuesto excedido.</strong>
                                                        Has gastado
                                                        ${{ number_format($gastado - $limite, 0, ',', '.') }}
                                                        por encima del límite de
                                                        {{ $presupuesto->categoria->nombre }}.
                                                    </div>
                                                </td>
                                            </tr>
                                        @elseif ($agotado)
                                            <tr wire:key="alerta-presupuesto-{{ $presupuesto->id }}">
                                                <td colspan="5" class="pt-0 border-top-0">
                                                    <div class="alert alert-danger py-2 mb-2" role="alert">
                                                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                                                        <strong>Presupuesto agotado.</strong>
                                                        Has utilizado el 100 % del límite de
                                                        {{ $presupuesto->categoria->nombre }}.
                                                    </div>
                                                </td>
                                            </tr>
                                        @elseif ($proximoAgotarse)
                                            <tr wire:key="alerta-presupuesto-{{ $presupuesto->id }}">
                                                <td colspan="5" class="pt-0 border-top-0">
                                                    <div class="alert alert-warning py-2 mb-2" role="alert">
                                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                        <strong>Presupuesto próximo a agotarse.</strong>
                                                        Has utilizado el {{ $porcentaje }} % del límite de
                                                        {{ $presupuesto->categoria->nombre }}.
                                                        Te quedan
                                                        ${{ number_format(max(0, $limite - $gastado), 0, ',', '.') }}.
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif

                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <i class="bi bi-wallet2 fs-1 d-block mb-2 text-body-secondary"></i>
                                                <div class="fw-semibold">
                                                    No hay presupuestos para este período.
                                                </div>
                                                <div class="text-body-secondary small">
                                                    Crea tu primer presupuesto con el formulario.
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Leyenda de colores --}}
                        @if ($presupuestos->isNotEmpty())
                            <div class="d-flex flex-wrap gap-3 small text-body-secondary mt-2">
                                <span>
                                    <span class="badge bg-success">&nbsp;</span>
                                    Menos del 80 %
                                </span>
                                <span>
                                    <span class="badge bg-warning">&nbsp;</span>
                                    Del 80 % al 99 %
                                </span>
                                <span>
                                    <span class="badge bg-danger">&nbsp;</span>
                                    100 % o más
                                </span>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
