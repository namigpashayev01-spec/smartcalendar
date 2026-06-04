@extends('layouts.app')
@section('title', 'İstifadəçilər — Smart Calendar')

@push('styles')
<style>
.users-wrap { max-width: 700px; margin: 2rem auto; padding: 0 1rem; }
.users-wrap h2 { font-size: 1.2rem; font-weight: 800; color: #fff; margin-bottom: 1.5rem; }
.user-card {
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 14px; padding: 1.25rem 1.5rem;
  margin-bottom: 1rem;
}
.user-card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; flex-wrap: wrap; gap: .5rem; }
.user-info { display: flex; align-items: center; gap: .75rem; }
.avatar {
  width: 40px; height: 40px; border-radius: 50%;
  background: linear-gradient(135deg, #0176D3, #7C3AED);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; color: #fff; font-size: .9rem; flex-shrink: 0;
}
.user-name { font-weight: 700; color: #fff; font-size: .95rem; }
.user-role { font-size: .75rem; color: rgba(255,255,255,.4); }
.badge-2fa-on  { background: rgba(5,150,105,.15); border: 1px solid rgba(5,150,105,.3); color: #34d399; border-radius: 20px; padding: .2rem .6rem; font-size: .72rem; font-weight: 600; }
.badge-2fa-off { background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.25); color: #f87171; border-radius: 20px; padding: .2rem .6rem; font-size: .72rem; font-weight: 600; }
.email-form { display: flex; gap: .6rem; align-items: flex-start; flex-wrap: wrap; }
.email-input {
  flex: 1; min-width: 200px;
  background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
  border-radius: 8px; padding: .55rem .85rem;
  color: #fff; font-size: .85rem; font-family: inherit; outline: none;
}
.email-input:focus { border-color: #0176D3; }
.email-input.is-invalid { border-color: #ef4444; }
.btn-save {
  background: linear-gradient(135deg, #0176D3, #0891B2);
  color: #fff; border: none; border-radius: 8px;
  padding: .55rem 1rem; font-size: .82rem; font-weight: 700;
  font-family: inherit; cursor: pointer; white-space: nowrap;
}
.btn-reset {
  background: rgba(239,68,68,.15); color: #f87171;
  border: 1px solid rgba(239,68,68,.3); border-radius: 8px;
  padding: .4rem .8rem; font-size: .75rem; font-weight: 600;
  font-family: inherit; cursor: pointer; white-space: nowrap;
}
.invalid-feedback { color: #f87171; font-size: .75rem; margin-top: .3rem; width: 100%; }
.current-email { font-size: .78rem; color: rgba(255,255,255,.35); margin-bottom: .5rem; }
</style>
@endpush

@section('content')
<div class="users-wrap">
  <h2>İstifadəçilər</h2>

  @foreach($users as $user)
  <div class="user-card">
    <div class="user-card-top">
      <div class="user-info">
        <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
        <div>
          <div class="user-name">{{ $user->name }}
            @if($user->id === auth()->id()) <span style="color:rgba(255,255,255,.3);font-size:.72rem;">(siz)</span> @endif
          </div>
          <div class="user-role">{{ $user->role === 'admin' ? 'Admin' : 'Redaktor' }} · {{ $user->username }}</div>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:.5rem;">
        @if($user->two_factor_confirmed_at)
          <span class="badge-2fa-on">2FA aktiv</span>
          <form method="POST" action="{{ route('users.reset-2fa', $user) }}" onsubmit="return confirm('{{ $user->name }} üçün 2FA sıfırlansın?')">
            @csrf
            <button type="submit" class="btn-reset">Sıfırla</button>
          </form>
        @else
          <span class="badge-2fa-off">2FA yoxdur</span>
        @endif
      </div>
    </div>

    @if($user->email)
      <div class="current-email">Mövcud Gmail: {{ $user->email }}</div>
    @endif

    <form method="POST" action="{{ route('users.update-email', $user) }}">
      @csrf @method('PATCH')
      <div class="email-form">
        <div style="flex:1;min-width:200px;">
          <input
            type="email"
            name="email"
            placeholder="example@gmail.com"
            value="{{ old('email_'.$user->id, $user->email) }}"
            class="email-input {{ $errors->has('email') && old('user_id') == $user->id ? 'is-invalid' : '' }}"
          />
          @if($errors->has('email') && old('user_id') == $user->id)
            <div class="invalid-feedback">{{ $errors->first('email') }}</div>
          @endif
        </div>
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <button type="submit" class="btn-save">Gmail Yenilə</button>
      </div>
    </form>
  </div>
  @endforeach
</div>
@endsection
