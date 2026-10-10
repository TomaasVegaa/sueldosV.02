<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $permissions = [
            'users.manage',
            'employers.view',
            'employers.manage',
            'workforce.view',
            'workforce.manage',
            'agreements.view',
            'agreements.manage',
            'catalogs.view',
            'payroll.view',
            'payroll.calculate',
            'payroll.approve',
            'payroll.export',
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Gate::define(
                $permission,
                fn (User $user, ?int $employerId = null): bool => $employerId === null
                    ? $user->hasAnyPermission($permission)
                    : $user->hasPermission($permission, $employerId)
            );
        }
    }
}
