@php
    $user = auth()->user();
    $title = match (true) {
        $user->hasUserRole(\App\Enums\UserRole::SUPER_ADMIN) => 'Согласование заявок',
        $user->hasUserRole(\App\Enums\UserRole::HR_MANAGER) => 'Заявки на подбор',
        default => 'Мои заявки',
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="h5 fw-semibold mb-0">
                <i class="bi bi-files me-2"></i>{{ $title }}
            </h2>

            @can('create', \App\Models\VacancyRequest::class)
                <a href="{{ route('statements.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Новая заявка
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <x-flash />

        <form method="GET" action="{{ route('statements.index') }}" class="row g-2 align-items-end mb-4">
            <div class="col-sm-4 col-md-3">
                <label class="form-label app-info-label" for="status">Статус</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">Все статусы</option>
                    @foreach (\App\Enums\VacancyRequestStatus::options() as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-5 col-md-4">
                <label class="form-label app-info-label" for="q">Поиск по должности</label>
                <input type="text" name="q" id="q" value="{{ request('q') }}"
                       class="form-control form-control-sm" maxlength="100" placeholder="Название должности">
            </div>

            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1"></i>Применить
                </button>
                <a href="{{ route('statements.index') }}" class="btn btn-sm btn-outline-secondary">Сбросить</a>
            </div>
        </form>

        <div class="card border-0 shadow-sm">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-list-ul me-2 text-app-primary"></i>Список заявок
                </h6>
                <span class="badge bg-primary rounded-pill">{{ $statements->total() }}</span>
            </div>

            <div class="card-body p-0">
                @if ($statements->isEmpty())
                    <div class="app-empty">
                        <i class="bi bi-inbox fs-1 opacity-25 d-block mb-3"></i>
                        <p class="fs-5 mb-1">Заявок нет</p>
                        <small>Здесь появятся заявки, доступные вашей роли</small>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Должность</th>
                                    <th>Заявитель</th>
                                    <th>Подразделение</th>
                                    <th>Статус</th>
                                    <th>Отправлена</th>
                                    <th>Срок закрытия</th>
                                    <th class="text-end pe-3">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($statements as $statement)
                                    <tr class="app-clickable"
                                        onclick="window.location='{{ route('statements.show', $statement) }}'">
                                        <td class="ps-3 text-muted small">
                                            {{ $statements->firstItem() + $loop->index }}
                                        </td>

                                        <td class="fw-semibold text-app">
                                            {{ $statement->position?->name ?? '—' }}
                                            @if ($statement->position_category)
                                                <span class="badge text-white ms-1 fs-07 app-cat-{{ strtolower($statement->position_category) }}">
                                                    Кат. {{ $statement->position_category }}
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="small text-app">{{ $statement->requester?->name ?? '—' }}</div>
                                            <div class="text-muted fs-07">{{ $statement->department?->name }}</div>
                                        </td>

                                        <td class="text-muted small">{{ $statement->subdivision?->name ?? '—' }}</td>

                                        <td>
                                            <span class="badge bg-{{ $statement->status_color }}">
                                                {{ $statement->status_label }}
                                            </span>
                                            @if ($statement->hrEditor)
                                                <div class="text-muted fs-07">
                                                    <i class="bi bi-pencil me-1"></i>{{ $statement->hrEditor->name }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="text-muted small">
                                            {{ $statement->submitted_at?->format('d.m.Y') ?? '—' }}
                                        </td>

                                        <td>
                                            @if ($statement->vacancy_close_deadline)
                                                @php
                                                    $daysLeft = now()->diffInDays($statement->vacancy_close_deadline, false);
                                                    $deadlineColor = $daysLeft < 3 ? 'danger' : ($daysLeft < 7 ? 'warning text-dark' : 'secondary');
                                                @endphp
                                                <span class="badge bg-{{ $deadlineColor }}">
                                                    {{ $statement->vacancy_close_deadline->format('d.m.Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>

                                        <td class="text-end pe-3" onclick="event.stopPropagation()">
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('statements.show', $statement) }}"
                                                   class="btn btn-sm btn-outline-secondary" title="Открыть">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                @can('edit', $statement)
                                                    <a href="{{ route('statements.edit', $statement) }}"
                                                       class="btn btn-sm btn-outline-secondary" title="Редактировать">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endcan

                                                @can('submit', $statement)
                                                    <form method="POST" action="{{ route('statements.submit-to-hr', $statement) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Отправить в HR">
                                                            <i class="bi bi-send"></i>
                                                        </button>
                                                    </form>
                                                @endcan

                                                @can('decide', $statement)
                                                    <a href="{{ route('statements.show', $statement) }}"
                                                       class="btn btn-sm btn-primary" title="Согласовать">
                                                        <i class="bi bi-check2-square"></i>
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-3 py-3">
                        {{ $statements->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
