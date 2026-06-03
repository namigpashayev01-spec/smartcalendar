@extends('layouts.app')

@section('title', $post->company . ' — Smart Calendar')

@section('content')
@php
  $color = $post->color ?? '#0176D3';
  $s = ['planned'=>['Planlaşdırılıb','#5C6B8A','#EEF1F6'],'progress'=>['Hazırlanır','#B45309','#FEF3E2'],'pending'=>['Təsdiq gözləyir','#7C3AED','#F3EAFE'],'published'=>['Paylaşılıb','#2E844A','#E6F4EA']][$post->status] ?? ['?','#999','#eee'];
@endphp
<div class="detail-page">
  <a href="{{ route('day', $post->date->format('Y-m-d')) }}" class="back-link">‹ Geri</a>

  <div class="detail-head">
    <div>
      <div class="detail-company">
        <span class="detail-dot" style="background:{{ $color }}"></span>
        {{ $post->company }}
      </div>
      <div class="detail-date">{{ $post->date->format('d.m.Y') }}, {{ $post->date->translatedFormat('l') }}</div>
      <span class="status-badge" style="color:{{ $s[1] }};background:{{ $s[2] }};margin-top:10px;display:inline-block">
        {{ $s[0] }}
      </span>
    </div>
    <div class="detail-actions">
      <a href="{{ route('posts.edit', $post) }}" class="ghost-btn">Redaktə</a>
      <form method="POST" action="{{ route('posts.destroy', $post) }}" style="display:inline"
            onsubmit="return confirm('\"{{ $post->company }}\" paylaşımını silmək istəyirsiniz?')">
        @csrf @method('DELETE')
        <button class="btn-danger">Sil</button>
      </form>
    </div>
  </div>

  @if($post->media->isNotEmpty())
    <div class="detail-section-title">Media ({{ $post->media->count() }})</div>
    <div class="detail-gallery">
      @foreach($post->media as $i => $m)
        <figure class="detail-media">
          @if($m->type === 'video')
            <video src="{{ asset('storage/'.$m->file_path) }}" controls></video>
          @else
            <img src="{{ asset('storage/'.$m->file_path) }}" alt="{{ $m->original_name }}"/>
          @endif
          <figcaption class="detail-media-bar">
            <div class="detail-media-info">
              <span class="detail-media-meta">{{ ucfirst($m->type) }} {{ $loop->iteration }}</span>
              @if($m->description)
                <p class="detail-media-desc">{{ $m->description }}</p>
              @endif
            </div>
            <a href="{{ asset('storage/'.$m->file_path) }}" download="{{ $m->original_name }}" class="ghost-btn small">
              Yüklə
            </a>
          </figcaption>
        </figure>
      @endforeach
    </div>
  @else
    <div class="no-media">Bu paylaşım üçün şəkil/video əlavə edilməyib</div>
  @endif

  <div class="detail-section-title">Paylaşılacaq mətn</div>
  @if($post->description)
    <div class="detail-text">{{ $post->description }}</div>
  @else
    <div class="detail-text detail-empty-text">Mətn əlavə edilməyib</div>
  @endif
</div>
@endsection
