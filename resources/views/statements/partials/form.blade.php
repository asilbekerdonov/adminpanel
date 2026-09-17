{{-- resources/views/statements/partials/form.blade.php --}}
{{-- Общая форма создания/редактирования заявки на подбор персонала.
     Ожидает: $action, $method, $submitLabel, $user, $allPositions,
     $vacantPositions и опционально $statement. --}}
@php
    $statement = $statement ?? null;

    $positionId         = old('position_id', $statement?->position_id);
    $grade              = old('grade', $statement?->grade ?? '');
    $reportsTo          = old('reports_to', $statement?->reports_to ?? ($user->subdivision?->head?->position?->name ?? ''));
    $workplace          = old('workplace', $statement?->workplace ?? ($user->branch?->name ?? ''));
    $workSchedule       = old('work_schedule', $statement?->work_schedule ?? '');
    $workStart          = old('work_start', $statement?->work_start ? \Illuminate\Support\Carbon::parse($statement->work_start)->format('H:i') : '09:00');
    $workEnd            = old('work_end', $statement?->work_end ? \Illuminate\Support\Carbon::parse($statement->work_end)->format('H:i') : '18:00');
    $salaryProbation    = old('salary_probation', $statement?->salary_probation ?? '');
    $salaryAfter        = old('salary_after_probation', $statement?->salary_after_probation ?? '');
    $bonuses            = old('bonuses', $statement?->bonuses ?? '');
    $openingReason      = old('opening_reason', $statement?->opening_reason ?? '');
    $ageCategory        = old('age_category', $statement?->age_category ?? '');
    $gender             = old('gender', $statement?->gender ?? '');
    $education          = old('education', $statement?->education ?? '');
    $experience         = old('experience', $statement?->experience ?? '');
    $specialized        = old('specialized_knowledge', $statement?->specialized_knowledge ?? '');
    $responsibilities   = old('job_responsibilities', $statement?->job_responsibilities ?? '');
    $additional         = old('additional_requirements', $statement?->additional_requirements ?? '');
    $deadline           = old('vacancy_close_deadline', $statement?->vacancy_close_deadline?->format('Y-m-d') ?? '');
    $categoryDisplay    = (string) ($statement?->position_category ?? '');

    // Динамические строки «Кто подчиняется»: из $statement, fallback [].
    $subordinateRows = old('subordinates', $statement?->subordinates ?? []);
    $subordinateRows = is_array($subordinateRows) ? array_values($subordinateRows) : [];

    // Динамические строки «Знание языков»: из $statement, fallback [].
    $languageRows = old('languages', $statement?->languages ?? []);
    $nextLanguageIndex = 0;
    if (is_array($languageRows)) {
        foreach (array_keys($languageRows) as $languageKey) {
            if (is_numeric($languageKey)) {
                $nextLanguageIndex = max($nextLanguageIndex, ((int) $languageKey) + 1);
            }
        }
        $languageRows = array_values($languageRows);
    } else {
        $languageRows = [];
    }

    $workSchedules   = ['5/2', '6/1', '7/0', '2/2'];
    $educations      = ['Среднее', 'Среднее специальное', 'Высшее', 'Магистратура'];
    $languageLevels  = ['Начальный', 'Средний', 'Свободный', 'Родной'];
    $languageHints   = ['Русский', 'Узбекский', 'Английский', 'Каракалпакский', 'Таджикский', 'Казахский'];

    $deadlineMin = now()->addDay()->format('Y-m-d');
    $deadlineMin = ($deadline !== '' && $deadline < $deadlineMin) ? null : $deadlineMin;

    $categoryClass = in_array(strtoupper($categoryDisplay), ['A', 'B', 'C', 'D'], true)
        ? 'app-cat-' . strtolower($categoryDisplay)
        : 'bg-secondary';
@endphp

