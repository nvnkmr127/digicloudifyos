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
        $user = \App\Models\User::where('role', strtoupper($role))->first();

        if (!$user) {
            // If user doesn't exist, try to create one or find any user
            $orgId = \App\Models\Organization::first()->id;
            $user = \App\Models\User::create([
                'organization_id' => $orgId,
                'email' => strtolower($role) . '@example.com',
                'full_name' => ucfirst(strtolower($role)) . ' User',
                'role' => strtoupper($role),
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'status' => 'ACTIVE',
            ]);
        }

        Auth::login($user);

        request()->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
