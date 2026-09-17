<?php

namespace App\Http\Controllers\Structure;

use App\Exceptions\StructureNotEmptyException;
use App\Http\Controllers\Controller;
use App\Services\StructureService;
use Illuminate\Http\RedirectResponse;

abstract class StructureController extends Controller
{
    public function __construct(
        protected readonly StructureService $service,
    ) {}

    /**
     * Выполняет удаление, переводя бизнес-ошибку структуры в flash-сообщение.
     *
     * @param  array<string, mixed>  $parameters
     */
    protected function deleteAction(
        callable $action,
        string $route,
        array $parameters,
        string $success,
    ): RedirectResponse {
        try {
            $action();
        } catch (StructureNotEmptyException $exception) {
            return redirect()->route($route, $parameters)->with('error', $exception->getMessage());
        }

        return redirect()->route($route, $parameters)->with('success', $success);
    }
}
