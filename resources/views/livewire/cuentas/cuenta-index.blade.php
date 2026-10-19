<div>
    @section('page_title', 'Cuentas')
    @section('page_description', 'Administra tus cuentas, bancos y billeteras digitales.')
    @if (session()->has('mensaje'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('mensaje') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-5">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Registrar cuenta</h3>
                </div>

                <form wire:submit="{{ $cuentaId ? 'actualizar' : 'guardar' }}">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre de la cuenta
                            </label>

                            <input id="nombre" type="text" class="form-control" wire:model="nombre"
                                placeholder="Ej. Efectivo">

                            @error('nombre')
                                <div class="text-danger small">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tipo" class="form-label">
                                Tipo de cuenta
                            </label>

                            <select id="tipo" class="form-select" wire:model="tipo">
                                <option value="efectivo">Efectivo</option>
                                <option value="banco">Cuenta bancaria</option>
                                <option value="billetera_digital">
                                    Billetera digital
                                </option>
                                <option value="ahorro">Ahorros</option>
                                <option value="otro">Otro</option>
                            </select>

                            @error('tipo')
                                <div class="text-danger small">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="saldo_inicial" class="form-label">
                                Saldo inicial (COP)
                            </label>

                            <input id="saldo_inicial" type="number" min="0" step="1" class="form-control"
                                wire:model="saldo_inicial" @disabled($cuentaId)>

                            @error('saldo_inicial')
                                <div class="text-danger small">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="fecha_apertura" class="form-label">
                                Fecha de apertura
                            </label>

                            <input id="fecha_apertura" type="date" class="form-control" wire:model="fecha_apertura">

                            @error('fecha_apertura')
                                <div class="text-danger small">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="observacion" class="form-label">
                                Observaciones
                            </label>

                            <textarea id="observacion" class="form-control" rows="3"
                                wire:model="observacion"></textarea>

                            @error('observacion')
                                <div class="text-danger small">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer d-flex gap-2">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">

                            {{ $cuentaId ? 'Actualizar cuenta' : 'Guardar cuenta' }}
                        </button>

                        @if ($cuentaId)
                            <button type="button" class="btn btn-secondary" wire:click="cancelarEdicion">
                                Cancelar
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Mis cuentas</h3>
                </div>

                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Cuenta</th>
                                <th>Tipo</th>
                                <th class="text-end">Saldo inicial</th>
                                <th class="text-end">Saldo actual</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($cuentas as $cuenta)
                                <tr wire:key="cuenta-{{ $cuenta->id }}">
                                    <td>
                                        <strong>{{ $cuenta->nombre }}</strong>
                                    </td>

                                    <td>
                                        {{ str($cuenta->tipo)->replace('_', ' ')->title() }}
                                    </td>

                                    <td class="text-end">
                                        ${{ number_format($cuenta->saldo_inicial, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        ${{ number_format($cuenta->saldo_actual, 0, ',', '.') }}
                                    </td>

                                    <td>
                                        @if ($cuenta->activa)
                                            <span class="badge text-bg-success">
                                                Activa
                                            </span>
                                        @else
                                            <span class="badge text-bg-secondary">
                                                Inactiva
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            wire:click="editar({{ $cuenta->id }})">
                                            <i class="bi bi-pencil"></i>
                                            Editar
                                        </button>

                                        <button type="button"
                                            class="btn btn-sm {{ $cuenta->activa ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                            wire:click="cambiarEstado({{ $cuenta->id }})"
                                            wire:confirm="¿Deseas {{ $cuenta->activa ? 'desactivar' : 'activar' }} esta cuenta?">
                                            <i class="bi {{ $cuenta->activa ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                            {{ $cuenta->activa ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Todavía no tienes cuentas registradas.
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