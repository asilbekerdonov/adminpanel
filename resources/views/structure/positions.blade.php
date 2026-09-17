{{-- resources/views/structure/positions.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="h5 fw-semibold mb-1">
                    <i class="bi bi-person-badge me-2"></i>Должности
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 fs-08">
                        <li class="breadcrumb-item">
                            <a href="{{ route('departments.index') }}" class="text-app-primary">
                                <i class="bi bi-diagram-3 me-1"></i>Отделы
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a
                                href="{{ route('subdivisions.index', ['department_id' => $subdivision->department_id]) }}"
                                class="text-app-primary"
                            >
                                {{ $subdivision->department->name }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active text-muted">{{ $subdivision->name }}</li>
                    </ol>
                </nav>
            </div>
            <a
                href="{{ route('subdivisions.index', ['department_id' => $subdivision->department_id]) }}"
                class="btn btn-sm btn-outline-secondary"
            >
                <i class="bi bi-arrow-left me-1"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <x-flash />

        {{-- Инфо-бейдж подразделения --}}
        <div class="mb-4 d-flex align-items-center gap-2 flex-wrap">
            <i class="bi bi-diagram-2 text-primary fs-5"></i>
            <span class="fw-semibold fs-6">{{ $subdivision->name }}</span>
            <code class="px-2 py-1 rounded">{{ $subdivision->code }}</code>
            <span class="text-muted small">
                <i class="bi bi-diagram-3 me-1"></i>{{ $subdivision->department->name }}
            </span>
            <span class="text-muted small">
                <i class="bi bi-building me-1"></i>{{ $subdivision->department->branch->name }}
            </span>
        </div>

        <div class="row g-4">

            @can('position.manage')
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-plus-circle me-2 text-primary"></i>Добавить должность
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('positions.store') }}">
                                @csrf
                                <input type="hidden" name="subdivision_id" value="{{ $subdivision->id }}">

                                {{-- Должность --}}
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold">
                                        Название должности <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control @error('name') is-invalid @enderror"
                                        id="name"
                                        name="name"
                                        value="{{ old('name') }}"
                                        placeholder="Главный бухгалтер"
                                        required
                                    >
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="category" class="form-label fw-semibold">
                                        Категория <span class="text-danger">*</span>
                                    </label>
                                    <select
                                        class="form-select @error('category') is-invalid @enderror"
                                        id="category"
                                        name="category"
                                        required
                                    >
                                        <option value="">— Выберите —</option>
                                        @foreach(['A', 'B', 'C', 'D'] as $cat)
                                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                                Категория {{ $cat }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="grade" class="form-label fw-semibold">
                                        Разряд <span class="text-danger">*</span>
                                    </label>
                                    <select
                                        class="form-select @error('grade') is-invalid @enderror"
                                        id="grade"
                                        name="grade"
                                        required
                                    >
                                        <option value="">— Выберите —</option>
                                        @foreach(range(1, 5) as $g)
                                            <option value="{{ $g }}" {{ old('grade') == $g ? 'selected' : '' }}>
                                                {{ $g }}-й разряд
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('grade')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <hr>

                                {{-- Сотрудник (необязательно) --}}
                                <p class="form-text mb-3">
                                    <i class="bi bi-person-plus me-1 text-primary"></i>
                                    <span class="fw-semibold text-app-soft">Сотрудник</span> — заполните,
                                    чтобы сразу назначить его на должность.
                                    Если указан email, поля ФИО, пароль и роль обязательны.
                                </p>

                                <div class="mb-3">
                                    <label for="user_name" class="form-label fw-semibold">ФИО сотрудника</label>
                                    <input
                                        type="text"
                                        class="form-control @error('user_name') is-invalid @enderror"
                                        id="user_name"
                                        name="user_name"
                                        value="{{ old('user_name') }}"
                                        placeholder="Иванов Иван Иванович"
                                    >
                                    @error('user_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold">Email</label>
                                    <input
                                        type="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="user@company.uz"
                                    >
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label fw-semibold">Пароль</label>
                                    <input
                                        type="text"
                                        class="form-control @error('password') is-invalid @enderror"
                                        id="password"
                                        name="password"
                                        placeholder="Минимум 8 символов"
                                    >
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="role" class="form-label fw-semibold">Роль</label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role">
                                        <option value="">— Без роли —</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i>Создать
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-list-ul me-2 text-primary"></i>Список должностей
                        </h6>
                        <span class="badge bg-primary rounded-pill">{{ $positions->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($positions->isEmpty())
                            <div class="app-empty">
                                <i class="bi bi-person-badge fs-1 d-block mb-2 opacity-25"></i>
                                Должности ещё не добавлены
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">#</th>
                                            <th>Название</th>
                                            <th>Категория</th>
                                            <th>Разряд</th>
                                            <th>Статус</th>
                                            <th>Сотрудники</th>
                                            <th class="text-end pe-3">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($positions as $position)
                                            <tr>
                                                <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                                <td class="fw-semibold text-app">{{ $position->name }}</td>
                                                <td>
                                                    <span class="badge app-cat-{{ strtolower($position->category) }} text-white">
                                                        Кат. {{ $position->category }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $position->grade }}-й разряд</span>
                                                </td>
                                                <td>
                                                    @if($position->is_vacant)
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-door-open me-1"></i>Вакантна
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                            Занята
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($position->users->isNotEmpty())
                                                        @foreach($position->users as $employee)
                                                            <div class="small text-app">
                                                                <i class="bi bi-person-fill me-1 text-muted"></i>{{ $employee->name }}
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-3">
                                                    @can('position.manage')
                                                        <form
                                                            method="POST"
                                                            action="{{ route('positions.destroy', $position) }}"
                                                            class="d-inline"
                                                        >
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
