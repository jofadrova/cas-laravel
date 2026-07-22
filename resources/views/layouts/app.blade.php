<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SCAS') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/favicon-96x96.png') }}">
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body>
        <div class="container-fluid p-0">
            <div class="app-shell">
                <aside class="app-sidebar">
                    @include('layouts.sidebar')
                </aside>
                <div class="app-content">
                    @include('layouts.topbar')
                    <main class="p-4">
                        @if (isset($header))
                            <div class="scas-page-header mb-4">
                                <h3 class="mb-0">
                                    {{ $header }}
                                </h3>
                            </div>
                        @endif
                        {{ $slot }}
                    </main>
                    <div id="scas-notify" class="position-fixed top-0 end-0 p-3" style="z-index:2000;width:380px;"></div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalConfirmarOperacion" tabindex="-1"
            aria-labelledby="tituloConfirmarOperacion" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="tituloConfirmarOperacion">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            <span id="textoTituloConfirmarOperacion">Confirmar Acción</span>
                        </h5>
                    </div>
                    <div class="modal-body">
                        <p id="mensajeConfirmarOperacion" class="mb-3"></p>
                        <div id="detallesConfirmarOperacion"
                            class="border rounded bg-light px-3 py-2 d-none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" id="btnConfirmarOperacion">
                            Confirmar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
