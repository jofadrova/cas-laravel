<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>CAS R.L.</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
</head>

<body>

    @yield('content')

</body>

</html>
