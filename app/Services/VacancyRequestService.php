<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\VacancyRequestStatus;
use App\Models\Position;
use App\Models\State;
use App\Models\User;
use App\Models\VacancyRequest;
use App\Models\VacancyRequestLog;
use App\Repositories\Contracts\VacancyRequestRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class VacancyRequestService
{
    public function __construct(
        private readonly VacancyRequestRepositoryInterface $repository,
        private readonly NotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDraft(array $data, User $creator): VacancyRequest
    {
        return DB::transaction(function () use ($data, $creator): VacancyRequest {
            $position = Position::findOrFail($data['position_id']);
            $this->assertPositionAllowed($position, $creator);

            $statement = $this->repository->create(array_merge($data, [
                'requester_id' => $creator->id,
                'branch_id' => $creator->branch_id,
                'department_id' => $creator->department_id,
                'subdivision_id' => $creator->subdivision_id,
                'position_category' => $position->category,
                'workplace' => $creator->branch?->name,
                'status' => VacancyRequestStatus::DRAFT,
                'state_id' => $this->stateId(VacancyRequestStatus::DRAFT),
            ]));

            $this->log($statement, $creator, VacancyRequestStatus::DRAFT, 'Заявка создана как черновик');

            return $statement;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDraft(VacancyRequest $statement, array $data, User $actor): VacancyRequest
    {
        return DB::transaction(function () use ($statement, $data, $actor): VacancyRequest {
            if (array_key_exists('position_id', $data)) {
                $position = Position::findOrFail($data['position_id']);
                $this->assertPositionAllowed($position, $actor);
                $data['position_category'] = $position->category;
            }

            $this->repository->update($statement, $data);
            $this->log($statement, $actor, VacancyRequestStatus::DRAFT, 'Черновик обновлён заявителем');

            return $statement;
        });
    }

    /**
     * Сохраняет правку заявки: HR на этапе проверки либо заявитель в черновике.
     *
     * @param  array<string, mixed>  $data
     */
    public function save(VacancyRequest $statement, array $data, User $actor): void
    {
        if ($actor->hasUserRole(UserRole::HR_MANAGER) && $statement->canEditByHr()) {
            $this->reviewByHr($statement, $data, $actor);

            return;
        }

        $this->updateDraft($statement, $data, $actor);
    }

    public function submitToHr(VacancyRequest $statement, User $actor): void
    {
        DB::transaction(function () use ($statement, $actor): void {
            $this->repository->update($statement, [
                'status' => VacancyRequestStatus::SUBMITTED,
                'state_id' => $this->stateId(VacancyRequestStatus::SUBMITTED),
                'submitted_at' => now(),
            ]);

            $this->log($statement, $actor, VacancyRequestStatus::SUBMITTED, 'Заявка отправлена в HR на рассмотрение');
            $this->notifications->onSubmittedToHr($statement);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function reviewByHr(VacancyRequest $statement, array $data, User $hr): void
    {
        DB::transaction(function () use ($statement, $data, $hr): void {
            $this->repository->update($statement, array_merge($data, [
                'status' => VacancyRequestStatus::HR_REVIEWED,
                'state_id' => $this->stateId(VacancyRequestStatus::HR_REVIEWED),
                'hr_editor_id' => $hr->id,
                'edited_by' => $hr->id,
            ]));

            $this->log($statement, $hr, VacancyRequestStatus::HR_REVIEWED, "HR ({$hr->name}) внёс изменения в заявку");
            $this->notifications->onHrEdited($statement, $hr);
        });
    }

    public function sendToSupervisor(VacancyRequest $statement, User $supervisor, User $actor): void
    {
        DB::transaction(function () use ($statement, $supervisor, $actor): void {
            $this->repository->update($statement, [
                'status' => VacancyRequestStatus::SUPERVISOR_REVIEW,
                'state_id' => $this->stateId(VacancyRequestStatus::SUPERVISOR_REVIEW),
                'supervisor_id' => $supervisor->id,
                'sent_to_supervisor_at' => now(),
            ]);

            $this->log($statement, $actor, VacancyRequestStatus::SUPERVISOR_REVIEW, 'HR отправил заявку на подпись руководителю');
            $this->notifications->onSentToSupervisor($statement);
        });
    }

    public function decide(
        VacancyRequest $statement,
        VacancyRequestStatus $decision,
        User $actor,
        ?string $comment = null,
    ): void {
        if (! in_array($decision, [
            VacancyRequestStatus::APPROVED,
            VacancyRequestStatus::REJECTED,
            VacancyRequestStatus::ON_HOLD,
        ], true)) {
            throw new InvalidArgumentException("Недопустимое решение по заявке: {$decision->value}");
        }

        DB::transaction(function () use ($statement, $decision, $actor, $comment): void {
            $data = [
                'status' => $decision,
                'state_id' => $this->stateId($decision),
                'supervisor_id' => $actor->id,
                'supervisor_comment' => $comment,
                'supervisor_reviewed_at' => now(),
            ];

            if ($decision === VacancyRequestStatus::APPROVED) {
                $data['approved_at'] = now();
            }

            $this->repository->update($statement, $data);

            $logComment = match ($decision) {
                VacancyRequestStatus::APPROVED => 'Заявка одобрена руководителем',
                VacancyRequestStatus::REJECTED => 'Заявка отклонена руководителем',
                VacancyRequestStatus::ON_HOLD => 'Заявка приостановлена руководителем',
            };

            $this->log($statement, $actor, $decision, $logComment.($comment ? ": {$comment}" : ''));
            $this->notifications->onSupervisorDecision($statement, $actor, $decision);
        });
    }

    public function confirmClose(VacancyRequest $statement, User $actor): void
    {
        DB::transaction(function () use ($statement, $actor): void {
            $this->repository->update($statement, [
                'status' => VacancyRequestStatus::CONFIRMED_CLOSED,
                'state_id' => $this->stateId(VacancyRequestStatus::CONFIRMED_CLOSED),
            ]);

            $this->log($statement, $actor, VacancyRequestStatus::CONFIRMED_CLOSED, 'Заявитель подтвердил закрытие вакансии');
            $this->notifications->onConfirmedClosed($statement);
        });
    }

    public function delete(VacancyRequest $statement): void
    {
        DB::transaction(fn () => $this->repository->delete($statement));
    }

    private function stateId(VacancyRequestStatus $status): ?int
    {
        return State::byKey($status->value)?->id;
    }

    private function log(VacancyRequest $statement, User $user, VacancyRequestStatus $status, string $comment): void
    {
        VacancyRequestLog::create([
            'vacancy_request_id' => $statement->id,
            'user_id' => $user->id,
            'status' => $status->value,
            'comment' => $comment,
        ]);
    }

    private function assertPositionAllowed(Position $position, User $user): void
    {
        if (! $user->hasUserRole(UserRole::DEPARTMENT_HEAD)) {
            return;
        }

        if ((int) $position->subdivision_id !== (int) $user->subdivision_id) {
            throw ValidationException::withMessages([
                'position_id' => 'Должность не относится к вашему подразделению.',
            ]);
        }
    }
}
