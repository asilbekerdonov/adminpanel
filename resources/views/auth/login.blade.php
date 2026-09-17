<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} — Вход</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="app-shell d-flex align-items-center justify-content-center py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-lg-5">

                        <div class="text-center mb-4">
                            <x-application-logo class="app-logo" />
                            <div class="h4 fw-semibold mt-3 mb-1 text-app">{{ config('app.name') }}</div>
                            <small class="text-muted">Войдите в свой аккаунт</small>
                        </div>

                        {{-- Session Status --}}
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            {{-- Email --}}
                            <div class="mb-3">
                                <label class="form-label small text-muted" for="email">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-app-surface border-app text-app-placeholder">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" name="email" id="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}" required autofocus autocomplete="username"
                                           placeholder="you@example.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Password --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label small text-muted mb-0" for="password">Пароль</label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="small text-app-primary text-decoration-none">Забыли пароль?</a>
                                    @endif
                                </div>
                                <div class="input-group mt-1">
                                    <span class="input-group-text bg-app-surface border-app text-app-placeholder">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" name="password" id="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           required autocomplete="current-password" placeholder="••••••••">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Remember me --}}
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                                <label class="form-check-label small text-muted" for="remember_me">Запомнить меня</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Войти
                            </button>

                            @if (Route::has('register'))
                                <div class="text-center mt-3">
                                    <a href="{{ route('register') }}" class="small text-app-primary text-decoration-none">
                                        Регистрация
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
