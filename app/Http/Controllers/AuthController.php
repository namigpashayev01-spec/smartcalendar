<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
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

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();

            AuditLog::create([
                'user_id'    => Auth::id(),
                'action'     => 'Daxil oldu',
                'target'     => null,
                'created_at' => now(),
            ]);

            return redirect()->intended(route('calendar'));
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
