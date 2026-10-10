<?php

namespace App\Http\Controllers;

use App\Domain\Tenant\EmployerContext;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('employers.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = 'login:'.Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Demasiados intentos. Espere {$seconds} segundos antes de volver a intentar.",
            ]);
        }

        $credentials['status'] = 'ACTIVE';

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();
            $user->update(['last_login_at' => now()]);

            // Set default active employer
            EmployerContext::getActiveEmployerId();

            AuditEvent::record(
                action: 'LOGIN',
                entityType: 'User',
                entityId: $user->id,
                newValues: ['email' => $user->email, 'office' => $user->office],
                reason: "Ingreso exitoso al sistema desde {$user->office}"
            );

            return redirect()->intended(route('employers.index'));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditEvent::record(
                action: 'LOGOUT',
                entityType: 'User',
                entityId: $user->id,
                reason: 'Cierre de sesión del operador'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
