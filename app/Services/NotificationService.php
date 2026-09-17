<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\VacancyRequestStatus;
use App\Models\Notification;
use App\Models\User;
use App\Models\VacancyRequest;
use App\Repositories\Contracts\UserRepositoryInterface;

class NotificationService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * Заявитель отправил заявку → уведомить всех HR-менеджеров.
     */
    public function onSubmittedToHr(VacancyRequest $request): void
    {
        $positionName = $request->position?->name ?? 'должность';

        foreach ($this->users->getByRole(UserRole::HR_MANAGER) as $hr) {
            $this->send(
                $hr->id,
                $request,
                VacancyRequestStatus::SUBMITTED->value,
                "📋 Новая заявка: «{$positionName}» от {$request->requester?->name}"
            );
        }
    }

    /**
     * HR внёс изменения → уведомить заявителя.
     */
    public function onHrEdited(VacancyRequest $request, User $editor): void
    {
        $this->send(
            $request->requester_id,
            $request,
            'hr_edited',
            "✏️ HR ({$editor->name}) внёс изменения в вашу заявку на «{$request->position?->name}»"
        );
    }

    /**
     * HR отправил заявку руководителю → уведомить всех super_admin.
     */
    public function onSentToSupervisor(VacancyRequest $request): void
    {
        $positionName = $request->position?->name ?? 'должность';

        foreach ($this->users->getByRole(UserRole::SUPER_ADMIN) as $supervisor) {
            $this->send(
                $supervisor->id,
                $request,
                VacancyRequestStatus::SUPERVISOR_REVIEW->value,
                "📝 Заявка на подбор «{$positionName}» ожидает вашего решения"
            );
        }
    }

    /**
     * Руководитель принял решение → уведомить заявителя и HR.
     */
    public function onSupervisorDecision(VacancyRequest $request, User $supervisor, VacancyRequestStatus $decision): void
    {
        $labels = [
            VacancyRequestStatus::APPROVED->value => 'одобрена ✅',
            VacancyRequestStatus::REJECTED->value => 'отклонена ❌',
            VacancyRequestStatus::ON_HOLD->value => 'приостановлена ⏸',
        ];

        $label = $labels[$decision->value] ?? $decision->value;
        $positionName = $request->position?->name ?? 'должность';

        $this->send(
            $request->requester_id,
            $request,
            "supervisor_{$decision->value}",
            "Ваша заявка на «{$positionName}» {$label} руководителем {$supervisor->name}"
        );

        if ($request->hr_editor_id) {
            $this->send(
                $request->hr_editor_id,
                $request,
                "supervisor_{$decision->value}",
                "Заявка на «{$positionName}» от {$request->requester?->name} {$label}"
            );

            return;
        }

        foreach ($this->users->getByRole(UserRole::HR_MANAGER) as $hr) {
            $this->send(
                $hr->id,
                $request,
                "supervisor_{$decision->value}",
                "Заявка на «{$positionName}» от {$request->requester?->name} {$label}"
            );
        }
    }

    /**
     * HR закрыл вакансию → уведомить заявителя для подтверждения.
     */
    public function onClosedByHr(VacancyRequest $request): void
    {
        $this->send(
            $request->requester_id,
            $request,
            VacancyRequestStatus::CLOSED->value,
            "🔒 Вакансия «{$request->position?->name}» закрыта. Пожалуйста, подтвердите закрытие."
        );
    }

    /**
     * Заявитель подтвердил закрытие → уведомить HR-редактора.
     */
    public function onConfirmedClosed(VacancyRequest $request): void
    {
        if (! $request->hr_editor_id) {
            return;
        }

        $this->send(
            $request->hr_editor_id,
            $request,
            VacancyRequestStatus::CONFIRMED_CLOSED->value,
            "✅ Заявитель подтвердил закрытие вакансии «{$request->position?->name}»"
        );
    }

    protected function send(int $userId, VacancyRequest $request, string $type, string $message): void
    {
        Notification::create([
            'user_id' => $userId,
            'vacancy_request_id' => $request->id,
            'type' => $type,
            'message' => $message,
        ]);
    }
}
