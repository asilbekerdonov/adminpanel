<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case HR_MANAGER = 'hr_manager';
    case DEPARTMENT_HEAD = 'department_head';
    case EMPLOYEE = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Руководитель / Администратор',
            self::HR_MANAGER => 'HR-менеджер',
            self::DEPARTMENT_HEAD => 'Руководитель подразделения',
            self::EMPLOYEE => 'Сотрудник',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $role): string => $role->value, self::cases());
    }
}
