{{-- resources/views/structure/branches.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-building me-2"></i>Филиалы
            </h2>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <x-flash />

        <div class="row g-4">

            @can('branch.manage')
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-plus-circle me-2 text-primary"></i>Добавить филиал
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('branches.store') }}">
                                @csrf

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
                                        placeholder="Главный офис"
                                        required
                                    >
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="code" class="form-label fw-semibold">
                                        Код <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control @error('code') is-invalid @enderror"
                                        id="code"
                                        name="code"
                                        value="{{ old('code') }}"
                                        placeholder="HQ"
                                        required
                                    >
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Уникальный код, например: HQ, BRANCH-01</div>
                                </div>

                                <div class="mb-4">
                                    <label for="address" class="form-label fw-semibold">Адрес</label>
                                    <textarea
                                        class="form-control @error('address') is-invalid @enderror"
                                        id="address"
                                        name="address"
                                        rows="3"
                                        placeholder="г. Ташкент, ул. Примерная 1"
                                    >{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i>Создать филиал
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
                            <i class="bi bi-list-ul me-2 text-primary"></i>Список филиалов
                        </h6>
                        <span class="badge bg-primary rounded-pill">{{ $branches->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($branches->isEmpty())
                            <div class="app-empty">
                                <i class="bi bi-building fs-1 d-block mb-2 opacity-25"></i>
                                Филиалы ещё не добавлены
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">#</th>
                                            <th>Название</th>
                                            <th>Код</th>
                                            <th>Адрес</th>
                                            <th>Отделов</th>
                                            <th class="text-end pe-3">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($branches as $branch)
                                            <tr>
                                                <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                                <td class="fw-semibold text-app">{{ $branch->name }}</td>
                                                <td>
                                                    <code class="px-2 py-1 rounded">{{ $branch->code }}</code>
                                                </td>
                                                <td class="text-muted small">{{ $branch->address ?: '—' }}</td>
                                                <td>
                                                    <span class="badge bg-secondary rounded-pill">
                                                        {{ $branch->departments_count }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-3">
                                                    <a
                                                        href="{{ route('departments.index', ['branch_id' => $branch->id]) }}"
                                                        class="btn btn-sm btn-outline-primary"
                                                    >
                                                        <i class="bi bi-diagram-3 me-1"></i>Отделы
                                                    </a>

                                                    @can('branch.manage')
                                                        <form
                                                            method="POST"
                                                            action="{{ route('branches.destroy', $branch) }}"
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
