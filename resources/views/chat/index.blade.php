@extends('layouts.app')
@section('title', 'Chat — Smart Calendar')

@push('styles')
<style>
/* ── Layout ── */
.chat-page { display:flex; height:calc(100vh - 64px); padding:1.25rem 1.5rem; gap:1.25rem; max-width:1280px; margin:0 auto; box-sizing:border-box; }

/* ── Sidebar ── */
.chat-sidebar { width:272px; flex-shrink:0; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow-sm); display:flex; flex-direction:column; overflow:hidden; }
.chat-sidebar-head { padding:.85rem 1rem .75rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:.5rem; }
.chat-sidebar-head h2 { font-size:.875rem; font-weight:700; color:var(--text); }
.chat-head-actions { display:flex; gap:.4rem; }
.chat-head-btn { background:var(--accent-soft); color:var(--accent); border:none; border-radius:8px; padding:.3rem .6rem; font-size:.72rem; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:.3rem; white-space:nowrap; transition:background .15s; }
.chat-head-btn:hover { background:var(--accent-soft-2); }
.chat-head-btn svg { flex-shrink:0; }

.chat-list { overflow-y:auto; flex:1; }
.chat-item { display:flex; align-items:center; gap:.7rem; padding:.7rem 1rem; cursor:pointer; border-bottom:1px solid var(--border); transition:background .15s; position:relative; }
.chat-item:hover { background:var(--surface-2); }
.chat-item.active { background:var(--accent-soft); }
.chat-item-avatar { width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.8rem; font-weight:700; color:#fff; flex-shrink:0; }
.avatar-direct { background:linear-gradient(135deg,#0176D3,#0891B2); }
.avatar-group  { background:linear-gradient(135deg,#7C3AED,#A855F7); }
.chat-item-info { flex:1; min-width:0; }
.chat-item-name { font-size:.83rem; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.chat-item-preview { font-size:.73rem; color:var(--text-faint); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px; }
.chat-item-meta { display:flex; flex-direction:column; align-items:flex-end; gap:.3rem; }
.chat-item-time { font-size:.68rem; color:var(--text-faint); white-space:nowrap; }
.chat-badge { background:var(--accent); color:#fff; font-size:.65rem; font-weight:700; border-radius:999px; min-width:18px; height:18px; display:flex; align-items:center; justify-content:center; padding:0 5px; }

/* ── Main panel ── */
.chat-main { flex:1; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow-sm); display:flex; flex-direction:column; overflow:hidden; }
.chat-top { padding:.85rem 1.25rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:.75rem; min-height:58px; }
.chat-top-name { font-size:.95rem; font-weight:700; color:var(--text); }
.chat-top-sub  { font-size:.75rem; color:var(--text-faint); }
.chat-top-placeholder { color:var(--text-faint); font-size:.85rem; }

.chat-msgs { flex:1; overflow-y:auto; padding:1.25rem 1.5rem; display:flex; flex-direction:column; gap:.4rem; }
.chat-empty { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:var(--text-faint); gap:.75rem; }
.chat-empty svg { opacity:.25; }
.chat-empty p { font-size:.85rem; }

.msg-date-sep { text-align:center; font-size:.7rem; color:var(--text-faint); margin:.5rem 0; display:flex; align-items:center; gap:.75rem; }
.msg-date-sep::before,.msg-date-sep::after { content:''; flex:1; height:1px; background:var(--border); }

.msg-row { display:flex; gap:.5rem; align-items:flex-end; max-width:72%; }
.msg-row.mine { align-self:flex-end; flex-direction:row-reverse; }
.msg-row.theirs { align-self:flex-start; }

.msg-avatar { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.65rem; font-weight:700; color:#fff; background:linear-gradient(135deg,#0176D3,#0891B2); flex-shrink:0; margin-bottom:2px; }

.msg-content { display:flex; flex-direction:column; gap:2px; }
.msg-sender { font-size:.68rem; font-weight:600; color:var(--accent); margin-bottom:2px; margin-left:4px; }
.msg-row.mine .msg-sender { display:none; }

.msg-bubble { padding:.55rem .85rem; border-radius:14px; font-size:.875rem; line-height:1.55; word-break:break-word; max-width:480px; }
.msg-row.theirs .msg-bubble { background:var(--surface-2); border:1px solid var(--border); color:var(--text); border-bottom-left-radius:4px; }
.msg-row.mine   .msg-bubble { background:var(--accent); color:#fff; border-bottom-right-radius:4px; }

.msg-attachment-img { max-width:260px; max-height:220px; border-radius:10px; display:block; cursor:pointer; object-fit:cover; }
.msg-attachment-vid { max-width:300px; max-height:220px; border-radius:10px; display:block; }
.msg-row.mine .msg-attachment-img,
.msg-row.mine .msg-attachment-vid { border:2px solid rgba(255,255,255,.25); }

.msg-time { font-size:.65rem; color:var(--text-faint); margin-top:2px; }
.msg-row.mine .msg-time { text-align:right; }

/* ── Input ── */
.chat-input { border-top:1px solid var(--border); padding:.85rem 1.25rem; display:flex; gap:.65rem; align-items:flex-end; }
.chat-input textarea { flex:1; border:1px solid var(--border-strong); border-radius:10px; padding:.6rem .9rem; font-size:.875rem; font-family:inherit; color:var(--text); background:var(--surface-2); resize:none; outline:none; max-height:120px; line-height:1.5; transition:border-color .2s; }
.chat-input textarea:focus { border-color:var(--accent); background:#fff; }
.chat-input textarea::placeholder { color:var(--text-faint); }
.chat-input-actions { display:flex; gap:.4rem; align-items:flex-end; }
.btn-icon { width:38px; height:38px; border:none; border-radius:9px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .15s,transform .1s; flex-shrink:0; }
.btn-icon:active { transform:scale(.94); }
.btn-attach { background:var(--surface-2); color:var(--text-dim); border:1px solid var(--border-strong); }
.btn-attach:hover { background:var(--accent-soft); color:var(--accent); border-color:var(--accent); }
.btn-send { background:var(--accent); color:#fff; }
.btn-send:hover { background:var(--accent-hover); }
.btn-send:disabled { opacity:.4; cursor:not-allowed; }

/* ── Modals ── */
.modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; display:flex; align-items:center; justify-content:center; padding:1rem; }
.modal-box { background:var(--surface); border-radius:var(--radius); padding:1.5rem; width:100%; max-width:380px; box-shadow:var(--shadow-lg); }
.modal-box h3 { font-size:1rem; font-weight:700; color:var(--text); margin-bottom:1rem; }
.modal-input { width:100%; border:1px solid var(--border-strong); border-radius:9px; padding:.6rem .9rem; font-size:.875rem; font-family:inherit; color:var(--text); background:var(--surface-2); outline:none; transition:border-color .2s; margin-bottom:.85rem; box-sizing:border-box; }
.modal-input:focus { border-color:var(--accent); }
.modal-user-list { max-height:220px; overflow-y:auto; border:1px solid var(--border); border-radius:9px; margin-bottom:1rem; }
.modal-user-row { display:flex; align-items:center; gap:.65rem; padding:.6rem .85rem; cursor:pointer; transition:background .15s; }
.modal-user-row:hover { background:var(--surface-2); }
.modal-user-row:not(:last-child) { border-bottom:1px solid var(--border); }
.modal-user-row input[type=checkbox],.modal-user-row input[type=radio] { accent-color:var(--accent); width:15px; height:15px; flex-shrink:0; }
.modal-user-name { font-size:.85rem; color:var(--text); }
.modal-actions { display:flex; gap:.65rem; justify-content:flex-end; }
.btn-primary { background:var(--accent); color:#fff; border:none; border-radius:9px; padding:.55rem 1.1rem; font-size:.85rem; font-weight:600; cursor:pointer; font-family:inherit; transition:background .15s; }
.btn-primary:hover { background:var(--accent-hover); }
.btn-cancel { background:var(--surface-2); color:var(--text-dim); border:1px solid var(--border-strong); border-radius:9px; padding:.55rem 1rem; font-size:.85rem; cursor:pointer; font-family:inherit; transition:background .15s; }
.btn-cancel:hover { background:var(--bg); }

/* ── Upload preview ── */
.upload-preview { display:flex; align-items:center; gap:.5rem; background:var(--accent-soft); border:1px solid var(--accent-soft-2); border-radius:8px; padding:.45rem .75rem; font-size:.78rem; color:var(--accent); margin-bottom:.4rem; }
.upload-preview svg { flex-shrink:0; }
.upload-preview-name { flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.upload-preview-remove { cursor:pointer; color:var(--text-faint); padding:0 .2rem; }
.upload-preview-remove:hover { color:var(--danger); }
</style>
@endpush

@section('content')
<div class="chat-page">

  {{-- ── Sidebar ── --}}
  <aside class="chat-sidebar">
    <div class="chat-sidebar-head">
      <h2>Söhbətlər</h2>
      <div class="chat-head-actions">
        <button class="chat-head-btn" onclick="showDirectModal()">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Birbaşa
        </button>
        <button class="chat-head-btn" onclick="showGroupModal()">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Qrup
        </button>
      </div>
    </div>

    <div class="chat-list" id="chatList">
      @forelse($convs as $c)
        <div class="chat-item" id="conv-item-{{ $c['conv']->id }}"
          data-conv-id="{{ $c['conv']->id }}"
          onclick="openConversation({{ $c['conv']->id }}, '{{ addslashes($c['displayName']) }}', {{ $c['conv']->isGroup() ? 'true' : 'false' }})">
          <div class="chat-item-avatar {{ $c['conv']->isGroup() ? 'avatar-group' : 'avatar-direct' }}">
            @if($c['conv']->isGroup())
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            @else
              {{ strtoupper(substr($c['displayName'], 0, 1)) }}
            @endif
          </div>
          <div class="chat-item-info">
            <div class="chat-item-name">{{ $c['displayName'] }}</div>
            <div class="chat-item-preview" id="preview-{{ $c['conv']->id }}">
              @if($c['lastMsg'])
                @if($c['lastMsg']->user_id === auth()->id()) Siz: @endif
                {{ $c['lastMsg']->body ? Str::limit($c['lastMsg']->body, 28) : '📎 Fayl' }}
              @else
                Hələ mesaj yoxdur
              @endif
            </div>
          </div>
          <div class="chat-item-meta">
            @if($c['lastMsg'])
              <span class="chat-item-time">{{ $c['lastMsg']->created_at->format('H:i') }}</span>
            @endif
            <span class="chat-badge" id="badge-{{ $c['conv']->id }}"
              style="{{ $c['unread'] > 0 ? '' : 'display:none' }}">{{ $c['unread'] }}</span>
          </div>
        </div>
      @empty
        <div style="padding:1.5rem 1rem;text-align:center;font-size:.8rem;color:var(--text-faint)">
          Hələ söhbət yoxdur
        </div>
      @endforelse
    </div>
  </aside>

  {{-- ── Main ── --}}
  <div class="chat-main">
    <div class="chat-top" id="chatTop">
      <span class="chat-top-placeholder">Sol tərəfdən söhbət seçin</span>
    </div>

    <div class="chat-msgs" id="chatMsgs">
      <div class="chat-empty" id="chatEmpty">
        <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <p>Söhbət başlatmaq üçün istifadəçi seçin</p>
      </div>
    </div>

    <div id="chatInputWrap" style="display:none">
      <div id="uploadPreview" style="display:none" class="upload-preview" style="margin:.4rem 1.25rem 0">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
        <span class="upload-preview-name" id="uploadFileName"></span>
        <span class="upload-preview-remove" onclick="clearUpload()" title="Ləğv et">✕</span>
      </div>
      <div class="chat-input">
        <textarea id="msgInput" placeholder="Mesaj yazın…" rows="1"
          onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
        <div class="chat-input-actions">
          <button class="btn-icon btn-attach" onclick="document.getElementById('fileInput').click()" title="Fayl əlavə et">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
          </button>
          <button class="btn-icon btn-send" id="sendBtn" onclick="sendMessage()" title="Göndər" disabled>
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          </button>
        </div>
        <input type="file" id="fileInput" accept="image/*,video/mp4,video/mov,video/avi,video/webm"
          style="display:none" onchange="handleFileSelect(event)">
      </div>
    </div>
  </div>
</div>

{{-- ── Modal: Birbaşa söhbət ── --}}
<div id="directModal" class="modal-backdrop" style="display:none" onclick="closeModalOnBg(event,'directModal')">
  <div class="modal-box">
    <h3>Birbaşa söhbət</h3>
    <div class="modal-user-list">
      @foreach($users as $u)
        <label class="modal-user-row">
          <input type="radio" name="directUser" value="{{ $u->id }}">
          <div class="chat-item-avatar avatar-direct" style="width:28px;height:28px;font-size:.7rem;flex-shrink:0">
            {{ strtoupper(substr($u->name, 0, 1)) }}
          </div>
          <span class="modal-user-name">{{ $u->name }}</span>
        </label>
      @endforeach
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="document.getElementById('directModal').style.display='none'">Ləğv et</button>
      <button class="btn-primary" onclick="startDirect()">Başlat</button>
    </div>
  </div>
</div>

{{-- ── Modal: Qrup yarat ── --}}
<div id="groupModal" class="modal-backdrop" style="display:none" onclick="closeModalOnBg(event,'groupModal')">
  <div class="modal-box">
    <h3>Yeni qrup</h3>
    <input type="text" class="modal-input" id="groupName" placeholder="Qrup adı">
    <div class="modal-user-list">
      @foreach($users as $u)
        <label class="modal-user-row">
          <input type="checkbox" class="group-user-cb" value="{{ $u->id }}">
          <div class="chat-item-avatar avatar-direct" style="width:28px;height:28px;font-size:.7rem;flex-shrink:0">
            {{ strtoupper(substr($u->name, 0, 1)) }}
          </div>
          <span class="modal-user-name">{{ $u->name }}</span>
        </label>
      @endforeach
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="document.getElementById('groupModal').style.display='none'">Ləğv et</button>
      <button class="btn-primary" onclick="createGroup()">Yarat</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF         = document.querySelector('meta[name="csrf-token"]').content;
const ME_ID        = {{ auth()->id() }};

let activeConvId   = null;
let activeIsGroup  = false;
let lastMsgId      = 0;
let pollTimer      = null;
let lastDate       = null;
let pendingFile    = null;

// ── Open conversation ─────────────────────────────────────────────────────────
function openConversation(convId, name, isGroup) {
  if (activeConvId === convId) return;

  clearInterval(pollTimer);
  activeConvId  = convId;
  activeIsGroup = isGroup;
  lastMsgId     = 0;
  lastDate      = null;
  pendingFile   = null;

  document.querySelectorAll('.chat-item').forEach(el => el.classList.remove('active'));
  document.getElementById(`conv-item-${convId}`)?.classList.add('active');

  const badge = document.getElementById(`badge-${convId}`);
  if (badge) badge.style.display = 'none';

  // Header
  const avatarHtml = isGroup
    ? `<div class="chat-item-avatar avatar-group" style="width:34px;height:34px"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>`
    : `<div class="chat-item-avatar avatar-direct" style="width:34px;height:34px;font-size:.8rem">${name.charAt(0).toUpperCase()}</div>`;

  document.getElementById('chatTop').innerHTML = `${avatarHtml}<div><div class="chat-top-name">${escHtml(name)}</div><div class="chat-top-sub">${isGroup ? 'Qrup söhbəti' : 'Birbaşa söhbət'}</div></div>`;

  document.getElementById('chatMsgs').innerHTML = '';
  document.getElementById('chatInputWrap').style.display = 'block';
  clearUpload();
  updateSendBtn();
  document.getElementById('msgInput').focus();

  loadMessages();
  pollTimer = setInterval(pollMessages, 2000);
}

// ── Load & poll ───────────────────────────────────────────────────────────────
async function loadMessages() {
  const res  = await apiFetch(`/chat/${activeConvId}/messages`);
  const msgs = await res.json();
  const box  = document.getElementById('chatMsgs');
  box.innerHTML = '';
  lastDate = null;
  msgs.forEach(m => appendMessage(m));
  scrollBottom();
  if (msgs.length) lastMsgId = msgs[msgs.length - 1].id;
}

async function pollMessages() {
  if (!activeConvId) return;
  const res  = await apiFetch(`/chat/${activeConvId}/messages?after=${lastMsgId}`);
  const msgs = await res.json();
  if (!msgs.length) return;

  const box      = document.getElementById('chatMsgs');
  const atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 80;
  msgs.forEach(m => appendMessage(m));
  if (atBottom) scrollBottom();
  lastMsgId = msgs[msgs.length - 1].id;
  updatePreview(activeConvId, msgs[msgs.length - 1]);
}

// ── Append message ────────────────────────────────────────────────────────────
function appendMessage(msg) {
  const box = document.getElementById('chatMsgs');

  if (msg.date !== lastDate) {
    lastDate = msg.date;
    const sep = document.createElement('div');
    sep.className = 'msg-date-sep';
    sep.textContent = msg.date;
    box.appendChild(sep);
  }

  const row = document.createElement('div');
  row.className = `msg-row ${msg.mine ? 'mine' : 'theirs'}`;

  let mediaHtml = '';
  if (msg.attachment_url) {
    if (msg.attachment_type === 'image') {
      mediaHtml = `<img src="${msg.attachment_url}" class="msg-attachment-img" onclick="window.open('${msg.attachment_url}','_blank')" loading="lazy">`;
    } else {
      mediaHtml = `<video src="${msg.attachment_url}" class="msg-attachment-vid" controls preload="metadata"></video>`;
    }
  }

  const bodyHtml = msg.body ? `<div class="msg-bubble">${msg.body ? escHtml(msg.body).replace(/\n/g,'<br>') : ''}</div>` : '';
  const mediaWrap = mediaHtml ? `<div class="msg-bubble" style="padding:.4rem;background:${msg.mine ? 'var(--accent)' : 'var(--surface-2)'};border:${msg.mine ? 'none' : '1px solid var(--border)'}">${mediaHtml}</div>` : '';

  const senderHtml = (!msg.mine && activeIsGroup)
    ? `<div class="msg-sender">${escHtml(msg.sender_name)}</div>` : '';

  const avatarHtml = !msg.mine
    ? `<div class="msg-avatar">${msg.sender_initial}</div>` : '';

  row.innerHTML = `
    ${avatarHtml}
    <div class="msg-content">
      ${senderHtml}
      ${mediaWrap}
      ${bodyHtml}
      <div class="msg-time">${msg.time}</div>
    </div>
  `;

  box.appendChild(row);
}

// ── Send ──────────────────────────────────────────────────────────────────────
async function sendMessage() {
  if (!activeConvId) return;
  const input = document.getElementById('msgInput');
  const text  = input.value.trim();
  if (!text && !pendingFile) return;

  const btn = document.getElementById('sendBtn');
  btn.disabled = true;

  const fd = new FormData();
  if (text) fd.append('body', text);
  if (pendingFile) fd.append('attachment', pendingFile);

  input.value = '';
  autoResize(input);
  clearUpload();
  updateSendBtn();

  try {
    const res = await fetch(`/chat/${activeConvId}/send`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
      body: fd,
    });
    const msg = await res.json();
    if (msg.error) { alert(msg.error); return; }
    appendMessage(msg);
    scrollBottom();
    lastMsgId = msg.id;
    updatePreview(activeConvId, msg);
  } finally {
    btn.disabled = false;
    input.focus();
  }
}

// ── File upload ───────────────────────────────────────────────────────────────
function handleFileSelect(e) {
  const file = e.target.files[0];
  if (!file) return;

  const maxMB = file.type.startsWith('image/') ? 10 : 50;
  if (file.size > maxMB * 1024 * 1024) {
    alert(`Fayl ölçüsü ${maxMB}MB-dən az olmalıdır.`);
    e.target.value = '';
    return;
  }

  pendingFile = file;
  document.getElementById('uploadFileName').textContent = file.name;
  document.getElementById('uploadPreview').style.display = 'flex';
  updateSendBtn();
  document.getElementById('msgInput').focus();
  e.target.value = '';
}

function clearUpload() {
  pendingFile = null;
  document.getElementById('uploadPreview').style.display = 'none';
  document.getElementById('uploadFileName').textContent = '';
  document.getElementById('fileInput').value = '';
  updateSendBtn();
}

function updateSendBtn() {
  const text = document.getElementById('msgInput')?.value.trim();
  document.getElementById('sendBtn').disabled = !text && !pendingFile;
}

// ── Direct & group ────────────────────────────────────────────────────────────
function showDirectModal() { document.getElementById('directModal').style.display = 'flex'; }
function showGroupModal()  { document.getElementById('groupModal').style.display = 'flex'; }

async function startDirect() {
  const sel = document.querySelector('input[name="directUser"]:checked');
  if (!sel) { alert('İstifadəçi seçin'); return; }
  const userId = sel.value;

  const res  = await fetch(`/chat/direct/${userId}`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
  });
  const data = await res.json();
  document.getElementById('directModal').style.display = 'none';

  if (!document.getElementById(`conv-item-${data.id}`)) {
    addConvToSidebar(data.id, data.displayName, false);
  }
  openConversation(data.id, data.displayName, false);
}

async function createGroup() {
  const name = document.getElementById('groupName').value.trim();
  if (!name) { alert('Qrup adı daxil edin'); return; }

  const ids = [...document.querySelectorAll('.group-user-cb:checked')].map(el => el.value);
  if (!ids.length) { alert('Ən az 1 üzv seçin'); return; }

  const res  = await fetch('/chat/group', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify({ name, user_ids: ids }),
  });
  const data = await res.json();
  document.getElementById('groupModal').style.display = 'none';
  document.getElementById('groupName').value = '';
  document.querySelectorAll('.group-user-cb').forEach(cb => cb.checked = false);

  if (!document.getElementById(`conv-item-${data.id}`)) {
    addConvToSidebar(data.id, data.displayName, true);
  }
  openConversation(data.id, data.displayName, true);
}

function addConvToSidebar(convId, name, isGroup) {
  const list = document.getElementById('chatList');
  const item = document.createElement('div');
  item.className = 'chat-item';
  item.id = `conv-item-${convId}`;
  item.dataset.convId = convId;
  item.onclick = () => openConversation(convId, name, isGroup);

  const avatarClass = isGroup ? 'avatar-group' : 'avatar-direct';
  const avatarContent = isGroup
    ? `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`
    : name.charAt(0).toUpperCase();

  item.innerHTML = `
    <div class="chat-item-avatar ${avatarClass}">${avatarContent}</div>
    <div class="chat-item-info">
      <div class="chat-item-name">${escHtml(name)}</div>
      <div class="chat-item-preview" id="preview-${convId}">Hələ mesaj yoxdur</div>
    </div>
    <div class="chat-item-meta">
      <span class="chat-badge" id="badge-${convId}" style="display:none">0</span>
    </div>
  `;
  list.prepend(item);
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function updatePreview(convId, msg) {
  const el = document.getElementById(`preview-${convId}`);
  if (!el) return;
  const prefix = msg.mine ? 'Siz: ' : '';
  el.textContent = prefix + (msg.body ? msg.body.substring(0, 28) : '📎 Fayl');
}

function handleKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
  updateSendBtn();
}

function autoResize(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

function scrollBottom() {
  const box = document.getElementById('chatMsgs');
  box.scrollTop = box.scrollHeight;
}

function escHtml(s) {
  return (s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function apiFetch(url) {
  return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
}

function closeModalOnBg(e, id) {
  if (e.target.id === id) document.getElementById(id).style.display = 'none';
}

// Nav badge
function refreshNavBadge() {
  apiFetch('/chat/unread-count').then(r => r.json()).then(d => {
    const b = document.getElementById('navChatBadge');
    if (!b) return;
    if (d.count > 0) { b.textContent = d.count; b.style.display = 'inline-flex'; }
    else b.style.display = 'none';
  }).catch(() => {});
}
setInterval(refreshNavBadge, 6000);
</script>
@endpush
