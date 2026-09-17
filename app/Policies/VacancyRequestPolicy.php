<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Enums\VacancyRequestStatus;
use App\Models\User;
use App\Models\VacancyRequest;

class VacancyRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyUserRole(
            UserRole::SUPER_ADMIN,
            UserRole::HR_MANAGER,
            UserRole::DEPARTMENT_HEAD,
        );
    }

    public function view(User $user, VacancyRequest $statement): bool
    {
        return $statement->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return $user->hasUserRole(UserRole::DEPARTMENT_HEAD);
    }

    /** Редактирование данных заявки (черновик заявителя либо этап проверки HR). */
    public function update(User $user, VacancyRequest $statement): bool
    {
        if ($user->hasUserRole(UserRole::DEPARTMENT_HEAD)) {
            return $this->owns($user, $statement) && $statement->canEditByRequester();
        }

        if ($user->hasUserRole(UserRole::HR_MANAGER)) {
            return $statement->canEditByHr();
        }

        return false;
    }

    /** Отдельная форма редактирования — только для черновика заявителя. */
    public function edit(User $user, VacancyRequest $statement): bool
    {
        return $user->hasUserRole(UserRole::DEPARTMENT_HEAD)
            && $this->owns($user, $statement)
            && $statement->isDraft();
    }

    public function submit(User $user, VacancyRequest $statement): bool
    {
        return $user->hasUserRole(UserRole::DEPARTMENT_HEAD)
            && $this->owns($user, $statement)
            && $statement->isDraft();
    }

    public function sendToSupervisor(User $user, VacancyRequest $statement): bool
    {
        return $user->hasUserRole(UserRole::HR_MANAGER) && $statement->canEditByHr();
    }

    public function decide(User $user, VacancyRequest $statement): bool
    {
        return $user->hasUserRole(UserRole::SUPER_ADMIN) && $statement->isSupervisorReview();
    }

    public function confirmClose(User $user, VacancyRequest $statement): bool
    {
        return $user->hasUserRole(UserRole::DEPARTMENT_HEAD)
            && $this->owns($user, $statement)
            && $statement->isClosed();
    }

    public function delete(User $user, VacancyRequest $statement): bool
    {
        if (! $user->hasAnyUserRole(UserRole::HR_MANAGER, UserRole::SUPER_ADMIN)) {
            return false;
        }

        return ! in_array($statement->status, [
            VacancyRequestStatus::APPROVED,
            VacancyRequestStatus::CLOSED,
            VacancyRequestStatus::CONFIRMED_CLOSED,
        ], true);
    }

    private function owns(User $user, VacancyRequest $statement): bool
    {
        return (int) $statement->requester_id === (int) $user->id;
    }
}
