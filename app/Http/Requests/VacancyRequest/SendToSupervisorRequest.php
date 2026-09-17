<?php

namespace App\Http\Requests\VacancyRequest;

use App\Models\VacancyRequest;
use Illuminate\Foundation\Http\FormRequest;

class SendToSupervisorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $statement = $this->route('statement');

        return $statement instanceof VacancyRequest
            && ($this->user()?->can('sendToSupervisor', $statement) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'supervisor_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
