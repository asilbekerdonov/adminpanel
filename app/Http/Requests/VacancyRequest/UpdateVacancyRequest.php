<?php

namespace App\Http\Requests\VacancyRequest;

use App\Http\Requests\VacancyRequest\Concerns\HasVacancyRequestRules;
use App\Models\VacancyRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVacancyRequest extends FormRequest
{
    use HasVacancyRequestRules;

    public function authorize(): bool
    {
        $statement = $this->route('statement');

        return $statement instanceof VacancyRequest
            && ($this->user()?->can('update', $statement) ?? false);
    }

    /**
     * position_id обязателен только когда заявитель пересохраняет черновик;
     * HR-правка его не передаёт.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->vacancyRequestRules(), [
            'position_id' => ['sometimes', 'required', 'integer', 'exists:positions,id'],
        ]);
    }
}
