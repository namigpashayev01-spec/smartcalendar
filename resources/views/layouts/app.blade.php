<!DOCTYPE html>
<html lang="az">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
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
    <a href="{{ route('chat.index') }}" class="ghost-btn" style="position:relative">
      Chat
      <span id="navChatBadge" style="display:none;position:absolute;top:-4px;right:-6px;background:var(--accent);color:#fff;font-size:.62rem;font-weight:700;border-radius:999px;min-width:16px;height:16px;align-items:center;justify-content:center;padding:0 4px"></span>
    </a>
    @if(auth()->user()->isAdmin())
    <a href="{{ route('users.index') }}" class="ghost-btn">İstifadəçilər</a>
    @endif
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
<script>
(function() {
  function updateChatBadge() {
    fetch('/chat/unread-count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(data => {
        const b = document.getElementById('navChatBadge');
        if (!b) return;
        if (data.count > 0) { b.textContent = data.count; b.style.display = 'inline-flex'; }
        else { b.style.display = 'none'; }
      }).catch(() => {});
  }
  updateChatBadge();
  setInterval(updateChatBadge, 8000);
})();
</script>
</body>
</html>
