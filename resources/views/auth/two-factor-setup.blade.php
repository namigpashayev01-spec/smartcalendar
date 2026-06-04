@extends('layouts.app')

@section('title', '2FA Qurulumu — Smart Calendar')

@section('content')
<div class="report-page" style="max-width:480px;">

  <div class="report-head">
    <div>
      <h2>İki addımlı doğrulama (2FA)</h2>
      <p class="report-sub">Google Authenticator ilə hesabını qoru</p>
    </div>
  </div>

  <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:1.75rem;">

    @if($user->two_factor_confirmed_at)

      {{-- Aktiv vəziyyət --}}
      <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;">
        <span style="background:#EDFAF1;border:1px solid #A3E6B4;color:#2E844A;border-radius:20px;padding:.3rem .75rem;font-size:.78rem;font-weight:600;">
          2FA Aktiv
        </span>
      </div>

      <p style="color:var(--text-dim);font-size:.88rem;line-height:1.6;margin-bottom:1.5rem;">
        2FA aktivdir. Deaktiv etmək üçün Google Authenticator-dakı kodu daxil edin.
      </p>

      <form method="POST" action="{{ route('two-factor.disable') }}">
        @csrf @method('DELETE')
        <div style="margin-bottom:1rem;">
          <label style="display:block;font-size:.78rem;font-weight:600;color:var(--text-dim);margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.5px;">Doğrulama kodu</label>
          <input type="text" name="code" maxlength="6" inputmode="numeric" placeholder="000000" autofocus
            style="width:100%;background:var(--surface-2);border:1.5px solid var(--border);border-radius:8px;padding:.65rem 1rem;color:var(--text);font-size:1.2rem;font-weight:700;text-align:center;letter-spacing:.4rem;font-family:monospace;outline:none;" />
          @error('code')
            <div style="color:#BA0517;font-size:.75rem;margin-top:.35rem;">{{ $message }}</div>
          @enderror
        </div>
        <button type="submit" style="width:100%;background:#FFF0F0;color:#BA0517;border:1px solid #FFB3B3;border-radius:8px;padding:.7rem;font-size:.9rem;font-weight:700;font-family:inherit;cursor:pointer;">
          2FA-nı Deaktiv Et
        </button>
      </form>

    @else

      {{-- Qurulum --}}
      <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;">
        <span style="background:#FFF0F0;border:1px solid #FFB3B3;color:#BA0517;border-radius:20px;padding:.3rem .75rem;font-size:.78rem;font-weight:600;">
          2FA Aktiv Deyil
        </span>
      </div>

      <p style="color:var(--text-dim);font-size:.88rem;line-height:1.6;margin-bottom:1.25rem;">
        Hesabınızı qorumaq üçün Google Authenticator tətbiqini quraşdırın.
      </p>

      {{-- Addımlar --}}
      <div style="margin-bottom:1.5rem;">
        @foreach(['Telefonunuzda Google Authenticator tətbiqini açın', '«+» düyməsinə basıb QR kodu skan edin', 'Tətbiqdəki 6 rəqəmli kodu aşağıya daxil edib təsdiqləyin'] as $i => $step)
        <div style="display:flex;gap:.75rem;margin-bottom:.75rem;align-items:flex-start;">
          <div style="width:24px;height:24px;border-radius:50%;background:#0176D3;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;color:#fff;flex-shrink:0;">{{ $i+1 }}</div>
          <div style="color:var(--text-dim);font-size:.85rem;line-height:1.5;padding-top:3px;">{{ $step }}</div>
        </div>
        @endforeach
      </div>

      {{-- QR Kod --}}
      <div style="text-align:center;margin-bottom:1.25rem;">
        <div style="display:inline-block;background:#fff;border:1px solid var(--border);border-radius:10px;padding:.75rem;">
          <img src="data:image/svg+xml;base64,{{ $qrSvg }}" width="200" height="200" alt="QR kod" />
        </div>
      </div>

      <p style="text-align:center;font-size:.78rem;color:var(--text-dim);margin-bottom:.5rem;">QR kod işləmirsə, açarı əl ilə daxil edin:</p>
      <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:8px;padding:.6rem 1rem;font-family:monospace;font-size:.88rem;color:#0176D3;text-align:center;letter-spacing:.12rem;word-break:break-all;margin-bottom:1.5rem;">
        {{ $secret }}
      </div>

      <hr style="border:none;border-top:1px solid var(--border);margin-bottom:1.5rem;">

      <form method="POST" action="{{ route('two-factor.enable') }}">
        @csrf
        <div style="margin-bottom:1rem;">
          <label style="display:block;font-size:.78rem;font-weight:600;color:var(--text-dim);margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.5px;">Doğrulama kodu</label>
          <input type="text" name="code" maxlength="6" inputmode="numeric" autocomplete="one-time-code" placeholder="000000" autofocus
            style="width:100%;background:var(--surface-2);border:1.5px solid var(--border);border-radius:8px;padding:.65rem 1rem;color:var(--text);font-size:1.2rem;font-weight:700;text-align:center;letter-spacing:.4rem;font-family:monospace;outline:none;" />
          @error('code')
            <div style="color:#BA0517;font-size:.75rem;margin-top:.35rem;">{{ $message }}</div>
          @enderror
        </div>
        <button type="submit" class="btn-primary" style="width:100%;padding:.72rem;">2FA-nı Aktivləşdir</button>
      </form>

    @endif
  </div>
</div>
@endsection
