<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Auto login as a user with the specified role.
     */
    public function autoLogin(string $role): RedirectResponse
    {
        $normalizedRole = strtoupper($role);

        if (! in_array($normalizedRole, ['OWNER', 'ADMIN', 'ANALYST', 'OPERATOR', 'VIEWER'], true)) {
            abort(404);
        }

        $email = strtolower($normalizedRole) . '@example.com';

        $org = \App\Models\Organization::firstOrCreate([
            'slug' => 'default-org',
        ], [
            'name' => 'Default Organization',
            'timezone' => 'UTC',
        ]);

        $user = \App\Models\User::firstOrCreate(
            [
                'organization_id' => $org->id,
                'email' => $email,
            ],
            [
                'full_name' => ucfirst(strtolower($normalizedRole)) . ' User',
                'role' => $normalizedRole,
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'status' => 'ACTIVE',
                'email_verified_at' => now(),
            ]
        );

        if (! $user->email_verified_at) {
            $user->update([
                'email_verified_at' => now(),
                'status' => 'ACTIVE',
            ]);
        }

        Auth::login($user);

        request()->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
