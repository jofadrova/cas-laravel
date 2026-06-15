<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <style>body {background: #f5f7f6;}</style>

    <title>CAS R.L.</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="bg-light" style="background: #f5f7f6;">

    <div class="container">

        <div class="row vh-100 justify-content-center align-items-center">

            <div class="col-md-6">

                <div class="card shadow-lg border-0 rounded-4">

                    <div class="card-body p-4">

                        @yield('content')

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>

</html>
