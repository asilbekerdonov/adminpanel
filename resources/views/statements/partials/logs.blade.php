{{-- resources/views/statements/partials/logs.blade.php --}}
{{-- Таймлайн истории заявки: $statement->logs. --}}

<div class="card border-0 shadow-sm">
    <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-clock-history me-2 text-primary"></i>История заявки
        </h6>
    </div>
    <div class="card-body">
        @forelse($statement->logs as $log)
            <div class="app-log">
                <span class="app-log-dot"></span>
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="badge bg-{{ $log->status_color }} bg-opacity-75 me-2">
                            {{ $log->status_label }}
                        </span>
                        <span class="small text-app">{{ $log->user?->name ?? '—' }}</span>
                        @if($log->comment)
                            <div class="text-app-muted small mt-1">{{ $log->comment }}</div>
                        @endif
                    </div>
                    <span class="text-app-muted small text-nowrap">
                        {{ $log->created_at?->format('d.m.Y H:i') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="app-empty">История пуста</div>
        @endforelse
    </div>
</div>
