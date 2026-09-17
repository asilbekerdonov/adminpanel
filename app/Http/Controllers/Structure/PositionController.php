<?php

namespace App\Http\Controllers\Structure;

use App\Http\Requests\Structure\StorePositionRequest;
use App\Models\Position;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\StructureService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PositionController extends StructureController
{
    public function __construct(
        StructureService $service,
        private readonly UserRepositoryInterface $users,
    ) {
        parent::__construct($service);
    }

    public function index(Request $request): View|RedirectResponse
    {
        Gate::authorize('position.view');

        $subdivisionId = $request->integer('subdivision_id') ?: null;

        if (! $subdivisionId) {
            return redirect()->route('subdivisions.index');
        }

        $subdivision = $this->service->subdivision($subdivisionId);

        return view('structure.positions', [
            'subdivision' => $subdivision,
            'positions' => $this->service->positions($subdivision),
            'roles' => $this->users->assignableRoles(),
        ]);
    }

    public function store(StorePositionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $subdivision = $this->service->subdivision((int) $data['subdivision_id']);
        unset($data['subdivision_id']);

        $this->service->createPosition($subdivision, $data);

        $message = ! empty($data['email'])
            ? 'Должность создана и сотрудник добавлен.'
            : 'Должность успешно создана.';

        return redirect()->route('positions.index', ['subdivision_id' => $subdivision->id])
            ->with('success', $message);
    }

    public function destroy(Position $position): RedirectResponse
    {
        Gate::authorize('position.manage');

        return $this->deleteAction(
            fn () => $this->service->deletePosition($position, request()->user()),
            'positions.index',
            ['subdivision_id' => $position->subdivision_id],
            'Должность и привязанный сотрудник удалены.',
        );
    }
}
