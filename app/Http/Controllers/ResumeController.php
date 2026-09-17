<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ResumeController extends Controller
{
    /**
     * Удаление резюме, пришедшего от resume-bot (внешняя БД resume_bot).
     */
    public function destroy(int $resume): RedirectResponse
    {
        abort_unless(
            request()->user()?->hasAnyUserRole(UserRole::HR_MANAGER, UserRole::SUPER_ADMIN),
            403,
        );

        try {
            DB::connection('resume_bot')->table('resumes')->where('id', $resume)->delete();
        } catch (Throwable) {
            return back()->with('error', 'Не удалось удалить резюме.');
        }

        return back()->with('success', 'Резюме удалено.');
    }
}
