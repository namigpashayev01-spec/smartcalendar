@extends('layouts.app')
@section('title','Hesabat — Smart Calendar')
@section('content')
<div class="report-page">
  <div class="report-head">
    <div>
      <h2>Hesabat</h2>
      <p class="report-sub">{{ $posts->count() }} paylaşım tapıldı</p>
    </div>
    <a href="{{ route('report.export', request()->query()) }}" class="ghost-btn">CSV yüklə</a>
  </div>

  <form method="GET" action="{{ route('report') }}" class="report-filters">
    <div class="form-field">
      <label>Şirkət</label>
      <select name="company" class="form-select">
        <option value="">Bütün şirkətlər</option>
        @foreach($companies as $c)
          <option value="{{ $c }}" {{ $filters['company'] ?? '' === $c ? 'selected' : '' }}>{{ $c }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-field">
      <label>Status</label>
      <select name="status" class="form-select">
        <option value="">Bütün statuslar</option>
        @foreach($statuses as $k => $label)
          <option value="{{ $k }}" {{ ($filters['status'] ?? '') === $k ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-field">
      <label>Başlanğıc</label>
      <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"/>
    </div>
    <div class="form-field">
      <label>Son tarix</label>
      <input type="date" name="to" value="{{ $filters['to'] ?? '' }}"/>
    </div>
    <div class="form-field rf-search-field">
      <label>Axtarış</label>
      <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Şirkət və ya təsvir..."/>
    </div>
    <button type="submit" class="btn-primary inline">Filtrə</button>
    <a href="{{ route('report') }}" class="ghost-btn small">Sıfırla</a>
  </form>

  @php
    $counts = collect($statuses)->mapWithKeys(fn($l,$k) => [$k => $posts->where('status',$k)->count()]);
  @endphp
  <div class="report-stats">
    <div class="stat-card total">
      <span class="stat-num">{{ $posts->count() }}</span>
      <span class="stat-lbl">Cəmi</span>
    </div>
    @foreach($statuses as $k => $label)
      @php $sc = ['planned'=>['#5C6B8A','#EEF1F6'],'progress'=>['#B45309','#FEF3E2'],'pending'=>['#7C3AED','#F3EAFE'],'published'=>['#2E844A','#E6F4EA']][$k]; @endphp
      <div class="stat-card" style="background:{{ $sc[1] }}">
        <span class="stat-num" style="color:{{ $sc[0] }}">{{ $counts[$k] }}</span>
        <span class="stat-lbl" style="color:{{ $sc[0] }}">{{ $label }}</span>
      </div>
    @endforeach
  </div>

  @if($posts->isEmpty())
    <div class="no-media">Filtrlərə uyğun paylaşım tapılmadı</div>
  @else
    <div class="report-table-wrap">
      <table class="report-table">
        <thead><tr><th>Tarix</th><th>Şirkət</th><th>Status</th><th>Təsvir</th><th>Media</th></tr></thead>
        <tbody>
          @foreach($posts as $post)
            @php $sc = ['planned'=>['#5C6B8A','#EEF1F6'],'progress'=>['#B45309','#FEF3E2'],'pending'=>['#7C3AED','#F3EAFE'],'published'=>['#2E844A','#E6F4EA']][$post->status] ?? ['#999','#eee']; @endphp
            <tr onclick="location.href='{{ route('posts.show',$post) }}'" style="cursor:pointer">
              <td class="c-date">{{ $post->date->format('d.m.Y') }}</td>
              <td class="c-company">
                <span class="row-dot sm" style="background:{{ $post->color ?? '#0176D3' }}"></span>
                {{ $post->company }}
              </td>
              <td><span class="status-badge" style="color:{{ $sc[0] }};background:{{ $sc[1] }}">{{ $statuses[$post->status] ?? '?' }}</span></td>
              <td class="c-desc">{{ Str::limit($post->description, 80) ?: '—' }}</td>
              <td class="c-media">{{ $post->media->count() ?: '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
@endsection
