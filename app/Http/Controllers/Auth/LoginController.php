<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * The number of minutes to throttle login attempts.
     */
    protected int $decayMinutes = 1;

    /**
     * The maximum number of login attempts allowed.
     */
    protected int $maxAttempts = 5;

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(LoginRequest $request)
    {
        $this->ensureIsNotRateLimited($request);

        $credentials = $request->only('username', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request), $this->decayMinutes * 60);
            $this->logLoginAttempt($request, false);

            throw ValidationException::withMessages([
                'username' => ['The provided credentials do not match our records.'],
            ]);
        }

        $user = Auth::user();

        if (! $user->is_active || $user->status !== 'active') {
            Auth::logout();

            throw ValidationException::withMessages([
                'username' => ['Your account is currently inactive. Please contact the administrator.'],
            ]);
        }

        $request->session()->regenerate();
        RateLimiter::clear($this->throttleKey($request));
        $this->logLoginAttempt($request, true);

        return redirect()->route('backend.dashboard');
    }

    /**
     * Log the current user out.
     */
    public function logout(Request $request)
    {
        $userId = Auth::id();

        Auth::logout();

        AuditLog::create([
            'user_id' => $userId,
            'module' => 'auth',
            'action' => 'logout',
            'description' => 'User logged out.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function ensureIsNotRateLimited(LoginRequest $request): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));

            throw ValidationException::withMessages([
                'username' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ]);
        }
    }

    protected function throttleKey(LoginRequest $request): string
    {
        return Str::lower(Str::transliterate($request->input('username'))).'|'.$request->ip();
    }

    protected function logLoginAttempt(LoginRequest $request, bool $success): void
    {
        $user = \App\Models\User::where('username', $request->input('username'))->first();

        AuditLog::create([
            'user_id' => $user?->id,
            'module' => 'auth',
            'action' => $success ? 'login_success' : 'login_failure',
            'description' => $success ? 'User logged in successfully.' : 'Failed login attempt.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
