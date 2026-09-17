{{-- resources/views/structure/departments.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="h5 fw-semibold mb-1">
                    <i class="bi bi-diagram-3 me-2"></i>Отделы
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 fs-08">
                        <li class="breadcrumb-item">
                            <a href="{{ route('departments.index') }}" class="text-app-primary">
                                <i class="bi bi-diagram-3 me-1"></i>Обзор
                            </a>
                        </li>
                        @if($branch)
                            <li class="breadcrumb-item active text-muted">{{ $branch->name }}</li>
                        @endif
                    </ol>
                </nav>
            </div>
            <a href="{{ route('departments.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <x-flash />

        @if($branch)

            {{-- Инфо-бейдж филиала --}}
            <div class="mb-4 d-flex align-items-center gap-2 flex-wrap">
                <i class="bi bi-building text-primary fs-5"></i>
                <span class="fw-semibold fs-6">{{ $branch->name }}</span>
                <code class="px-2 py-1 rounded">{{ $branch->code }}</code>
                @if($branch->address)
                    <span class="text-muted small">
                        <i class="bi bi-geo-alt me-1"></i>{{ $branch->address }}
                    </span>
                @endif
            </div>

            <div class="row g-4">

                @can('department.manage')
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header py-3">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="bi bi-plus-circle me-2 text-primary"></i>Добавить отдел
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('departments.store') }}">
                                    @csrf
                                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                                    <div class="mb-3">
                                        <label for="name" class="form-label fw-semibold">
                                            Название <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control @error('name') is-invalid @enderror"
                                            id="name"
                                            name="name"
                                            value="{{ old('name') }}"
                                            placeholder="Бухгалтерия"
                                            required
                                        >
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="code" class="form-label fw-semibold">
                                            Код <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control @error('code') is-invalid @enderror"
                                            id="code"
                                            name="code"
                                            value="{{ old('code') }}"
                                            placeholder="BUCH"
                                            required
                                        >
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Уникальный код отдела</div>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i>Создать отдел
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
                                <i class="bi bi-list-ul me-2 text-primary"></i>Список отделов
                            </h6>
                            <span class="badge bg-primary rounded-pill">{{ $departments->count() }}</span>
                        </div>
                        <div class="card-body p-0">
                            @if($departments->isEmpty())
                                <div class="app-empty">
                                    <i class="bi bi-diagram-3 fs-1 d-block mb-2 opacity-25"></i>
                                    Отделы ещё не добавлены
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3">#</th>
                                                <th>Название</th>
                                                <th>Код</th>
                                                <th>Подразделений</th>
                                                <th class="text-end pe-3">Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($departments as $department)
                                                <tr>
                                                    <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                                    <td class="fw-semibold text-app">{{ $department->name }}</td>
                                                    <td>
                                                        <code class="px-2 py-1 rounded">{{ $department->code }}</code>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary rounded-pill">
                                                            {{ $department->subdivisions_count }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end pe-3">
                                                        <a
                                                            href="{{ route('subdivisions.index', ['department_id' => $department->id]) }}"
                                                            class="btn btn-sm btn-outline-primary"
                                                        >
                                                            <i class="bi bi-diagram-2 me-1"></i>Подразделения
                                                        </a>

                                                        @can('department.manage')
                                                            <form
                                                                method="POST"
                                                                action="{{ route('departments.destroy', $department) }}"
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

        @else

            {{-- Обзор для HR: все филиалы со списком отделов --}}
            @forelse($branches as $b)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-building me-2 text-warning"></i>{{ $b->name }}
                            <code class="ms-2">{{ $b->code }}</code>
                        </h6>
                        <span class="badge bg-primary rounded-pill">{{ $b->departments->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($b->departments->isEmpty())
                            <div class="app-empty">
                                <i class="bi bi-diagram-3 fs-1 d-block mb-2 opacity-25"></i>
                                Отделов нет
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">#</th>
                                            <th>Название</th>
                                            <th>Код</th>
                                            <th>Статус</th>
                                            <th class="text-end pe-3">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($b->departments as $dept)
                                            <tr>
                                                <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                                <td class="fw-semibold text-app">{{ $dept->name }}</td>
                                                <td>
                                                    <code class="px-2 py-1 rounded">{{ $dept->code }}</code>
                                                </td>
                                                <td>
                                                    @if($dept->is_active)
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                            Активен
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                            Неактивен
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-3">
                                                    <a
                                                        href="{{ route('departments.index', ['branch_id' => $b->id]) }}"
                                                        class="btn btn-sm btn-outline-primary"
                                                    >
                                                        <i class="bi bi-box-arrow-up-right me-1"></i>Открыть
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="app-empty">
                    <i class="bi bi-building fs-1 d-block mb-2 opacity-25"></i>
                    Филиалы не найдены
                </div>
            @endforelse

        @endif
    </div>
</x-app-layout>
