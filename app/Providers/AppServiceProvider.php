<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\VacancyRequest;
use App\Policies\VacancyRequestPolicy;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\VacancyRequestRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\VacancyRequestRepository;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(VacancyRequestRepositoryInterface::class, VacancyRequestRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::policy(VacancyRequest::class, VacancyRequestPolicy::class);

        // Ролевые проверки оргструктуры. Если в проекте засеян
        // RolesAndPermissionsSeeder, Spatie-права сработают раньше (Gate::before).
        $manageRules = [
            'branch.manage' => UserRole::SUPER_ADMIN,
            'department.manage' => UserRole::SUPER_ADMIN,
            'subdivision.manage' => UserRole::SUPER_ADMIN,
            'position.manage' => UserRole::SUPER_ADMIN,
        ];

        foreach ($manageRules as $ability => $role) {
            Gate::define($ability, fn (User $user): bool => $user->hasUserRole($role));
        }

        foreach (['branch', 'department', 'subdivision', 'position'] as $entity) {
            Gate::define("{$entity}.view", fn (User $user): bool => $user->hasAnyUserRole(
                UserRole::SUPER_ADMIN,
                UserRole::HR_MANAGER,
                UserRole::DEPARTMENT_HEAD,
                UserRole::EMPLOYEE,
            ));
        }
    }
}
