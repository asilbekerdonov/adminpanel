<?php

namespace App\Http\Controllers\Structure;

use App\Enums\UserRole;
use App\Http\Requests\Structure\StoreDepartmentRequest;
use App\Models\Department;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DepartmentController extends StructureController
{
    public function index(Request $request): View
    {
        Gate::authorize('department.view');

        $user = $request->user();
        $branchId = $request->integer('branch_id') ?: null;

        // HR, привязанный к филиалу, видит только его.
        if ($user->hasUserRole(UserRole::HR_MANAGER) && $user->branch_id) {
            $branchId = (int) $user->branch_id;
        }

        $branches = $this->service->activeBranches($branchId);
        $branch = $branchId ? $branches->first() : null;

        return view('structure.departments', [
            'branches' => $branches,
            'branch' => $branch,
            'departments' => $branch ? $this->service->departments($branch) : new Collection,
        ]);
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $branch = $this->service->branch((int) $data['branch_id']);
        unset($data['branch_id']);

        $this->service->createDepartment($branch, $data);

        return redirect()->route('departments.index', ['branch_id' => $branch->id])
            ->with('success', 'Отдел успешно создан.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        Gate::authorize('department.manage');

        return $this->deleteAction(
            fn () => $this->service->deleteDepartment($department),
            'departments.index',
            ['branch_id' => $department->branch_id],
            'Отдел удалён.',
        );
    }
}
