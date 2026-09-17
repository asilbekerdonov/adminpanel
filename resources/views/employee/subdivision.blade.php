{{-- resources/views/employee/subdivision.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-diagram-2 me-2"></i>Моё подразделение
            </h2>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">

        @if($subdivision)
            <div class="row g-4">

                {{-- Карточка: место в структуре --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-person-circle me-2 text-primary"></i>Ваше место в структуре
                            </h6>
                        </div>
                        <div class="card-body d-flex flex-column gap-3">

                            @if($user->branch)
                                <div>
                                    <div class="app-info-label"><i class="bi bi-building me-1"></i>Филиал</div>
                                    <div class="app-info-value">{{ $user->branch->name }}</div>
                                </div>
                            @endif

                            @if($user->department)
                                <div>
                                    <div class="app-info-label"><i class="bi bi-diagram-3 me-1"></i>Отдел</div>
                                    <div class="app-info-value">{{ $user->department->name }}</div>
                                </div>
                            @endif

                            <div>
                                <div class="app-info-label"><i class="bi bi-diagram-2 me-1"></i>Подразделение</div>
                                <div class="app-info-value">{{ $subdivision->name }}</div>
                            </div>

                            @if($subdivision->head)
                                <div>
                                    <div class="app-info-label"><i class="bi bi-person-check me-1"></i>Ответственный</div>
                                    <div class="app-info-value text-app-soft">{{ $subdivision->head->name }}</div>
                                </div>
                            @endif

                            <div>
                                <div class="app-info-label">Код</div>
                                <div><code class="px-2 py-1 rounded">{{ $subdivision->code }}</code></div>
                            </div>

                            <hr>

                            @if($user->position)
                                <div>
                                    <div class="app-info-label">Ваша должность</div>
                                    <div class="app-info-value">{{ $user->position->name }}</div>
                                </div>
                                <div>
                                    <div class="app-info-label">Категория / Разряд</div>
                                    <span class="badge app-cat-{{ strtolower($user->position->category) }} text-white">
                                        Кат. {{ $user->position->category }}, {{ $user->position->grade }}-й разряд
                                    </span>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

                {{-- Карточка: должности с сотрудниками --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header py-3 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-person-badge me-2 text-primary"></i>Должности подразделения
                            </h6>
                            <span class="badge bg-primary rounded-pill">{{ $positions->count() }}</span>
                        </div>
                        <div class="card-body p-0">
                            @if($positions->isEmpty())
                                <div class="app-empty">
                                    <i class="bi bi-person-badge fs-1 d-block mb-2 opacity-25"></i>
                                    Должности не найдены
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3">#</th>
                                                <th>Должность</th>
                                                <th>Сотрудники</th>
                                                <th>Категория</th>
                                                <th>Разряд</th>
                                                <th>Статус</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($positions as $position)
                                                <tr class="{{ $user->position_id === $position->id ? 'table-active' : '' }}">
                                                    <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                                    <td class="fw-semibold text-app">
                                                        {{ $position->name }}
                                                        @if($user->position_id === $position->id)
                                                            <span class="badge bg-primary ms-1 fs-07">Ваша</span>
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
                                                    <td>
                                                        <span class="badge app-cat-{{ strtolower($position->category) }} text-white">
                                                            Кат. {{ $position->category }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">{{ $position->grade }}-й</span>
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
            <div class="app-empty">
                <i class="bi bi-diagram-2 opacity-25 d-block mb-3 app-kpi-icon"></i>
                <p class="fs-5 mb-1">Вы не привязаны к подразделению</p>
                <small>Обратитесь к администратору системы</small>
            </div>
        @endif

    </div>
</x-app-layout>