<form method="POST" action="{{ $action }}" id="statement_form" novalidate>
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <div class="row g-4">

        {{-- ─── ЛЕВАЯ КОЛОНКА ──────────────────────────── --}}
        <div class="col-lg-8">

            {{-- БЛОК 1: Основная информация --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-person-badge me-2 text-app-primary"></i>Основная информация
                    </h6>
                </div>
                <div class="card-body">

                    <p class="app-section-title text-uppercase fs-07 fw-bold mb-3 pb-2 border-bottom">Заявитель</p>

                    <div class="mb-3 d-flex align-items-center gap-3">
                        <span class="app-info-label app-col-sm">Ф.И.О.</span>
                        <div class="app-info-value border rounded px-3 py-2 flex-grow-1">
                            <i class="bi bi-person me-2 text-app-primary"></i>{{ $user->name }}
                        </div>
                    </div>

                    <div class="mb-3 d-flex align-items-center gap-3">
                        <span class="app-info-label app-col-sm">Филиал</span>
                        <div class="app-info-value border rounded px-3 py-2 flex-grow-1">
                            <i class="bi bi-building me-2 text-app-primary"></i>{{ $user->branch?->name ?? '—' }}
                        </div>
                    </div>

                    <div class="mb-3 d-flex align-items-center gap-3">
                        <span class="app-info-label app-col-sm">Отдел</span>
                        <div class="app-info-value border rounded px-3 py-2 flex-grow-1">
                            <i class="bi bi-diagram-3 me-2 text-app-primary"></i>{{ $user->department?->name ?? '—' }}
                        </div>
                    </div>

                    <div class="mb-3 d-flex align-items-center gap-3">
                        <span class="app-info-label app-col-sm">Подразделение</span>
                        <div class="app-info-value border rounded px-3 py-2 flex-grow-1">
                            <i class="bi bi-diagram-2 me-2 text-app-primary"></i>{{ $user->subdivision?->name ?? '—' }}
                        </div>
                    </div>

                    <div class="mb-4 d-flex align-items-center gap-3">
                        <span class="app-info-label app-col-sm">Должность</span>
                        <div class="app-info-value border rounded px-3 py-2 flex-grow-1">
                            <i class="bi bi-briefcase me-2 text-app-primary"></i>{{ $user->position?->name ?? '—' }}
                        </div>
                    </div>

                    <hr>

                    <p class="app-section-title text-uppercase fs-07 fw-bold mt-3 mb-3 pb-2 border-bottom">Вакантная должность</p>

                    {{-- Должность --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="position_select">
                            Должность <span class="text-danger">*</span>
                        </label>
                        <select
                            class="form-select @error('position_id') is-invalid @enderror"
                            name="position_id"
                            id="position_select"
                            required
                        >
                            <option value="">— Выберите должность —</option>
                            @foreach($allPositions as $pos)
                                <option
                                    value="{{ $pos->id }}"
                                    data-category="{{ $pos->category }}"
                                    data-grade="{{ $pos->grade }}"
                                    {{ (int) $positionId === (int) $pos->id ? 'selected' : '' }}
                                >
                                    {{ $pos->name }}@if($pos->is_vacant) — вакантна @endif (Кат. {{ $pos->category }}, {{ $pos->grade }}-й разряд)
                                </option>
                            @endforeach
                        </select>
                        @error('position_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($vacantPositions->isEmpty())
                            <div class="form-text text-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Нет вакантных должностей в вашем подразделении
                            </div>
                        @endif
                    </div>

                    {{-- Категория (авто) + Разряд --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="category_display">Категория</label>
                            <div class="d-flex align-items-center gap-2">
                                <span id="category_badge" class="badge {{ $categoryClass }}">
                                    {{ $categoryDisplay !== '' ? $categoryDisplay : '—' }}
                                </span>
                                <span id="category_display" class="app-info-label">— выберите должность —</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="grade">Разряд</label>
                            <select class="form-select @error('grade') is-invalid @enderror" name="grade" id="grade">
                                <option value="">— Выберите —</option>
                                @foreach(range(1, 5) as $g)
                                    <option value="{{ $g }}" {{ (string) $grade === (string) $g ? 'selected' : '' }}>
                                        {{ $g }}-й разряд
                                    </option>
                                @endforeach
                            </select>
                            @error('grade')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Кому подчиняется --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Кому подчиняется</label>
                        <div class="app-info-value border rounded px-3 py-2">
                            @if($user->subdivision?->head)
                                <i class="bi bi-person-check me-2 text-success"></i>
                                {{ $user->subdivision->head->name }}
                                @if($user->subdivision->head->position)
                                    — <span class="text-muted">{{ $user->subdivision->head->position->name }}</span>
                                @endif
                            @else
                                <span class="text-muted">Руководитель не назначен</span>
                            @endif
                        </div>
                        <input type="hidden" name="reports_to" value="{{ $reportsTo }}">
                    </div>

                    {{-- Кто подчиняется --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="add_subordinate">Кто подчиняется (если есть)</label>

                        <div id="subordinates_container">
                            @foreach($subordinateRows as $subordinate)
                                <div class="subordinate-row d-flex align-items-center gap-2 mb-2">
                                    <input
                                        type="text"
                                        class="form-control @error('subordinates.' . $loop->index) is-invalid @enderror"
                                        name="subordinates[]"
                                        value="{{ $subordinate }}"
                                        list="positions_datalist"
                                        placeholder="Название должности"
                                    >
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-subordinate" aria-label="Удалить строку">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <template id="subordinate_template">
                            <div class="subordinate-row d-flex align-items-center gap-2 mb-2">
                                <input
                                    type="text"
                                    class="form-control"
                                    name="subordinates[]"
                                    value=""
                                    list="positions_datalist"
                                    placeholder="Название должности"
                                >
                                <button type="button" class="btn btn-sm btn-outline-danger remove-subordinate" aria-label="Удалить строку">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </template>

                        <button type="button" class="btn btn-sm btn-outline-primary" id="add_subordinate">
                            <i class="bi bi-plus-lg me-1"></i>Добавить должность
                        </button>

                        <datalist id="positions_datalist">
                            @foreach($allPositions as $pos)
                                <option value="{{ $pos->name }}"></option>
                            @endforeach
                        </datalist>

                        @error('subordinates')
                            <div class="text-danger fs-08 mt-1">{{ $message }}</div>
                        @enderror
                        @if($allPositions->isEmpty())
                            <div class="form-text text-muted">Нет других должностей в подразделении</div>
                        @endif
                    </div>

                </div>
            </div>

            {{-- БЛОК 2: График и зарплата --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-clock me-2 text-app-primary"></i>График и зарплата
                    </h6>
                </div>
                <div class="card-body">

                    <p class="app-section-title text-uppercase fs-07 fw-bold mb-3 pb-2 border-bottom">Рабочий график</p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="work_schedule">График</label>
                            <select class="form-select @error('work_schedule') is-invalid @enderror" name="work_schedule" id="work_schedule">
                                <option value="">— Выберите —</option>
                                @foreach($workSchedules as $schedule)
                                    <option value="{{ $schedule }}" {{ (string) $workSchedule === (string) $schedule ? 'selected' : '' }}>
                                        {{ $schedule }}
                                    </option>
                                @endforeach
                            </select>
                            @error('work_schedule')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="work_start">Начало работы</label>
                            <input type="time" class="form-control @error('work_start') is-invalid @enderror" name="work_start" id="work_start"
                                value="{{ $workStart }}">
                            @error('work_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="work_end">Конец работы</label>
                            <input type="time" class="form-control @error('work_end') is-invalid @enderror" name="work_end" id="work_end"
                                value="{{ $workEnd }}">
                            @error('work_end')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>
                    <p class="app-section-title text-uppercase fs-07 fw-bold mt-3 mb-3 pb-2 border-bottom">Заработная плата</p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="salary_probation">На испытательный срок</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control @error('salary_probation') is-invalid @enderror"
                                    name="salary_probation"
                                    id="salary_probation"
                                    value="{{ $salaryProbation }}"
                                    placeholder="0"
                                    min="0"
                                >
                                <span class="input-group-text bg-dark text-white-50 border-secondary">сум</span>
                            </div>
                            @error('salary_probation')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="salary_after_probation">После испытательного срока</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control @error('salary_after_probation') is-invalid @enderror"
                                    name="salary_after_probation"
                                    id="salary_after_probation"
                                    value="{{ $salaryAfter }}"
                                    placeholder="0"
                                    min="0"
                                >
                                <span class="input-group-text bg-dark text-white-50 border-secondary">сум</span>
                            </div>
                            @error('salary_after_probation')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="bonuses">Бонусы и льготы</label>
                        <textarea
                            class="form-control @error('bonuses') is-invalid @enderror"
                            name="bonuses"
                            id="bonuses"
                            rows="3"
                            placeholder="Опишите бонусы, льготы и дополнительные условия..."
                        >{{ $bonuses }}</textarea>
                        @error('bonuses')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- БЛОК 3: Требования к кандидату --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-person-check me-2 text-app-primary"></i>Требования к кандидату
                    </h6>
                </div>
                <div class="card-body">

                    <p class="app-section-title text-uppercase fs-07 fw-bold mb-3 pb-2 border-bottom">Основные данные</p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="age_category">Возраст</label>
                            <input type="text" class="form-control @error('age_category') is-invalid @enderror" name="age_category" id="age_category"
                                value="{{ $ageCategory }}" placeholder="н-р 25–40">
                            @error('age_category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="gender">Пол</label>
                            <select class="form-select @error('gender') is-invalid @enderror" name="gender" id="gender">
                                <option value="">— Не важно —</option>
                                <option value="male"   {{ $gender === 'male'   ? 'selected' : '' }}>Мужской</option>
                                <option value="female" {{ $gender === 'female' ? 'selected' : '' }}>Женский</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="education">Образование</label>
                            <select class="form-select @error('education') is-invalid @enderror" name="education" id="education">
                                <option value="">— Выберите —</option>
                                @foreach($educations as $edu)
                                    <option value="{{ $edu }}" {{ (string) $education === (string) $edu ? 'selected' : '' }}>
                                        {{ $edu }}
                                    </option>
                                @endforeach
                            </select>
                            @error('education')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="experience">Опыт работы</label>
                        <input type="text" class="form-control @error('experience') is-invalid @enderror" name="experience" id="experience"
                            value="{{ $experience }}" placeholder="н-р от 2 лет в сфере...">
                        @error('experience')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <p class="app-section-title text-uppercase fs-07 fw-bold mt-3 mb-3 pb-2 border-bottom">Знание языков</p>

                    <div id="languages_container">
                        @foreach($languageRows as $index => $language)
                            <div class="language-row row g-2 align-items-center mb-2">
                                <div class="col-7 col-md-5">
                                    <input
                                        type="text"
                                        class="form-control @error('languages.' . $index . '.lang') is-invalid @enderror"
                                        name="languages[{{ $index }}][lang]"
                                        value="{{ is_array($language) ? ($language['lang'] ?? '') : '' }}"
                                        list="languages_datalist"
                                        placeholder="Язык"
                                    >
                                    @error('languages.' . $index . '.lang')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-9 col-md-5">
                                    <select class="form-select @error('languages.' . $index . '.level') is-invalid @enderror" name="languages[{{ $index }}][level]">
                                        <option value="">— Уровень не указан —</option>
                                        @foreach($languageLevels as $level)
                                            <option value="{{ $level }}" {{ is_array($language) && ($language['level'] ?? '') === $level ? 'selected' : '' }}>
                                                {{ $level }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('languages.' . $index . '.level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-2 col-md-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-language" aria-label="Удалить язык">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <template id="language_template">
                        <div class="language-row row g-2 align-items-center mb-2">
                            <div class="col-7 col-md-5">
                                <input
                                    type="text"
                                    class="form-control"
                                    name="languages[__INDEX__][lang]"
                                    value=""
                                    list="languages_datalist"
                                    placeholder="Язык"
                                >
                            </div>
                            <div class="col-9 col-md-5">
                                <select class="form-select" name="languages[__INDEX__][level]">
                                    <option value="">— Уровень не указан —</option>
                                    @foreach($languageLevels as $level)
                                        <option value="{{ $level }}">{{ $level }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2 col-md-2">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-language" aria-label="Удалить язык">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </template>

                    <button type="button" class="btn btn-sm btn-outline-primary" id="add_language">
                        <i class="bi bi-plus-lg me-1"></i>Добавить язык
                    </button>

                    <datalist id="languages_datalist">
                        @foreach($languageHints as $languageHint)
                            <option value="{{ $languageHint }}"></option>
                        @endforeach
                    </datalist>

                    <div class="form-text text-muted mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Выберите уровень для тех языков, которыми владеет кандидат. Строки без языка или без уровня не сохраняются.
                    </div>

                    <hr class="mt-4">
                    <p class="app-section-title text-uppercase fs-07 fw-bold mt-3 mb-3 pb-2 border-bottom">Профессиональные требования</p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="specialized_knowledge">Специализированные знания</label>
                        <textarea class="form-control @error('specialized_knowledge') is-invalid @enderror" name="specialized_knowledge" id="specialized_knowledge" rows="3"
                            placeholder="1С, Excel, знание налогового законодательства...">{{ $specialized }}</textarea>
                        @error('specialized_knowledge')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="job_responsibilities">Должностные обязанности</label>
                        <textarea class="form-control @error('job_responsibilities') is-invalid @enderror" name="job_responsibilities" id="job_responsibilities" rows="4"
                            placeholder="Подробно опишите обязанности сотрудника...">{{ $responsibilities }}</textarea>
                        @error('job_responsibilities')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="additional_requirements">Дополнительные требования</label>
                        <textarea class="form-control @error('additional_requirements') is-invalid @enderror" name="additional_requirements" id="additional_requirements" rows="3"
                            placeholder="Любые другие требования...">{{ $additional }}</textarea>
                        @error('additional_requirements')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

        </div>

        {{-- ─── ПРАВАЯ КОЛОНКА ──────────────────────────── --}}
        <div class="col-lg-4">

            {{-- Причина открытия --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-question-circle me-2 text-app-primary"></i>Причина открытия
                    </h6>
                </div>
                <div class="card-body">
                    <label class="form-label fw-semibold" for="opening_reason">Причина <span class="text-danger">*</span></label>
                    <select class="form-select @error('opening_reason') is-invalid @enderror" name="opening_reason" id="opening_reason" required>
                        <option value="">— Выберите причину —</option>
                        @foreach(\App\Models\VacancyRequest::OPENING_REASON_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ (string) $openingReason === (string) $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('opening_reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Место работы --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-building me-2 text-app-primary"></i>Место работы
                    </h6>
                </div>
                <div class="card-body">
                    <div class="app-info-value border rounded px-3 py-2">
                        <i class="bi bi-geo-alt me-2 text-warning"></i>{{ $user->branch?->name ?? '—' }}
                    </div>
                    <input type="hidden" name="workplace" value="{{ $workplace }}">
                </div>
            </div>

            {{-- Срок закрытия --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-calendar me-2 text-app-primary"></i>Срок закрытия вакансии
                    </h6>
                </div>
                <div class="card-body">
                    <label class="form-label fw-semibold" for="vacancy_close_deadline">Дата</label>
                    <input type="date" class="form-control @error('vacancy_close_deadline') is-invalid @enderror" name="vacancy_close_deadline" id="vacancy_close_deadline"
                        value="{{ $deadline }}"
                        @if($deadlineMin) min="{{ $deadlineMin }}" @endif>
                    @error('vacancy_close_deadline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Кнопки --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>{{ $submitLabel }}
                    </button>
                    <a href="{{ route('statements.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x me-1"></i>Отмена
                    </a>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        После сохранения вы сможете проверить и отправить заявку
                    </small>
                </div>
            </div>

        </div>
    </div>
</form>

@push('scripts')
<script>
    (function () {
        'use strict';

        // ── Категория/разряд выбранной должности ───────────────────
        var positionSelect = document.getElementById('position_select');
        var categoryBadge = document.getElementById('category_badge');
        var categoryDisplay = document.getElementById('category_display');
        var gradeSelect = document.getElementById('grade');

        function syncCategory() {
            if (!positionSelect || !categoryBadge || !categoryDisplay) {
                return;
            }

            var option = positionSelect.options[positionSelect.selectedIndex];
            var category = option ? (option.getAttribute('data-category') || '') : '';
            var grade = option ? (option.getAttribute('data-grade') || '') : '';

            if (!option || option.value === '' || category === '') {
                categoryBadge.className = 'badge bg-secondary';
                categoryBadge.textContent = '—';
                categoryDisplay.textContent = '— выберите должность —';
                return;
            }

            var normalized = category.toUpperCase();
            categoryBadge.className = ['A', 'B', 'C', 'D'].indexOf(normalized) !== -1
                ? 'badge app-cat-' + normalized.toLowerCase()
                : 'badge bg-secondary';
            categoryBadge.textContent = normalized;
            categoryDisplay.textContent = grade ? ('Разряд: ' + grade) : 'Разряд не указан';

            if (gradeSelect && gradeSelect.value === '' && grade) {
                gradeSelect.value = grade;
            }
        }

        if (positionSelect) {
            positionSelect.addEventListener('change', syncCategory);
            syncCategory();
        }

        // ── Динамические строки «Кто подчиняется» ──────────────────
        var subordinatesContainer = document.getElementById('subordinates_container');
        var subordinateTemplate = document.getElementById('subordinate_template');
        var addSubordinate = document.getElementById('add_subordinate');

        if (subordinatesContainer) {
            subordinatesContainer.addEventListener('click', function (event) {
                var button = event.target.closest('.remove-subordinate');
                if (button) {
                    var row = button.closest('.subordinate-row');
                    if (row) {
                        row.remove();
                    }
                }
            });
        }

        if (addSubordinate && subordinatesContainer && subordinateTemplate) {
            addSubordinate.addEventListener('click', function () {
                subordinatesContainer.appendChild(subordinateTemplate.content.cloneNode(true));
            });
        }

        // ── Динамические строки «Знание языков» ────────────────────
        var languagesContainer = document.getElementById('languages_container');
        var languageTemplate = document.getElementById('language_template');
        var addLanguage = document.getElementById('add_language');
        var languageIndex = {{ (int) $nextLanguageIndex }};

        if (languagesContainer) {
            languagesContainer.addEventListener('click', function (event) {
                var button = event.target.closest('.remove-language');
                if (button) {
                    var row = button.closest('.language-row');
                    if (row) {
                        row.remove();
                    }
                }
            });
        }

        if (addLanguage && languagesContainer && languageTemplate) {
            addLanguage.addEventListener('click', function () {
                var markup = languageTemplate.innerHTML.replace(/__INDEX__/g, String(languageIndex));
                languageIndex += 1;

                var wrapper = document.createElement('div');
                wrapper.innerHTML = markup.trim();

                if (wrapper.firstElementChild) {
                    languagesContainer.appendChild(wrapper.firstElementChild);
                }
            });
        }

        // ── Перед отправкой убираем незаполненные строки языков ────
        var statementForm = document.getElementById('statement_form');
        if (statementForm && languagesContainer) {
            statementForm.addEventListener('submit', function () {
                Array.prototype.slice.call(languagesContainer.querySelectorAll('.language-row')).forEach(function (row) {
                    var lang = row.querySelector('input[name$="[lang]"]');
                    var level = row.querySelector('select[name$="[level]"]');
                    var langValue = lang ? lang.value.trim() : '';
                    var levelValue = level ? level.value.trim() : '';

                    if (langValue === '' || levelValue === '') {
                        row.remove();
                    }
                });
            });
        }
    })();
</script>
@endpush
