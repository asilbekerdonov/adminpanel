<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class EmployeeCodeGenerator
{
    public function generate(): string
    {
        do {
            $code = 'EMP-'.strtoupper(Str::random(3)).str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
        } while (User::where('employee_code', $code)->exists());

        return $code;
    }
}
