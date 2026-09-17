{{-- resources/views/statements/partials/actions.blade.php --}}
{{-- Диспетчер доступных действий по роли и правам. --}}

@php
    $user = auth()->user();
    $isRequester = (int) auth()->id() === (int) $statement->requester_id;

    $hasRequesterActions = $isRequester && (
        $user->can('edit', $statement)
        || $user->can('submit', $statement)
        || $user->can('confirmClose', $statement)
    );

    $hasHrActions = $user->hasUserRole(\App\Enums\UserRole::HR_MANAGER)
        && $user->can('update', $statement);

    $hasSupervisorActions = $user->hasUserRole(\App\Enums\UserRole::SUPER_ADMIN)
        && ($user->can('decide', $statement) || $statement->supervisor_reviewed_at !== null);
@endphp

@if($hasRequesterActions)
    @include('statements.partials.actions_requester', ['statement' => $statement])
@endif

@can('update', $statement)
    @if($user->hasUserRole(\App\Enums\UserRole::HR_MANAGER))
        @include('statements.partials.actions_hr', [
            'statement' => $statement,
            'supervisors' => $supervisors ?? collect(),
        ])
    @endif
@endcan

@if($hasSupervisorActions)
    @include('statements.partials.actions_supervisor', ['statement' => $statement])
@endif

@unless($hasRequesterActions || $hasHrActions || $hasSupervisorActions)
    <div class="text-app-muted small text-center py-2">
        <i class="bi bi-lock me-1"></i>
        Действия недоступны в текущем статусе
    </div>
@endunless
