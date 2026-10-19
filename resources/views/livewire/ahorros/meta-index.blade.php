<div>
    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Ahorros y Metas</h1>
            <p class="text-muted mb-0">
                Organiza tus objetivos y planifica tu ahorro.
            </p>
        </div>

        <button type="button" class="btn btn-primary" wire:click="crear">
            <i class="bi bi-plus-circle me-1"></i>
            Nueva meta
        </button>
    </div>

    {{-- Mensajes --}}
    @if (session()->has('mensaje'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('mensaje') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    {{-- Formulario de creación y edición --}}
    @if ($mostrarFormulario)
        <div class="card card-primary card-outline mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    {{ $metaEditandoId ? 'Editar meta de ahorro' : 'Nueva meta de ahorro' }}
                </h3>
            </div>

            <form wire:submit="guardar">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="nombre" class="form-label">
                                Nombre de la meta *
                            </label>

                            <input id="nombre" type="text" class="form-control @error('nombre') is-invalid @enderror"
                                wire:model="nombre" maxlength="150" placeholder="Ej. Fondo de emergencia">

                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="monto_objetivo" class="form-label">
                                Monto objetivo (COP) *
                            </label>

                            <input id="monto_objetivo" type="number"
                                class="form-control @error('monto_objetivo') is-invalid @enderror"
                                wire:model="monto_objetivo" min="1" step="1" placeholder="5000000">

                            @error('monto_objetivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="descripcion" class="form-label">
                                Descripción
                            </label>

                            <textarea id="descripcion" class="form-control @error('descripcion') is-invalid @enderror"
                                wire:model="descripcion" rows="3" maxlength="5000"
                                placeholder="Describe para qué quieres ahorrar..."></textarea>

                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="fecha_inicio" class="form-label">
                                Fecha de inicio *
                            </label>

                            <input id="fecha_inicio" type="date"
                                class="form-control @error('fecha_inicio') is-invalid @enderror" wire:model="fecha_inicio">

                            @error('fecha_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="fecha_objetivo" class="form-label">
                                Fecha objetivo
                            </label>

                            <input id="fecha_objetivo" type="date"
                                class="form-control @error('fecha_objetivo') is-invalid @enderror"
                                wire:model="fecha_objetivo">

                            @error('fecha_objetivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="prioridad" class="form-label">
                                Prioridad *
                            </label>

                            <select id="prioridad" class="form-select @error('prioridad') is-invalid @enderror"
                                wire:model="prioridad">
                                <option value="baja">Baja</option>
                                <option value="media">Media</option>
                                <option value="alta">Alta</option>
                            </select>

                            @error('prioridad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <p class="text-muted small mb-0">
                        Los campos marcados con * son obligatorios.
                    </p>
                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" wire:click="cancelar" wire:loading.attr="disabled">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="guardar">
                        <span wire:loading.remove wire:target="guardar">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ $metaEditandoId ? 'Guardar cambios' : 'Guardar meta' }}
                        </span>

                        <span wire:loading wire:target="guardar">
                            Guardando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Formulario de aportes y retiros --}}
    @if ($mostrarFormularioMovimiento)
        <div class="card card-primary card-outline mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    {{ $tipoMovimiento === 'aporte' ? 'Registrar aporte' : 'Registrar retiro' }}
                </h3>
            </div>

            <form wire:submit="guardarMovimiento">
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Meta:</strong> {{ $nombreMetaMovimiento }}
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cuentaOrigenId" class="form-label">
                                {{ $tipoMovimiento === 'aporte'
            ? 'Cuenta de donde sale el dinero'
            : 'Cuenta donde está el ahorro' }}
                            </label>

                            <select id="cuentaOrigenId" class="form-select @error('cuentaOrigenId') is-invalid @enderror"
                                wire:model="cuentaOrigenId">
                                <option value="">Selecciona una cuenta</option>

                                @foreach ($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}">
                                        {{ $cuenta->nombre }}
                                        — Saldo: ${{ number_format($cuenta->saldo_actual, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>

                            @error('cuentaOrigenId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="cuentaDestinoId" class="form-label">
                                {{ $tipoMovimiento === 'aporte'
            ? 'Cuenta donde guardar el dinero'
            : 'Cuenta que recibirá el dinero' }}
                            </label>

                            <select id="cuentaDestinoId" class="form-select @error('cuentaDestinoId') is-invalid @enderror"
                                wire:model="cuentaDestinoId">
                                <option value="">Selecciona una cuenta</option>

                                @foreach ($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}">
                                        {{ $cuenta->nombre }}
                                    </option>
                                @endforeach
                            </select>

                            @error('cuentaDestinoId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tipoMovimiento" class="form-label">
                                Tipo de movimiento
                            </label>

                            <select id="tipoMovimiento" class="form-select @error('tipoMovimiento') is-invalid @enderror"
                                wire:model="tipoMovimiento">
                                <option value="aporte">Aporte</option>
                                <option value="retiro">Retiro</option>
                            </select>

                            @error('tipoMovimiento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="montoMovimiento" class="form-label">
                                Monto (COP) *
                            </label>

                            <input id="montoMovimiento" type="number"
                                class="form-control @error('montoMovimiento') is-invalid @enderror"
                                wire:model="montoMovimiento" min="1" step="1" placeholder="100000">

                            @error('montoMovimiento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="fechaMovimiento" class="form-label">
                                Fecha *
                            </label>

                            <input id="fechaMovimiento" type="date"
                                class="form-control @error('fechaMovimiento') is-invalid @enderror"
                                wire:model="fechaMovimiento">

                            @error('fechaMovimiento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="descripcionMovimiento" class="form-label">
                                Descripción
                            </label>

                            <input id="descripcionMovimiento" type="text" maxlength="255"
                                class="form-control @error('descripcionMovimiento') is-invalid @enderror"
                                wire:model="descripcionMovimiento" placeholder="Ej. Ahorro de la quincena">

                            @error('descripcionMovimiento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <p class="text-muted small mb-0">
                        El monto debe ser positivo. No se permiten retiros
                        superiores al saldo disponible.
                    </p>
                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" wire:click="cancelarMovimiento"
                        wire:loading.attr="disabled">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled"
                        wire:target="guardarMovimiento">
                        <span wire:loading.remove wire:target="guardarMovimiento">
                            Guardar movimiento
                        </span>

                        <span wire:loading wire:target="guardarMovimiento">
                            Guardando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Historial de una meta --}}
    @if ($mostrarHistorial)
        <div class="card card-primary card-outline mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    Historial: {{ $nombreMetaHistorial }}
                </h3>

                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-secondary" wire:click="cerrarHistorial">
                        <i class="bi bi-x-circle me-1"></i>
                        Cerrar historial
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Monto</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($movimientosHistorial as $movimiento)
                                <tr wire:key="movimiento-{{ $movimiento->id }}">
                                    <td class="text-nowrap">
                                        {{ $movimiento->fecha->format('d/m/Y') }}
                                    </td>

                                    <td>
                                        @if ($movimiento->tipo === 'aporte')
                                            <span class="badge text-bg-success">
                                                Aporte
                                            </span>
                                        @else
                                            <span class="badge text-bg-danger">
                                                Retiro
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-nowrap">
                                        ${{ number_format((float) $movimiento->monto, 0, ',', '.') }}
                                    </td>

                                    <td>
                                        {{ $movimiento->descripcion ?: 'Sin descripción' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="bi bi-clock-history fs-3 text-muted"></i>

                                        <p class="mt-2 mb-0">
                                            Esta meta todavía no tiene movimientos.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($movimientosHistorial->hasPages())
                <div class="card-footer">
                    {{ $movimientosHistorial->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Listado principal --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Mis metas</h3>

            <div class="card-tools">
                <div style="width: 260px;">
                    <input type="search" class="form-control form-control-sm" wire:model.live.debounce.300ms="busqueda"
                        placeholder="Buscar meta..." aria-label="Buscar meta de ahorro">
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Meta</th>
                            <th>Objetivo</th>
                            <th>Ahorrado</th>
                            <th>Avance</th>
                            <th>Fecha inicio</th>
                            <th>Fecha objetivo</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($metas as $meta)
                            <tr wire:key="meta-{{ $meta->id }}">
                                <td>
                                    <strong>{{ $meta->nombre }}</strong>

                                    @if ($meta->descripcion)
                                        <div class="text-muted small">
                                            {{ \Illuminate\Support\Str::limit($meta->descripcion, 60) }}
                                        </div>
                                    @endif
                                </td>

                                <td class="text-nowrap">
                                    ${{ number_format((float) $meta->monto_objetivo, 0, ',', '.') }}
                                </td>

                                <td class="text-nowrap">
                                    ${{ number_format($meta->saldo_ahorrado, 0, ',', '.') }}
                                </td>

                                <td style="min-width: 160px;">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>
                                            {{ number_format($meta->porcentaje_cumplido, 1, ',', '.') }}%
                                        </span>

                                        <span class="text-muted">
                                            Faltan ${{ number_format($meta->monto_faltante, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <div class="progress" role="progressbar"
                                        aria-valuenow="{{ min(100, max(0, $meta->porcentaje_cumplido)) }}" aria-valuemin="0"
                                        aria-valuemax="100" style="height: 8px;">
                                        <div class="progress-bar"
                                            style="width: {{ min(100, max(0, $meta->porcentaje_cumplido)) }}%"></div>
                                    </div>
                                </td>

                                <td class="text-nowrap">
                                    {{ $meta->fecha_inicio->format('d/m/Y') }}
                                </td>

                                <td class="text-nowrap">
                                    {{ $meta->fecha_objetivo?->format('d/m/Y') ?? 'Sin fecha' }}
                                </td>

                                <td>
                                    @if ($meta->prioridad === 'alta')
                                        <span class="badge text-bg-danger">Alta</span>
                                    @elseif ($meta->prioridad === 'media')
                                        <span class="badge text-bg-warning">Media</span>
                                    @else
                                        <span class="badge text-bg-secondary">Baja</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($meta->estado === 'activa')
                                        <span class="badge text-bg-success">Activa</span>
                                    @elseif ($meta->estado === 'cumplida')
                                        <span class="badge text-bg-primary">Cumplida</span>
                                    @else
                                        <span class="badge text-bg-secondary">Cancelada</span>
                                    @endif
                                </td>

                                <td class="text-nowrap">
                                    @if ($meta->estado === 'activa')
                                        <button type="button" class="btn btn-sm btn-outline-primary mb-1"
                                            wire:click="editar({{ $meta->id }})" title="Editar meta">
                                            <i class="bi bi-pencil"></i>
                                            Editar
                                        </button>

                                        <button type="button" class="btn btn-sm btn-success mb-1"
                                            wire:click="abrirMovimiento({{ $meta->id }}, 'aporte')" title="Registrar aporte">
                                            <i class="bi bi-plus-circle"></i>
                                            Aportar
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger mb-1"
                                            wire:click="abrirMovimiento({{ $meta->id }}, 'retiro')" title="Registrar retiro"
                                            @disabled($meta->saldo_ahorrado <= 0)>
                                            <i class="bi bi-dash-circle"></i>
                                            Retirar
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-1"
                                        wire:click="verHistorial({{ $meta->id }})" title="Consultar historial">
                                        <i class="bi bi-clock-history"></i>
                                        Historial
                                    </button>
                                    @if ($meta->estado === 'activa')
                                        <button type="button" class="btn btn-sm btn-success"
                                            wire:click="marcarCumplida({{ $meta->id }})"
                                            wire:confirm="¿Deseas marcar esta meta como cumplida?">
                                            <i class="bi bi-check-circle"></i>
                                            Cumplir
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            wire:click="cancelarMeta({{ $meta->id }})"
                                            wire:confirm="¿Estás seguro de cancelar esta meta?">
                                            <i class="bi bi-x-circle"></i>
                                            Cancelar
                                        </button>
                                    @elseif ($meta->estado === 'cancelada')
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            wire:click="reactivarMeta({{ $meta->id }})"
                                            wire:confirm="¿Deseas reactivar esta meta?">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                            Reactivar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="bi bi-piggy-bank fs-1 text-muted"></i>

                                    <p class="mt-3 mb-1">
                                        <strong>No tienes metas de ahorro registradas.</strong>
                                    </p>

                                    <p class="text-muted mb-3">
                                        Crea tu primera meta para comenzar a planificar tus ahorros.
                                    </p>

                                    <button type="button" class="btn btn-primary btn-sm" wire:click="crear">
                                        Crear primera meta
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($metas->hasPages())
            <div class="card-footer">
                {{ $metas->links() }}
            </div>
        @endif
    </div>
</div>