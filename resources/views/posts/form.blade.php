@extends('layouts.app')

@section('title', ($post ? 'Redaktə' : 'Yeni paylaşım') . ' — Smart Calendar')

@section('content')
<div class="form-page">
  <a href="{{ $post ? route('posts.show',$post) : route('day',$date) }}" class="back-link">‹ Geri</a>
  <h2>{{ $post ? 'Paylaşımı redaktə et' : 'Yeni paylaşım' }}</h2>

  <form method="POST"
        action="{{ $post ? route('posts.update',$post) : route('posts.store') }}"
        enctype="multipart/form-data"
        class="post-form">
    @csrf
    @if($post) @method('PUT') @endif

    @if($errors->any())
      <div class="alert alert-error">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
      </div>
    @endif

    <div class="form-row">
      <div class="form-field">
        <label>Tarix</label>
        <input type="date" name="date" value="{{ old('date', $date) }}" required/>
      </div>
      <div class="form-field">
        <label>Şirkət adı</label>
        <input type="text" name="company" value="{{ old('company', $post?->company) }}" placeholder="Məs: Coca-Cola" required/>
      </div>
    </div>

    <div class="form-row">
      <div class="form-field">
        <label>Status</label>
        <select name="status" class="form-select">
          @foreach($statuses as $key => $label)
            <option value="{{ $key }}" {{ old('status',$post?->status ?? 'planned') === $key ? 'selected' : '' }}>
              {{ $label }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="form-field">
        <label>Rəng</label>
        @php $activeColor = old('color', $post?->color ?? '#0176D3'); @endphp
        <div class="color-picker-wrap">
          <div class="color-swatches" id="color-swatches">
            @foreach($colors as $c)
              <button type="button"
                      class="cswatch {{ $activeColor === $c ? 'active' : '' }}"
                      style="--c:{{ $c }}"
                      data-color="{{ $c }}"
                      title="{{ $c }}">
                <svg class="cswatch-check" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </button>
            @endforeach
          </div>

          <label class="custom-color-btn" title="Xüsusi rəng seç">
            <span class="custom-swatch-preview" id="custom-preview" style="background:{{ $activeColor }}"></span>
            <span class="custom-color-text">Xüsusi</span>
            <input type="color" id="color-native" value="{{ $activeColor }}" style="position:absolute;opacity:0;width:0;height:0;pointer-events:none"/>
          </label>

          <input type="hidden" name="color" id="color-value" value="{{ $activeColor }}"/>
        </div>
      </div>
    </div>

    <div class="form-field">
      <label>Mətn (description)</label>
      <textarea name="description" rows="5" placeholder="Paylaşılacaq mətni yazın...">{{ old('description',$post?->description) }}</textarea>
    </div>

    {{-- Mövcud media (edit rejimində) --}}
    @if($post && $post->media->isNotEmpty())
      <div class="form-field">
        <label>Mövcud media</label>
        <div class="media-edit-list">
          @foreach($post->media as $m)
            <div class="media-edit-item" id="mei-{{ $m->id }}">
              <div class="media-edit-thumb">
                @if($m->type === 'image')
                  <img src="{{ asset('storage/'.$m->file_path) }}" alt="{{ $m->original_name }}"/>
                @else
                  <video src="{{ asset('storage/'.$m->file_path) }}" muted></video>
                @endif
              </div>
              <div class="media-edit-body">
                <span class="media-edit-name">{{ $m->original_name }}</span>
                <textarea
                  name="media_desc_existing[{{ $m->id }}]"
                  class="media-desc-input"
                  rows="2"
                  placeholder="Bu fayl üçün açıqlama...">{{ old('media_desc_existing.'.$m->id, $m->description) }}</textarea>
              </div>
              <label class="media-del-btn" title="Sil">
                <input type="checkbox" name="delete_media[]" value="{{ $m->id }}" onchange="this.closest('.media-edit-item').classList.toggle('marked-delete', this.checked)"/>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
              </label>
            </div>
          @endforeach
        </div>
      </div>
    @endif

    <div class="form-field">
      <label>{{ $post ? 'Yeni şəkil/video əlavə et' : 'Şəkillər və videolar' }}</label>
      <div class="upload-box" onclick="document.getElementById('media-input').click()">
        <div class="upload-placeholder">
          <span>Klikləyin və ya faylları bura atın</span>
          <small>Şəkil (jpg, png, webp) · Video (mp4, mov) · maks 50MB</small>
        </div>
        <input type="file" id="media-input" name="media[]" accept="image/*,video/*" multiple hidden
               onchange="previewFiles(this)"/>
      </div>
      <div id="preview-gallery" class="media-edit-list hidden" style="margin-top:.75rem"></div>
    </div>

    <div class="form-actions">
      <a href="{{ $post ? route('posts.show',$post) : route('day',$date) }}" class="ghost-btn">Ləğv et</a>
      <button type="submit" class="btn-primary inline">Yadda saxla</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
function previewFiles(input) {
  const gallery = document.getElementById('preview-gallery');
  gallery.innerHTML = '';
  if (!input.files.length) { gallery.classList.add('hidden'); return; }
  gallery.classList.remove('hidden');
  Array.from(input.files).forEach((file, i) => {
    const url  = URL.createObjectURL(file);
    const isVideo = file.type.startsWith('video/');
    const item = document.createElement('div');
    item.className = 'media-edit-item';
    item.innerHTML = `
      <div class="media-edit-thumb">
        ${isVideo
          ? `<video src="${url}" muted></video>`
          : `<img src="${url}" alt="${file.name}"/>`}
      </div>
      <div class="media-edit-body">
        <span class="media-edit-name">${file.name}</span>
        <textarea
          name="media_descriptions[${i}]"
          class="media-desc-input"
          rows="2"
          placeholder="Bu fayl üçün açıqlama..."></textarea>
      </div>`;
    gallery.appendChild(item);
  });
}
// Rəng seçimi
const colorVal     = document.getElementById('color-value');
const colorNative  = document.getElementById('color-native');
const customPreview= document.getElementById('custom-preview');
const swatchBtns   = document.querySelectorAll('.cswatch');

function setColor(hex, fromSwatch = null) {
  colorVal.value = hex;
  colorNative.value = hex;
  customPreview.style.background = hex;
  swatchBtns.forEach(b => b.classList.toggle('active', b === fromSwatch));
}

swatchBtns.forEach(btn => {
  btn.addEventListener('click', () => setColor(btn.dataset.color, btn));
});

document.querySelector('.custom-color-btn').addEventListener('click', () => {
  colorNative.click();
});

colorNative.addEventListener('input', () => {
  setColor(colorNative.value, null);
});
</script>
@endpush
