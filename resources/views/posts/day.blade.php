@extends('layouts.app')

@section('title', \Carbon\Carbon::parse($date)->format('d.m.Y') . ' — Smart Calendar')

@section('content')
@php $d = \Carbon\Carbon::parse($date); @endphp
<div class="report-page">
  <div class="report-head">
    <div>
      <a href="{{ route('calendar.month', [$d->year, $d->month]) }}" class="back-link">‹ Geri</a>
      <h2>{{ $d->format('d') }} {{ $d->translatedFormat('F Y') }}</h2>
      <p class="report-sub">{{ $posts->count() }} paylaşım</p>
    </div>
    <a href="{{ route('posts.create', ['date' => $date]) }}" class="btn-primary inline">+ Yeni paylaşım</a>
  </div>

  @if($posts->isEmpty())
    <div class="no-media">Bu gün üçün heç bir paylaşım yoxdur</div>
  @else
    <div class="company-rows">
      @foreach($posts as $post)
        @php
          $color = $post->color ?? '#0176D3';
          $s = ['planned'=>['Planlaşdırılıb','#5C6B8A','#EEF1F6'],'progress'=>['Hazırlanır','#B45309','#FEF3E2'],'pending'=>['Təsdiq gözləyir','#7C3AED','#F3EAFE'],'published'=>['Paylaşılıb','#2E844A','#E6F4EA']][$post->status] ?? ['?','#999','#eee'];
        @endphp
        <a href="{{ route('posts.show', $post) }}" class="company-row" style="border-left-color:{{ $color }}">
          <span class="row-dot" style="background:{{ $color }}"></span>
          <span class="row-name">{{ $post->company }}</span>
          <span class="row-media">
            @if($post->media->isNotEmpty())
              {{ $post->media->count() }} fayl
            @else
              Mətn
            @endif
          </span>
          <span class="status-badge" style="color:{{ $s[1] }};background:{{ $s[2] }}">{{ $s[0] }}</span>
        </a>
      @endforeach
    </div>
  @endif
</div>
@endsection
