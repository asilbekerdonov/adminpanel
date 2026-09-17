{{-- resources/views/statements/partials/details.blade.php --}}
{{-- Read-only данные заявки. --}}

@php
    $genderLabel = match ($statement->gender) {
        'male' => 'Мужской',
        'female' => 'Женский',
        default => 'Не важно',
    };
    $category = strtoupper((string) $statement->position_category);
    $salaries = [
        'salary_probation' => $statement->salary_probation,
        'salary_after_probation' => $statement->salary_after_probation,
    ];
@endphp

{{-- ═══ Организация и должность ═══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-building me-2 text-primary"></i>Организация и должность
        </h6>
    </div>
    <div class="card-body">

        <p class="app-section-title">Организация</p>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="app-info-label">Филиал</div>
                <div class="app-info-value">{{ $statement->branch?->name ?? '—' }}</div>
            </div>
            <div class="col-md-6">
                <div class="app-info-label">Отдел</div>
                <div class="app-info-value">{{ $statement->department?->name ?? '—' }}</div>
            </div>
            <div class="col-md-6">
                <div class="app-info-label">Подразделение</div>
                <div class="app-info-value">{{ $statement->subdivision?->name ?? '—' }}</div>
            </div>
            <div class="col-md-6">
                <div class="app-info-label">Место работы</div>
                <div class="app-info-value">{{ $statement->workplace ?? '—' }}</div>
            </div>
        </div>

        <hr>

        <p class="app-section-title mt-3">Вакантная должность</p>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="app-info-label">Должность</div>
                <div class="app-info-value fw-semibold">{{ $statement->position?->name ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="app-info-label">Категория</div>
                <div class="app-info-value">
                    @if(in_array($category, ['A', 'B', 'C', 'D'], true))
                        <span class="badge text-white app-cat-{{ strtolower($category) }}">
                            Кат. {{ $category }}
                        </span>
                    @else
                        —
                    @endif
                </div>
            </div>
            <div class="col-md-3">
                <div class="app-info-label">Разряд</div>
                <div class="app-info-value">
                    {{ $statement->grade ? $statement->grade.'-й разряд' : '—' }}
                </div>
            </div>
        </div>

        <hr>

        <p class="app-section-title mt-3">Должностные связи</p>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="app-info-label">Кому подчиняется</div>
                <div class="app-info-value">{{ $statement->reports_to ?: '—' }}</div>
            </div>
            <div class="col-md-6">
                <div class="app-info-label">Кто подчиняется</div>
                <div class="app-info-value">
                    {{ $statement->subordinates ? implode(', ', $statement->subordinates) : '—' }}
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ═══ График и зарплата ═══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-clock me-2 text-primary"></i>График и зарплата
        </h6>
    </div>
    <div class="card-body">

        <p class="app-section-title">Рабочий график</p>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="app-info-label">График</div>
                <div class="app-info-value">{{ $statement->work_schedule ?? '—' }}</div>
            </div>
            <div class="col-md-4">
                <div class="app-info-label">Начало</div>
                <div class="app-info-value">
                    {{ $statement->work_start ? substr($statement->work_start, 0, 5) : '—' }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="app-info-label">Конец</div>
                <div class="app-info-value">
                    {{ $statement->work_end ? substr($statement->work_end, 0, 5) : '—' }}
                </div>
            </div>
        </div>

        <hr>

        <p class="app-section-title mt-3">Заработная плата</p>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="app-info-label">На испытательный срок</div>
                <div class="app-info-value">
                    {{ $salaries['salary_probation'] ? number_format($salaries['salary_probation'], 0, '.', ' ').' сум' : '—' }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="app-info-label">После испытательного срока</div>
                <div class="app-info-value">
                    {{ $salaries['salary_after_probation'] ? number_format($salaries['salary_after_probation'], 0, '.', ' ').' сум' : '—' }}
                </div>
            </div>
        </div>

        <div>
            <div class="app-info-label">Бонусы и льготы</div>
            <div class="app-info-value app-pre">{{ $statement->bonuses ?: '—' }}</div>
        </div>

    </div>
</div>

{{-- ═══ Требования к кандидату ═══ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-person-check me-2 text-primary"></i>Требования к кандидату
        </h6>
    </div>
    <div class="card-body">

        <p class="app-section-title">Основные данные</p>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="app-info-label">Возраст</div>
                <div class="app-info-value">{{ $statement->age_category ?: '—' }}</div>
            </div>
            <div class="col-md-4">
                <div class="app-info-label">Пол</div>
                <div class="app-info-value">{{ $genderLabel }}</div>
            </div>
            <div class="col-md-4">
                <div class="app-info-label">Образование</div>
                <div class="app-info-value">{{ $statement->education ?: '—' }}</div>
            </div>
            <div class="col-12">
                <div class="app-info-label">Опыт работы</div>
                <div class="app-info-value">{{ $statement->experience ?: '—' }}</div>
            </div>
            <div class="col-12">
                <div class="app-info-label">Языки</div>
                <div class="app-info-value">
                    @if($statement->languages)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($statement->languages as $language)
                                <span class="badge bg-primary">
                                    {{ $language['lang'] ?? '—' }} — {{ $language['level'] ?? '—' }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>

        <hr>

        <p class="app-section-title mt-3">Профессиональные требования</p>
        <div class="mb-3">
            <div class="app-info-label">Специализированные знания</div>
            <div class="app-info-value app-pre">{{ $statement->specialized_knowledge ?: '—' }}</div>
        </div>
        <div class="mb-3">
            <div class="app-info-label">Должностные обязанности</div>
            <div class="app-info-value app-pre-lg">{{ $statement->job_responsibilities ?: '—' }}</div>
        </div>
        <div>
            <div class="app-info-label">Дополнительные требования</div>
            <div class="app-info-value app-pre">{{ $statement->additional_requirements ?: '—' }}</div>
        </div>

    </div>
</div>

{{-- ═══ Условия и служебная информация ═══ --}}
<div class="card border-0 shadow-sm">
    <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-info-circle me-2 text-primary"></i>Условия и служебная информация
        </h6>
    </div>
    <div class="card-body">

        <p class="app-section-title">Условия</p>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="app-info-label">Причина открытия</div>
                <div class="app-info-value">
                    {{ \App\Models\VacancyRequest::OPENING_REASON_LABELS[$statement->opening_reason] ?? '—' }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="app-info-label">Срок закрытия вакансии</div>
                <div class="app-info-value">
                    {{ $statement->vacancy_close_deadline?->format('d.m.Y') ?? '—' }}
                </div>
            </div>
        </div>

        <hr>

        <p class="app-section-title mt-3">Служебная информация</p>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="app-info-label">Создана</div>
                <div class="app-info-value">{{ $statement->created_at?->format('d.m.Y H:i') ?? '—' }}</div>
            </div>
            @if($statement->submitted_at)
                <div class="col-md-6">
                    <div class="app-info-label">Отправлена в HR</div>
                    <div class="app-info-value">{{ $statement->submitted_at->format('d.m.Y H:i') }}</div>
                </div>
            @endif
            @if($statement->sent_to_supervisor_at)
                <div class="col-md-6">
                    <div class="app-info-label">Отправлена руководителю</div>
                    <div class="app-info-value">{{ $statement->sent_to_supervisor_at->format('d.m.Y H:i') }}</div>
                </div>
            @endif
            @if($statement->supervisor)
                <div class="col-md-6">
                    <div class="app-info-label">Руководитель</div>
                    <div class="app-info-value">{{ $statement->supervisor->name }}</div>
                </div>
            @endif
            @if($statement->supervisor_reviewed_at)
                <div class="col-md-6">
                    <div class="app-info-label">Решение принято</div>
                    <div class="app-info-value">{{ $statement->supervisor_reviewed_at->format('d.m.Y H:i') }}</div>
                </div>
            @endif
            @if($statement->approved_at)
                <div class="col-md-6">
                    <div class="app-info-label">Одобрена</div>
                    <div class="app-info-value">{{ $statement->approved_at->format('d.m.Y H:i') }}</div>
                </div>
            @endif
            @if($statement->closed_at)
                <div class="col-md-6">
                    <div class="app-info-label">Закрыта</div>
                    <div class="app-info-value">{{ $statement->closed_at->format('d.m.Y H:i') }}</div>
                </div>
            @endif
            @if($statement->hrEditor)
                <div class="col-md-6">
                    <div class="app-info-label">Редактировал HR</div>
                    <div class="app-info-value">{{ $statement->hrEditor->name }}</div>
                </div>
            @endif
            @if($statement->editedBy)
                <div class="col-md-6">
                    <div class="app-info-label">Последнее изменение</div>
                    <div class="app-info-value">{{ $statement->editedBy->name }}</div>
                </div>
            @endif
        </div>

    </div>
</div>
