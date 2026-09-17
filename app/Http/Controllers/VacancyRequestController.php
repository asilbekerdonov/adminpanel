<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\VacancyRequestStatus;
use App\Http\Requests\VacancyRequest\DecisionVacancyRequest;
use App\Http\Requests\VacancyRequest\SendToSupervisorRequest;
use App\Http\Requests\VacancyRequest\StoreVacancyRequest;
use App\Http\Requests\VacancyRequest\UpdateVacancyRequest;
use App\Models\Position;
use App\Models\User;
use App\Models\VacancyRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\VacancyRequestRepositoryInterface;
use App\Services\VacancyRequestService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VacancyRequestController extends Controller
{
    public function __construct(
        private readonly VacancyRequestService $service,
        private readonly VacancyRequestRepositoryInterface $repository,
        private readonly UserRepositoryInterface $users,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', VacancyRequest::class);

        return view('statements.index', [
            'statements' => $this->repository->getForUser(
                $request->user(),
                $request->only(['status', 'q', 'per_page']),
            ),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', VacancyRequest::class);

        return view('statements.create', $this->formData($request));
    }

    public function store(StoreVacancyRequest $request): RedirectResponse
    {
        $this->service->createDraft($request->validated(), $request->user());

        return redirect()->route('statements.index')->with('success', 'Заявка сохранена как черновик.');
    }

    public function show(VacancyRequest $statement): View
    {
        $this->authorize('view', $statement);

        return view('statements.show', [
            'statement' => $this->repository->findById($statement->id) ?? $statement,
            'supervisors' => $this->users->getByRole(UserRole::SUPER_ADMIN),
        ]);
    }

    public function edit(Request $request, VacancyRequest $statement): View
    {
        $this->authorize('edit', $statement);

        return view('statements.edit', array_merge($this->formData($request), ['statement' => $statement]));
    }

    public function update(UpdateVacancyRequest $request, VacancyRequest $statement): RedirectResponse
    {
        $this->service->save($statement, $request->validated(), $request->user());

        $message = $request->user()->hasUserRole(UserRole::HR_MANAGER)
            ? 'Изменения сохранены.'
            : 'Заявка обновлена.';

        return redirect()->route('statements.show', $statement)->with('success', $message);
    }

    public function submit(Request $request, VacancyRequest $statement): RedirectResponse
    {
        $this->authorize('submit', $statement);
        $this->service->submitToHr($statement, $request->user());

        return redirect()->route('statements.show', $statement)->with('success', 'Заявка отправлена в HR.');
    }

    public function sendToSupervisor(SendToSupervisorRequest $request, VacancyRequest $statement): RedirectResponse
    {
        $supervisor = $this->users->find($request->integer('supervisor_id'));
        abort_if($supervisor === null, 422, 'Руководитель не найден.');

        $this->service->sendToSupervisor($statement, $supervisor, $request->user());

        return redirect()->route('statements.show', $statement)
            ->with('success', 'Заявка отправлена руководителю на подпись.');
    }

    public function approve(DecisionVacancyRequest $request, VacancyRequest $statement): RedirectResponse
    {
        $this->service->decide($statement, VacancyRequestStatus::APPROVED, $request->user(), $request->validated('comment'));

        return redirect()->route('statements.show', $statement)->with('success', 'Заявка одобрена.');
    }

    public function reject(DecisionVacancyRequest $request, VacancyRequest $statement): RedirectResponse
    {
        $this->service->decide($statement, VacancyRequestStatus::REJECTED, $request->user(), $request->validated('comment'));

        return redirect()->route('statements.show', $statement)->with('success', 'Заявка отклонена.');
    }

    public function onHold(DecisionVacancyRequest $request, VacancyRequest $statement): RedirectResponse
    {
        $this->service->decide($statement, VacancyRequestStatus::ON_HOLD, $request->user(), $request->validated('comment'));

        return redirect()->route('statements.show', $statement)->with('success', 'Заявка приостановлена.');
    }

    public function confirmClose(Request $request, VacancyRequest $statement): RedirectResponse
    {
        $this->authorize('confirmClose', $statement);
        $this->service->confirmClose($statement, $request->user());

        return redirect()->route('statements.show', $statement)->with('success', 'Закрытие вакансии подтверждено.');
    }

    public function destroy(Request $request, VacancyRequest $statement): RedirectResponse
    {
        $this->authorize('delete', $statement);
        $this->service->delete($statement);

        return redirect()->route('statements.index')->with('success', 'Заявка удалена.');
    }

    /**
     * @return array{user: User, allPositions: Collection<int, Position>, vacantPositions: Collection<int, Position>}
     */
    private function formData(Request $request): array
    {
        $user = $request->user()->load(['branch', 'department', 'subdivision', 'position']);
        $positions = $this->repository->getPositionsForSubdivision($user->subdivision_id);

        return [
            'user' => $user,
            'allPositions' => $positions,
            'vacantPositions' => $positions->where('is_vacant', true),
        ];
    }
}
