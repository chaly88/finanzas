<div>
    @section('page_title', 'Movimientos recurrentes')
    @section('page_description', 'Administra tus ingresos y gastos programados.')

    <div class="container-fluid">
        {{-- Mensajes --}}
        @if (session()->has('mensaje'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('mensaje') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <div class="alert alert-light border mb-4" role="note">
            <i class="bi bi-info-circle me-2"></i>
            Las programaciones no modifican los saldos por sí solas. El saldo cambiará cuando registres el movimiento real.
        </div>

        <div class="row g-3">
            {{-- Formulario --}}
            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <h3 class="card-title mb-0">
                            <i class="bi bi-arrow-repeat me-2"></i>Nueva programación
                        </h3>
                    </div>

                    <div class="card-body">
                        <form wire:submit="guardar">
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select id="tipo" class="form-select @error('tipo') is-invalid @enderror" wire:model.live="tipo" required>
                                    <option value="gasto">Gasto</option>
                                    <option value="ingreso">Ingreso</option>
                                </select>
                                @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="concepto" class="form-label">Concepto</label>
                                <input id="concepto" type="text" class="form-control @error('concepto') is-invalid @enderror"
                                    wire:model="concepto" maxlength="150" placeholder="Ej. Arriendo, internet, salario" required>
                                @error('concepto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="valor" class="form-label">Valor (COP)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input id="valor" type="number" class="form-control @error('valor') is-invalid @enderror"
                                        wire:model="valor" min="1" step="1" required>
                                    @error('valor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="cuenta_id" class="form-label">Cuenta</label>
                                <select id="cuenta_id" class="form-select @error('cuenta_id') is-invalid @enderror" wire:model="cuenta_id" required>
                                    <option value="">Selecciona una cuenta</option>
                                    @foreach ($cuentas as $cuenta)
                                        <option value="{{ $cuenta->id }}">{{ $cuenta->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('cuenta_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="categoria_id" class="form-label">Categoría <span class="text-body-secondary">(opcional)</span></label>
                                <select id="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" wire:model="categoria_id">
                                    <option value="">Sin categoría</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('categoria_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="frecuencia" class="form-label">Frecuencia</label>
                                <select id="frecuencia" class="form-select @error('frecuencia') is-invalid @enderror" wire:model="frecuencia" required>
                                    <option value="semanal">Semanal</option>
                                    <option value="quincenal">Quincenal</option>
                                    <option value="mensual">Mensual</option>
                                    <option value="trimestral">Trimestral</option>
                                    <option value="anual">Anual</option>
                                </select>
                                @error('frecuencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
                                <input id="fecha_inicio" type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" wire:model="fecha_inicio" required>
                                @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="proxima_fecha" class="form-label">Próxima fecha prevista</label>
                                <input id="proxima_fecha" type="date" class="form-control @error('proxima_fecha') is-invalid @enderror" wire:model="proxima_fecha" required>
                                @error('proxima_fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="fecha_fin" class="form-label">Fecha final <span class="text-body-secondary">(opcional)</span></label>
                                <input id="fecha_fin" type="date" class="form-control @error('fecha_fin') is-invalid @enderror" wire:model="fecha_fin">
                                @error('fecha_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="medio_pago" class="form-label">Medio de pago</label>
                                <select id="medio_pago" class="form-select @error('medio_pago') is-invalid @enderror" wire:model="medio_pago">
                                    <option value="">No especificado</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="tarjeta_debito">Tarjeta débito</option>
                                    <option value="tarjeta_credito">Tarjeta crédito</option>
                                    <option value="nequi">Nequi</option>
                                    <option value="daviplata">Daviplata</option>
                                    <option value="otro">Otro</option>
                                </select>
                                @error('medio_pago') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="observacion" class="form-label">Observaciones</label>
                                <textarea id="observacion" class="form-control @error('observacion') is-invalid @enderror" rows="3" wire:model="observacion" maxlength="2000"></textarea>
                                @error('observacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1" wire:loading.attr="disabled" wire:target="guardar">
                                    <span wire:loading.remove wire:target="guardar"><i class="bi bi-save me-1"></i>Guardar programación</span>
                                    <span wire:loading wire:target="guardar"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Guardando...</span>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" wire:click="cancelar">
                                    <i class="bi bi-eraser me-1"></i>Limpiar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Listado --}}
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <h3 class="card-title mb-0"><i class="bi bi-list-ul me-2"></i>Programaciones registradas</h3>
                        <span class="badge text-bg-secondary">{{ $programaciones->count() }} registros</span>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Frecuencia</th>
                                        <th>Próxima fecha</th>
                                        <th class="text-end">Valor</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($programaciones as $programacion)
                                        @php
                                            $hoy = now()->toDateString();
                                            $fechaInicio = $programacion->fecha_inicio->format('Y-m-d');
                                            $fechaProxima = $programacion->proxima_fecha->format('Y-m-d');
                                            $fechaFin = $programacion->fecha_fin?->format('Y-m-d');
                                            $finalizadaPorFecha = $fechaFin !== null && $fechaProxima > $fechaFin;
                                            $fechaFinalVencida = $fechaFin !== null && $fechaFin < $hoy;
                                            $fechasIncoherentes = $fechaProxima < $fechaInicio;
                                            $pendiente = $fechaProxima > $hoy;
                                        @endphp
                                        <tr wire:key="programacion-{{ $programacion->id }}">
                                            <td>
                                                <span class="badge {{ $programacion->tipo === 'ingreso' ? 'text-bg-success' : 'text-bg-danger' }}">
                                                    {{ ucfirst($programacion->tipo) }}
                                                </span>
                                                <div class="fw-semibold mt-1">{{ $programacion->concepto }}</div>
                                                <div class="small text-body-secondary">
                                                    {{ $programacion->cuenta?->nombre ?? 'Sin cuenta' }}
                                                    @if ($programacion->categoria)
                                                        · {{ $programacion->categoria->nombre }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ ucfirst($programacion->frecuencia) }}</td>
                                            <td class="text-nowrap">
                                                {{ $programacion->proxima_fecha->format('d/m/Y') }}
                                                @if ($fechaFinalVencida && !$finalizadaPorFecha)
                                                    <div class="small text-warning-emphasis">Fecha final vencida</div>
                                                @endif
                                            </td>
                                            <td class="text-end text-nowrap fw-semibold">${{ number_format($programacion->valor, 0, ',', '.') }}</td>
                                            <td>
                                                @if ($finalizadaPorFecha)
                                                    <span class="badge text-bg-dark">Finalizada</span>
                                                @elseif ($programacion->activo && $pendiente)
                                                    <span class="badge text-bg-info">Pendiente</span>
                                                @elseif ($programacion->activo)
                                                    <span class="badge text-bg-success">Activa</span>
                                                @else
                                                    <span class="badge text-bg-secondary">Inactiva</span>
                                                @endif
                                                @if ($fechasIncoherentes)
                                                    <div class="small text-danger mt-1">Revisar fechas</div>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex flex-wrap justify-content-end gap-1">
                                                    @if ($programacion->activo)
                                                        @if ($finalizadaPorFecha)
                                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="No hay vencimientos pendientes">
                                                                <i class="bi bi-check2-circle me-1"></i>Finalizada
                                                            </button>
                                                        @elseif ($pendiente)
                                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Disponible desde {{ $programacion->proxima_fecha->format('d/m/Y') }}">
                                                                <i class="bi bi-calendar-event me-1"></i>Pendiente
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-sm btn-primary" wire:click="registrarMovimiento({{ $programacion->id }})"
                                                                wire:confirm="¿Confirmas registrar este movimiento real?" wire:loading.attr="disabled"
                                                                wire:target="registrarMovimiento({{ $programacion->id }})">
                                                                <span wire:loading.remove wire:target="registrarMovimiento({{ $programacion->id }})"><i class="bi bi-check-circle me-1"></i>Registrar</span>
                                                                <span wire:loading wire:target="registrarMovimiento({{ $programacion->id }})"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Registrando...</span>
                                                            </button>
                                                        @endif

                                                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="cambiarEstado({{ $programacion->id }})"
                                                            wire:confirm="¿Confirmas desactivar esta programación?" wire:loading.attr="disabled"
                                                            wire:target="cambiarEstado({{ $programacion->id }})">
                                                            <i class="bi bi-pause-circle me-1"></i>Desactivar
                                                        </button>
                                                    @else
                                                        @if (!$finalizadaPorFecha && !$fechaFinalVencida && !$fechasIncoherentes)
                                                            <button type="button" class="btn btn-sm btn-outline-success" wire:click="cambiarEstado({{ $programacion->id }})"
                                                                wire:confirm="¿Confirmas activar esta programación?" wire:loading.attr="disabled"
                                                                wire:target="cambiarEstado({{ $programacion->id }})">
                                                                <i class="bi bi-play-circle me-1"></i>Activar
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="La programación no se puede reactivar con las fechas actuales">
                                                                <i class="bi bi-lock me-1"></i>No reactivable
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <i class="bi bi-arrow-repeat fs-2 text-body-secondary"></i>
                                                <p class="text-body-secondary mt-2 mb-0">Todavía no tienes programaciones recurrentes.</p>
                                                <p class="text-body-secondary small mb-0">Usa el formulario para crear la primera.</p>
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
</div>
