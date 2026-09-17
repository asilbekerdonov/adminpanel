{{-- resources/views/statements/partials/header.blade.php --}}
{{-- Шапка заявки: должность, статус, заявитель, кнопка «Назад». --}}

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">

            <div class="d-flex align-items-start gap-3">
                <span class="app-primary-bar"></span>
                <div>
                    <h5 class="mb-1 fw-semibold">{{ $statement->position?->name ?? '—' }}</h5>
                    <div class="text-app-muted small">
                        <i class="bi bi-hash me-1"></i>Заявка #{{ $statement->id }}
                    </div>
                    <div class="text-app-muted small mt-1">
                        <i class="bi bi-person me-1"></i>
                        Заявитель: {{ $statement->requester?->name ?? '—' }}
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="app-status-pill bg-{{ $statement->status->color() }} bg-opacity-25">
                    <span class="app-dot"></span>
                    {{ $statement->status->label() }}
                </span>

                <a href="{{ route('statements.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Назад
                </a>
            </div>

        </div>
    </div>
</div>
