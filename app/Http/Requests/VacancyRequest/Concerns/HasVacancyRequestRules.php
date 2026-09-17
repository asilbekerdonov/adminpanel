<?php

namespace App\Http\Requests\VacancyRequest\Concerns;

trait HasVacancyRequestRules
{
    /**
     * Общие правила полей заявки на подбор (без position_id — он отличается
     * для создания и обновления).
     *
     * @return array<string, mixed>
     */
    protected function vacancyRequestRules(): array
    {
        return [
            'reports_to' => ['nullable', 'string', 'max:255'],
            'subordinates' => ['nullable', 'array', 'max:20'],
            'subordinates.*' => ['nullable', 'string', 'max:255'],
            'work_schedule' => ['nullable', 'string', 'max:50'],
            'work_start' => ['nullable', 'date_format:H:i'],
            'work_end' => ['nullable', 'date_format:H:i'],
            'grade' => ['nullable', 'integer', 'min:1', 'max:5'],
            'salary_probation' => ['nullable', 'numeric', 'min:0'],
            'salary_after_probation' => ['nullable', 'numeric', 'min:0'],
            'bonuses' => ['nullable', 'string', 'max:2000'],
            'opening_reason' => ['nullable', 'string', 'in:employee_resigned,new_position,workload_increased,rotation,handover_needed,other'],
            'age_category' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'string', 'max:50'],
            'education' => ['nullable', 'string', 'max:255'],
            'experience' => ['nullable', 'string', 'max:255'],
            'languages' => ['nullable', 'array', 'max:10'],
            'languages.*.lang' => ['required_with:languages', 'string', 'max:50'],
            'languages.*.level' => ['required_with:languages', 'string', 'max:50'],
            'specialized_knowledge' => ['nullable', 'string', 'max:5000'],
            'job_responsibilities' => ['nullable', 'string', 'max:5000'],
            'additional_requirements' => ['nullable', 'string', 'max:5000'],
            'vacancy_close_deadline' => ['nullable', 'date'],
        ];
    }
}
