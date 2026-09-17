<?php

namespace App\Http\Requests\VacancyRequest;

use App\Models\VacancyRequest;
use Illuminate\Foundation\Http\FormRequest;

class DecisionVacancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $statement = $this->route('statement');

        return $statement instanceof VacancyRequest
            && ($this->user()?->can('decide', $statement) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'comment' => [$this->routeIs('statements.reject') ? 'required' : 'nullable', 'string', 'max:1000'],
        ];
    }
}
