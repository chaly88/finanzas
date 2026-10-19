
<div>
    @section('page_title', 'Movimientos financieros')
    @section('page_description', 'Registra y consulta tus ingresos, gastos y transferencias.')

    {{-- Mensajes --}}
    @if (session()->has('mensaje'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('mensaje') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Cerrar">
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Cerrar">
            </button>
        </div>
    @endif

    @error('movimientoEditandoId')
        <div class="alert alert-warning" role="alert">
            {{ $message }}
        </div>
    @enderror

    <div class="row">
        {{-- Formulario --}}
        <div class="col-12 col-xl-5">
            <div class="card card-primary card-outline">

                <div class="card-header">
                    <h3 class="card-title">
                        {{ $movimientoEditandoId ? 'Editar movimiento' : 'Registrar movimiento' }}
                    </h3>
                </div>

                <form wire:submit="guardar">
                    <div class="card-body">

                        {{-- Advertencia por saldo insuficiente --}}
                        @if ($confirmarGastoSinSaldo)
                            <div class="alert alert-warning" role="alert">
                                <h5>
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    Saldo insuficiente
                                </h5>

                                <p>
                                    El gasto supera el saldo disponible de la cuenta.
                                    Puedes cancelar o confirmar el registro si el gasto
                                    realmente se realizó.
                                </p>

                                <ul class="mb-2">
                                    <li>
                                        <strong>Saldo disponible:</strong>
                                        {{ $saldoDisponibleGasto < 0 ? '-$' . number_format(abs($saldoDisponibleGasto), 0, ',', '.') : '$' . number_format($saldoDisponibleGasto, 0, ',', '.') }}
                                    </li>

                                    <li>
                                        <strong>Saldo después del gasto:</strong>
                                        {{ $saldoResultanteGasto < 0 ? '-$' . number_format(abs($saldoResultanteGasto), 0, ',', '.') : '$' . number_format($saldoResultanteGasto, 0, ',', '.') }}
                                    </li>
                                </ul>

                                <p class="mb-0">
                                    ¿Deseas registrar el gasto aunque la cuenta quede
                                    con saldo negativo?
                                </p>
                            </div>
                        @endif

                        {{-- Tipo --}}
                        <div class="mb-3">
                            <label for="tipo" class="form-label">
                                Tipo de movimiento
                            </label>

                            <select
                                id="tipo"
                                class="form-select @error('tipo') is-invalid @enderror"
                                wire:model.live="tipo">

                                <option value="ingreso">Ingreso</option>
                                <option value="gasto">Gasto</option>
                                <option value="transferencia">Transferencia</option>
                            </select>

                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Categoría --}}
                        @if ($tipo !== 'transferencia')
                            <div class="mb-3">
                                <label for="categoria_id" class="form-label">
                                    Categoría
                                </label>

                                <select
                                    id="categoria_id"
                                    class="form-select @error('categoria_id') is-invalid @enderror"
                                    wire:model.live="categoria_id">

                                    <option value="">Selecciona una categoría</option>

                                    @foreach ($categorias->where('tipo', $tipo) as $categoria)
                                        <option value="{{ $categoria->id }}">
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('categoria_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        {{-- Cuenta de origen --}}
                        <div class="mb-3">
                            <label for="cuenta_id" class="form-label">
                                Cuenta de origen
                            </label>

                            <select
                                id="cuenta_id"
                                class="form-select @error('cuenta_id') is-invalid @enderror"
                                wire:model.live="cuenta_id">

                                <option value="">Selecciona una cuenta</option>

                                @foreach ($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}">
                                        {{ $cuenta->nombre }}
                                        — Saldo: ${{ number_format($cuenta->saldo_actual, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>

                            @error('cuenta_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Cuenta de destino --}}
                        @if ($tipo === 'transferencia')
                            <div class="mb-3">
                                <label for="cuenta_destino_id" class="form-label">
                                    Cuenta de destino
                                </label>

                                <select
                                    id="cuenta_destino_id"
                                    class="form-select @error('cuenta_destino_id') is-invalid @enderror"
                                    wire:model.live="cuenta_destino_id">

                                    <option value="">Selecciona una cuenta</option>

                                    @foreach ($cuentas as $cuenta)
                                        @if ($cuenta->id !== $cuenta_id)
                                            <option value="{{ $cuenta->id }}">
                                                {{ $cuenta->nombre }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>

                                @error('cuenta_destino_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        {{-- Concepto --}}
                        <div class="mb-3">
                            <label for="concepto" class="form-label">
                                Concepto
                            </label>

                            <input
                                id="concepto"
                                type="text"
                                class="form-control @error('concepto') is-invalid @enderror"
                                wire:model="concepto"
                                placeholder="Ej. Mercado, salario o traslado a Nequi">

                            @error('concepto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Valor --}}
                        <div class="mb-3">
                            <label for="valor" class="form-label">
                                Valor (COP)
                            </label>

                            <input
                                id="valor"
                                type="number"
                                min="1"
                                step="1"
                                class="form-control @error('valor') is-invalid @enderror"
                                wire:model.live="valor">

                            @error('valor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Fecha --}}
                        <div class="mb-3">
                            <label for="fecha" class="form-label">
                                Fecha
                            </label>

                            <input
                                id="fecha"
                                type="date"
                                class="form-control @error('fecha') is-invalid @enderror"
                                wire:model="fecha">

                            @error('fecha')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Medio de pago --}}
                        <div class="mb-3">
                            <label for="medio_pago" class="form-label">
                                Medio de pago
                            </label>

                            <select
                                id="medio_pago"
                                class="form-select @error('medio_pago') is-invalid @enderror"
                                wire:model="medio_pago">

                                <option value="">No especificado</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia bancaria</option>
                                <option value="tarjeta_debito">Tarjeta débito</option>
                                <option value="tarjeta_credito">Tarjeta crédito</option>
                                <option value="nequi">Nequi</option>
                                <option value="daviplata">Daviplata</option>
                                <option value="otro">Otro</option>
                            </select>

                            @error('medio_pago')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Observaciones --}}
                        <div class="mb-3">
                            <label for="observacion" class="form-label">
                                Observaciones
                            </label>

                            <textarea
                                id="observacion"
                                rows="2"
                                class="form-control @error('observacion') is-invalid @enderror"
                                wire:model="observacion"></textarea>

                            @error('observacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    {{-- Botones --}}
                    <div class="card-footer">
                        @if ($confirmarGastoSinSaldo)
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary flex-fill"
                                    wire:click="cancelarConfirmacionGasto"
                                    wire:loading.attr="disabled"
                                    wire:target="cancelarConfirmacionGasto,guardar">

                                    <i class="bi bi-x-circle me-1"></i>
                                    Cancelar
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-danger flex-fill"
                                    wire:click="guardar(true)"
                                    wire:loading.attr="disabled"
                                    wire:target="guardar">

                                    <span wire:loading.remove wire:target="guardar">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Confirmar y registrar gasto
                                    </span>

                                    <span wire:loading wire:target="guardar">
                                        Registrando...
                                    </span>
                                </button>
                            </div>
                        @else
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                @if ($movimientoEditandoId)
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary flex-fill"
                                        wire:click="cancelarEdicion"
                                        wire:loading.attr="disabled">

                                        <i class="bi bi-x-circle me-1"></i>
                                        Cancelar edición
                                    </button>
                                @endif

                                <button
                                    type="submit"
                                    class="btn btn-primary flex-fill"
                                    wire:loading.attr="disabled"
                                    wire:target="guardar">

                                    <span wire:loading.remove wire:target="guardar">
                                        <i class="bi bi-save me-1"></i>
                                        {{ $movimientoEditandoId ? 'Actualizar movimiento' : 'Guardar movimiento' }}
                                    </span>

                                    <span wire:loading wire:target="guardar">
                                        Guardando...
                                    </span>
                                </button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Últimos movimientos --}}
        <div class="col-12 col-xl-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Últimos movimientos</h3>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Movimiento</th>
                                    <th>Cuenta</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($movimientos as $movimiento)
                                    <tr wire:key="movimiento-{{ $movimiento->id }}">
                                        <td class="text-nowrap">
                                            {{ $movimiento->fecha->format('d/m/Y') }}
                                        </td>

                                        <td>
                                            <span class="badge {{ $movimiento->tipo === 'ingreso' ? 'text-bg-success' : ($movimiento->tipo === 'gasto' ? 'text-bg-danger' : 'text-bg-primary') }}">
                                                {{ ucfirst($movimiento->tipo) }}
                                            </span>

                                            <div class="small text-break">
                                                {{ $movimiento->concepto }}
                                            </div>

                                            @if ($movimiento->tipo === 'transferencia')
                                                <div class="small text-muted">
                                                    Destino:
                                                    {{ $movimiento->cuentaDestino?->nombre }}
                                                </div>
                                            @endif
                                        </td>

                                        <td>
                                            {{ $movimiento->cuenta?->nombre }}
                                        </td>

                                        <td class="text-end text-nowrap fw-semibold">
                                            ${{ number_format($movimiento->valor, 0, ',', '.') }}
                                        </td>

                                        <td class="text-center text-nowrap">
                                            @if (
                                                $movimiento->abonoDeuda
                                                || $movimiento->movimientoAhorro
                                                || $movimiento->movimientoRecurrente
                                            )
                                                <span
                                                    class="badge text-bg-secondary"
                                                    title="Este movimiento debe gestionarse desde su módulo correspondiente.">

                                                    <i class="bi bi-lock me-1"></i>
                                                    Protegido
                                                </span>
                                            @else
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    wire:click="editar({{ $movimiento->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="editar({{ $movimiento->id }})">

                                                    <i class="bi bi-pencil me-1"></i>
                                                    Editar
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Todavía no tienes movimientos registrados.
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
</div>
