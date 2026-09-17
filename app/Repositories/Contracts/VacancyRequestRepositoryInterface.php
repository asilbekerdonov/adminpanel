<?php

namespace App\Repositories\Contracts;

use App\Models\Position;
use App\Models\User;
use App\Models\VacancyRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface VacancyRequestRepositoryInterface
{
    /**
     * Список заявок, видимых пользователю согласно его роли.
     *
     * @param  array{status?: string|null, q?: string|null, per_page?: int|null}  $filters
     */
    public function getForUser(User $user, array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?VacancyRequest;

    /**
     * Должности подразделения — для формы заявки.
     *
     * @return Collection<int, Position>
     */
    public function getPositionsForSubdivision(?int $subdivisionId): Collection;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): VacancyRequest;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(VacancyRequest $request, array $data): bool;

    public function delete(VacancyRequest $request): void;
}
