<?php

namespace App\Repositories\Contracts;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

interface UserRepositoryInterface
{
    /**
     * @return Collection<int, User>
     */
    public function getByRole(UserRole $role): Collection;

    public function find(int $id): ?User;

    /**
     * Роли, которые можно назначить сотруднику (кроме super_admin).
     *
     * @return Collection<int, Role>
     */
    public function assignableRoles(): Collection;
}
