@extends('layouts.guest')

@section('title', '2FA Doğrulama — Smart Calendar')

@push('styles')
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Inter', system-ui, sans-serif;
    background: #0f172a;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
  }
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
      radial-gradient(ellipse 80% 50% at 20% 20%, rgba(0,118,211,.18) 0%, transparent 60%),
      radial-gradient(ellipse 60% 60% at 80% 80%, rgba(124,58,237,.15) 0%, transparent 60%);
    pointer-events: none;
  }
  .card {
    position: relative;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.1);
    backdrop-filter: blur(20px);
    border-radius: 18px;
    padding: 2rem 1.75rem;
    width: 100%;
    max-width: 360px;
    box-shadow: 0 20px 40px rgba(0,0,0,.5);
    text-align: center;
  }
  .icon {
    width: 56px; height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, #0176D3, #0891B2);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
    box-shadow: 0 6px 18px rgba(1,118,211,.45);
  }
  h1 { font-size: 1.3rem; font-weight: 800; color: #fff; margin-bottom: .35rem; }
  p  { color: rgba(255,255,255,.45); font-size: .85rem; margin-bottom: 1.5rem; line-height: 1.5; }
  .form-group { margin-bottom: 1rem; text-align: left; }
  .form-group label { display: block; font-size: .75rem; font-weight: 600; color: rgba(255,255,255,.55); margin-bottom: .4rem; text-transform: uppercase; letter-spacing: .5px; }
  .code-input {
    width: 100%; background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 9px; padding: .7rem 1rem;
    color: #fff; font-size: 1.4rem; font-weight: 700;
    text-align: center; letter-spacing: .5rem;
    font-family: monospace; outline: none;
    transition: border-color .2s;
  }
  .code-input:focus { border-color: #0176D3; background: rgba(1,118,211,.08); }
  .code-input.is-invalid { border-color: #ef4444; }
  .invalid-feedback { color: #f87171; font-size: .75rem; margin-top: .35rem; }
  .btn {
    width: 100%; background: linear-gradient(135deg, #0176D3, #0891B2);
    color: #fff; border: none; border-radius: 9px;
    padding: .72rem 1rem; font-size: .9rem; font-weight: 700;
    font-family: inherit; cursor: pointer;
    transition: opacity .2s;
  }
  .btn:hover { opacity: .9; }
  .back-link { display: block; margin-top: 1rem; color: rgba(255,255,255,.35); font-size: .8rem; text-decoration: none; }
  .back-link:hover { color: rgba(255,255,255,.6); }
</style>
@endpush

@section('content')
<div class="card">
  <div class="icon">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>
      <circle cx="12" cy="16" r="1" fill="#fff" stroke="none"/>
    </svg>
  </div>

  <h1>İki addımlı doğrulama</h1>
  <p>Google Authenticator tətbiqindəki<br>6 rəqəmli kodu daxil edin.</p>

  <form method="POST" action="{{ route('two-factor.verify') }}">
    @csrf
    <div class="form-group">
      <label for="code">Doğrulama kodu</label>
      <input
        type="text"
        id="code"
        name="code"
        maxlength="6"
        inputmode="numeric"
        autocomplete="one-time-code"
        autofocus
        placeholder="000000"
        class="code-input {{ $errors->has('code') ? 'is-invalid' : '' }}"
      />
      @error('code')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <button type="submit" class="btn">Daxil ol</button>
  </form>

  <a href="{{ route('login') }}" class="back-link">← Girişə qayıt</a>
</div>
@endsection
