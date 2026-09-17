{{-- resources/views/statements/partials/actions_hr.blade.php --}}
{{-- Действия HR-менеджера: правка полей заявки, отправка руководителю, удаление. --}}

@php
    $supervisors = $supervisors ?? collect();

    $languageRows = old('languages', $statement->languages ?? []);
    if (empty($languageRows)) {
        $languageRows = [['lang' => '', 'level' => '']];
    }

    $subordinateRows = old('subordinates', $statement->subordinates ?? []);
    if (empty($subordinateRows)) {
        $subordinateRows = [''];
    }
@endphp

@if($statement->canEditByHr())

    {{-- ═══ Правка основных полей ═══ --}}
    <form method="POST" action="{{ route('statements.update', $statement) }}" id="hr_edit_form">
        @csrf
        @method('PUT')

        <p class="app-section-title">
            <i class="bi bi-diagram-3 me-2 text-primary"></i>Должностные связи
        </p>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_reports_to">Кому подчиняется</label>
            <input type="text" id="hr_reports_to" name="reports_to"
                class="form-control @error('reports_to') is-invalid @enderror"
                value="{{ old('reports_to', $statement->reports_to) }}">
            @error('reports_to')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Кто подчиняется</label>
            <div id="hr_subordinates_container">
                @foreach($subordinateRows as $subordinate)
                    <div class="subordinate-row d-flex align-items-center gap-2 mb-2">
                        <input type="text" name="subordinates[]" class="form-control"
                            value="{{ $subordinate }}" placeholder="Должность">
                        <button type="button" class="btn btn-sm btn-outline-danger hr-remove-subordinate"
                            aria-label="Удалить">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mt-1" id="hr_add_subordinate">
                <i class="bi bi-plus me-1"></i>Добавить
            </button>
        </div>

        <hr>

        <p class="app-section-title">
            <i class="bi bi-clock me-2 text-primary"></i>График
        </p>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_work_schedule">График</label>
            <select id="hr_work_schedule" name="work_schedule"
                class="form-select @error('work_schedule') is-invalid @enderror">
                <option value="">—</option>
                @foreach(['5/2', '6/1', '7/0', '2/2'] as $schedule)
                    <option value="{{ $schedule }}"
                        {{ old('work_schedule', $statement->work_schedule) === $schedule ? 'selected' : '' }}>
                        {{ $schedule }}
                    </option>
                @endforeach
            </select>
            @error('work_schedule')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label fw-semibold" for="hr_work_start">Начало</label>
                <input type="time" id="hr_work_start" name="work_start"
                    class="form-control @error('work_start') is-invalid @enderror"
                    value="{{ old('work_start', $statement->work_start) }}">
                @error('work_start')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-6">
                <label class="form-label fw-semibold" for="hr_work_end">Конец</label>
                <input type="time" id="hr_work_end" name="work_end"
                    class="form-control @error('work_end') is-invalid @enderror"
                    value="{{ old('work_end', $statement->work_end) }}">
                @error('work_end')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_grade">Разряд</label>
            <select id="hr_grade" name="grade" class="form-select @error('grade') is-invalid @enderror">
                <option value="">—</option>
                @foreach(range(1, 5) as $grade)
                    <option value="{{ $grade }}"
                        {{ (string) old('grade', $statement->grade) === (string) $grade ? 'selected' : '' }}>
                        {{ $grade }}-й разряд
                    </option>
                @endforeach
            </select>
            @error('grade')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <hr>

        <p class="app-section-title">
            <i class="bi bi-cash-coin me-2 text-primary"></i>Заработная плата
        </p>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_salary_probation">На испытательный срок</label>
            <div class="input-group">
                <input type="number" id="hr_salary_probation" name="salary_probation" min="0"
                    class="form-control @error('salary_probation') is-invalid @enderror"
                    value="{{ old('salary_probation', $statement->salary_probation) }}">
                <span class="input-group-text">сум</span>
                @error('salary_probation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_salary_after_probation">После испытательного срока</label>
            <div class="input-group">
                <input type="number" id="hr_salary_after_probation" name="salary_after_probation" min="0"
                    class="form-control @error('salary_after_probation') is-invalid @enderror"
                    value="{{ old('salary_after_probation', $statement->salary_after_probation) }}">
                <span class="input-group-text">сум</span>
                @error('salary_after_probation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_bonuses">Бонусы и льготы</label>
            <textarea id="hr_bonuses" name="bonuses" rows="3"
                class="form-control @error('bonuses') is-invalid @enderror">{{ old('bonuses', $statement->bonuses) }}</textarea>
            @error('bonuses')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <hr>

        <p class="app-section-title">
            <i class="bi bi-info-circle me-2 text-primary"></i>Условия</p>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_opening_reason">Причина открытия</label>
            <select id="hr_opening_reason" name="opening_reason"
                class="form-select @error('opening_reason') is-invalid @enderror">
                <option value="">—</option>
                @foreach(\App\Models\VacancyRequest::OPENING_REASON_LABELS as $value => $label)
                    <option value="{{ $value }}"
                        {{ old('opening_reason', $statement->opening_reason) === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('opening_reason')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_vacancy_close_deadline">Срок закрытия</label>
            <input type="date" id="hr_vacancy_close_deadline" name="vacancy_close_deadline"
                class="form-control @error('vacancy_close_deadline') is-invalid @enderror"
                value="{{ old('vacancy_close_deadline', $statement->vacancy_close_deadline?->format('Y-m-d')) }}">
            @error('vacancy_close_deadline')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <hr>

        <p class="app-section-title">
            <i class="bi bi-person-check me-2 text-primary"></i>Требования к кандидату
        </p>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_age_category">Возраст</label>
            <input type="text" id="hr_age_category" name="age_category" placeholder="25–40"
                class="form-control @error('age_category') is-invalid @enderror"
                value="{{ old('age_category', $statement->age_category) }}">
            @error('age_category')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_gender">Пол</label>
            <select id="hr_gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                <option value="">— Не важно —</option>
                <option value="male" {{ old('gender', $statement->gender) === 'male' ? 'selected' : '' }}>Мужской</option>
                <option value="female" {{ old('gender', $statement->gender) === 'female' ? 'selected' : '' }}>Женский</option>
            </select>
            @error('gender')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_education">Образование</label>
            <select id="hr_education" name="education" class="form-select @error('education') is-invalid @enderror">
                <option value="">—</option>
                @foreach(['Среднее', 'Среднее специальное', 'Высшее', 'Магистратура'] as $education)
                    <option value="{{ $education }}"
                        {{ old('education', $statement->education) === $education ? 'selected' : '' }}>
                        {{ $education }}
                    </option>
                @endforeach
            </select>
            @error('education')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_experience">Опыт работы</label>
            <input type="text" id="hr_experience" name="experience"
                class="form-control @error('experience') is-invalid @enderror"
                value="{{ old('experience', $statement->experience) }}">
            @error('experience')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Языки</label>
            <div id="hr_languages_container">
                @foreach($languageRows as $index => $language)
                    <div class="lang-row d-flex align-items-center gap-2 mb-2">
                        <select class="form-select app-col-sm" name="languages[{{ $index }}][lang]">
                            <option value="">— Язык —</option>
                            @foreach(['Русский', 'Английский', 'Узбекский', 'Другой'] as $langName)
                                <option value="{{ $langName }}"
                                    {{ ($language['lang'] ?? '') === $langName ? 'selected' : '' }}>
                                    {{ $langName }}
                                </option>
                            @endforeach
                        </select>
                        <select class="form-select app-col-md" name="languages[{{ $index }}][level]">
                            <option value="">— Уровень —</option>
                            @foreach(['Начальный', 'Средний', 'Свободный', 'Родной'] as $level)
                                <option value="{{ $level }}"
                                    {{ ($language['level'] ?? '') === $level ? 'selected' : '' }}>
                                    {{ $level }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-danger hr-remove-lang"
                            aria-label="Удалить язык">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mt-1" id="hr_add_lang">
                <i class="bi bi-plus me-1"></i>Добавить язык
            </button>
            @error('languages')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_specialized_knowledge">Специализированные знания</label>
            <textarea id="hr_specialized_knowledge" name="specialized_knowledge" rows="3"
                class="form-control @error('specialized_knowledge') is-invalid @enderror">{{ old('specialized_knowledge', $statement->specialized_knowledge) }}</textarea>
            @error('specialized_knowledge')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_job_responsibilities">Должностные обязанности</label>
            <textarea id="hr_job_responsibilities" name="job_responsibilities" rows="4"
                class="form-control @error('job_responsibilities') is-invalid @enderror">{{ old('job_responsibilities', $statement->job_responsibilities) }}</textarea>
            @error('job_responsibilities')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold" for="hr_additional_requirements">Дополнительные требования</label>
            <textarea id="hr_additional_requirements" name="additional_requirements" rows="3"
                class="form-control @error('additional_requirements') is-invalid @enderror">{{ old('additional_requirements', $statement->additional_requirements) }}</textarea>
            @error('additional_requirements')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-save me-1"></i>Сохранить изменения
            </button>
        </div>
    </form>

    <hr>

    {{-- ═══ Отправка руководителю ═══ --}}
    @can('sendToSupervisor', $statement)
        <div class="card border-app bg-app-surface mb-3">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-send me-2 text-primary"></i>Отправить руководителю
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('statements.send-to-supervisor', $statement) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="hr_supervisor_id">
                            Руководитель <span class="text-danger">*</span>
                        </label>
                        <select id="hr_supervisor_id" name="supervisor_id" required
                            class="form-select @error('supervisor_id') is-invalid @enderror">
                            <option value="">— Выберите руководителя —</option>
                            @foreach($supervisors as $supervisor)
                                <option value="{{ $supervisor->id }}"
                                    {{ (string) old('supervisor_id') === (string) $supervisor->id ? 'selected' : '' }}>
                                    {{ $supervisor->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supervisor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Отправить заявку на согласование руководителю?')">
                            <i class="bi bi-send me-1"></i>Отправить на согласование
                        </button>
                    </div>
                </form>
                @if($supervisors->isEmpty())
                    <div class="form-text text-warning mt-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Нет пользователей с ролью super_admin
                    </div>
                @endif
            </div>
        </div>
    @endcan

    {{-- ═══ Удаление ═══ --}}
    @can('delete', $statement)
        <form method="POST" action="{{ route('statements.destroy', $statement) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger w-100"
                onclick="return confirm('Удалить заявку безвозвратно?')">
                <i class="bi bi-trash me-1"></i>Удалить заявку
            </button>
        </form>
    @endcan

    @push('scripts')
    <script>
    (function () {
        var languagesContainer = document.getElementById('hr_languages_container');
        var addLanguage = document.getElementById('hr_add_lang');
        var editForm = document.getElementById('hr_edit_form');

        if (languagesContainer && addLanguage) {
            var languageIndex = languagesContainer.querySelectorAll('.lang-row').length + 1000;

            addLanguage.addEventListener('click', function () {
                var row = document.createElement('div');
                row.className = 'lang-row d-flex align-items-center gap-2 mb-2';
                row.innerHTML = '<select class="form-select app-col-sm" name="languages[' + languageIndex + '][lang]">'
                    + '<option value="">— Язык —</option>'
                    + '<option value="Русский">Русский</option>'
                    + '<option value="Английский">Английский</option>'
                    + '<option value="Узбекский">Узбекский</option>'
                    + '<option value="Другой">Другой</option>'
                    + '</select>'
                    + '<select class="form-select app-col-md" name="languages[' + languageIndex + '][level]">'
                    + '<option value="">— Уровень —</option>'
                    + '<option value="Начальный">Начальный</option>'
                    + '<option value="Средний">Средний</option>'
                    + '<option value="Свободный">Свободный</option>'
                    + '<option value="Родной">Родной</option>'
                    + '</select>'
                    + '<button type="button" class="btn btn-sm btn-outline-danger hr-remove-lang" aria-label="Удалить язык">'
                    + '<i class="bi bi-x"></i></button>';
                languagesContainer.appendChild(row);
                languageIndex++;
            });

            languagesContainer.addEventListener('click', function (event) {
                var button = event.target.closest('.hr-remove-lang');
                if (button) {
                    button.closest('.lang-row').remove();
                }
            });

            if (editForm) {
                editForm.addEventListener('submit', function () {
                    languagesContainer.querySelectorAll('.lang-row').forEach(function (row) {
                        var lang = row.querySelector('select[name$="[lang]"]');
                        if (lang && lang.value === '') {
                            row.remove();
                        }
                    });
                });
            }
        }

        var subordinatesContainer = document.getElementById('hr_subordinates_container');
        var addSubordinate = document.getElementById('hr_add_subordinate');

        if (subordinatesContainer && addSubordinate) {
            addSubordinate.addEventListener('click', function () {
                var row = document.createElement('div');
                row.className = 'subordinate-row d-flex align-items-center gap-2 mb-2';
                row.innerHTML = '<input type="text" name="subordinates[]" class="form-control" placeholder="Должность">'
                    + '<button type="button" class="btn btn-sm btn-outline-danger hr-remove-subordinate" aria-label="Удалить">'
                    + '<i class="bi bi-x"></i></button>';
                subordinatesContainer.appendChild(row);
            });

            subordinatesContainer.addEventListener('click', function (event) {
                var button = event.target.closest('.hr-remove-subordinate');
                if (button) {
                    button.closest('.subordinate-row').remove();
                }
            });
        }
    })();
    </script>
    @endpush

@endif
