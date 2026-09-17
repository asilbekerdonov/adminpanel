<?php

namespace App\Repositories\Eloquent;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class UserRepository implements UserRepositoryInterface
{
    public function getByRole(UserRole $role): Collection
    {
        return User::role($role->value)->get();
    }

    public function find(int $id): ?User
    {
        return User::find($id);
    }

    public function assignableRoles(): Collection
    {
        return Role::whereNotIn('name', [UserRole::SUPER_ADMIN->value])->get();
    }
}
