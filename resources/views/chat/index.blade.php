@extends('layouts.app')

@section('title', 'Chat — Smart Calendar')

@push('styles')
<style>
.chat-wrap {
  display: flex;
  height: calc(100vh - 72px);
  gap: 1.25rem;
  padding: 1.25rem 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
  box-sizing: border-box;
}

/* ── Sidebar ── */
.chat-sidebar {
  width: 280px;
  flex-shrink: 0;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.chat-sidebar-head {
  padding: 1rem 1.1rem .75rem;
  border-bottom: 1px solid var(--border);
}

.chat-sidebar-head h2 {
  font-size: .9rem;
  font-weight: 700;
  color: var(--text);
  letter-spacing: -.3px;
}

.chat-user-list {
  overflow-y: auto;
  flex: 1;
}

.chat-user-item {
  display: flex;
  align-items: center;
  gap: .75rem;
  padding: .75rem 1.1rem;
  cursor: pointer;
  border-bottom: 1px solid var(--border);
  transition: background .15s;
  position: relative;
}

.chat-user-item:hover { background: var(--surface-2); }
.chat-user-item.active { background: var(--accent-soft); }

.chat-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), #0891B2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .85rem;
  font-weight: 700;
  color: #fff;
  flex-shrink: 0;
}

.chat-user-info { flex: 1; min-width: 0; }
.chat-user-name {
  font-size: .85rem;
  font-weight: 600;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.chat-user-preview {
  font-size: .75rem;
  color: var(--text-faint);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-top: 2px;
}

.chat-unread-badge {
  background: var(--accent);
  color: #fff;
  font-size: .68rem;
  font-weight: 700;
  border-radius: 999px;
  min-width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 5px;
  flex-shrink: 0;
}

/* ── Main panel ── */
.chat-main {
  flex: 1;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.chat-header {
  padding: .85rem 1.25rem;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: .75rem;
  min-height: 58px;
}

.chat-header-name {
  font-size: .95rem;
  font-weight: 700;
  color: var(--text);
}

.chat-header-empty {
  color: var(--text-faint);
  font-size: .85rem;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1.25rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: .5rem;
}

.chat-empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--text-faint);
  gap: .75rem;
}

.chat-empty-state svg { opacity: .3; }
.chat-empty-state p { font-size: .85rem; }

.chat-date-divider {
  text-align: center;
  font-size: .72rem;
  color: var(--text-faint);
  margin: .5rem 0;
  position: relative;
}

.chat-date-divider::before,
.chat-date-divider::after {
  content: '';
  position: absolute;
  top: 50%;
  width: calc(50% - 40px);
  height: 1px;
  background: var(--border);
}
.chat-date-divider::before { left: 0; }
.chat-date-divider::after  { right: 0; }

.msg-row {
  display: flex;
  gap: .5rem;
  align-items: flex-end;
}
.msg-row.mine { flex-direction: row-reverse; }

.msg-bubble {
  max-width: 65%;
  padding: .6rem .85rem;
  border-radius: 14px;
  font-size: .875rem;
  line-height: 1.5;
  word-break: break-word;
  color: var(--text);
  background: var(--surface-2);
  border: 1px solid var(--border);
  position: relative;
}

.msg-row.mine .msg-bubble {
  background: var(--accent);
  color: #fff;
  border-color: var(--accent);
}

.msg-time {
  font-size: .68rem;
  color: var(--text-faint);
  margin-bottom: 2px;
  flex-shrink: 0;
}

.msg-row.mine .msg-time { color: var(--text-faint); }

/* ── Input area ── */
.chat-input-area {
  border-top: 1px solid var(--border);
  padding: .85rem 1.25rem;
  display: flex;
  gap: .75rem;
  align-items: flex-end;
}

