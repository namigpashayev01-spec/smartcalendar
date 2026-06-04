<!DOCTYPE html>
<html lang="az">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>@yield('title','Smart Calendar')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>
  @stack('styles')
</head>
<body>

<header class="app-header">
  <a href="{{ route('login') }}" class="brand">
    <span class="brand-mark"><img src="{{ asset('images/logo.webp') }}" alt="logo" onerror="this.style.display='none'"/></span>
    <span class="brand-text">Smart Calendar</span>
  </a>
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
