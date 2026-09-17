<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Enums\VacancyRequestStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    #[Test]
    public function user_role_labels_and_values_are_complete(): void
    {
        $this->assertSame(
            ['super_admin', 'hr_manager', 'department_head', 'employee'],
            UserRole::values(),
        );

        foreach (UserRole::cases() as $role) {
            $this->assertNotSame('', $role->label());
        }
    }

    #[Test]
    public function every_status_has_label_and_color(): void
    {
        foreach (VacancyRequestStatus::cases() as $status) {
            $this->assertNotSame('', $status->label());
            $this->assertNotSame('', $status->color());
            $this->assertSame('badge-'.$status->color(), $status->colorClass());
        }
    }

    #[Test]
    public function decided_statuses_are_approved_rejected_and_on_hold(): void
    {
        $this->assertTrue(VacancyRequestStatus::APPROVED->isDecided());
        $this->assertTrue(VacancyRequestStatus::REJECTED->isDecided());
        $this->assertTrue(VacancyRequestStatus::ON_HOLD->isDecided());
        $this->assertFalse(VacancyRequestStatus::DRAFT->isDecided());
        $this->assertFalse(VacancyRequestStatus::SUPERVISOR_REVIEW->isDecided());
    }

    #[Test]
    public function options_map_values_to_labels(): void
    {
        $options = VacancyRequestStatus::options();

        $this->assertCount(count(VacancyRequestStatus::cases()), $options);
        $this->assertSame('Черновик', $options['draft']);
    }
}