.chat-input-area textarea {
  flex: 1;
  border: 1px solid var(--border-strong);
  border-radius: 10px;
  padding: .6rem .9rem;
  font-size: .875rem;
  font-family: inherit;
  color: var(--text);
  background: var(--surface-2);
  resize: none;
  outline: none;
  max-height: 120px;
  line-height: 1.5;
  transition: border-color .2s;
}

.chat-input-area textarea:focus {
  border-color: var(--accent);
  background: #fff;
}

.chat-input-area textarea::placeholder { color: var(--text-faint); }

.chat-send-btn {
  background: var(--accent);
  color: #fff;
  border: none;
  border-radius: 10px;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: background .15s, transform .1s;
}
.chat-send-btn:hover { background: var(--accent-hover); }
.chat-send-btn:active { transform: scale(.95); }
.chat-send-btn:disabled { opacity: .45; cursor: not-allowed; }

.chat-no-select {
  color: var(--text-faint);
  font-size: .85rem;
  text-align: center;
  padding: 2rem;
}
</style>
@endpush

@section('content')
<div class="chat-wrap">

  {{-- Sidebar --}}
  <aside class="chat-sidebar">
    <div class="chat-sidebar-head">
      <h2>Söhbətlər</h2>
    </div>
    <div class="chat-user-list" id="userList">
      @forelse($conversations as $conv)
        <div
          class="chat-user-item"
          data-user-id="{{ $conv['user']->id }}"
          data-user-name="{{ $conv['user']->name }}"
          onclick="openChat({{ $conv['user']->id }}, '{{ addslashes($conv['user']->name) }}')"
        >
          <div class="chat-avatar">{{ strtoupper(substr($conv['user']->name, 0, 1)) }}</div>
          <div class="chat-user-info">
            <div class="chat-user-name">{{ $conv['user']->name }}</div>
            @if($conv['last'])
              <div class="chat-user-preview">
                {{ $conv['last']->from_user_id === auth()->id() ? 'Siz: ' : '' }}{{ Str::limit($conv['last']->body, 30) }}
              </div>
            @else
              <div class="chat-user-preview">Hələ mesaj yoxdur</div>
            @endif
          </div>
          @if($conv['unread'] > 0)
            <span class="chat-unread-badge" id="badge-{{ $conv['user']->id }}">{{ $conv['unread'] }}</span>
          @else
            <span class="chat-unread-badge" id="badge-{{ $conv['user']->id }}" style="display:none">0</span>
          @endif
        </div>
      @empty
        <div class="chat-no-select">Başqa istifadəçi yoxdur</div>
      @endforelse
    </div>
  </aside>

  {{-- Main --}}
  <div class="chat-main">
    <div class="chat-header" id="chatHeader">
      <span class="chat-header-empty">Sol tərəfdən istifadəçi seçin</span>
    </div>

    <div class="chat-messages" id="chatMessages">
      <div class="chat-empty-state" id="emptyState">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <p>Söhbət başlatmaq üçün istifadəçi seçin</p>
      </div>
    </div>

    <div class="chat-input-area" id="chatInputArea" style="display:none">
      <textarea
        id="msgInput"
        placeholder="Mesaj yazın..."
        rows="1"
        onkeydown="handleKey(event)"
        oninput="autoResize(this)"
      ></textarea>
      <button class="chat-send-btn" id="sendBtn" onclick="sendMessage()" title="Göndər">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="22" y1="2" x2="11" y2="13"/>
          <polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>
      </button>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
const ME_ID        = {{ auth()->id() }};
const CSRF         = document.querySelector('meta[name="csrf-token"]')?.content
                     || '{{ csrf_token() }}';

let activeUserId   = null;
let activeUserName = null;
let lastMsgId      = 0;
let pollTimer      = null;
let lastDate       = null;

