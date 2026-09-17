<?php

namespace App\Enums;

enum VacancyRequestStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case HR_REVIEWED = 'hr_reviewed';
    case SUPERVISOR_REVIEW = 'supervisor_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case ON_HOLD = 'on_hold';
    case SEARCHING = 'searching';
    case CLOSED = 'closed';
    case CONFIRMED_CLOSED = 'confirmed_closed';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Черновик',
            self::SUBMITTED => 'Отправлена в HR',
            self::HR_REVIEWED => 'HR рассматривает',
            self::SUPERVISOR_REVIEW => 'На подписи у руководителя',
            self::APPROVED => 'Одобрена',
            self::REJECTED => 'Отклонена',
            self::ON_HOLD => 'Приостановлена',
            self::SEARCHING => 'Идёт поиск',
            self::CLOSED => 'HR закрыл',
            self::CONFIRMED_CLOSED => 'Закрыта (подтверждено)',
        };
    }

    /** Bootstrap color name (`bg-{color}` in views). */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT, self::CLOSED => 'secondary',
            self::SUBMITTED => 'info',
            self::HR_REVIEWED, self::SEARCHING => 'primary',
            self::SUPERVISOR_REVIEW, self::ON_HOLD => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::CONFIRMED_CLOSED => 'dark',
        };
    }

    public function colorClass(): string
    {
        return 'badge-'.$this->color();
    }

    /** Статусы, видимые руководителю (super_admin) в архиве согласования. */
    public function isDecided(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED, self::ON_HOLD], true);
    }

    /**
     * @return array<string, string> value => label (для select'ов)
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
