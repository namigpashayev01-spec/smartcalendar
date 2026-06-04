<!DOCTYPE html>
<html lang="az">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>@yield('title','Smart Calendar')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}"/>
  @stack('styles')
</head>
<body>

<header class="app-header">
  {{-- Sol: Brand --}}
  <a href="{{ route('calendar') }}" class="brand">
    <span class="brand-mark"><img src="{{ asset('images/logo.webp') }}" alt="logo" onerror="this.style.display='none'"/></span>
    <span class="brand-text">Smart Calendar</span>
  </a>

  {{-- Orta: Ay naviqasiyası (calendar view-dan push olunur) --}}
  <div class="header-center-slot">
    @stack('header-center')
  </div>

  {{-- Sağ: Əməliyyatlar --}}
  @auth
  <nav class="header-right">
    <a href="{{ route('report') }}" class="ghost-btn">Hesabat</a>
    <a href="{{ route('log') }}" class="ghost-btn">Jurnal</a>
    @if(auth()->user()->isAdmin())
    <a href="{{ route('users.index') }}" class="ghost-btn">İstifadəçilər</a>
    @endif
    <a href="{{ route('two-factor.setup') }}" class="ghost-btn" title="2FA Qurulumu">
      @if(auth()->user()->two_factor_confirmed_at)
        <svg width="14" height="14" fill="none" stroke="#34d399" stroke-width="2.2" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
      @else
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
      @endif
      2FA
    </a>
    <span class="user-badge">{{ auth()->user()->name }}</span>
    @stack('header-actions')
    <form method="POST" action="{{ route('logout') }}" style="display:inline">
      @csrf
      <button class="ghost-btn logout-btn" title="Çıxış">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>Çıxış</span>
      </button>
    </form>
  </nav>
  @endauth
</header>

<main class="main-content">
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
  @endif

  @yield('content')
</main>

@stack('scripts')
</body>
</html>
