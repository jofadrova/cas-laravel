<div class="bg-success text-white h-100 p-3" style="min-height:100vh;">
        <img src="{{ asset('images/cas_sidebar.png') }}" alt="CAS" class="d-block mx-auto mb-3" style="max-height: 120px;  filter: drop-shadow(0px 8px 16px rgba(0, 0, 0, 0.3));">

    <hr>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}"
            class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active-menu' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item mt-3">
           <h6 class="text-uppercase text-light mt-4 mb-3">Seguridad</h6>

        </li>
        @can('usuarios.ver')
        <li class="nav-item">
            <a href="{{ route('usuarios.index') }}"
            class="nav-link text-white {{ request()->routeIs('usuarios.*') ? 'active-menu' : '' }}">
                <i class="bi bi-people-fill me-2"></i>
                Usuarios
            </a>
        </li>
        @endcan
        @can('roles.ver')
        <li class="nav-item">
            <a href="{{ route('roles.index') }}"
            class="nav-link text-white {{ request()->routeIs('roles.*') ? 'active-menu' : '' }}">
                <i class="bi bi-person-badge-fill me-2"></i>
                Roles
            </a>
        </li>
        @endcan
        @can('permisos.ver')
        <li class="nav-item">
            <a href="{{ route('permisos.index') }}"
            class="nav-link text-white {{ request()->routeIs('permisos.*') ? 'active-menu' : '' }}">
                <i class="bi bi-shield-lock-fill me-2"></i>
                <span>Permisos</span>
            </a>
        </li>
        @endcan
        <li class="nav-item mt-3">
            <h6 class="text-uppercase text-light mt-4 mb-3">
                Socios
            </h6>
        </li>

        @can('socios.ver')
        <li class="nav-item">
            <a href="{{ route('socios.index') }}"
            class="nav-link text-white
            {{
                request()->routeIs(
                    'socios.index',
                    'socios.create',
                    'socios.store',
                    'socios.show',
                    'socios.edit',
                    'socios.update'
                )
                ? 'active-menu'
                : ''
            }}">
                <i class="bi bi-people me-2"></i>
                Gestión de Socios
            </a>
        </li>
        @endcan
        @can('socios.reportes')
        <li class="nav-item">
            <a href="{{ route('socios.reportes') }}"
            class="nav-link text-white {{ request()->routeIs('socios.reportes') ? 'active-menu' : '' }}">
                <i class="bi bi-file-earmark-bar-graph me-2"></i>
                Reporte Socios
            </a>
        </li>
        @endcan
        <li class="nav-item mt-3">
            <h6 class="text-uppercase text-light mt-4 mb-3">
                Prestamos
            </h6>
        </li>

        <li class="nav-item">
            <a href="{{ route('prestamos.index') }}" class="nav-link text-white
            {{ request()->routeIs('prestamos.index') ? 'active:menu' : '' }}">
                <i class="bi bi-cash-coin me-2"></i>
                Gestión de Préstamos
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('prestamos.tipos.index') }}" class="nav-link text-white
            {{ request()->routeIs('prestamos.tipos.*') ? 'active-menu' : '' }}">
                <i class="bi bi-tags me-2"></i>
                Tipo de Préstamos
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('prestamos.proyeccion') }}"
            class="nav-link text-white">
                <i class="bi bi-graph-up-arrow me-2"></i>
                Proyección de Préstamos
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('prestamos.depositos') }}"
            class="nav-link text-white">
                <i class="bi bi-piggy-bank me-2"></i>
                Registro de Depósitos
            </a>
        </li>

    </ul>

</div>
