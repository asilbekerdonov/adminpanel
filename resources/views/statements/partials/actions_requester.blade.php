{{-- resources/views/statements/partials/actions_requester.blade.php --}}
{{-- Действия заявителя: редактирование черновика, отправка в HR, подтверждение закрытия. --}}

<div class="d-grid gap-2">
    @can('edit', $statement)
        <a href="{{ route('statements.edit', $statement) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i>Редактировать
        </a>
    @endcan

    @can('submit', $statement)
        <form method="POST" action="{{ route('statements.submit-to-hr', $statement) }}">
            @csrf
            <button type="submit" class="btn btn-primary w-100"
                onclick="return confirm('Отправить заявку в HR? После отправки редактирование будет недоступно.')">
                <i class="bi bi-send me-1"></i>Отправить в HR
            </button>
        </form>
    @endcan

    @can('confirmClose', $statement)
        <form method="POST" action="{{ route('statements.confirm-close', $statement) }}">
            @csrf
            <button type="submit" class="btn btn-success w-100"
                onclick="return confirm('Подтвердить закрытие вакансии?')">
                <i class="bi bi-check-circle me-1"></i>Подтвердить закрытие
            </button>
        </form>
    @endcan
</div>
