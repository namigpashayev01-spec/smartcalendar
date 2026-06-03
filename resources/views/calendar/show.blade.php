@extends('layouts.app')

@section('title', $current->translatedFormat('F Y') . ' — Smart Calendar')

{{-- Ay naviqasiyası → header ortasına --}}
@push('header-center')
<div class="month-nav">
  <a href="{{ route('calendar.month', [$prev->year, $prev->month]) }}" class="nav-btn" title="Əvvəlki ay">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
  </a>
  <span class="month-label">{{ $current->translatedFormat('F Y') }}</span>
  <a href="{{ route('calendar.month', [$next->year, $next->month]) }}" class="nav-btn" title="Növbəti ay">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
  </a>
</div>
@if(!$current->isCurrentMonth())
  <a href="{{ route('calendar') }}" class="ghost-btn small today-btn">Bu gün</a>
@endif
@endpush

{{-- + düyməsi → header sağına --}}
@push('header-actions')
<a href="{{ route('posts.create', ['date' => today()->format('Y-m-d')]) }}"
   class="add-btn" title="Yeni paylaşım">
  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
       fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
  </svg>
</a>
@endpush

@section('content')
<div class="calendar-wrapper">

  {{-- Həftə başlıqları --}}
  <div class="weekday-row">
    @foreach(['B.e','Ç.a','Çər','C.a','Cüm','Şən','Baz'] as $d)
      <div class="{{ in_array($d,['Şən','Baz']) ? 'we' : '' }}">{{ $d }}</div>
    @endforeach
  </div>

  {{-- Günlər --}}
  <div class="calendar-grid">
    @for($i = 0; $i < $firstDow; $i++)
      <div class="day-card empty-cell"></div>
    @endfor

    @for($day = 1; $day <= $daysInMonth; $day++)
      @php
        $date    = \Carbon\Carbon::create($year, $month, $day);
        $dateStr = $date->format('Y-m-d');
        $isToday = $date->isToday();
        $isWe    = $date->isWeekend();
        $dayPosts = $posts->get($dateStr, collect());
      @endphp
      <a href="{{ route('day', $dateStr) }}"
         class="day-card {{ $isToday ? 'today' : '' }} {{ $isWe ? 'weekend' : '' }}">
        <div class="day-head">
          <span class="day-num">{{ $day }}</span>
          @if($isToday)<span class="day-today-tag">Bu gün</span>@endif
        </div>
        <div class="company-list">
          @foreach($dayPosts->take(3) as $post)
            <span class="company-chip" style="--chip-color:{{ $post->color ?? '#0176D3' }}">
              <span class="chip-name">{{ Str::limit($post->company, 20) }}</span>
            </span>
          @endforeach
          @if($dayPosts->count() > 3)
            <span class="chip-more">+{{ $dayPosts->count() - 3 }}</span>
          @endif
        </div>
      </a>
    @endfor
  </div>

</div>
@endsection
