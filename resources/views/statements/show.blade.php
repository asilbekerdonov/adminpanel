{{-- resources/views/statements/show.blade.php --}}
{{-- Единая роль-независимая детальная страница заявки. --}}

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h2 class="h5 fw-semibold mb-1">
                    <i class="bi bi-file-earmark-text me-2"></i>Заявка #{{ $statement->id }}
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 fs-08">
                        <li class="breadcrumb-item">
                            <a href="{{ route('statements.index') }}" class="text-app-primary">Заявки</a>
                        </li>
                        <li class="breadcrumb-item active text-app-muted" aria-current="page">
                            #{{ $statement->id }}
                        </li>
                    </ol>
                </nav>
            </div>

            <span class="app-status-pill bg-{{ $statement->status->color() }} bg-opacity-25">
                <span class="app-dot"></span>
                {{ $statement->status->label() }}
            </span>
        </div>
    </x-slot>

    <div class="container-fluid py-4">

        <x-flash />

        @include('statements.partials.header', ['statement' => $statement])

        <div class="row g-4">

            {{-- ─── ЛЕВАЯ КОЛОНКА: детали и история ─────────────── --}}
            <div class="col-lg-8">
                @include('statements.partials.details', ['statement' => $statement])
                @include('statements.partials.logs', ['statement' => $statement])
            </div>

            {{-- ─── ПРАВАЯ КОЛОНКА: действия ────────────────────── --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-lightning me-2 text-primary"></i>Действия
                        </h6>
                    </div>
                    <div class="card-body">
                        @include('statements.partials.actions', [
                            'statement' => $statement,
                            'supervisors' => $supervisors ?? collect(),
                        ])
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
