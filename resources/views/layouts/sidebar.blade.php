<div class="bg-success text-white vh-100 p-3">
        <img src="{{ asset('images/cas_sidebar.png') }}" alt="CAS" class="d-block mx-auto mb-3" style="max-height: 120px;  filter: drop-shadow(0px 8px 16px rgba(0, 0, 0, 0.3));">

    <hr>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link text-white">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item mt-3">
           <h6 class="text-uppercase text-light mt-4 mb-3">Seguridad</h6>

        </li>
        <li class="nav-item">
            <a href="{{ route('usuarios.index') }}"class="nav-link text-white">
                <i class="bi bi-people-fill me-2"></i>
                Usuarios
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('roles.index') }}"
            class="nav-link text-white">
                <i class="bi bi-person-badge-fill me-2"></i>
                Roles
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white">
                <i class="bi bi-shield-lock-fill me-2"></i>
                Permisos
            </a>
        </li>

    </ul>

</div>
