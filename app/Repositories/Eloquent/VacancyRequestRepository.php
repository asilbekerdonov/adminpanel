<?php

namespace App\Repositories\Eloquent;

use App\Enums\VacancyRequestStatus;
use App\Models\Position;
use App\Models\User;
use App\Models\VacancyRequest;
use App\Repositories\Contracts\VacancyRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class VacancyRequestRepository implements VacancyRequestRepositoryInterface
{
    private const LIST_RELATIONS = [
        'position',
        'subdivision',
        'requester',
        'state',
        'editedBy',
        'hrEditor',
        'branch',
        'department',
    ];

    private const DETAIL_RELATIONS = [
        'position',
        'subdivision.head',
        'department',
        'branch',
        'requester',
        'hrEditor',
        'editedBy',
        'supervisor',
        'logs.user',
        'state',
    ];

    private const DEFAULT_PER_PAGE = 15;

    private const MAX_PER_PAGE = 50;

    public function getForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? self::DEFAULT_PER_PAGE);
        $perPage = max(1, min($perPage, self::MAX_PER_PAGE));

        $status = VacancyRequestStatus::tryFrom((string) ($filters['status'] ?? ''));
        $search = trim((string) ($filters['q'] ?? ''));

        return VacancyRequest::query()
            ->visibleTo($user)
            ->with(self::LIST_RELATIONS)
            ->when($status, fn ($query) => $query->where('status', $status->value))
            ->when($search !== '', function ($query) use ($search): void {
                $term = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%';

                $query->whereHas('position', fn ($position) => $position->where('name', 'like', $term));
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(int $id): ?VacancyRequest
    {
        return VacancyRequest::with(self::DETAIL_RELATIONS)->find($id);
    }

    public function getPositionsForSubdivision(?int $subdivisionId): Collection
    {
        if (! $subdivisionId) {
            return new Collection;
        }

        return Position::where('subdivision_id', $subdivisionId)->orderBy('name')->get();
    }

    public function create(array $data): VacancyRequest
    {
        return VacancyRequest::create($data);
    }

    public function update(VacancyRequest $request, array $data): bool
    {
        return $request->update($data);
    }

    public function delete(VacancyRequest $request): void
    {
        $request->delete();
    }
}
