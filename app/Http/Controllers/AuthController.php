<?php

namespace App\Http\Controllers;

use App\Domain\Tenant\EmployerContext;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('employers.index');
        }

        $operators = User::where('status', 'ACTIVE')->get();
        return view('auth.login', compact('operators'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
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
