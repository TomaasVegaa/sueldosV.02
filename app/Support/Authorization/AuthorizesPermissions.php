<?php

namespace App\Support\Authorization;

use App\Domain\Tenant\EmployerContext;
use Illuminate\Support\Facades\Auth;

trait AuthorizesPermissions
{
    protected function authorizeAnyPermission(string $permission): void
    {
        $user = Auth::user();

        abort_unless($user && $user->hasAnyPermission($permission), 403);
    }

    protected function authorizeGlobalPermission(string $permission): void
    {
        $user = Auth::user();

        abort_unless($user && $user->hasPermission($permission), 403);
    }

    protected function authorizeEmployerPermission(string $permission, ?int $employerId = null): void
    {
        $user = Auth::user();
        $employerId ??= EmployerContext::getActiveEmployerId();

        abort_unless(
            $user && $employerId !== null && $user->hasPermission($permission, $employerId),
            403
        );
    }
}
