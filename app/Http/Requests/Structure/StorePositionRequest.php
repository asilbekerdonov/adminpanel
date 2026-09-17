<?php

namespace App\Http\Requests\Structure;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('position.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'subdivision_id' => ['required', 'integer', 'exists:subdivisions,id'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:A,B,C,D'],
            'grade' => ['required', 'integer', 'min:1', 'max:5'],
            'user_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ];

        if ($this->filled('email')) {
            $rules['user_name'] = ['required', 'string', 'max:255'];
            $rules['password'] = ['required', 'string', 'min:8', 'max:255'];
            $rules['role'] = ['required', 'string', 'exists:roles,name'];
        }

        return $rules;
    }
}