function openChat(userId, userName) {
  if (activeUserId === userId) return;

  // Update sidebar active state
  document.querySelectorAll('.chat-user-item').forEach(el => el.classList.remove('active'));
  document.querySelector(`[data-user-id="${userId}"]`)?.classList.add('active');

  activeUserId   = userId;
  activeUserName = userName;
  lastMsgId      = 0;
  lastDate       = null;

  // Update header
  document.getElementById('chatHeader').innerHTML = `
    <div class="chat-avatar" style="width:36px;height:36px;font-size:.8rem">
      ${userName.charAt(0).toUpperCase()}
    </div>
    <span class="chat-header-name">${userName}</span>
  `;

  // Clear messages
  const box = document.getElementById('chatMessages');
  box.innerHTML = '';

  // Show input
  document.getElementById('chatInputArea').style.display = 'flex';
  document.getElementById('msgInput').focus();

  // Hide badge
  const badge = document.getElementById(`badge-${userId}`);
  if (badge) badge.style.display = 'none';

  // Stop old poll, start fresh
  clearInterval(pollTimer);
  loadMessages();
  pollTimer = setInterval(pollMessages, 2000);
}

async function loadMessages() {
  const res  = await fetch(`/chat/${activeUserId}/messages`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  const msgs = await res.json();
  const box  = document.getElementById('chatMessages');
  box.innerHTML = '';
  lastDate = null;
  msgs.forEach(m => appendMessage(m));
  scrollBottom();
  if (msgs.length) lastMsgId = msgs[msgs.length - 1].id;
}

async function pollMessages() {
  if (!activeUserId) return;
  const res  = await fetch(`/chat/${activeUserId}/messages?after=${lastMsgId}`, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  });
  const msgs = await res.json();
  if (msgs.length) {
    const box      = document.getElementById('chatMessages');
    const atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 60;
    msgs.forEach(m => appendMessage(m));
    if (atBottom) scrollBottom();
    lastMsgId = msgs[msgs.length - 1].id;
    updateSidebarPreview(msgs[msgs.length - 1]);
  }
}

function appendMessage(msg) {
  const box = document.getElementById('chatMessages');

  // Date divider
  if (msg.date !== lastDate) {
    lastDate = msg.date;
    const div = document.createElement('div');
    div.className = 'chat-date-divider';
    div.textContent = msg.date;
    box.appendChild(div);
  }

  const row = document.createElement('div');
  row.className = 'msg-row' + (msg.mine ? ' mine' : '');
  row.innerHTML = `
    <div class="msg-bubble">${escHtml(msg.body)}</div>
    <span class="msg-time">${msg.time}</span>
  `;
  box.appendChild(row);
}

async function sendMessage() {
  const input = document.getElementById('msgInput');
  const body  = input.value.trim();
  if (!body || !activeUserId) return;

  const btn = document.getElementById('sendBtn');
  btn.disabled = true;
  input.value  = '';
  autoResize(input);

  try {
    const res = await fetch(`/chat/${activeUserId}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ body }),
    });
    const msg = await res.json();
    appendMessage(msg);
    scrollBottom();
    lastMsgId = msg.id;
    updateSidebarPreview(msg);
  } catch (e) {
    input.value = body;
  } finally {
    btn.disabled = false;
    input.focus();
  }
}

function updateSidebarPreview(msg) {
  const item = document.querySelector(`[data-user-id="${activeUserId}"] .chat-user-preview`);
  if (item) {
    item.textContent = (msg.mine ? 'Siz: ' : '') + msg.body.substring(0, 30);
  }
}

function handleKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendMessage();
  }
}

function autoResize(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

function scrollBottom() {
  const box = document.getElementById('chatMessages');
  box.scrollTop = box.scrollHeight;
}

function escHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
}

// Poll unread count for nav badge
function pollNavBadge() {
  fetch('/chat/unread-count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
      const badge = document.getElementById('navChatBadge');
      if (!badge) return;
      if (data.count > 0) {
        badge.textContent = data.count;
        badge.style.display = 'inline-flex';
      } else {
        badge.style.display = 'none';
      }
    });
}

// Keep nav badge updated
setInterval(pollNavBadge, 5000);
</script>
@endpush
