<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    // 2FA qurulum səhifəsi
    public function setup()
    {
        $user = Auth::user();

        $secret = session('2fa_setup_secret') ?? $this->google2fa->generateSecretKey();
        session(['2fa_setup_secret' => $secret]);

        $qrUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->username,
            $secret
        );

        $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd());
        $qrSvg    = base64_encode((new Writer($renderer))->writeString($qrUrl));

        return view('auth.two-factor-setup', compact('qrSvg', 'secret', 'user'));
    }

    // 2FA aktivləşdirmə
    public function enable(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $secret = session('2fa_setup_secret');

        if (!$secret || !$this->google2fa->verifyKey($secret, $request->code)) {
            return back()->withErrors(['code' => 'Kod yanlışdır. Yenidən cəhd edin.']);
        }

        Auth::user()->update([
            'two_factor_secret'       => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        session()->forget('2fa_setup_secret');

        return redirect()->route('calendar')->with('success', '2FA uğurla aktivləşdirildi.');
    }

    // 2FA deaktiv etmə
    public function disable(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $user = Auth::user();

        if (!$this->google2fa->verifyKey($user->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'Kod yanlışdır.']);
        }

        $user->update([
            'two_factor_secret'       => null,
            'two_factor_confirmed_at' => null,
        ]);

        return redirect()->route('calendar')->with('success', '2FA deaktiv edildi.');
    }

    // Giriş zamanı 2FA yoxlama səhifəsi
    public function challenge()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    // Giriş zamanı 2FA kodu yoxla
    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $userId = session('2fa_user_id');
        $user   = User::find($userId);

        if (!$user || !$this->google2fa->verifyKey($user->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'Kod yanlışdır.']);
        }

        session()->forget('2fa_user_id');

        Auth::login($user);
        $request->session()->regenerate();
        session(['2fa_verified' => true]);

        $user->update(['login_attempts' => 0, 'locked_until' => null]);

        AuditLog::create([
            'user_id'    => $user->id,
            'action'     => 'Daxil oldu (2FA)',
            'target'     => null,
            'created_at' => now(),
        ]);

        return redirect()->intended(route('calendar'));
    }
}
