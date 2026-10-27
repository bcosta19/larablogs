<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function login(): Response
    {
        return Inertia::render('Login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $key = 'login:'.hash('sha256', Str::lower($data['email']).'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => 'auth.throttled']);
        }
        if (! Auth::attempt(['email' => Str::lower($data['email']), 'password' => $data['password'], 'is_owner' => true])) {
            RateLimiter::hit($key, 60);

            return back()->withErrors(['email' => 'auth.failed']);
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();

        return $request->user()->must_change_password
            ? redirect()->route('password.edit')
            : redirect()->intended(route('home'));
    }

    public function password(): Response
    {
        return Inertia::render('ChangePassword');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12), 'different:current_password'],
        ], [
            'current_password.current_password' => 'auth.current_invalid',
            'password.min' => 'auth.password_min',
            'password.confirmed' => 'auth.password_confirmation',
            'password.different' => 'auth.password_different',
        ]);
        $request->user()->forceFill([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
            'remember_token' => null,
        ])->save();
        if (config('session.driver') === 'database') {
            DB::table('sessions')->where('user_id', $request->user()->id)->where('id', '!=', $request->session()->getId())->delete();
        }
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
