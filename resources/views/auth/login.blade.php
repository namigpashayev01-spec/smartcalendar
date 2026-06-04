@extends('layouts.guest')

@section('title', 'Giriş — Smart Calendar')

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

  .login-card {
    position: relative;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.1);
    backdrop-filter: blur(20px);
    border-radius: 18px;
    padding: 1.75rem 1.75rem 1.5rem;
    width: 100%;
    max-width: 360px;
    box-shadow: 0 20px 40px rgba(0,0,0,.5);
  }

  .login-brand {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .5rem;
    margin-bottom: 1.25rem;
  }

  .login-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #0176D3, #0891B2);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 18px rgba(1,118,211,.45);
  }

  .login-brand-name {
    font-size: 1.1rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -.3px;
  }

  .login-card h1 {
    font-size: 1.3rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: .25rem;
    letter-spacing: -.4px;
    text-align: center;
  }

  .login-sub {
    color: rgba(255,255,255,.45);
    font-size: .82rem;
    margin-bottom: 1.25rem;
    text-align: center;
  }

  .form-group {
    margin-bottom: .85rem;
  }

  .form-group label {
    display: block;
    font-size: .75rem;
    font-weight: 600;
    color: rgba(255,255,255,.55);
    margin-bottom: .4rem;
    text-transform: uppercase;
    letter-spacing: .5px;
  }

  .form-group input {
    width: 100%;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 9px;
    padding: .62rem .9rem;
    color: #fff;
    font-size: .9rem;
    font-family: inherit;
    transition: border-color .2s, background .2s;
    outline: none;
  }

  .form-group input::placeholder { color: rgba(255,255,255,.22); }

  .form-group input:focus {
    border-color: #0176D3;
    background: rgba(1,118,211,.08);
  }

  .form-group input.is-invalid {
    border-color: #ef4444;
    background: rgba(239,68,68,.08);
  }

  .invalid-feedback {
    color: #f87171;
    font-size: .75rem;
    margin-top: .35rem;
  }

  .btn-login {
    width: 100%;
    background: linear-gradient(135deg, #0176D3, #0891B2);
    color: #fff;
    border: none;
    border-radius: 9px;
    padding: .72rem 1rem;
    font-size: .9rem;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: opacity .2s, transform .1s;
    letter-spacing: .2px;
  }

  .btn-login:hover  { opacity: .9; }
  .btn-login:active { transform: scale(.98); }

  .login-footer {
    text-align: center;
    margin-top: 1.5rem;
    color: rgba(255,255,255,.25);
    font-size: .78rem;
  }
</style>
@endpush

@section('content')
<form class="login-card" method="POST" action="{{ route('login') }}">
  @csrf

  <div class="login-brand">
    <div class="login-icon">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="3"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
        <circle cx="8" cy="15" r="1" fill="#fff" stroke="none"/>
        <circle cx="12" cy="15" r="1" fill="#fff" stroke="none"/>
        <circle cx="16" cy="15" r="1" fill="#fff" stroke="none"/>
      </svg>
    </div>
    <span class="login-brand-name">Smart Calendar</span>
  </div>

  <h1>Xoş gəldiniz</h1>
  <p class="login-sub">Kontent planınıza daxil olun</p>

  <div class="form-group">
    <label for="login">E-poçt və ya istifadəçi adı</label>
    <input
      type="text"
      id="login"
      name="login"
      value="{{ old('login') }}"
      placeholder="email@example.com"
      autocomplete="username"
      autofocus
      class="{{ $errors->has('login') ? 'is-invalid' : '' }}"
    />
    @error('login')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-group">
    <label for="password">Şifrə</label>
    <input
      type="password"
      id="password"
      name="password"
      placeholder="••••••••"
      autocomplete="current-password"
      class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
    />
    @error('password')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <button type="submit" class="btn-login">Daxil ol</button>

  <p class="login-footer">Smart Calendar &copy; {{ date('Y') }}</p>
</form>
@endsection
