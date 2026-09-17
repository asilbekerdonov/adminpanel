<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="app-shell d-flex align-items-center justify-content-center py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-4 p-lg-5">

                        <x-application-logo class="app-logo" />
                        <h1 class="h4 fw-semibold mt-3 mb-2 text-app">{{ config('app.name', 'Laravel') }}</h1>
                        <p class="text-muted mb-4">Панель управления подбором персонала.</p>

                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary px-4">
                                <i class="bi bi-speedometer2 me-2"></i>Перейти в панель
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary px-4">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Войти
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-outline-secondary px-4 ms-2">
                                    Регистрация
                                </a>
                            @endif
                        @endauth

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
