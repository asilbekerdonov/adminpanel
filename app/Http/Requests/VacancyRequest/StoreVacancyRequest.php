<?php

namespace App\Http\Requests\VacancyRequest;

use App\Http\Requests\VacancyRequest\Concerns\HasVacancyRequestRules;
use App\Models\VacancyRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreVacancyRequest extends FormRequest
{
    use HasVacancyRequestRules;

    public function authorize(): bool
    {
        return $this->user()?->can('create', VacancyRequest::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->vacancyRequestRules(), [
            'position_id' => ['required', 'integer', 'exists:positions,id'],
        ]);
    }
}
