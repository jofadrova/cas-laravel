@php
    $seguridadActiva = request()->routeIs('usuarios.*', 'roles.*', 'permisos.*');
    $sociosActiva = request()->routeIs('socios.*');
    $prestamosActiva = request()->routeIs('prestamos.*');
    $procesamientoActivo = request()->routeIs('procesamiento-mensual.*');
    $contabilidadActiva = request()->routeIs('contabilidad.*');
@endphp

<div id="sidebarMenu" class="sidebar-menu scas-page-header text-white h-100 p-3">
    <div class="sidebar-header">
        <img src="{{ asset('images/cas_sidebar.png') }}" alt="CAS" class="sidebar-logo d-block mx-auto mb-3">
        <button type="button" id="sidebarToggle" class="sidebar-toggle btn btn-light btn-sm"
            aria-label="Contraer menú lateral" aria-controls="sidebarMenu" aria-expanded="true" title="Contraer menú">
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
        </button>
    </div>
    <hr>

    <ul class="nav flex-column sidebar-navigation">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}"
                class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active-menu' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i>
                <span><strong>Dashboard</strong></span>
            </a>
        </li>

        <li class="nav-item mt-2">
            <button class="nav-link sidebar-section-toggle text-white w-100 {{ $seguridadActiva ? 'active-section' : 'collapsed' }}"
                type="button" data-bs-toggle="collapse" data-bs-target="#sidebarSeguridad"
                aria-expanded="{{ $seguridadActiva ? 'true' : 'false' }}" aria-controls="sidebarSeguridad">
                <i class="bi bi-shield-lock me-2"></i>
                <span><strong>Seguridad</strong></span>
                <i class="bi bi-chevron-down sidebar-section-chevron ms-auto"></i>
            </button>
            <div id="sidebarSeguridad" class="collapse {{ $seguridadActiva ? 'show' : '' }}">
                <ul class="nav flex-column sidebar-submenu">
                    @can('usuarios.ver')
                        <li class="nav-item">
                            <a href="{{ route('usuarios.index') }}"
                                class="nav-link text-white {{ request()->routeIs('usuarios.*') ? 'active-menu' : '' }}">
                                <i class="bi bi-people-fill me-2"></i><span>Usuarios</span>
                            </a>
                        </li>
                    @endcan
                    @can('roles.ver')
                        <li class="nav-item">
                            <a href="{{ route('roles.index') }}"
                                class="nav-link text-white {{ request()->routeIs('roles.*') ? 'active-menu' : '' }}">
                                <i class="bi bi-person-badge-fill me-2"></i><span><strong>Roles</strong></span>
                            </a>
                        </li>
                    @endcan
                    @can('permisos.ver')
                        <li class="nav-item">
                            <a href="{{ route('permisos.index') }}"
                                class="nav-link text-white {{ request()->routeIs('permisos.*') ? 'active-menu' : '' }}">
                                <i class="bi bi-shield-lock-fill me-2"></i><span><strong>Permisos</strong></span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        </li>

        <li class="nav-item mt-2">
            <button class="nav-link sidebar-section-toggle text-white w-100 {{ $sociosActiva ? 'active-section' : 'collapsed' }}"
                type="button" data-bs-toggle="collapse" data-bs-target="#sidebarSocios"
                aria-expanded="{{ $sociosActiva ? 'true' : 'false' }}" aria-controls="sidebarSocios">
                <i class="bi bi-people me-2"></i>
                <span><strong>Socios</strong></span>
                <i class="bi bi-chevron-down sidebar-section-chevron ms-auto"></i>
            </button>
            <div id="sidebarSocios" class="collapse {{ $sociosActiva ? 'show' : '' }}">
                <ul class="nav flex-column sidebar-submenu">
                    @can('socios.ver')
                        <li class="nav-item">
                            <a href="{{ route('socios.index') }}"
                                class="nav-link text-white {{ request()->routeIs('socios.index', 'socios.create', 'socios.store', 'socios.show', 'socios.edit', 'socios.update') ? 'active-menu' : '' }}">
                                <i class="bi bi-people me-2"></i><span>Gestión de Socios</span>
                            </a>
                        </li>
                    @endcan
                    @can('socios.reportes')
                        <li class="nav-item">
                            <a href="{{ route('socios.reportes') }}"
                                class="nav-link text-white {{ request()->routeIs('socios.reportes') ? 'active-menu' : '' }}">
                                <i class="bi bi-file-earmark-bar-graph me-2"></i><span>Reporte Socios</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        </li>

        <li class="nav-item mt-2">
            <button class="nav-link sidebar-section-toggle text-white w-100 {{ $prestamosActiva ? 'active-section' : 'collapsed' }}"
                type="button" data-bs-toggle="collapse" data-bs-target="#sidebarPrestamos"
                aria-expanded="{{ $prestamosActiva ? 'true' : 'false' }}" aria-controls="sidebarPrestamos">
                <i class="bi bi-cash-stack me-2"></i>
                <span><strong>Préstamos</strong></span>
                <i class="bi bi-chevron-down sidebar-section-chevron ms-auto"></i>
            </button>
            <div id="sidebarPrestamos" class="collapse {{ $prestamosActiva ? 'show' : '' }}">
                <ul class="nav flex-column sidebar-submenu">
                    <li class="nav-item">
                        <a href="{{ route('prestamos.index') }}"
                            class="nav-link text-white {{ request()->routeIs('prestamos.index', 'prestamos.create', 'prestamos.store', 'prestamos.edit', 'prestamos.update', 'prestamos.detalle', 'prestamos.pagos', 'prestamos.pagos.*', 'prestamos.refinanciamiento', 'prestamos.refinanciamiento.*', 'prestamos.amortizacion-capital', 'prestamos.amortizacion-capital.*') ? 'active-menu' : '' }}">
                            <i class="bi bi-cash-coin me-2"></i><span>Gestión de Préstamos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('prestamos.tipos.index') }}"
                            class="nav-link text-white {{ request()->routeIs('prestamos.tipos.*') ? 'active-menu' : '' }}">
                            <i class="bi bi-tags me-2"></i><span>Tipo de Préstamos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('prestamos.proyeccion') }}"
                            class="nav-link text-white {{ request()->routeIs('prestamos.proyeccion*') ? 'active-menu' : '' }}">
                            <i class="bi bi-graph-up-arrow me-2"></i><span>Proyección de Préstamos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('prestamos.depositos') }}"
                            class="nav-link text-white {{ request()->routeIs('prestamos.depositos') ? 'active-menu' : '' }}">
                            <i class="bi bi-piggy-bank me-2"></i><span>Registro de Depósitos</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item mt-2">
            <button class="nav-link sidebar-section-toggle text-white w-100 {{ $procesamientoActivo ? 'active-section' : 'collapsed' }}"
                type="button" data-bs-toggle="collapse" data-bs-target="#sidebarProcesamiento"
                aria-expanded="{{ $procesamientoActivo ? 'true' : 'false' }}" aria-controls="sidebarProcesamiento">
                <i class="bi bi-calendar2-check me-2"></i>
                <span><strong>Procesamiento mensual</strong></span>
                <i class="bi bi-chevron-down sidebar-section-chevron ms-auto"></i>
            </button>
            <div id="sidebarProcesamiento" class="collapse {{ $procesamientoActivo ? 'show' : '' }}">
                <ul class="nav flex-column sidebar-submenu">
                    <li class="nav-item">
                        <a href="{{ route('procesamiento-mensual.lotes.index') }}"
                            class="nav-link text-white {{ request()->routeIs('procesamiento-mensual.lotes.*') ? 'active-menu' : '' }}">
                            <i class="bi bi-calendar2-check me-2"></i><span><strong>Lotes mensuales</strong></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('procesamiento-mensual.envios-mensuales.index') }}"
                            class="nav-link text-white {{ request()->routeIs('procesamiento-mensual.envios-mensuales.*') ? 'active-menu' : '' }}">
                            <i class="bi bi-send me-2"></i><span><strong>Envíos mensuales</strong></span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item mt-2">
            <button class="nav-link sidebar-section-toggle text-white w-100 {{ $contabilidadActiva ? 'active-section' : 'collapsed' }}"
                type="button" data-bs-toggle="collapse" data-bs-target="#sidebarContabilidad"
                aria-expanded="{{ $contabilidadActiva ? 'true' : 'false' }}" aria-controls="sidebarContabilidad">
                <i class="bi bi-journal-text me-2"></i>
                <span><strong>Contabilidad</strong></span>
                <i class="bi bi-chevron-down sidebar-section-chevron ms-auto"></i>
            </button>
            <div id="sidebarContabilidad" class="collapse {{ $contabilidadActiva ? 'show' : '' }}">
                <ul class="nav flex-column sidebar-submenu">
                    <li class="nav-item">
                        <a href="{{ route('contabilidad.cuentas.index') }}"
                            class="nav-link text-white {{ request()->routeIs('contabilidad.cuentas.*') ? 'active-menu' : '' }}">
                            <i class="bi bi-diagram-3 me-2"></i><span><strong>Nomenclatura de cuentas</strong></span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
</div>
