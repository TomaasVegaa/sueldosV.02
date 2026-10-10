<?php

namespace App\Domain\Tenant;

use App\Models\Employer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class EmployerContext
{
    protected static ?int $overrideEmployerId = null;

    /**
     * Get the active employer ID for the current session/request.
     */
    public static function getActiveEmployerId(): ?int
    {
        if (static::$overrideEmployerId !== null) {
            return static::$overrideEmployerId;
        }

        if (! Auth::check()) {
            return null;
        }

        $user = Auth::user();

        if (Session::has('active_employer_id')) {
            $employerId = (int) Session::get('active_employer_id');

            if ($user->accessibleEmployers()->contains('id', $employerId)) {
                return $employerId;
            }

            Session::forget('active_employer_id');
        }

        // Auto-select first accessible employer if user is logged in
        $first = $user->accessibleEmployers()->first();
        if ($first) {
            Session::put('active_employer_id', $first->id);

            return $first->id;
        }

        return null;
    }

    /**
     * Set the active employer ID in session.
     */
    public static function setActiveEmployerId(?int $employerId): void
    {
        if ($employerId === null) {
            Session::forget('active_employer_id');
        } else {
            if (! Auth::check() || ! Auth::user()->accessibleEmployers()->contains('id', $employerId)) {
                throw new AuthorizationException('No tiene acceso al empleador solicitado.');
            }

            Session::put('active_employer_id', $employerId);
        }
    }

    /**
     * Get the active Employer model.
     */
    public static function getActiveEmployer(): ?Employer
    {
        $id = static::getActiveEmployerId();

        return $id ? Employer::find($id) : null;
    }

    /**
     * Temporarily run a callback under a specific employer context.
     */
    public static function runInContext(int $employerId, callable $callback): mixed
    {
        $previous = static::$overrideEmployerId;
        static::$overrideEmployerId = $employerId;

        try {
            return $callback();
        } finally {
            static::$overrideEmployerId = $previous;
        }
    }
}
