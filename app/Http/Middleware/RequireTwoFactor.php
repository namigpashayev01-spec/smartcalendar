<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireTwoFactor
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->two_factor_confirmed_at && !session('2fa_verified')) {
            Auth::logout();
            session(['2fa_user_id' => $user->id, '2fa_remember' => true]);
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
