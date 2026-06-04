<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('calendar');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if ($user && $user->locked_until && now()->lt($user->locked_until)) {
            $remaining = now()->diffInMinutes($user->locked_until) + 1;
            return back()->withErrors([
                'username' => "Hesab müvəqqəti bloklanıb. {$remaining} dəqiqə sonra yenidən cəhd edin.",
            ])->onlyInput('username');
        }

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'is_active' => true], $request->boolean('remember'))) {
            if ($user->two_factor_confirmed_at) {
                Auth::logout();
                session(['2fa_user_id' => $user->id]);
                return redirect()->route('two-factor.challenge');
            }

            $request->session()->regenerate();
            $user->update(['login_attempts' => 0, 'locked_until' => null]);

            AuditLog::create([
                'user_id'    => Auth::id(),
                'action'     => 'Daxil oldu',
                'target'     => null,
                'created_at' => now(),
            ]);

            return redirect()->intended(route('calendar'));
        }

        if ($user) {
            $attempts = $user->login_attempts + 1;
            $lockedUntil = $attempts >= self::MAX_ATTEMPTS
                ? now()->addMinutes(self::LOCKOUT_MINUTES)
                : null;

            $user->update(['login_attempts' => $attempts, 'locked_until' => $lockedUntil]);

            $remaining = self::MAX_ATTEMPTS - $attempts;

            if ($lockedUntil) {
                return back()->withErrors([
                    'username' => self::MAX_ATTEMPTS . ' uğursuz cəhddən sonra hesab ' . self::LOCKOUT_MINUTES . ' dəqiqəlik bloklandı.',
                ])->onlyInput('username');
            }

            if ($remaining > 0) {
                return back()->withErrors([
                    'username' => "İstifadəçi adı və ya şifrə yanlışdır. Daha {$remaining} cəhd qalıb.",
                ])->onlyInput('username');
            }
        }

        return back()->withErrors([
            'username' => 'İstifadəçi adı və ya şifrə yanlışdır.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Çıxış etdi',
            'target'     => null,
            'created_at' => now(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
