<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-2 p-0">
                    @include('layouts.sidebar')
                </div>
                <div class="col-md-10 p-0">
                    @include('layouts.topbar')
                    <main class="p-4">
                        @if (isset($header))
                            <h3 class="mb-4">
                                {{ $header }}
                            </h3>
                        @endif
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
