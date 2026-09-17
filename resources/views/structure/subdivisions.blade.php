{{-- resources/views/structure/subdivisions.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="h5 fw-semibold mb-1">
                    <i class="bi bi-diagram-2 me-2"></i>Подразделения
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
                                href="{{ route('departments.index', ['branch_id' => $department->branch_id]) }}"
                                class="text-app-primary"
                            >
                                {{ $department->branch->name }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active text-muted">{{ $department->name }}</li>
                    </ol>
                </nav>
            </div>
            <a
                href="{{ route('departments.index', ['branch_id' => $department->branch_id]) }}"
                class="btn btn-sm btn-outline-secondary"
            >
                <i class="bi bi-arrow-left me-1"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <x-flash />

        {{-- Инфо-бейдж отдела --}}
        <div class="mb-4 d-flex align-items-center gap-2 flex-wrap">
            <i class="bi bi-diagram-3 text-primary fs-5"></i>
            <span class="fw-semibold fs-6">{{ $department->name }}</span>
            <code class="px-2 py-1 rounded">{{ $department->code }}</code>
            <span class="text-muted small">
                <i class="bi bi-building me-1"></i>{{ $department->branch->name }}
            </span>
        </div>

        <div class="row g-4">

            @can('subdivision.manage')
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-plus-circle me-2 text-primary"></i>Добавить подразделение
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('subdivisions.store') }}">
                                @csrf
                                <input type="hidden" name="department_id" value="{{ $department->id }}">

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
                                        placeholder="Отдел разработки"
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
                                        placeholder="DEV"
                                        required
                                    >
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Уникальный код подразделения</div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i>Создать подразделение
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
                            <i class="bi bi-list-ul me-2 text-primary"></i>Список подразделений
                        </h6>
                        <span class="badge bg-primary rounded-pill">{{ $subdivisions->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($subdivisions->isEmpty())
                            <div class="app-empty">
                                <i class="bi bi-diagram-2 fs-1 d-block mb-2 opacity-25"></i>
                                Подразделения ещё не добавлены
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">#</th>
                                            <th>Название</th>
                                            <th>Код</th>
                                            <th>Должностей</th>
                                            <th class="text-end pe-3">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subdivisions as $subdivision)
                                            <tr>
                                                <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                                <td class="fw-semibold text-app">{{ $subdivision->name }}</td>
                                                <td>
                                                    <code class="px-2 py-1 rounded">{{ $subdivision->code }}</code>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary rounded-pill">
                                                        {{ $subdivision->positions_count }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-3">
                                                    <a
                                                        href="{{ route('positions.index', ['subdivision_id' => $subdivision->id]) }}"
                                                        class="btn btn-sm btn-outline-primary"
                                                    >
                                                        <i class="bi bi-person-badge me-1"></i>Должности
                                                    </a>

                                                    @can('subdivision.manage')
                                                        <form
                                                            method="POST"
                                                            action="{{ route('subdivisions.destroy', $subdivision) }}"
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
