<nav class="navbar navbar-expand-lg navbar-light border-bottom shadow-sm scas-topbar">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold text-success">
            CAS R.L.
        </span>
        <div class="ms-auto d-flex align-items-center">
            <li class="nav-item">
                <button id="themeToggle" class="btn btn-outline-secondary btn-sm me-3" title="Cambiar tema">
                    <i class="bi bi-moon-stars-fill"></i>
                </button>                    
            </li>
            <span class="me-3">
                {{ Auth::user()->name }} <br>
                @foreach (auth()->user()->getRoleNames() as $role )
                    {{ $role }}
                @endforeach
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">Salir</button>
            </form>
        </div>
    </div>
</nav>
