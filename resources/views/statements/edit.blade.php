{{-- resources/views/statements/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="h5 fw-semibold mb-1">
                    <i class="bi bi-pencil-square me-2"></i>Редактирование заявки
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 fs-08">
                        <li class="breadcrumb-item">
                            <a href="{{ route('statements.index') }}" class="text-app-primary">Мои заявки</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('statements.show', $statement) }}" class="text-app-primary">Заявка</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Редактирование</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('statements.show', $statement) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <x-flash />

        @include('statements.partials.form', [
            'action' => route('statements.update', $statement),
            'method' => 'PUT',
            'submitLabel' => 'Сохранить изменения',
            'statement' => $statement,
        ])
    </div>
</x-app-layout>
