@extends('layouts.app')
@section('title', 'İstifadəçilər — Smart Calendar')

@section('content')
<div class="report-page">
  <div class="report-head">
    <div>
      <h2>İstifadəçilər</h2>
      <p class="report-sub">{{ $users->count() }} istifadəçi</p>
    </div>
  </div>

  @foreach($users as $user)
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:1.25rem 1.5rem;margin-bottom:1rem;">

    {{-- Başlıq --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem;">
      <div style="display:flex;align-items:center;gap:.75rem;">
        <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#0176D3,#7C3AED);display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:.9rem;flex-shrink:0;">
          {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
          <div style="font-weight:700;color:var(--text);font-size:.95rem;">
            {{ $user->name }}
            @if($user->id === auth()->id())
              <span style="color:var(--text-faint);font-size:.72rem;font-weight:400;">(siz)</span>
            @endif
          </div>
          <div style="font-size:.75rem;color:var(--text-dim);">
            {{ $user->role === 'admin' ? 'Admin' : 'Redaktor' }} · {{ $user->username }}
          </div>
        </div>
      </div>

      <div style="display:flex;align-items:center;gap:.5rem;">
        @if($user->two_factor_confirmed_at)
          <span style="background:#EDFAF1;border:1px solid #A3E6B4;color:#2E844A;border-radius:20px;padding:.2rem .65rem;font-size:.72rem;font-weight:600;">2FA aktiv</span>
          <form method="POST" action="{{ route('users.reset-2fa', $user) }}"
                onsubmit="return confirm('{{ $user->name }} üçün 2FA sıfırlansın?')">
            @csrf
            <button type="submit" class="ghost-btn small" style="color:#BA0517;border-color:#f5c6cb;">Sıfırla</button>
          </form>
        @else
          <span style="background:#FFF0F0;border:1px solid #FFB3B3;color:#BA0517;border-radius:20px;padding:.2rem .65rem;font-size:.72rem;font-weight:600;">2FA yoxdur</span>
        @endif
      </div>
    </div>

    {{-- Gmail --}}
    @if($user->email)
      <div style="font-size:.78rem;color:var(--text-dim);margin-bottom:.6rem;">
        Mövcud Gmail: <strong style="color:var(--text);">{{ $user->email }}</strong>
      </div>
    @endif

    <form method="POST" action="{{ route('users.update-email', $user) }}">
      @csrf @method('PATCH')
      <input type="hidden" name="user_id" value="{{ $user->id }}">
      <div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-start;">
        <div style="flex:1;min-width:220px;">
          <input
            type="email"
            name="email"
            placeholder="example@gmail.com"
            value="{{ old('email', $user->email) }}"
            style="width:100%;background:var(--surface-2);border:1.5px solid var(--border);border-radius:8px;padding:.55rem .85rem;color:var(--text);font-size:.85rem;font-family:inherit;outline:none;"
          />
          @if($errors->has('email') && old('user_id') == $user->id)
            <div style="color:#BA0517;font-size:.75rem;margin-top:.3rem;">{{ $errors->first('email') }}</div>
          @endif
        </div>
        <button type="submit" class="btn-primary inline" style="padding:.55rem 1rem;font-size:.82rem;">Gmail Yenilə</button>
      </div>
    </form>

  </div>
  @endforeach
</div>
@endsection
