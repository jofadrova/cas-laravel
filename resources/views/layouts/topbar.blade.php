<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold text-success">
            CAS R.L.
        </span>
        <div class="ms-auto d-flex align-items-center">
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
