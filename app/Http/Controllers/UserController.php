<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $this->requireAdmin();
        $users = User::orderBy('role')->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function updateEmail(Request $request, User $user)
    {
        $this->requireAdmin();

        $request->validate([
            'email' => ['required', 'email', 'regex:/@gmail\.com$/i', 'unique:users,email,' . $user->id],
        ], [
            'email.regex' => 'Yalnız Gmail (@gmail.com) ünvanları qəbul edilir.',
        ]);

        // Əvvəlki email dəyişirsə 2FA sıfırla
        if ($user->email !== $request->email) {
            $user->two_factor_secret       = null;
            $user->two_factor_confirmed_at = null;
        }

        $user->email = $request->email;
        $user->save();

        return back()->with('success', "{$user->name} üçün Gmail yeniləndi. Növbəti girişdə 2FA qurulacaq.");
    }

    public function resetTwoFactor(User $user)
    {
        $this->requireAdmin();

        $user->update([
            'two_factor_secret'       => null,
            'two_factor_confirmed_at' => null,
        ]);

        return back()->with('success', "{$user->name} üçün 2FA sıfırlandı.");
    }

    private function requireAdmin(): void
    {
        abort_if(!Auth::user()->isAdmin(), 403);
    }
}
