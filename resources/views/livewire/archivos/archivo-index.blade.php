<div>
    @section('page_title', 'Archivos')
    @section('page_description', 'Guarda y organiza tus documentos en un solo lugar.')

    <div class="container-fluid pb-4">
        @if (session()->has('mensaje'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="status">
                <i class="bi bi-check-circle me-2"></i>{{ session('mensaje') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-4">
                <div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
                    <span class="rounded-3 bg-primary-subtle text-primary p-3 fs-4"><i class="bi bi-file-earmark"></i></span>
                    <div><div class="text-body-secondary small">Archivos guardados</div><div class="fs-4 fw-semibold">{{ number_format($totalArchivos, 0, ',', '.') }}</div></div>
                </div></div>
            </div>
            <div class="col-6 col-sm-4">
                <div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
                    <span class="rounded-3 bg-warning-subtle text-warning p-3 fs-4"><i class="bi bi-folder2"></i></span>
                    <div><div class="text-body-secondary small">Carpetas</div><div class="fs-4 fw-semibold">{{ number_format($totalCarpetas, 0, ',', '.') }}</div></div>
                </div></div>
            </div>
            <div class="col-6 col-sm-4">
                <div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
                    <span class="rounded-3 bg-success-subtle text-success p-3 fs-4"><i class="bi bi-hdd"></i></span>
                    <div><div class="text-body-secondary small">Espacio utilizado</div><div class="fs-4 fw-semibold">{{ Illuminate\Support\Number::fileSize($espacioUsado, precision: 1) }}</div></div>
                </div></div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h2 class="h5 mb-1">Subir documentos</h2>
                        <p class="text-body-secondary small mb-0">Hasta 20 archivos por carga, 50 MB cada uno.</p>
                    </div>
                    <button type="button" class="btn btn-outline-primary" wire:click="$set('dialogo', 'carpeta')">
                        <i class="bi bi-folder-plus me-1"></i> Nueva carpeta
                    </button>
                </div>
                <div class="row g-2 align-items-start">
                    <div class="col-12 col-lg">
                        <input type="file" class="form-control" wire:model="uploads" multiple
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.gif,.webp,.zip,.ppt,.pptx">
                        @error('uploads') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        @foreach ($errors->get('uploads.*') as $messages)
                            @foreach ($messages as $message)
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @endforeach
                        @endforeach
                        @if ($uploads)
                            <div class="small text-body-secondary mt-2">
                                <i class="bi bi-paperclip me-1"></i>{{ count($uploads) }} archivo(s) listo(s) para subir
                                @foreach ($uploads as $upload)
                                    <span class="badge text-bg-light border ms-1">{{ $upload->getClientOriginalName() }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="col-12 col-lg-auto">
                        <button type="button" class="btn btn-primary w-100" wire:click="subirArchivos" wire:loading.attr="disabled" wire:target="uploads,subirArchivos">
                            <span wire:loading.remove wire:target="subirArchivos"><i class="bi bi-cloud-arrow-up me-1"></i> Subir archivos</span>
                            <span wire:loading wire:target="subirArchivos"><span class="spinner-border spinner-border-sm me-1"></span> Subiendo…</span>
                        </button>
                    </div>
                </div>
                <div wire:loading wire:target="uploads" class="small text-primary mt-2"><span class="spinner-border spinner-border-sm me-1"></span> Preparando archivos…</div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h2 class="h5 mb-0 me-2">Mis archivos</h2>
                    <nav aria-label="Ruta de carpetas">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item {{ $carpeta ? '' : 'active' }}">
                                @if ($carpeta)<button class="btn btn-link btn-sm p-0 text-decoration-none" wire:click="abrirCarpeta(null)">Inicio</button>@else Inicio @endif
                            </li>
                            @foreach ($migas as $miga)
                                <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                    @if (!$loop->last)<button class="btn btn-link btn-sm p-0 text-decoration-none" wire:click="abrirCarpeta({{ $miga['id'] }})">{{ $miga['name'] }}</button>@else {{ $miga['name'] }} @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                </div>
                <span class="badge text-bg-light border">{{ $elementos->total() }} elementos</span>
            </div>

            <div class="card-body border-bottom">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-body"><i class="bi bi-search"></i></span>
                            <input type="search" class="form-control" placeholder="Buscar en esta carpeta…" wire:model.live.debounce.300ms="busqueda" aria-label="Buscar archivos">
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <select class="form-select" wire:model.live="tipo" aria-label="Filtrar por tipo">
                            <option value="todos">Todos los elementos</option>
                            <option value="carpetas">Carpetas</option>
                            <option value="archivos">Archivos</option>
                            <option value="imagenes">Imágenes</option>
                            <option value="documentos">Documentos</option>
                            <option value="pdf">PDF</option>
                            <option value="hojas">Hojas de cálculo</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <select class="form-select" wire:model.live="orden" aria-label="Ordenar archivos">
                            <option value="nombre">Nombre</option>
                            <option value="recientes">Más recientes</option>
                            <option value="grandes">Mayor tamaño</option>
                            <option value="tipo">Tipo de archivo</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-1 d-flex justify-content-md-end">
                        <div class="btn-group" role="group" aria-label="Tipo de vista">
                            <button class="btn {{ $vista === 'cuadricula' ? 'btn-primary' : 'btn-outline-secondary' }}" wire:click="$set('vista', 'cuadricula')" aria-label="Vista de cuadrícula"><i class="bi bi-grid-3x3-gap"></i></button>
                            <button class="btn {{ $vista === 'lista' ? 'btn-primary' : 'btn-outline-secondary' }}" wire:click="$set('vista', 'lista')" aria-label="Vista de lista"><i class="bi bi-list-ul"></i></button>
                        </div>
                    </div>
                </div>

                @if (count($seleccionados))
                    <div class="alert alert-primary d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 mt-3 mb-0">
                        <span>{{ count($seleccionados) }} elemento(s) seleccionado(s)</span>
                        <button class="btn btn-sm btn-outline-danger" wire:click="eliminarSeleccionados" wire:confirm="¿Eliminar los elementos seleccionados y todo su contenido?">
                            <i class="bi bi-trash me-1"></i> Eliminar selección
                        </button>
                    </div>
                @endif
            </div>

            <div class="card-body">
                @if ($elementos->count())
                    @if ($vista === 'cuadricula')
                        <div class="row g-3">
                            @foreach ($elementos as $elemento)
                                @php
                                    $icono = $elemento->is_folder ? 'bi-folder-fill text-warning' : match ($elemento->extension) {
                                        'pdf' => 'bi-file-earmark-pdf text-danger',
                                        'jpg', 'jpeg', 'png', 'gif', 'webp' => 'bi-file-earmark-image text-success',
                                        'xls', 'xlsx', 'csv' => 'bi-file-earmark-spreadsheet text-success',
                                        'doc', 'docx', 'ppt', 'pptx' => 'bi-file-earmark-word text-primary',
                                        'zip' => 'bi-file-earmark-zip text-warning',
                                        default => 'bi-file-earmark-text text-secondary',
                                    };
                                    $previsualizable = in_array($elemento->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'], true);
                                @endphp
                                <div class="col-12 col-sm-6 col-lg-4 col-xl-3" wire:key="archivo-card-{{ $elemento->id }}">
                                    <div class="border rounded-3 h-100 p-3 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <label class="form-check mb-0"><input class="form-check-input" type="checkbox" value="{{ $elemento->id }}" wire:model.live="seleccionados" aria-label="Seleccionar {{ $elemento->name }}"></label>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown" aria-label="Acciones para {{ $elemento->name }}"><i class="bi bi-three-dots-vertical"></i></button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @if (!$elemento->is_folder)
                                                        <li><button class="dropdown-item" wire:click="descargar({{ $elemento->id }})"><i class="bi bi-download me-2"></i>Descargar</button></li>
                                                    @endif
                                                    <li><button class="dropdown-item" wire:click="prepararRenombrar({{ $elemento->id }})"><i class="bi bi-pencil me-2"></i>Renombrar</button></li>
                                                    <li><button class="dropdown-item" wire:click="prepararMover({{ $elemento->id }})"><i class="bi bi-folder-symlink me-2"></i>Mover</button></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><button class="dropdown-item text-danger" wire:click="confirmarEliminar({{ $elemento->id }})"><i class="bi bi-trash me-2"></i>Eliminar</button></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="text-center py-3">
                                            @if ($elemento->is_folder)
                                                <button class="btn btn-link p-0" wire:click="abrirCarpeta({{ $elemento->id }})" aria-label="Abrir carpeta {{ $elemento->name }}"><i class="bi {{ $icono }}" style="font-size:3rem"></i></button>
                                            @else
                                                <i class="bi {{ $icono }}" style="font-size:3rem"></i>
                                            @endif
                                        </div>
                                        <div class="mt-auto">
                                            @if ($elemento->is_folder)
                                                <button class="btn btn-link text-body fw-semibold text-decoration-none p-0 text-truncate w-100 text-start" wire:click="abrirCarpeta({{ $elemento->id }})">{{ $elemento->name }}</button>
                                                <div class="small text-body-secondary">Carpeta · {{ $elemento->children_count }} elementos</div>
                                            @else
                                                <div class="fw-semibold text-truncate" title="{{ $elemento->name }}">{{ $elemento->name }}</div>
                                                <div class="small text-body-secondary d-flex justify-content-between gap-2"><span>{{ strtoupper($elemento->extension ?: 'ARCHIVO') }}</span><span>{{ Illuminate\Support\Number::fileSize($elemento->size, precision: 1) }}</span></div>
                                                <div class="small text-body-secondary">{{ $elemento->created_at->format('d/m/Y') }}</div>
                                                @if ($previsualizable)<a class="small" href="{{ route('archivos.preview', $elemento) }}" target="_blank" rel="noopener">Vista previa</a>@endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead><tr><th style="width:40px"></th><th>Nombre</th><th>Tipo</th><th>Tamaño</th><th>Modificado</th><th class="text-end">Acciones</th></tr></thead>
                                <tbody>
                                    @foreach ($elementos as $elemento)
                                        @php
                                            $icono = $elemento->is_folder ? 'bi-folder-fill text-warning' : match ($elemento->extension) {
                                                'pdf' => 'bi-file-earmark-pdf text-danger',
                                                'jpg', 'jpeg', 'png', 'gif', 'webp' => 'bi-file-earmark-image text-success',
                                                'xls', 'xlsx', 'csv' => 'bi-file-earmark-spreadsheet text-success',
                                                'doc', 'docx', 'ppt', 'pptx' => 'bi-file-earmark-word text-primary',
                                                default => 'bi-file-earmark-text text-secondary',
                                            };
                                        @endphp
                                        <tr wire:key="archivo-row-{{ $elemento->id }}">
                                            <td><input class="form-check-input" type="checkbox" value="{{ $elemento->id }}" wire:model.live="seleccionados" aria-label="Seleccionar {{ $elemento->name }}"></td>
                                            <td><i class="bi {{ $icono }} me-2"></i>@if ($elemento->is_folder)<button class="btn btn-link p-0 text-body text-decoration-none" wire:click="abrirCarpeta({{ $elemento->id }})">{{ $elemento->name }}</button>@else {{ $elemento->name }} @endif</td>
                                            <td>{{ $elemento->is_folder ? 'Carpeta' : strtoupper($elemento->extension ?: 'Archivo') }}</td>
                                            <td>{{ $elemento->is_folder ? '—' : Illuminate\Support\Number::fileSize($elemento->size, precision: 1) }}</td>
                                            <td>{{ $elemento->updated_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-end text-nowrap">
                                                @if (!$elemento->is_folder)<button class="btn btn-sm btn-light" wire:click="descargar({{ $elemento->id }})" title="Descargar"><i class="bi bi-download"></i></button>@endif
                                                <button class="btn btn-sm btn-light" wire:click="prepararRenombrar({{ $elemento->id }})" title="Renombrar"><i class="bi bi-pencil"></i></button>
                                                <button class="btn btn-sm btn-light" wire:click="prepararMover({{ $elemento->id }})" title="Mover"><i class="bi bi-folder-symlink"></i></button>
                                                <button class="btn btn-sm btn-outline-danger" wire:click="confirmarEliminar({{ $elemento->id }})" title="Eliminar"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="mt-3">{{ $elementos->links() }}</div>
                @else
                    <div class="text-center py-5">
                        <div class="display-5 text-body-tertiary mb-3"><i class="bi bi-folder2-open"></i></div>
                        <h3 class="h5">{{ $busqueda || $tipo !== 'todos' ? 'No encontramos elementos' : 'Esta carpeta está vacía' }}</h3>
                        <p class="text-body-secondary mb-0">{{ $busqueda || $tipo !== 'todos' ? 'Prueba con otra búsqueda o cambia el filtro.' : 'Sube un archivo o crea una carpeta para empezar.' }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($dialogo)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3" style="z-index:1055;background:rgba(0,0,0,.45)" wire:click.self="cerrarDialogo">
            <div class="card shadow-lg w-100" style="max-width:480px" role="dialog" aria-modal="true">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">{{ ['carpeta' => 'Nueva carpeta', 'renombrar' => 'Renombrar elemento', 'mover' => 'Mover elemento', 'eliminar' => 'Eliminar elemento'][$dialogo] ?? 'Confirmar' }}</h2>
                    <button class="btn-close" wire:click="cerrarDialogo" aria-label="Cerrar"></button>
                </div>
                <div class="card-body">
                    @if ($dialogo === 'carpeta')
                        <label class="form-label" for="nombreCarpeta">Nombre de la carpeta</label>
                        <input id="nombreCarpeta" class="form-control" wire:model="nombreCarpeta" wire:keydown.enter="crearCarpeta" maxlength="120" autofocus>
                        @error('nombreCarpeta')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @elseif ($dialogo === 'renombrar')
                        <label class="form-label" for="nuevoNombre">Nuevo nombre</label>
                        <input id="nuevoNombre" class="form-control" wire:model="nuevoNombre" wire:keydown.enter="renombrar" maxlength="180" autofocus>
                        @error('nuevoNombre')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @elseif ($dialogo === 'mover')
                        <label class="form-label" for="destinoId">Carpeta de destino</label>
                        <select id="destinoId" class="form-select" wire:model="destinoId">
                            <option value="">Inicio / raíz</option>
                            @foreach ($carpetasDestino as $destino)<option value="{{ $destino->id }}">{{ $destino->name }}</option>@endforeach
                        </select>
                        <p class="small text-body-secondary mt-2 mb-0">No puedes mover una carpeta dentro de sí misma o de una carpeta que contiene.</p>
                    @else
                        <p class="mb-0">Se eliminará este elemento y, si es una carpeta, todo su contenido. Esta acción no se puede deshacer.</p>
                    @endif
                </div>
                <div class="card-footer bg-transparent d-flex justify-content-end gap-2">
                    <button class="btn btn-light" wire:click="cerrarDialogo">Cancelar</button>
                    @if ($dialogo === 'carpeta')<button class="btn btn-primary" wire:click="crearCarpeta">Crear carpeta</button>
                    @elseif ($dialogo === 'renombrar')<button class="btn btn-primary" wire:click="renombrar">Guardar nombre</button>
                    @elseif ($dialogo === 'mover')<button class="btn btn-primary" wire:click="mover">Mover</button>
                    @else<button class="btn btn-danger" wire:click="eliminar">Eliminar</button>@endif
                </div>
            </div>
        </div>
    @endif
</div>
