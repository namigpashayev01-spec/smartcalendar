@extends('layouts.app')

@section('title', 'Smart Calendar — Kontent Planı')

@section('content')

  <!-- GİRİŞ EKRANI (gizli — artıq auth var) -->
  <div id="login-screen" class="login-screen hidden"></div>

  <!-- ƏSAS TƏTBİQ -->
  <div id="app" class="app"
       data-user="{{ $user->username }}"
       data-name="{{ $user->name }}"
       data-role="{{ $user->role }}"
       data-logout="{{ route('logout') }}"
       data-csrf="{{ csrf_token() }}">

    <header class="app-header">
      <div class="brand" id="brand-home">
        <span class="brand-mark"><img src="{{ asset('images/logo.webp') }}" alt="logo" /></span>
        <span class="brand-text">Smart Calendar</span>
      </div>

      <div class="header-center">
        <div class="month-nav">
          <button id="prev-btn" class="nav-btn" title="Əvvəlki ay">‹</button>
          <span id="month-label" class="month-label"></span>
          <button id="next-btn" class="nav-btn" title="Növbəti ay">›</button>
        </div>
        <button id="today-btn" class="ghost-btn">Bu gün</button>
        <button id="report-btn" class="ghost-btn">Hesabat</button>
        <button id="log-btn" class="ghost-btn">Change history</button>
      </div>

      <div class="header-right">
        <input type="month" id="date-picker" class="date-picker" title="Ay seç" />
        <button id="add-btn" class="add-btn" title="Yeni paylaşım əlavə et">+</button>
        <span id="user-badge" class="user-badge"></span>
        <button id="logout-btn" class="ghost-btn">Çıxış</button>
      </div>
    </header>

    <!-- TƏQVİM -->
    <section id="view-calendar" class="view">
      <div class="calendar-wrapper">
        <div class="weekday-row">
          <div>B.e</div><div>Ç.a</div><div>Çər</div><div>C.a</div><div>Cüm</div>
          <div class="we">Şən</div><div class="we">Baz</div>
        </div>
        <div id="calendar-grid" class="calendar-grid"></div>
      </div>
    </section>

    <!-- GÜN GÖRÜNÜŞÜ -->
    <section id="view-day" class="view hidden">
      <div class="day-page" id="day-content"></div>
    </section>

    <!-- HESABAT -->
    <section id="view-report" class="view hidden">
      <div class="report-page">
        <div class="report-head">
          <div>
            <h2>Hesabat</h2>
            <p class="report-sub" id="report-sub"></p>
          </div>
          <button class="ghost-btn" id="report-csv">CSV yüklə</button>
        </div>
        <div class="report-filters">
          <div class="form-field"><label>Şirkət</label><select id="rf-company" class="form-select"></select></div>
          <div class="form-field"><label>Status</label><select id="rf-status" class="form-select"></select></div>
          <div class="form-field"><label>Başlanğıc tarix</label><input type="date" id="rf-from" /></div>
          <div class="form-field"><label>Son tarix</label><input type="date" id="rf-to" /></div>
          <div class="form-field rf-search-field"><label>Axtarış</label><input type="text" id="rf-search" placeholder="Şirkət və ya təsvir..." /></div>
          <button class="ghost-btn small" id="rf-reset">Sıfırla</button>
        </div>
        <div class="report-stats" id="report-stats"></div>
        <div class="report-table-wrap" id="report-table"></div>
      </div>
    </section>

    <!-- JURNAL -->
    <section id="view-log" class="view hidden">
      <div class="report-page">
        <div class="report-head">
          <div>
            <h2>Change history</h2>
            <p class="report-sub" id="log-sub"></p>
          </div>
          <button class="ghost-btn" id="log-clear">Təmizlə</button>
        </div>
        <div class="report-filters">
          <div class="form-field"><label>İstifadəçi</label><select id="lf-user" class="form-select"></select></div>
          <div class="form-field"><label>Əməliyyat</label><select id="lf-action" class="form-select"></select></div>
          <div class="form-field rf-search-field"><label>Axtarış</label><input type="text" id="lf-search" placeholder="Obyekt (şirkət/tarix)..." /></div>
          <button class="ghost-btn small" id="lf-reset">Sıfırla</button>
        </div>
        <div class="report-table-wrap" id="log-table"></div>
      </div>
    </section>

    <!-- YENİ / REDAKTƏ FORM -->
    <section id="view-add" class="view hidden">
      <div class="form-page">
        <button class="back-link" data-back>‹ Geri</button>
        <h2 id="add-title">Yeni paylaşım</h2>
        <form id="post-form" class="post-form">
          <input type="hidden" id="post-id" />
          <div class="form-row">
            <div class="form-field"><label>Tarix</label><input type="date" id="post-date" required /></div>
            <div class="form-field"><label>Şirkət adı</label><input type="text" id="post-company" placeholder="Məs: Coca-Cola" required /></div>
          </div>
          <div class="form-row">
            <div class="form-field">
              <label>Status</label>
              <select id="post-status" class="form-select"></select>
            </div>
            <div class="form-field">
              <label>Rəng</label>
              <div class="color-picker" id="color-picker">
                <div class="swatches" id="color-swatches"></div>
                <label class="custom-color" title="Xüsusi rəng seç">
                  <input type="color" id="post-color-custom" />
                  <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".75" fill="currentColor" stroke="none"/><circle cx="17.5" cy="10.5" r=".75" fill="currentColor" stroke="none"/><circle cx="8.5" cy="7.5" r=".75" fill="currentColor" stroke="none"/><circle cx="6.5" cy="12.5" r=".75" fill="currentColor" stroke="none"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
                  <span>Xüsusi</span>
                </label>
              </div>
            </div>
          </div>
          <div class="form-field">
            <label>Mətn (description)</label>
            <textarea id="post-desc" rows="5" placeholder="Paylaşılacaq mətni yazın..."></textarea>
          </div>
          <div class="form-field">
            <label>Şəkillər və videolar</label>
            <div class="upload-box" id="upload-box">
              <input type="file" id="post-media" accept="image/*,video/*" multiple hidden />
              <div id="upload-placeholder" class="upload-placeholder">
                <span class="up-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 13v8"/><path d="m8 17 4-4 4 4"/><path d="M20 16.7A5 5 0 0 0 18 7h-1.26A8 8 0 1 0 4 15.25"/></svg>
                </span>
                <span>Klikləyin və ya faylları bura atın</span>
                <small>Bir neçə şəkil/video seçin</small>
              </div>
            </div>
            <div id="media-gallery" class="media-gallery hidden"></div>
          </div>
          <div class="form-actions">
            <button type="button" class="ghost-btn" data-back>Ləğv et</button>
            <button type="submit" class="btn-primary inline">Yadda saxla</button>
          </div>
        </form>
      </div>
    </section>

    <!-- DETAL -->
    <section id="view-detail" class="view hidden">
      <div class="detail-page" id="detail-content"></div>
    </section>

  </div><!-- /app -->

@endsection

@push('scripts')
<script>
  // app.js-ə data ötür
  const ROOT_DATA = {
    user:    "{{ $user->username }}",
    name:    "{{ $user->name }}",
    role:    "{{ $user->role }}",
    logout:  "{{ route('logout') }}",
    csrf:    "{{ csrf_token() }}"
  };
</script>
<script src="{{ asset('js/app.js') }}"></script>
@endpush
