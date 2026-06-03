@extends('layouts.app')
@section('title','Jurnal — Smart Calendar')
@section('content')
<div class="report-page">
  <div class="report-head">
    <div>
      <h2>Change history</h2>
      <p class="report-sub">{{ $logs->count() }} qeyd</p>
    </div>
    @if(auth()->user()->isAdmin())
      <form method="POST" action="{{ route('log.destroy') }}"
            onsubmit="return confirm('Bütün jurnal qeydləri silinsin?')">
        @csrf @method('DELETE')
        <button class="ghost-btn">Təmizlə</button>
      </form>
    @endif
  </div>

  <form method="GET" class="report-filters">
    <div class="form-field">
      <label>İstifadəçi</label>
      <select name="user" class="form-select">
        <option value="">Hamısı</option>
        @foreach($users as $u)
          <option value="{{ $u }}" {{ request('user') === $u ? 'selected' : '' }}>{{ $u }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-field">
      <label>Əməliyyat</label>
      <select name="action" class="form-select">
        <option value="">Hamısı</option>
        @foreach($actions as $a)
          <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-field rf-search-field">
      <label>Axtarış</label>
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Obyekt..."/>
    </div>
    <button type="submit" class="btn-primary inline">Filtrə</button>
    <a href="{{ route('log') }}" class="ghost-btn small">Sıfırla</a>
  </form>

  @php
    $actionColors = ['Yaratdı'=>'#2E844A','Redaktə etdi'=>'#0176D3','Sildi'=>'#BA0517','Daxil oldu'=>'#5C6B8A','Çıxış etdi'=>'#5C6B8A'];
  @endphp

  @if($logs->isEmpty())
    <div class="no-media">Qeyd tapılmadı</div>
  @else
    <div class="report-table-wrap">
      <table class="report-table">
        <thead><tr><th>Vaxt</th><th>İstifadəçi</th><th>Əməliyyat</th><th>Obyekt</th></tr></thead>
        <tbody>
          @foreach($logs as $log)
            @php $ac = $actionColors[$log->action] ?? '#5C6B8A'; @endphp
            <tr>
              <td class="c-date">{{ $log->created_at->format('d.m.Y H:i') }}</td>
              <td class="c-company">{{ $log->user?->name ?? '—' }}</td>
              <td><span class="log-action" style="color:{{ $ac }};background:{{ $ac }}22">{{ $log->action }}</span></td>
              <td class="c-desc">{{ $log->target ?? '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
@endsection
