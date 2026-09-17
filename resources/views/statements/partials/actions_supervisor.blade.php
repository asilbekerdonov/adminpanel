{{-- resources/views/statements/partials/actions_supervisor.blade.php --}}
{{-- Действия руководителя (super_admin): решение по заявке и принятое решение. --}}

@if($statement->isSupervisorReview())
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-semibold text-warning">
                <i class="bi bi-exclamation-circle me-2"></i>Требует вашего решения
            </h6>
        </div>
        <div class="card-body d-flex flex-column gap-3">

            {{-- Одобрить --}}
            <form method="POST" action="{{ route('statements.approve', $statement) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small fw-semibold" for="approve_comment">
                        Комментарий (необязательно)
                    </label>
                    <textarea id="approve_comment" name="comment" rows="2" class="form-control"
                        placeholder="Добавьте комментарий..."></textarea>
                </div>
                <button type="submit" class="btn btn-success w-100"
                    onclick="return confirm('Одобрить заявку?')">
                    <i class="bi bi-check-circle me-1"></i>Одобрить
                </button>
            </form>

            <hr>

            {{-- Отклонить --}}
            <form method="POST" action="{{ route('statements.reject', $statement) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small fw-semibold" for="reject_comment">
                        Причина отклонения <span class="text-danger">*</span>
                    </label>
                    <textarea id="reject_comment" name="comment" rows="2" class="form-control"
                        placeholder="Укажите причину..." required></textarea>
                </div>
                <button type="submit" class="btn btn-danger w-100"
                    onclick="return confirm('Отклонить заявку?')">
                    <i class="bi bi-x-circle me-1"></i>Отклонить
                </button>
            </form>

            <hr>

            {{-- Приостановить --}}
            <form method="POST" action="{{ route('statements.hold', $statement) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small fw-semibold" for="hold_comment">
                        Причина (необязательно)
                    </label>
                    <textarea id="hold_comment" name="comment" rows="2" class="form-control"
                        placeholder="Добавьте комментарий..."></textarea>
                </div>
                <button type="submit" class="btn btn-warning w-100"
                    onclick="return confirm('Приостановить заявку?')">
                    <i class="bi bi-pause-circle me-1"></i>Приостановить
                </button>
            </form>

        </div>
    </div>
@endif

@if($statement->supervisor_comment || $statement->approved_at)
    <div class="card border-0 shadow-sm">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-chat-quote me-2 text-primary"></i>Решение по заявке
            </h6>
        </div>
        <div class="card-body">
            <span class="app-status-pill bg-{{ $statement->status->color() }} bg-opacity-25">
                <span class="app-dot"></span>
                {{ $statement->status->label() }}
            </span>

            @if($statement->supervisor_comment)
                <div class="app-info-label mt-3">Комментарий руководителя</div>
                <div class="app-info-value app-pre">{{ $statement->supervisor_comment }}</div>
            @endif

            @if($statement->supervisor_reviewed_at)
                <div class="text-app-muted small mt-2">
                    <i class="bi bi-clock me-1"></i>{{ $statement->supervisor_reviewed_at->format('d.m.Y H:i') }}
                </div>
            @endif
        </div>
    </div>
@endif
