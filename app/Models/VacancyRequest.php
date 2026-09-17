<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\VacancyRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacancyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id', 'branch_id', 'department_id', 'subdivision_id', 'position_id',
        'reports_to', 'subordinates', 'work_schedule', 'work_start', 'work_end',
        'position_category', 'grade', 'daily_rate', 'salary_probation', 'salary_after_probation',
        'bonuses', 'workplace', 'opening_reason', 'age_category', 'gender',
        'education', 'experience', 'languages', 'specialized_knowledge',
        'job_responsibilities', 'additional_requirements', 'vacancy_close_deadline',
        'status', 'state_id', 'hr_editor_id', 'edited_by',
        'supervisor_id', 'supervisor_comment', 'supervisor_reviewed_at', 'sent_to_supervisor_at',
        'submitted_at', 'approved_at', 'closed_at',
    ];

    protected $casts = [
        'status' => VacancyRequestStatus::class,
        'subordinates' => 'array',
        'languages' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'closed_at' => 'datetime',
        'supervisor_reviewed_at' => 'datetime',
        'sent_to_supervisor_at' => 'datetime',
        'vacancy_close_deadline' => 'date',
    ];

    // ─── Relationships ────────────────────────────────────────

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function subdivision()
    {
        return $this->belongsTo(Subdivision::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function hrEditor()
    {
        return $this->belongsTo(User::class, 'hr_editor_id');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function logs()
    {
        return $this->hasMany(VacancyRequestLog::class)->orderBy('created_at');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // ─── Status helpers ───────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === VacancyRequestStatus::DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === VacancyRequestStatus::SUBMITTED;
    }

    public function isHrReviewed(): bool
    {
        return $this->status === VacancyRequestStatus::HR_REVIEWED;
    }

    public function isSupervisorReview(): bool
    {
        return $this->status === VacancyRequestStatus::SUPERVISOR_REVIEW;
    }

    public function isApproved(): bool
    {
        return $this->status === VacancyRequestStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === VacancyRequestStatus::REJECTED;
    }

    public function isOnHold(): bool
    {
        return $this->status === VacancyRequestStatus::ON_HOLD;
    }

    public function isSearching(): bool
    {
        return $this->status === VacancyRequestStatus::SEARCHING;
    }

    public function isClosed(): bool
    {
        return $this->status === VacancyRequestStatus::CLOSED;
    }

    public function isConfirmedClosed(): bool
    {
        return $this->status === VacancyRequestStatus::CONFIRMED_CLOSED;
    }

    /** Заявитель может редактировать и отправлять только черновик. */
    public function canEditByRequester(): bool
    {
        return $this->isDraft();
    }

    /** HR может редактировать до отправки руководителю. */
    public function canEditByHr(): bool
    {
        return in_array($this->status, [
            VacancyRequestStatus::SUBMITTED,
            VacancyRequestStatus::HR_REVIEWED,
        ], true);
    }

    // ─── Visibility (используется Repository и Policy) ────────

    public function isVisibleTo(User $user): bool
    {
        if ($user->hasUserRole(UserRole::SUPER_ADMIN)) {
            return $this->isSupervisorReview() || (bool) $this->status?->isDecided();
        }

        if ($user->hasUserRole(UserRole::HR_MANAGER)) {
            return ! $this->isDraft();
        }

        if ($user->hasUserRole(UserRole::DEPARTMENT_HEAD)) {
            return (int) $this->requester_id === (int) $user->id;
        }

        return false;
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match (true) {
            $user->hasUserRole(UserRole::SUPER_ADMIN) => $query->whereIn('status', [
                VacancyRequestStatus::SUPERVISOR_REVIEW->value,
                VacancyRequestStatus::APPROVED->value,
                VacancyRequestStatus::REJECTED->value,
                VacancyRequestStatus::ON_HOLD->value,
            ]),
            $user->hasUserRole(UserRole::HR_MANAGER) => $query->where('status', '!=', VacancyRequestStatus::DRAFT->value),
            $user->hasUserRole(UserRole::DEPARTMENT_HEAD) => $query->where('requester_id', $user->id),
            default => $query->whereRaw('1 = 0'),
        };
    }

    // ─── Status label & color (из state или fallback) ─────────

    public function getStatusLabelAttribute(): string
    {
        return $this->state?->label_ru ?? $this->status?->label() ?? '';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->state?->color ?? $this->status?->color() ?? 'secondary';
    }

    const OPENING_REASON_LABELS = [
        'employee_resigned' => 'Уволился сотрудник',
        'new_position' => 'Новая должность',
        'workload_increased' => 'Увеличился объём работы',
        'rotation' => 'Ротация сотрудника',
        'handover_needed' => 'Передача дел новому сотруднику',
        'other' => 'Другое',
    ];

    // ─── Sync state_id from states table ──────────────────────

    public function syncState(): void
    {
        $state = State::byKey($this->status->value);
        if ($state) {
            $this->update(['state_id' => $state->id]);
        }
    }
}
