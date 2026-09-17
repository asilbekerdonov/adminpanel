<?php

namespace App\Http\Controllers\Structure;

use App\Http\Requests\Structure\StoreSubdivisionRequest;
use App\Models\Subdivision;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SubdivisionController extends StructureController
{
    public function index(Request $request): View|RedirectResponse
    {
        Gate::authorize('subdivision.view');

        $departmentId = $request->integer('department_id') ?: null;

        if (! $departmentId) {
            return redirect()->route('departments.index');
        }

        $department = $this->service->department($departmentId);

        return view('structure.subdivisions', [
            'department' => $department,
            'subdivisions' => $this->service->subdivisions($department),
        ]);
    }

    /** Страница «Моё подразделение» для сотрудника. */
    public function mine(Request $request): View
    {
        return view('employee.subdivision', $this->service->employeeStructure($request->user()));
    }

    public function store(StoreSubdivisionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $department = $this->service->department((int) $data['department_id']);
        unset($data['department_id']);

        $this->service->createSubdivision($department, $data);

        return redirect()->route('subdivisions.index', ['department_id' => $department->id])
            ->with('success', 'Подразделение успешно создано.');
    }

    public function destroy(Subdivision $subdivision): RedirectResponse
    {
        Gate::authorize('subdivision.manage');

        return $this->deleteAction(
            fn () => $this->service->deleteSubdivision($subdivision),
            'subdivisions.index',
            ['department_id' => $subdivision->department_id],
            'Подразделение удалено.',
        );
    }
}
