<?php

namespace App\Http\Controllers\Structure;

use App\Http\Requests\Structure\StoreBranchRequest;
use App\Models\Branch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class BranchController extends StructureController
{
    public function index(): View
    {
        Gate::authorize('branch.view');

        return view('structure.branches', [
            'branches' => $this->service->branches(),
        ]);
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $this->service->createBranch($request->validated());

        return redirect()->route('branches.index')->with('success', 'Филиал успешно создан.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        Gate::authorize('branch.manage');

        return $this->deleteAction(
            fn () => $this->service->deleteBranch($branch),
            'branches.index',
            [],
            'Филиал удалён.',
        );
    }
}
