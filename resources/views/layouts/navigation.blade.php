<nav class="navbar navbar-expand-sm bg-app-surface border-bottom border-app">
    <div class="container-fluid px-4">

        {{-- Логотип --}}
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <x-application-logo class="app-logo" />
        </a>

        {{-- Ссылки --}}
        <div class="me-auto">
            <a href="{{ route('dashboard') }}"
               class="btn btn-sm fs-09 {{ request()->routeIs('dashboard') ? 'text-app' : 'text-app-muted' }}">
                Dashboard
            </a>
        </div>

        {{-- Уведомления --}}
        @include('components.notifications-bell')

        {{-- Дропдаун пользователя --}}
        <div class="dropdown ms-2">
            <button class="btn btn-sm dropdown-toggle d-flex align-items-center gap-2 btn-user-menu"
                    type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i>
                {{ Auth::user()->name }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Log Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>

    </div>
</nav>
