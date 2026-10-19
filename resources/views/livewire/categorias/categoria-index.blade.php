
<div>
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Categorías</h1>
                <p class="text-body-secondary mb-0">
                    Organiza tus ingresos y gastos.
                </p>
            </div>

            <button type="button" class="btn btn-primary" wire:click="crear">
                <i class="bi bi-plus-lg me-1"></i>
                Nueva categoría
            </button>
        </div>

        @if (session()->has('mensaje'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('mensaje') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Cerrar"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-5">
                        <label for="busqueda" class="form-label">Buscar</label>
                        <input id="busqueda" type="search" class="form-control"
                            wire:model.live.debounce.300ms="busqueda"
                            placeholder="Nombre de categoría">
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="filtroTipo" class="form-label">Tipo</label>
                        <select id="filtroTipo" class="form-select"
                            wire:model.live="filtroTipo">
                            <option value="todos">Todos</option>
                            <option value="ingreso">Ingresos</option>
                            <option value="gasto">Gastos</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="filtroEstado" class="form-label">Estado</label>
                        <select id="filtroEstado" class="form-select"
                            wire:model.live="filtroEstado">
                            <option value="todas">Todas</option>
                            <option value="activas">Activas</option>
                            <option value="inactivas">Inactivas</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        @if ($mostrarFormulario)
            <div class="card border-primary shadow-sm mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">
                        {{ $categoriaId ? 'Editar categoría' : 'Nueva categoría' }}
                    </h2>
                </div>

                <div class="card-body">
                    <form wire:submit="guardar">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="nombre" class="form-label">
                                    Nombre *
                                </label>
                                <input id="nombre" type="text" class="form-control"
                                    wire:model="nombre" maxlength="100"
                                    placeholder="Ej. Alimentación">

                                @error('nombre')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="tipo" class="form-label">Tipo *</label>
                                <select id="tipo" class="form-select" wire:model="tipo">
                                    <option value="gasto">Gasto</option>
                                    <option value="ingreso">Ingreso</option>
                                </select>

                                @error('tipo')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="icono" class="form-label">
                                    Icono (opcional)
                                </label>
                                <input id="icono" type="text" class="form-control"
                                    wire:model="icono" maxlength="50"
                                    placeholder="Ej. bi bi-cart">

                                <div class="form-text">
                                    Puedes usar una clase de Bootstrap Icons.
                                </div>

                                @error('icono')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="color" class="form-label">Color</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input id="color" type="color"
                                        class="form-control form-control-color"
                                        wire:model.live="color" title="Seleccionar color">
                                    <span>{{ $color }}</span>
                                </div>

                                @error('color')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input id="activa" type="checkbox"
                                        class="form-check-input" wire:model="activa">
                                    <label for="activa" class="form-check-label">
                                        Categoría activa
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary"
                                wire:loading.attr="disabled"
                                wire:target="guardar">
                                <span wire:loading.remove wire:target="guardar">
                                    Guardar categoría
                                </span>
                                <span wire:loading wire:target="guardar">
                                    Guardando...
                                </span>
                            </button>

                            <button type="button" class="btn btn-outline-secondary"
                                wire:click="cancelar">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Listado de categorías</h2>
                <span class="badge text-bg-secondary">
                    {{ $categorias->total() }} registros
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Color</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($categorias as $categoria)
                                <tr wire:key="categoria-{{ $categoria->id }}">
                                    <td>
                                        @if ($categoria->icono)
                                            <i class="{{ $categoria->icono }} me-2"></i>
                                        @endif
                                        <strong>{{ $categoria->nombre }}</strong>
                                    </td>

                                    <td>
                                        @if ($categoria->tipo === 'ingreso')
                                            <span class="badge text-bg-success">Ingreso</span>
                                        @else
                                            <span class="badge text-bg-danger">Gasto</span>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="d-inline-block rounded-circle border me-1"
                                            style="width: 16px; height: 16px; background-color: {{ $categoria->color ?: '#6c757d' }};">
                                        </span>
                                        <span>{{ $categoria->color ?: 'Sin color' }}</span>
                                    </td>

                                    <td>
                                        @if ($categoria->activa)
                                            <span class="badge text-bg-success">Activa</span>
                                        @else
                                            <span class="badge text-bg-secondary">Inactiva</span>
                                        @endif
                                    </td>

                                    <td class="text-end text-nowrap">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            wire:click="editar({{ $categoria->id }})"
                                            title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <button type="button"
                                            class="btn btn-sm {{ $categoria->activa ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                            wire:click="cambiarEstado({{ $categoria->id }})"
                                            wire:confirm="¿Deseas {{ $categoria->activa ? 'desactivar' : 'activar' }} esta categoría?"
                                            title="{{ $categoria->activa ? 'Desactivar' : 'Activar' }}">
                                            <i class="bi {{ $categoria->activa ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-body-secondary py-5">
                                        No se encontraron categorías.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($categorias->hasPages())
                <div class="card-footer">
                    {{ $categorias->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
