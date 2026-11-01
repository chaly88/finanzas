<div>
    @section('page_title', 'Enlaces')
    @section('page_description', 'Guarda tus sitios favoritos y encuentra rápido lo que necesitas.')

    <div class="container-fluid pb-4">
        @if (session()->has('mensaje'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="status">
                <i class="bi bi-check-circle me-2"></i>{{ session('mensaje') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-3 bg-primary-subtle text-primary p-3 fs-4"><i class="bi bi-bookmark-check"></i></span>
                <div><div class="text-body-secondary small">Enlaces guardados</div><div class="fs-4 fw-semibold">{{ number_format($totalEnlaces, 0, ',', '.') }}</div></div>
            </div></div></div>
            <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-3 bg-danger-subtle text-danger p-3 fs-4"><i class="bi bi-heart-fill"></i></span>
                <div><div class="text-body-secondary small">Favoritos</div><div class="fs-4 fw-semibold">{{ number_format($totalFavoritos, 0, ',', '.') }}</div></div>
            </div></div></div>
            <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-3 bg-warning-subtle text-warning p-3 fs-4"><i class="bi bi-folder2"></i></span>
                <div><div class="text-body-secondary small">Carpetas</div><div class="fs-4 fw-semibold">{{ number_format($totalCarpetas, 0, ',', '.') }}</div></div>
            </div></div></div>
            <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-3 bg-success-subtle text-success p-3 fs-4"><i class="bi bi-box-arrow-up-right"></i></span>
                <div><div class="text-body-secondary small">Visitas registradas</div><div class="fs-4 fw-semibold">{{ number_format($totalVisitas, 0, ',', '.') }}</div></div>
            </div></div></div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-3 py-3 pe-0">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <h2 class="h5 mb-0 me-2">Mi colección</h2>
                    <nav aria-label="Ruta de carpetas">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item {{ $carpeta ? '' : 'active' }}">
                                @if ($carpeta)<button class="btn btn-link btn-sm p-0 text-decoration-none" wire:click="abrirCarpeta(null)">Todos los enlaces</button>@else Todos los enlaces @endif
                            </li>
                            @foreach ($migas as $miga)
                                <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                    @if (!$loop->last)<button class="btn btn-link btn-sm p-0 text-decoration-none" wire:click="abrirCarpeta({{ $miga['id'] }})">{{ $miga['title'] }}</button>@else {{ $miga['title'] }} @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2 ms-auto">
                    <button class="btn btn-outline-primary" wire:click="nuevaCarpeta"><i class="bi bi-folder-plus me-1"></i> Carpeta</button>
                    <button class="btn btn-primary" wire:click="nuevoEnlace"><i class="bi bi-plus-lg me-1"></i> Guardar enlace</button>
                </div>
            </div>

            <div class="card-body border-bottom">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-body"><i class="bi bi-search"></i></span>
                            <input type="search" class="form-control" placeholder="Buscar nombre, dirección o etiqueta…" wire:model.live.debounce.300ms="busqueda" aria-label="Buscar enlaces">
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <select class="form-select" wire:model.live="filtro" aria-label="Filtrar enlaces">
                            <option value="todos">Todo</option>
                            <option value="favoritos">Solo favoritos</option>
                            <option value="enlaces">Solo enlaces</option>
                            <option value="carpetas">Solo carpetas</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <select class="form-select" wire:model.live="orden" aria-label="Ordenar enlaces">
                            <option value="recientes">Última actualización</option>
                            <option value="alfabetico">Nombre A–Z</option>
                            <option value="visitas">Más visitados</option>
                            <option value="dominio">Dominio</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-flex justify-content-md-end">
                        <div class="btn-group" role="group" aria-label="Tipo de vista">
                            <button class="btn {{ $vista === 'cuadricula' ? 'btn-primary' : 'btn-outline-secondary' }}" wire:click="$set('vista', 'cuadricula')" aria-label="Vista de tarjetas"><i class="bi bi-grid-3x3-gap"></i></button>
                            <button class="btn {{ $vista === 'lista' ? 'btn-primary' : 'btn-outline-secondary' }}" wire:click="$set('vista', 'lista')" aria-label="Vista de lista"><i class="bi bi-list-ul"></i></button>
                        </div>
                    </div>
                </div>
                <div class="small text-body-secondary mt-2">Puedes pegar una dirección como <code>youtube.com</code>; se guardará automáticamente con HTTPS.</div>
            </div>

            <div class="card-body">
                @if ($elementos->count())
                    @if ($vista === 'cuadricula')
                        <div class="row g-3">
                            @foreach ($elementos as $elemento)
                                <div class="col-12 col-md-6 col-xl-4" wire:key="enlace-card-{{ $elemento->id }}">
                                    <article class="border rounded-3 h-100 p-3 d-flex flex-column">
                                        @if ($elemento->is_folder)
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <span class="rounded-3 bg-warning-subtle text-warning p-3 fs-4"><i class="bi bi-folder-fill"></i></span>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown" aria-label="Acciones de carpeta"><i class="bi bi-three-dots-vertical"></i></button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><button class="dropdown-item" wire:click="prepararRenombrarCarpeta({{ $elemento->id }})"><i class="bi bi-pencil me-2"></i>Renombrar</button></li>
                                                        <li><button class="dropdown-item text-danger" wire:click="eliminarCarpeta({{ $elemento->id }})" wire:confirm="Solo se puede eliminar si la carpeta está vacía. ¿Continuar?"><i class="bi bi-trash me-2"></i>Eliminar carpeta</button></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <button class="btn btn-link text-body fw-semibold text-decoration-none p-0 mt-3 text-start" wire:click="abrirCarpeta({{ $elemento->id }})">{{ $elemento->title }}</button>
                                            <div class="small text-body-secondary mt-1">{{ $elemento->children_count }} elemento(s)</div>
                                            <button class="btn btn-sm btn-outline-secondary mt-auto align-self-start" wire:click="abrirCarpeta({{ $elemento->id }})"><i class="bi bi-folder2-open me-1"></i>Abrir</button>
                                        @else
                                            @php
                                                $host = preg_replace('/^www\./i', '', (string) parse_url($elemento->url, PHP_URL_HOST));
                                            @endphp
                                            <div class="d-flex align-items-start justify-content-between gap-2">
                                                <div class="d-flex align-items-center gap-2 min-w-0">
                                                    <span class="rounded-3 bg-primary-subtle text-primary p-2 fs-5 flex-shrink-0"><i class="bi bi-globe2"></i></span>
                                                    <div class="min-w-0">
                                                        <h3 class="h6 mb-1 text-truncate" title="{{ $elemento->title }}">{{ $elemento->title }}</h3>
                                                        <a class="small text-body-secondary text-truncate d-block" href="{{ route('enlaces.open', $elemento) }}" target="_blank" rel="noopener noreferrer">{{ $host }}</a>
                                                    </div>
                                                </div>
                                                <button class="btn btn-sm {{ $elemento->is_favorite ? 'text-danger' : 'text-body-secondary' }}" wire:click="alternarFavorito({{ $elemento->id }})" title="{{ $elemento->is_favorite ? 'Quitar de favoritos' : 'Añadir a favoritos' }}" aria-label="Alternar favorito"><i class="bi {{ $elemento->is_favorite ? 'bi-heart-fill' : 'bi-heart' }}"></i></button>
                                            </div>
                                            @if ($elemento->description)
                                                <p class="small text-body-secondary mt-3 mb-2">{{ \Illuminate\Support\Str::limit($elemento->description, 130) }}</p>
                                            @else
                                                <div class="mt-3"></div>
                                            @endif
                                            <div class="d-flex flex-wrap gap-1 mb-3">
                                                @foreach ($elemento->tags ?? [] as $tag)<span class="badge rounded-pill text-bg-light border">{{ $tag }}</span>@endforeach
                                            </div>
                                            <div class="small text-body-secondary mb-3"><i class="bi bi-box-arrow-up-right me-1"></i>{{ number_format($elemento->visits, 0, ',', '.') }} visitas
                                                @if ($elemento->last_visited_at)<span class="ms-2">Última: {{ $elemento->last_visited_at->diffForHumans() }}</span>@endif
                                            </div>
                                            <div class="d-flex gap-2 mt-auto">
                                                <a class="btn btn-sm btn-primary flex-grow-1" href="{{ route('enlaces.open', $elemento) }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right me-1"></i>Abrir sitio</a>
                                                <button class="btn btn-sm btn-outline-secondary" x-data="{copiado:false}" data-url="{{ $elemento->url }}" x-on:click="navigator.clipboard.writeText($el.dataset.url).then(() => {copiado=true; setTimeout(() => copiado=false, 1400)})" :title="copiado ? 'Copiado' : 'Copiar dirección'" aria-label="Copiar dirección"><i class="bi" :class="copiado ? 'bi-check2' : 'bi-clipboard'"></i></button>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-label="Más acciones"><i class="bi bi-three-dots"></i></button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><button class="dropdown-item" wire:click="editar({{ $elemento->id }})"><i class="bi bi-pencil me-2"></i>Editar detalles</button></li>
                                                        <li><button class="dropdown-item text-danger" wire:click="confirmarEliminar({{ $elemento->id }})"><i class="bi bi-trash me-2"></i>Eliminar</button></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        @endif
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead><tr><th>Nombre</th><th>Dirección / contenido</th><th>Etiquetas</th><th>Visitas</th><th class="text-end">Acciones</th></tr></thead>
                                <tbody>
                                    @foreach ($elementos as $elemento)
                                        <tr wire:key="enlace-row-{{ $elemento->id }}">
                                            <td class="fw-semibold">
                                                @if ($elemento->is_folder)<i class="bi bi-folder-fill text-warning me-2"></i><button class="btn btn-link p-0 text-body text-decoration-none" wire:click="abrirCarpeta({{ $elemento->id }})">{{ $elemento->title }}</button>
                                                @else <i class="bi bi-globe2 text-primary me-2"></i>{{ $elemento->title }} @endif
                                            </td>
                                            <td>@if (!$elemento->is_folder)<a href="{{ route('enlaces.open', $elemento) }}" target="_blank" rel="noopener noreferrer" class="text-body-secondary">{{ $elemento->url }}</a>@else <span class="text-body-secondary">{{ $elemento->children_count }} elemento(s)</span>@endif</td>
                                            <td>@foreach ($elemento->tags ?? [] as $tag)<span class="badge text-bg-light border me-1">{{ $tag }}</span>@endforeach</td>
                                            <td>{{ $elemento->is_folder ? '—' : number_format($elemento->visits, 0, ',', '.') }}</td>
                                            <td class="text-end text-nowrap">
                                                @if ($elemento->is_folder)
                                                    <button class="btn btn-sm btn-light" wire:click="abrirCarpeta({{ $elemento->id }})" title="Abrir"><i class="bi bi-folder2-open"></i></button>
                                                    <button class="btn btn-sm btn-light" wire:click="prepararRenombrarCarpeta({{ $elemento->id }})" title="Renombrar"><i class="bi bi-pencil"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" wire:click="eliminarCarpeta({{ $elemento->id }})" wire:confirm="Solo se puede eliminar si la carpeta está vacía. ¿Continuar?" title="Eliminar"><i class="bi bi-trash"></i></button>
                                                @else
                                                    <a class="btn btn-sm btn-primary" href="{{ route('enlaces.open', $elemento) }}" target="_blank" rel="noopener noreferrer" title="Abrir"><i class="bi bi-box-arrow-up-right"></i></a>
                                                    <button class="btn btn-sm btn-light" wire:click="alternarFavorito({{ $elemento->id }})" title="Favorito"><i class="bi {{ $elemento->is_favorite ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i></button>
                                                    <button class="btn btn-sm btn-light" wire:click="editar({{ $elemento->id }})" title="Editar"><i class="bi bi-pencil"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" wire:click="confirmarEliminar({{ $elemento->id }})" title="Eliminar"><i class="bi bi-trash"></i></button>
                                                @endif
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
                        <div class="display-5 text-body-tertiary mb-3"><i class="bi bi-bookmark-plus"></i></div>
                        <h3 class="h5">{{ $busqueda || $filtro !== 'todos' ? 'No encontramos enlaces' : 'Tu colección está vacía' }}</h3>
                        <p class="text-body-secondary mb-3">{{ $busqueda || $filtro !== 'todos' ? 'Prueba otra búsqueda o cambia el filtro.' : 'Guarda YouTube, tus páginas favoritas y cualquier sitio que quieras tener a mano.' }}</p>
                        @if (!$busqueda && $filtro === 'todos')<button class="btn btn-primary" wire:click="nuevoEnlace"><i class="bi bi-plus-lg me-1"></i> Guardar mi primer enlace</button>@endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($dialogo)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3" style="z-index:1055;background:rgba(0,0,0,.45)" wire:click.self="cerrarDialogo">
            <div class="card shadow-lg w-100" style="max-width:560px" role="dialog" aria-modal="true">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">{{ ['enlace' => $enlaceId ? 'Editar enlace' : 'Guardar enlace', 'carpeta' => 'Nueva carpeta', 'renombrar-carpeta' => 'Renombrar carpeta', 'eliminar' => 'Eliminar enlace'][$dialogo] ?? 'Enlaces' }}</h2>
                    <button class="btn-close" wire:click="cerrarDialogo" aria-label="Cerrar"></button>
                </div>
                <div class="card-body">
                    @if ($dialogo === 'enlace')
                        <div class="mb-3">
                            <label class="form-label" for="enlaceTitulo">Nombre <span class="text-body-secondary fw-normal">(opcional, se toma del dominio si se deja vacío)</span></label>
                            <input id="enlaceTitulo" class="form-control" wire:model="titulo" maxlength="180" placeholder="Por ejemplo, YouTube">
                            @error('titulo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="enlaceUrl">Dirección del sitio</label>
                            <input id="enlaceUrl" class="form-control" wire:model="url" maxlength="2048" placeholder="youtube.com" inputmode="url" autocomplete="url">
                            @error('url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="enlaceDescripcion">Descripción o notas</label>
                            <textarea id="enlaceDescripcion" class="form-control" wire:model="descripcion" rows="3" maxlength="2000" placeholder="Añade contexto o una nota para encontrarlo después"></textarea>
                            @error('descripcion')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="enlaceEtiquetas">Etiquetas</label>
                            <input id="enlaceEtiquetas" class="form-control" wire:model="etiquetasTexto" maxlength="500" placeholder="video, música, aprendizaje">
                            <div class="form-text">Separa las etiquetas con comas; puedes añadir hasta 15.</div>
                            @error('etiquetasTexto')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="enlaceCarpeta">Guardar en carpeta</label>
                            <select id="enlaceCarpeta" class="form-select" wire:model="destinoId">
                                <option value="">Colección principal</option>
                                @foreach ($carpetas as $folder)<option value="{{ $folder->id }}">{{ $folder->title }}</option>@endforeach
                            </select>
                            @error('destinoId')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" wire:model="favorito">
                            <span class="form-check-label"><i class="bi bi-heart text-danger me-1"></i>Marcar como favorito</span>
                        </label>
                    @elseif ($dialogo === 'carpeta' || $dialogo === 'renombrar-carpeta')
                        <label class="form-label" for="carpetaTitulo">Nombre de la carpeta</label>
                        <input id="carpetaTitulo" class="form-control" wire:model="titulo" wire:keydown.enter="{{ $dialogo === 'carpeta' ? 'crearCarpeta' : 'renombrarCarpeta' }}" maxlength="120" autofocus>
                        @error('titulo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @else
                        <p class="mb-0">Se eliminará este enlace de tu colección. Esta acción no se puede deshacer.</p>
                    @endif
                </div>
                <div class="card-footer bg-transparent d-flex justify-content-end gap-2">
                    <button class="btn btn-light" wire:click="cerrarDialogo">Cancelar</button>
                    @if ($dialogo === 'enlace')<button class="btn btn-primary" wire:click="guardar"><i class="bi bi-bookmark-check me-1"></i>Guardar enlace</button>
                    @elseif ($dialogo === 'carpeta')<button class="btn btn-primary" wire:click="crearCarpeta">Crear carpeta</button>
                    @elseif ($dialogo === 'renombrar-carpeta')<button class="btn btn-primary" wire:click="renombrarCarpeta">Guardar nombre</button>
                    @else<button class="btn btn-danger" wire:click="eliminar">Eliminar enlace</button>@endif
                </div>
            </div>
        </div>
    @endif
</div>
