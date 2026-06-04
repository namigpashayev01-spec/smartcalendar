@extends('layouts.app')

@section('title', '2FA Qurulumu — Smart Calendar')

@push('styles')
<style>
  .tfa-wrap {
    max-width: 480px; margin: 2rem auto; padding: 0 1rem;
  }
  .tfa-card {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 16px;
    padding: 2rem;
  }
  .tfa-card h2 { font-size: 1.2rem; font-weight: 800; color: #fff; margin-bottom: .5rem; }
  .tfa-card p  { color: rgba(255,255,255,.5); font-size: .85rem; line-height: 1.6; margin-bottom: 1.25rem; }
  .steps { counter-reset: step; margin-bottom: 1.5rem; }
  .step  { display: flex; gap: .75rem; margin-bottom: 1rem; }
  .step-num {
    counter-increment: step;
    width: 26px; height: 26px; flex-shrink: 0;
    background: #0176D3; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 700; color: #fff;
  }
  .step-num::before { content: counter(step); }
  .step-text { color: rgba(255,255,255,.65); font-size: .85rem; line-height: 1.5; padding-top: 3px; }
  .qr-box {
    background: #fff; border-radius: 12px;
    padding: .75rem; display: inline-block;
    margin: 0 auto 1.25rem; display: block; width: fit-content;
  }
  .secret-box {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 8px; padding: .6rem 1rem;
    font-family: monospace; font-size: .9rem;
    color: #a5f3fc; letter-spacing: .15rem;
    text-align: center; margin-bottom: 1.5rem; word-break: break-all;
  }
  .divider { border: none; border-top: 1px solid rgba(255,255,255,.08); margin: 1.5rem 0; }
  .form-group { margin-bottom: 1rem; }
  .form-group label { display: block; font-size: .75rem; font-weight: 600; color: rgba(255,255,255,.55); margin-bottom: .4rem; text-transform: uppercase; letter-spacing: .5px; }
  .code-input {
    width: 100%; background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 9px; padding: .7rem 1rem;
    color: #fff; font-size: 1.2rem; font-weight: 700;
    text-align: center; letter-spacing: .4rem;
    font-family: monospace; outline: none;
    transition: border-color .2s;
  }
  .code-input:focus { border-color: #0176D3; }
  .code-input.is-invalid { border-color: #ef4444; }
  .invalid-feedback { color: #f87171; font-size: .75rem; margin-top: .35rem; }
  .btn-enable {
    width: 100%; background: linear-gradient(135deg, #059669, #0891B2);
    color: #fff; border: none; border-radius: 9px;
    padding: .72rem 1rem; font-size: .9rem; font-weight: 700;
    font-family: inherit; cursor: pointer; transition: opacity .2s;
  }
  .btn-enable:hover { opacity: .9; }
  @if(auth()->user()->two_factor_confirmed_at)
  .btn-disable {
    width: 100%; background: rgba(239,68,68,.15);
    color: #f87171; border: 1px solid rgba(239,68,68,.3);
    border-radius: 9px; padding: .72rem 1rem;
    font-size: .9rem; font-weight: 700;
    font-family: inherit; cursor: pointer; transition: opacity .2s;
    margin-top: .75rem;
  }
  .btn-disable:hover { background: rgba(239,68,68,.25); }
  @endif
  .badge-active {
    display: inline-flex; align-items: center; gap: .4rem;
    background: rgba(5,150,105,.15); border: 1px solid rgba(5,150,105,.3);
    color: #34d399; border-radius: 20px;
    padding: .3rem .75rem; font-size: .78rem; font-weight: 600;
    margin-bottom: 1rem;
  }
  .badge-inactive {
    display: inline-flex; align-items: center; gap: .4rem;
    background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.25);
    color: #f87171; border-radius: 20px;
    padding: .3rem .75rem; font-size: .78rem; font-weight: 600;
    margin-bottom: 1rem;
  }
</style>
@endpush

@section('content')
<div class="tfa-wrap">
  <div class="tfa-card">
    <h2>İki addımlı doğrulama (2FA)</h2>

    @if($user->two_factor_confirmed_at)
      <span class="badge-active">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
        Aktiv
      </span>
      <p>2FA aktivdir. Deaktiv etmək üçün Google Authenticator kodunu daxil edin.</p>

      <form method="POST" action="{{ route('two-factor.disable') }}">
        @csrf @method('DELETE')
        <div class="form-group">
          <label>Doğrulama kodu</label>
          <input type="text" name="code" maxlength="6" inputmode="numeric" placeholder="000000" autofocus class="code-input {{ $errors->has('code') ? 'is-invalid' : '' }}" />
          @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn-disable">2FA-nı Deaktiv Et</button>
      </form>

    @else
      <span class="badge-inactive">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Aktiv deyil
      </span>
      <p>Hesabınızı qorumaq üçün Google Authenticator ilə 2FA qurun.</p>

      <div class="steps">
        <div class="step"><div class="step-num"></div><div class="step-text">Telefonunuzda <strong>Google Authenticator</strong> tətbiqini açın</div></div>
        <div class="step"><div class="step-num"></div><div class="step-text">«+» düyməsinə basıb <strong>QR kodu skan edin</strong></div></div>
        <div class="step"><div class="step-num"></div><div class="step-text">Tətbiqdəki 6 rəqəmli kodu aşağıya daxil edib təsdiqləyin</div></div>
      </div>

      <div class="qr-box">
        <img src="data:image/svg+xml;base64,{{ $qrSvg }}" width="200" height="200" alt="QR kod" />
      </div>

      <p style="text-align:center;margin-bottom:.5rem;font-size:.78rem;">QR kod işləmirsə, bu açarı əl ilə daxil edin:</p>
      <div class="secret-box">{{ $secret }}</div>

      <hr class="divider">

      <form method="POST" action="{{ route('two-factor.enable') }}">
        @csrf
        <div class="form-group">
          <label>Doğrulama kodu (skan etdikdən sonra)</label>
          <input type="text" name="code" maxlength="6" inputmode="numeric" autocomplete="one-time-code" placeholder="000000" autofocus class="code-input {{ $errors->has('code') ? 'is-invalid' : '' }}" />
          @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn-enable">2FA-nı Aktivləşdir</button>
      </form>
    @endif
  </div>
</div>
@endsection
