<div>
    @section('page_title', 'Deudas y créditos')
    @section('page_description', 'Controla tus obligaciones y el dinero que te deben.')

    <div class="container-fluid">

        @if (session()->has('mensaje'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('mensaje') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Resumen de deudas --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-body-secondary mb-2">Por pagar</div>
                                <h3 class="fw-bold text-danger mb-0">
                                    ${{ number_format($totalPorPagar, 0, ',', '.') }}
                                </h3>
                            </div>
                            <div class="rounded-circle bg-danger-subtle text-danger p-3">
                                <i class="bi bi-credit-card fs-3"></i>
                            </div>
                        </div>
                        <div class="small text-body-secondary mt-2">
                            Saldo pendiente de tus obligaciones
                        </div>
                    </div>
                    <div class="border-top border-danger border-3"></div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-body-secondary mb-2">Por cobrar</div>
                                <h3 class="fw-bold text-success mb-0">
                                    ${{ number_format($totalPorCobrar, 0, ',', '.') }}
                                </h3>
                            </div>
                            <div class="rounded-circle bg-success-subtle text-success p-3">
                                <i class="bi bi-cash-coin fs-3"></i>
                            </div>
                        </div>
                        <div class="small text-body-secondary mt-2">
                            Dinero pendiente de recibir
                        </div>
                    </div>
                    <div class="border-top border-success border-3"></div>
                </div>
            </div>
        </div>

        {{-- Formulario de abono --}}
        @if ($mostrarFormularioAbono)
            <div class="card card-success card-outline mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-cash-coin me-1"></i>
                        Registrar abono
                    </h3>
                </div>

                <div class="card-body">
                    @if ($deudaAbono)
                        <div class="alert alert-light border">
                            <div>
                                <strong>Concepto:</strong>
                                {{ $deudaAbono->concepto }}
                            </div>

                            <div>
                                <strong>
                                    {{ $deudaAbono->tipo === 'por_pagar' ? 'Acreedor' : 'Deudor' }}:
                                </strong>
                                {{ $deudaAbono->acreedor_deudor }}
                            </div>

                            <div>
                                <strong>Valor inicial:</strong>
                                ${{ number_format($deudaAbono->valor_inicial, 0, ',', '.') }}
                            </div>

                            <div>
                                <strong>Saldo pendiente:</strong>
                                <span class="fw-bold text-danger">
                                    ${{ number_format(
                                        max(
                                            0,
                                            (int) $deudaAbono->valor_inicial
                                            - (int) ($deudaAbono->abonos_sum_valor ?? 0)
                                        ),
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </span>
                            </div>
                        </div>
                    @endif

                    <form wire:submit="guardarAbono">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cuentaId" class="form-label">Cuenta</label>
                                <select
                                    id="cuentaId"
                                    class="form-select"
                                    wire:model.live="cuentaId"
                                    required
                                >
                                    <option value="">Seleccione una cuenta</option>
                                    @foreach ($cuentas as $cuenta)
                                        <option value="{{ $cuenta->id }}">
                                            {{ $cuenta->nombre }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('cuentaId')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror

                                @if ($cuentas->isEmpty())
                                    <div class="text-danger small mt-1">
                                        No hay cuentas activas. Registra o activa una cuenta antes de continuar.
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="valorAbono" class="form-label">Valor del abono</label>
                                <input
                                    id="valorAbono"
                                    type="number"
                                    class="form-control"
                                    wire:model.live="valorAbono"
                                    min="1"
                                    step="1"
                                    required
                                >
                                @error('valorAbono')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="fechaAbono" class="form-label">Fecha del abono</label>
                                <input
                                    id="fechaAbono"
                                    type="date"
                                    class="form-control"
                                    wire:model.live="fechaAbono"
                                    required
                                >
                                @error('fechaAbono')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="observacionAbono" class="form-label">Observación</label>
                                <input
                                    id="observacionAbono"
                                    type="text"
                                    class="form-control"
                                    wire:model.live="observacionAbono"
                                    maxlength="2000"
                                    placeholder="Observaciones del abono"
                                >
                                @error('observacionAbono')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button
                                type="submit"
                                class="btn btn-success"
                                wire:loading.attr="disabled"
                                @disabled($cuentas->isEmpty())
                            >
                                <i class="bi bi-check-circle me-1"></i>
                                Guardar abono
                            </button>

                            <button
                                type="button"
                                class="btn btn-secondary"
                                wire:click="cancelarAbono"
                            >
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Historial de abonos --}}
        @if ($mostrarHistorial && $deudaHistorial)
            <div class="card card-info card-outline mb-4">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-clock-history me-1"></i>
                        Historial de abonos
                    </h3>

                    <button
                        type="button"
                        class="btn btn-sm btn-secondary ms-auto"
                        wire:click="cerrarHistorial"
                    >
                        <i class="bi bi-x-lg me-1"></i>
                        Cerrar
                    </button>
                </div>

                <div class="card-body">
                    <h5>{{ $deudaHistorial->concepto }}</h5>

                    <p class="text-muted mb-3">
                        {{ $deudaHistorial->tipo === 'por_pagar' ? 'Acreedor' : 'Deudor' }}:
                        {{ $deudaHistorial->acreedor_deudor }}
                    </p>

                    <div class="row mb-4">
                        <div class="col-md-4 mb-2">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted">Valor inicial</small>
                                <h5 class="mb-0">
                                    ${{ number_format($deudaHistorial->valor_inicial, 0, ',', '.') }}
                                </h5>
                            </div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted">Total abonado</small>
                                <h5 class="mb-0 text-success">
                                    ${{ number_format(
                                        (int) ($deudaHistorial->abonos_sum_valor ?? 0),
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </h5>
                            </div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted">Saldo pendiente</small>
                                <h5 class="mb-0 text-danger">
                                    ${{ number_format(
                                        max(
                                            0,
                                            (int) $deudaHistorial->valor_inicial
                                            - (int) ($deudaHistorial->abonos_sum_valor ?? 0)
                                        ),
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </h5>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cuenta</th>
                                    <th>Movimiento</th>
                                    <th>Observación</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($abonosHistorial as $abono)
                                    <tr wire:key="abono-historial-{{ $abono->id }}">
                                        <td class="text-nowrap">
                                            {{ $abono->fecha->format('d/m/Y') }}
                                        </td>
                                        <td>{{ $abono->cuenta?->nombre ?? 'Cuenta eliminada' }}</td>
                                        <td>
                                            @if ($abono->movimiento)
                                                <span class="badge text-bg-secondary">
                                                    {{ ucfirst($abono->movimiento->tipo) }}
                                                </span>
                                                {{ $abono->movimiento->concepto }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $abono->observacion ?: '—' }}</td>
                                        <td class="text-end text-nowrap">
                                            ${{ number_format($abono->valor, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Esta deuda todavía no tiene abonos registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Listado de deudas --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center gap-2">
                <h3 class="card-title mb-0">
                    <i class="bi bi-journal-text me-2"></i>
                    Mis deudas
                </h3>

                <button
                    type="button"
                    class="btn btn-primary ms-auto"
                    wire:click="crear"
                >
                    <i class="bi bi-plus-circle me-1"></i>
                    Nueva deuda
                </button>
            </div>

            {{-- Filtros --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="filtroTipo" class="form-label">Tipo de deuda</label>
                        <select
                            id="filtroTipo"
                            class="form-select"
                            wire:model.live="filtroTipo"
                        >
                            <option value="todos">Todas</option>
                            <option value="por_pagar">Por pagar</option>
                            <option value="por_cobrar">Por cobrar</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="filtroEstado" class="form-label">Estado</label>
                        <select
                            id="filtroEstado"
                            class="form-select"
                            wire:model.live="filtroEstado"
                        >
                            <option value="todos">Todos los estados</option>
                            <option value="pendiente">Pendientes</option>
                            <option value="vencida">Vencidas</option>
                            <option value="pagada">Pagadas</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="busqueda" class="form-label">Buscar</label>
                        <input
                            id="busqueda"
                            type="search"
                            class="form-control"
                            placeholder="Persona, entidad o concepto..."
                            wire:model.live.debounce.300ms="busqueda"
                        >
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Persona o entidad</th>
                                <th>Concepto</th>
                                <th>Fecha</th>
                                <th>Vencimiento</th>
                                <th class="text-end">Valor inicial</th>
                                <th class="text-end">Abonado</th>
                                <th class="text-end">Pendiente</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($deudas as $deuda)
                                <tr wire:key="deuda-{{ $deuda->id }}">
                                    <td>
                                        @if ($deuda->tipo === 'por_pagar')
                                            <span class="badge text-bg-danger">Por pagar</span>
                                        @else
                                            <span class="badge text-bg-success">Por cobrar</span>
                                        @endif
                                    </td>

                                    <td>{{ $deuda->acreedor_deudor }}</td>
                                    <td>{{ $deuda->concepto }}</td>

                                    <td class="text-nowrap">
                                        {{ $deuda->fecha->format('d/m/Y') }}
                                    </td>

                                    <td class="text-nowrap">
                                        {{ $deuda->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                                    </td>

                                    <td class="text-end text-nowrap">
                                        ${{ number_format($deuda->valor_inicial, 0, ',', '.') }}
                                    </td>

                                    <td class="text-end text-nowrap text-success">
                                        ${{ number_format($deuda->total_abonado, 0, ',', '.') }}
                                    </td>

                                    <td class="text-end text-nowrap fw-bold">
                                        ${{ number_format($deuda->saldo_pendiente, 0, ',', '.') }}
                                    </td>

                                    <td>
                                        @switch($deuda->estado_calculado)
                                            @case('pagada')
                                                <span class="badge text-bg-success">Pagada</span>
                                                @break

                                            @case('vencida')
                                                <span class="badge text-bg-danger">Vencida</span>
                                                @break

                                            @default
                                                <span class="badge text-bg-warning">Pendiente</span>
                                        @endswitch
                                    </td>

                                    <td class="text-end text-nowrap">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            wire:click="editar({{ $deuda->id }})"
                                            title="Editar deuda"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            wire:click="eliminar({{ $deuda->id }})"
                                            wire:confirm="¿Estás seguro de eliminar esta deuda?"
                                            title="Eliminar deuda"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>

                                        @if ($deuda->saldo_pendiente > 0)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-success"
                                                wire:click="registrarAbono({{ $deuda->id }})"
                                            >
                                                <i class="bi bi-cash-coin"></i>
                                                Abonar
                                            </button>
                                        @endif

                                        @if ($deuda->total_abonado > 0)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-info"
                                                wire:click="verHistorial({{ $deuda->id }})"
                                                title="Ver historial de abonos"
                                            >
                                                <i class="bi bi-clock-history"></i>
                                                Historial
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-body-secondary py-5">
                                        <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                                        No hay deudas que coincidan con los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-transparent">
                <span class="text-body-secondary small">
                    {{ $deudas->count() }} registros
                </span>
            </div>
        </div>

        {{-- Formulario para crear o editar deuda --}}
        @if ($mostrarFormulario)
            <div class="card border-0 shadow-sm mt-2">
                <div class="card-header bg-transparent">
                    <h3 class="card-title">
                        <i class="bi bi-pencil-square me-2"></i>
                        {{ $deudaId ? 'Editar deuda' : 'Registrar deuda' }}
                    </h3>
                </div>

                <form wire:submit="guardar">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="tipo" class="form-label">Tipo de deuda *</label>
                                <select id="tipo" class="form-select" wire:model="tipo">
                                    <option value="por_pagar">Deuda por pagar</option>
                                    <option value="por_cobrar">Dinero por cobrar</option>
                                </select>
                                @error('tipo')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="acreedor_deudor" class="form-label">
                                    {{ $tipo === 'por_pagar' ? 'Acreedor o entidad' : 'Persona que debe' }} *
                                </label>
                                <input
                                    id="acreedor_deudor"
                                    type="text"
                                    class="form-control"
                                    wire:model="acreedor_deudor"
                                    maxlength="255"
                                    placeholder="Nombre de la persona o entidad"
                                >
                                @error('acreedor_deudor')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="concepto" class="form-label">Concepto *</label>
                                <input
                                    id="concepto"
                                    type="text"
                                    class="form-control"
                                    wire:model="concepto"
                                    maxlength="255"
                                    placeholder="Ej. Préstamo personal"
                                >
                                @error('concepto')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="valor_inicial" class="form-label">Valor inicial (COP) *</label>
                                <input
                                    id="valor_inicial"
                                    type="number"
                                    class="form-control"
                                    wire:model="valor_inicial"
                                    min="1"
                                    step="1"
                                    placeholder="500000"
                                >
                                @error('valor_inicial')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="fecha" class="form-label">Fecha de la deuda *</label>
                                <input
                                    id="fecha"
                                    type="date"
                                    class="form-control"
                                    wire:model="fecha"
                                >
                                @error('fecha')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="fecha_vencimiento" class="form-label">
                                    Fecha de vencimiento
                                </label>
                                <input
                                    id="fecha_vencimiento"
                                    type="date"
                                    class="form-control"
                                    wire:model="fecha_vencimiento"
                                >
                                @error('fecha_vencimiento')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="observacion" class="form-label">Observaciones</label>
                                <textarea
                                    id="observacion"
                                    class="form-control"
                                    wire:model="observacion"
                                    rows="3"
                                    maxlength="2000"
                                    placeholder="Detalles adicionales (opcional)"
                                ></textarea>
                                @error('observacion')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-transparent d-flex flex-wrap justify-content-end gap-2">
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            wire:click="cancelar"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="btn btn-primary"
                            wire:loading.attr="disabled"
                            wire:target="guardar"
                        >
                            <span wire:loading.remove wire:target="guardar">
                                <i class="bi bi-check-circle me-1"></i>
                                Guardar deuda
                            </span>

                            <span wire:loading wire:target="guardar">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Guardando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        @endif

    </div>
</div>