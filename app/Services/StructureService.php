<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Exceptions\StructureNotEmptyException;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Position;
use App\Models\Subdivision;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StructureService
{
    public function __construct(
        private readonly EmployeeCodeGenerator $codes,
    ) {}

    public function branch(int $id): Branch
    {
        return Branch::findOrFail($id);
    }

    public function department(int $id): Department
    {
        return Department::findOrFail($id);
    }

    public function subdivision(int $id): Subdivision
    {
        return Subdivision::findOrFail($id);
    }

    /**
     * @return Collection<int, Branch>
     */
    public function branches(): Collection
    {
        return Branch::withCount('departments')->orderByDesc('created_at')->get();
    }

    /**
     * Активные филиалы с отделами; при $branchId — только выбранный.
     *
     * @return Collection<int, Branch>
     */
    public function activeBranches(?int $branchId = null): Collection
    {
        return Branch::query()
            ->with('departments')
            ->where('is_active', true)
            ->when($branchId, fn ($query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Department>
     */
    public function departments(Branch $branch): Collection
    {
        return Department::where('branch_id', $branch->id)
            ->withCount('subdivisions')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @return Collection<int, Subdivision>
     */
    public function subdivisions(Department $department): Collection
    {
        return Subdivision::where('department_id', $department->id)
            ->with(['positions.users'])
            ->withCount('positions')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Position>
     */
    public function positions(Subdivision $subdivision): Collection
    {
        return Position::where('subdivision_id', $subdivision->id)
            ->with('users')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Данные для страницы «Моё подразделение».
     *
     * @return array{user: User, subdivision: Subdivision|null, positions: Collection<int, Position>}
     */
    public function employeeStructure(User $user): array
    {
        $user->load(['branch', 'department', 'subdivision.positions.users', 'subdivision.head', 'position']);

        return [
            'user' => $user,
            'subdivision' => $user->subdivision,
            'positions' => $user->subdivision?->positions ?? new Collection,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createBranch(array $data): Branch
    {
        return Branch::create($data);
    }

    public function deleteBranch(Branch $branch): void
    {
        if ($branch->departments()->exists()) {
            throw new StructureNotEmptyException('Нельзя удалить филиал с отделами.');
        }

        $branch->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDepartment(Branch $branch, array $data): Department
    {
        return $branch->departments()->create($data);
    }

    public function deleteDepartment(Department $department): void
    {
        if ($department->subdivisions()->exists()) {
            throw new StructureNotEmptyException('Нельзя удалить отдел с подразделениями.');
        }

        $department->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createSubdivision(Department $department, array $data): Subdivision
    {
        return $department->subdivisions()->create($data);
    }

    public function deleteSubdivision(Subdivision $subdivision): void
    {
        if ($subdivision->positions()->exists()) {
            throw new StructureNotEmptyException('Нельзя удалить подразделение с должностями.');
        }

        $subdivision->delete();
    }

    /**
     * Создаёт должность и, если передан email, сразу сотрудника на ней.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPosition(Subdivision $subdivision, array $data): Position
    {
        return DB::transaction(function () use ($subdivision, $data): Position {
            $withUser = ! empty($data['email']);

            $position = $subdivision->positions()->create([
                'name' => $data['name'],
                'category' => $data['category'],
                'grade' => $data['grade'],
                'is_vacant' => ! $withUser,
            ]);

            if (! $withUser) {
                return $position;
            }

            $subdivision->load('department.branch');

            $user = User::create([
                'name' => $data['user_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'employee_code' => $this->codes->generate(),
                'position_id' => $position->id,
                'subdivision_id' => $subdivision->id,
                'department_id' => $subdivision->department_id,
                'branch_id' => $subdivision->department->branch_id,
            ]);

            $user->assignRole($data['role']);

            if ($data['role'] === UserRole::DEPARTMENT_HEAD->value) {
                $subdivision->update(['head_user_id' => $user->id]);
            }

            return $position;
        });
    }

    public function deletePosition(Position $position, User $actor): void
    {
        if ((int) $actor->position_id === (int) $position->id) {
            throw new StructureNotEmptyException('Нельзя удалить свою собственную должность.');
        }

        DB::transaction(function () use ($position): void {
            $position->users()->each(function (User $user): void {
                $user->roles()->detach();
                $user->delete();
            });

            $position->delete();
        });
    }
}
