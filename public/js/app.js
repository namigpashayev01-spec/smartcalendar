// ====== CONFIG ======
const ROOT   = typeof ROOT_DATA !== "undefined" ? ROOT_DATA : {};
const ME     = { username: ROOT.user, name: ROOT.name, role: ROOT.role };
const LOGOUT = ROOT.logout;
const CSRF   = ROOT.csrf || document.querySelector('meta[name="csrf-token"]')?.content;

// ====== API ======
async function api(method, path, body = null, isFile = false) {
  const opts = {
    method,
    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    credentials: 'same-origin',
  };
  if (body && !isFile) {
    opts.headers['Content-Type'] = 'application/json';
    opts.body = JSON.stringify(body);
  } else if (body && isFile) {
    opts.body = body; // FormData
  }
  const res = await fetch('/api' + path, opts);
  if (res.status === 204) return null;
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw new Error(data?.message || data?.error || 'Xəta baş verdi');
  return data;
}

// ====== STATUSLAR ======
const STATUSES = {
  planned:   { label: 'Planlaşdırılıb',  color: '#5C6B8A', bg: '#EEF1F6' },
  progress:  { label: 'Hazırlanır',      color: '#B45309', bg: '#FEF3E2' },
  pending:   { label: 'Təsdiq gözləyir', color: '#7C3AED', bg: '#F3EAFE' },
  published: { label: 'Paylaşılıb',      color: '#2E844A', bg: '#E6F4EA' },
};
const DEFAULT_STATUS = 'planned';
const DEFAULT_COLOR  = '#0176D3';
const COLOR_PRESETS  = ['#0176D3','#2E844A','#B45309','#BA0517','#7C3AED','#0891B2','#DB2777','#475569'];

function statusInfo(key) { return STATUSES[key] || STATUSES[DEFAULT_STATUS]; }

// ====== SVG ICONS ======
const ICONS = {
  image:    `<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg>`,
  video:    `<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 8-6 4 6 4V8Z"/><rect x="2" y="6" width="14" height="12" rx="2"/></svg>`,
  text:     `<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>`,
  calendar: `<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>`,
  inbox:    `<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>`,
  user:     `<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
  plus:     `<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>`,
  download: `<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>`,
  close:    `<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>`,
};

// ====== HELPERS ======
function hexToRgba(hex, alpha) {
  const h = (hex || DEFAULT_COLOR).replace('#', '');
  const full = h.length === 3 ? h.split('').map(c => c + c).join('') : h;
  const r = parseInt(full.slice(0,2),16), g = parseInt(full.slice(2,4),16), b = parseInt(full.slice(4,6),16);
  return `rgba(${r},${g},${b},${alpha})`;
}
function escapeHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtDate(d) {
  return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}
function startOfToday() { const d = new Date(); d.setHours(0,0,0,0); return d; }

function mediaSummary(post) {
  const media = post.media || [];
  if (!media.length) return ICONS.text + ' Mətn';
  const imgs = media.filter(m => m.type === 'image').length;
  const vids = media.filter(m => m.type === 'video').length;
  const parts = [];
  if (imgs) parts.push(ICONS.image + ' ' + imgs);
  if (vids) parts.push(ICONS.video + ' ' + vids);
  return parts.join(' · ');
}

function showToast(msg, type = 'success') {
  let t = document.getElementById('sc-toast');
  if (!t) { t = document.createElement('div'); t.id = 'sc-toast'; document.body.appendChild(t); }
  t.textContent = msg;
  t.className = 'sc-toast ' + type;
  t.classList.add('show');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), 3000);
}

// ====== ELEMENTLƏR ======
const app        = document.getElementById('app');
const userBadge  = document.getElementById('user-badge');
const logoutBtn  = document.getElementById('logout-btn');

const views = {
  calendar: document.getElementById('view-calendar'),
  day:      document.getElementById('view-day'),
  report:   document.getElementById('view-report'),
  log:      document.getElementById('view-log'),
  add:      document.getElementById('view-add'),
  detail:   document.getElementById('view-detail'),
};

function showView(name) {
  Object.values(views).forEach(v => v.classList.add('hidden'));
  views[name]?.classList.remove('hidden');
}

// ====== TƏQVİM dəyişənləri (GİRİŞ-dən əvvəl elan edilməlidir) ======
const grid       = document.getElementById('calendar-grid');
const monthLabel = document.getElementById('month-label');
const datePicker = document.getElementById('date-picker');

const MONTHS     = ['Yanvar','Fevral','Mart','Aprel','May','İyun','İyul','Avqust','Sentyabr','Oktyabr','Noyabr','Dekabr'];
const DAYS       = ['Baz','B.e','Ç.a','Çər','C.a','Cüm','Şən'];
const MAX_CHIPS  = 3;
const expandedDays = new Set();

let current;
let postsCache = {};

// ====== GİRİŞ ======
if (ME.username) {
  userBadge.innerHTML = ICONS.user + ' ' + escapeHtml(ME.name);
  buildCalendar();
  showView('calendar');
}

logoutBtn?.addEventListener('click', () => {
  const form = document.createElement('form');
  form.method = 'POST'; form.action = LOGOUT;
  const t = document.createElement('input');
  t.type = 'hidden'; t.name = '_token'; t.value = CSRF;
  form.appendChild(t); document.body.appendChild(form); form.submit();
});

function buildCalendar() {
  const today = startOfToday();
  current = new Date(today.getFullYear(), today.getMonth(), 1);
  loadAndRender();
}

async function loadAndRender() {
  const month = `${current.getFullYear()}-${String(current.getMonth()+1).padStart(2,'0')}`;
  try {
    const posts = await api('GET', `/posts?month=${month}`);
    postsCache = {};
    (posts || []).forEach(p => {
      if (!postsCache[p.date]) postsCache[p.date] = [];
      postsCache[p.date].push(p);
    });
  } catch(e) { showToast('Məlumatlar yüklənmədi', 'error'); }
  renderMonth();
}

function postsByDate(dateStr) { return postsCache[dateStr] || []; }

function renderMonth() {
  grid.innerHTML = '';
  const today = startOfToday();
  const year  = current.getFullYear();
  const month = current.getMonth();
  const firstDow    = (new Date(year, month, 1).getDay() + 6) % 7;
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  for (let i = 0; i < firstDow; i++) {
    const e = document.createElement('div');
    e.className = 'day-card empty-cell';
    grid.appendChild(e);
  }

  for (let day = 1; day <= daysInMonth; day++) {
    const d       = new Date(year, month, day);
    const dateStr = fmtDate(d);
    const isToday = d.getTime() === today.getTime();
    const dow     = d.getDay();

    const card = document.createElement('div');
    card.className = 'day-card';
    card.dataset.date = dateStr;
    if (dow === 0 || dow === 6) card.classList.add('weekend');
    if (isToday) card.classList.add('today');

    const dayPosts     = postsByDate(dateStr);
    const expanded     = expandedDays.has(dateStr);
    const visiblePosts = expanded ? dayPosts : dayPosts.slice(0, MAX_CHIPS);

    let chips = visiblePosts.map(p => {
      const color = p.color || DEFAULT_COLOR;
      return `<button class="company-chip" data-post="${p.id}"
        style="background:${hexToRgba(color,.12)};border-color:${hexToRgba(color,.28)}">
        <span class="chip-dot" style="background:${color}"></span>
        <span class="chip-name">${escapeHtml(p.company)}</span>
      </button>`;
    }).join('');

    const hidden = dayPosts.length - visiblePosts.length;
    if (hidden > 0) {
      chips += `<button class="chip-more" data-expand="${dateStr}">+${hidden} daha</button>`;
    } else if (expanded && dayPosts.length > MAX_CHIPS) {
      chips += `<button class="chip-more" data-collapse="${dateStr}">↑ Yığ</button>`;
    }

    card.innerHTML = `
      <div class="day-head">
        <span class="day-num">${day}</span>
        ${isToday ? `<span class="day-today-tag">Bu gün</span>` : ''}
      </div>
      <div class="company-list">${chips}</div>`;
    grid.appendChild(card);
  }

  monthLabel.textContent = `${MONTHS[month]} ${year}`;
  datePicker.value = `${year}-${String(month+1).padStart(2,'0')}`;

  grid.querySelectorAll('.day-card[data-date]').forEach(el => {
    el.addEventListener('click', () => openDay(el.dataset.date));
  });
  grid.querySelectorAll('[data-post]').forEach(el => {
    el.addEventListener('click', e => { e.stopPropagation(); openDetail(el.dataset.post); });
  });
  grid.querySelectorAll('[data-expand]').forEach(el => {
    el.addEventListener('click', e => { e.stopPropagation(); expandedDays.add(el.dataset.expand); renderMonth(); });
  });
  grid.querySelectorAll('[data-collapse]').forEach(el => {
    el.addEventListener('click', e => { e.stopPropagation(); expandedDays.delete(el.dataset.collapse); renderMonth(); });
  });
}

// NAVİQASİYA
document.getElementById('prev-btn')?.addEventListener('click', () => {
  current = new Date(current.getFullYear(), current.getMonth()-1, 1);
  loadAndRender();
});
document.getElementById('next-btn')?.addEventListener('click', () => {
  current = new Date(current.getFullYear(), current.getMonth()+1, 1);
  loadAndRender();
});
document.getElementById('today-btn')?.addEventListener('click', buildCalendar);
document.getElementById('brand-home')?.addEventListener('click', () => showView('calendar'));
datePicker?.addEventListener('change', () => {
  if (!datePicker.value) return;
  const [y, m] = datePicker.value.split('-').map(Number);
  current = new Date(y, m-1, 1);
  loadAndRender();
});
document.querySelectorAll('[data-back]').forEach(btn => {
  btn.addEventListener('click', () => showView('calendar'));
});

// ====== GÜN GÖRÜNÜŞÜ ======
function openDay(dateStr) {
  const container = document.getElementById('day-content');
  const d = new Date(dateStr + 'T00:00:00');
  const dateLabel = `${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}, ${DAYS[d.getDay()]}`;
  const dayPosts  = postsByDate(dateStr);

  let listHtml;
  if (!dayPosts.length) {
    listHtml = `<div class="no-media">${ICONS.inbox} Bu gün üçün heç bir paylaşım yoxdur</div>`;
  } else {
    listHtml = `<div class="company-rows">` + dayPosts.map(p => {
      const color = p.color || DEFAULT_COLOR;
      const s     = statusInfo(p.status);
      return `<button class="company-row" data-post="${p.id}" style="border-left-color:${color}">
        <span class="row-dot" style="background:${color}"></span>
        <span class="row-name">${escapeHtml(p.company)}</span>
        <span class="row-media">${mediaSummary(p)}</span>
        <span class="status-badge" style="color:${s.color};background:${s.bg}">${s.label}</span>
      </button>`;
    }).join('') + `</div>`;
  }

  container.innerHTML = `
    <button class="back-link" id="day-back">‹ Geri</button>
    <div class="detail-head">
      <div>
        <div class="detail-company">${ICONS.calendar} ${d.getDate()} ${MONTHS[d.getMonth()]}</div>
        <div class="detail-date">${dateLabel} · ${dayPosts.length} paylaşım</div>
      </div>
      <div class="detail-actions">
        <button class="btn-primary inline" id="day-add">${ICONS.plus} Yeni paylaşım</button>
      </div>
    </div>
    ${listHtml}`;

  document.getElementById('day-back').addEventListener('click', () => showView('calendar'));
  document.getElementById('day-add').addEventListener('click', () => openAddForm(dateStr));
  container.querySelectorAll('[data-post]').forEach(el => {
    el.addEventListener('click', () => openDetail(el.dataset.post));
  });
  showView('day');
}

// ====== HESABAT ======
const reportEls = {
  company: document.getElementById('rf-company'),
  status:  document.getElementById('rf-status'),
  from:    document.getElementById('rf-from'),
  to:      document.getElementById('rf-to'),
  search:  document.getElementById('rf-search'),
  reset:   document.getElementById('rf-reset'),
  stats:   document.getElementById('report-stats'),
  table:   document.getElementById('report-table'),
  sub:     document.getElementById('report-sub'),
  csv:     document.getElementById('report-csv'),
};

let reportPosts = [];

document.getElementById('report-btn')?.addEventListener('click', openReport);

async function openReport() {
  try {
    const allPosts = await api('GET', '/posts');
    reportPosts = allPosts || [];
    const companies = [...new Set(reportPosts.map(p => p.company).filter(Boolean))].sort((a,b) => a.localeCompare(b,'az'));
    reportEls.company.innerHTML = `<option value="">Bütün şirkətlər</option>` +
      companies.map(c => `<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`).join('');
    reportEls.status.innerHTML = `<option value="">Bütün statuslar</option>` +
      Object.entries(STATUSES).map(([k,s]) => `<option value="${k}">${s.label}</option>`).join('');
    renderReport();
    showView('report');
  } catch(e) { showToast('Hesabat yüklənmədi', 'error'); }
}

function getFilteredPosts() {
  const company = reportEls.company.value;
  const status  = reportEls.status.value;
  const from    = reportEls.from.value;
  const to      = reportEls.to.value;
  const q       = reportEls.search.value.trim().toLowerCase();
  return reportPosts.filter(p => {
    if (company && p.company !== company) return false;
    if (status  && (p.status || DEFAULT_STATUS) !== status) return false;
    if (from    && p.date < from) return false;
    if (to      && p.date > to)   return false;
    if (q && !(`${p.company} ${p.description || ''}`).toLowerCase().includes(q)) return false;
    return true;
  }).sort((a,b) => a.date.localeCompare(b.date) || a.company.localeCompare(b.company,'az'));
}

function shortDate(dateStr) {
  const d = new Date(dateStr + 'T00:00:00');
  return `${String(d.getDate()).padStart(2,'0')} ${MONTHS[d.getMonth()].slice(0,3)} ${d.getFullYear()}`;
}

function renderReport() {
  const posts = getFilteredPosts();
  const counts = {};
  Object.keys(STATUSES).forEach(k => counts[k] = 0);
  posts.forEach(p => { const k = p.status || DEFAULT_STATUS; counts[k] = (counts[k]||0)+1; });

  reportEls.sub.textContent = `${posts.length} paylaşım tapıldı`;
  reportEls.stats.innerHTML =
    `<div class="stat-card total"><span class="stat-num">${posts.length}</span><span class="stat-lbl">Cəmi</span></div>` +
    Object.entries(STATUSES).map(([k,s]) =>
      `<div class="stat-card" style="background:${s.bg}">
        <span class="stat-num" style="color:${s.color}">${counts[k]}</span>
        <span class="stat-lbl" style="color:${s.color}">${s.label}</span>
      </div>`).join('');

  if (!posts.length) {
    reportEls.table.innerHTML = `<div class="no-media">${ICONS.inbox} Filtrlərə uyğun paylaşım tapılmadı</div>`;
    return;
  }

  const rows = posts.map(p => {
    const s     = statusInfo(p.status);
    const color = p.color || DEFAULT_COLOR;
    return `<tr data-post="${p.id}">
      <td class="c-date">${shortDate(p.date)}</td>
      <td class="c-company"><span class="row-dot sm" style="background:${color}"></span>${escapeHtml(p.company)}</td>
      <td><span class="status-badge" style="color:${s.color};background:${s.bg}">${s.label}</span></td>
      <td class="c-desc">${p.description ? escapeHtml(p.description) : '<span class="muted">—</span>'}</td>
      <td class="c-media">${(p.media||[]).length ? (p.media.length + ' fayl') : '<span class="muted">—</span>'}</td>
    </tr>`;
  }).join('');

  reportEls.table.innerHTML = `
    <table class="report-table">
      <thead><tr><th>Tarix</th><th>Şirkət</th><th>Status</th><th>Təsvir</th><th>Media</th></tr></thead>
      <tbody>${rows}</tbody>
    </table>`;
  reportEls.table.querySelectorAll('tr[data-post]').forEach(tr => {
    tr.addEventListener('click', () => openDetail(tr.dataset.post, () => { renderReport(); showView('report'); }));
  });
}

['company','status','from','to'].forEach(k => reportEls[k]?.addEventListener('change', renderReport));
reportEls.search?.addEventListener('input', renderReport);
reportEls.reset?.addEventListener('click', () => {
  ['company','status','from','to','search'].forEach(k => { if(reportEls[k]) reportEls[k].value = ''; });
  renderReport();
});

reportEls.csv?.addEventListener('click', () => {
  const posts = getFilteredPosts();
  if (!posts.length) { alert('İxrac üçün məlumat yoxdur.'); return; }
  const header = ['Tarix','Şirkət','Status','Təsvir','Media sayı'];
  const lines  = posts.map(p => [p.date, p.company, statusInfo(p.status).label, (p.description||'').replace(/\r?\n/g,' '), (p.media||[]).length]);
  const csv    = [header,...lines].map(r => r.map(v => { const s = String(v??''); return /[",\r\n]/.test(s)?`"${s.replace(/"/g,'""')}"`:s; }).join(',')).join('\r\n');
  const blob   = new Blob(['﻿'+csv], { type:'text/csv;charset=utf-8' });
  const url    = URL.createObjectURL(blob);
  const a      = Object.assign(document.createElement('a'), { href:url, download:`hesabat_${fmtDate(startOfToday())}.csv` });
  document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
});

// ====== JURNAL ======
const logEls = {
  user:   document.getElementById('lf-user'),
  action: document.getElementById('lf-action'),
  search: document.getElementById('lf-search'),
  reset:  document.getElementById('lf-reset'),
  table:  document.getElementById('log-table'),
  sub:    document.getElementById('log-sub'),
  clear:  document.getElementById('log-clear'),
};
const LOG_ACTIONS = { 'Yaratdı':'#2E844A','Redaktə etdi':'#0176D3','Sildi':'#BA0517','Daxil oldu':'#5C6B8A','Çıxış etdi':'#5C6B8A' };
let logsCache = [];

document.getElementById('log-btn')?.addEventListener('click', openLog);

async function openLog() {
  try {
    logsCache = await api('GET', '/logs') || [];
    const users   = [...new Set(logsCache.map(l => l.user))];
    const actions = [...new Set(logsCache.map(l => l.action))];
    logEls.user.innerHTML   = `<option value="">Bütün istifadəçilər</option>` + users.map(u => `<option value="${escapeHtml(u)}">${escapeHtml(u)}</option>`).join('');
    logEls.action.innerHTML = `<option value="">Bütün əməliyyatlar</option>` + actions.map(a => `<option value="${escapeHtml(a)}">${a}</option>`).join('');
    logEls.clear.style.display = ME.role === 'admin' ? '' : 'none';
    renderLog();
    showView('log');
  } catch(e) { showToast('Jurnal yüklənmədi', 'error'); }
}

function getFilteredLogs() {
  const u = logEls.user.value, a = logEls.action.value;
  const q = logEls.search.value.trim().toLowerCase();
  return logsCache.filter(l => {
    if (u && l.user !== u) return false;
    if (a && l.action !== a) return false;
    if (q && !(`${l.target} ${l.user_name} ${l.action}`).toLowerCase().includes(q)) return false;
    return true;
  });
}

function fmtTs(iso) {
  const d = new Date(iso);
  const p = n => String(n).padStart(2,'0');
  return `${p(d.getDate())}.${p(d.getMonth()+1)}.${d.getFullYear()} ${p(d.getHours())}:${p(d.getMinutes())}`;
}

function renderLog() {
  const logs = getFilteredLogs();
  logEls.sub.textContent = `${logs.length} qeyd`;
  if (!logs.length) { logEls.table.innerHTML = `<div class="no-media">${ICONS.inbox} Qeyd tapılmadı</div>`; return; }
  const rows = logs.map(l => {
    const color = LOG_ACTIONS[l.action] || '#5C6B8A';
    return `<tr>
      <td class="c-date">${fmtTs(l.ts)}</td>
      <td class="c-company">${ICONS.user} ${escapeHtml(l.user_name)}</td>
      <td><span class="log-action" style="color:${color};background:${hexToRgba(color,.12)}">${l.action}</span></td>
      <td class="c-desc">${l.target ? escapeHtml(l.target) : '<span class="muted">—</span>'}</td>
    </tr>`;
  }).join('');
  logEls.table.innerHTML = `<table class="report-table"><thead><tr><th>Vaxt</th><th>İstifadəçi</th><th>Əməliyyat</th><th>Obyekt</th></tr></thead><tbody>${rows}</tbody></table>`;
}

['user','action'].forEach(k => logEls[k]?.addEventListener('change', renderLog));
logEls.search?.addEventListener('input', renderLog);
logEls.reset?.addEventListener('click', () => { ['user','action','search'].forEach(k => { if(logEls[k]) logEls[k].value=''; }); renderLog(); });
logEls.clear?.addEventListener('click', async () => {
  if (!confirm('Bütün jurnal qeydləri silinsin?')) return;
  try {
    await api('DELETE', '/logs');
    showToast('Jurnal təmizləndi');
    openLog();
  } catch(e) { showToast(e.message, 'error'); }
});

// ====== FORM (YENİ / REDAKTƏ) ======
const postForm     = document.getElementById('post-form');
const addTitle     = document.getElementById('add-title');
const fId          = document.getElementById('post-id');
const fDate        = document.getElementById('post-date');
const fCompany     = document.getElementById('post-company');
const fDesc        = document.getElementById('post-desc');
const fStatus      = document.getElementById('post-status');
const colorSwatches   = document.getElementById('color-swatches');
const customColorInput = document.getElementById('post-color-custom');
const uploadBox    = document.getElementById('upload-box');
const fMedia       = document.getElementById('post-media');
const mediaGallery = document.getElementById('media-gallery');

let currentColor     = DEFAULT_COLOR;
let currentMediaList = []; // { id?, type, url?, file?, name }
let deletedMediaIds  = [];

fStatus.innerHTML = Object.entries(STATUSES).map(([k,s]) => `<option value="${k}">${s.label}</option>`).join('');
colorSwatches.innerHTML = COLOR_PRESETS.map(c => `<button type="button" class="swatch" data-color="${c}" style="background:${c}" title="${c}"></button>`).join('');

document.getElementById('add-btn')?.addEventListener('click', () => openAddForm(fmtDate(startOfToday())));

function setColor(hex) {
  currentColor = hex;
  colorSwatches.querySelectorAll('.swatch').forEach(sw => sw.classList.toggle('selected', sw.dataset.color.toLowerCase() === hex.toLowerCase()));
  customColorInput.value = hex;
}
colorSwatches.querySelectorAll('.swatch').forEach(sw => sw.addEventListener('click', () => setColor(sw.dataset.color)));
customColorInput?.addEventListener('input', () => setColor(customColorInput.value));

function openAddForm(dateStr, post = null) {
  postForm.reset();
  currentMediaList = [];
  deletedMediaIds  = [];

  if (post) {
    addTitle.textContent = 'Paylaşımı redaktə et';
    fId.value      = post.id;
    fDate.value    = post.date;
    fCompany.value = post.company;
    fDesc.value    = post.description || '';
    fStatus.value  = post.status || DEFAULT_STATUS;
    setColor(post.color || DEFAULT_COLOR);
    currentMediaList = (post.media || []).map(m => ({ id: m.id, type: m.type, url: m.url, name: m.original_name }));
  } else {
    addTitle.textContent = 'Yeni paylaşım';
    fId.value   = '';
    fDate.value = dateStr || fmtDate(startOfToday());
    fStatus.value = DEFAULT_STATUS;
    setColor(DEFAULT_COLOR);
  }
  renderMediaGallery();
  showView('add');
}

uploadBox?.addEventListener('click', () => fMedia.click());
fMedia?.addEventListener('change', () => { handleFiles(fMedia.files); fMedia.value = ''; });
['dragover','dragenter'].forEach(ev => uploadBox?.addEventListener(ev, e => { e.preventDefault(); uploadBox.classList.add('dragover'); }));
['dragleave','drop'].forEach(ev => uploadBox?.addEventListener(ev, e => { e.preventDefault(); uploadBox.classList.remove('dragover'); }));
uploadBox?.addEventListener('drop', e => { if (e.dataTransfer.files?.length) handleFiles(e.dataTransfer.files); });

function handleFiles(fileList) {
  Array.from(fileList || []).forEach(file => {
    const isImage = file.type.startsWith('image/');
    const isVideo = file.type.startsWith('video/');
    if (!isImage && !isVideo) { alert(`"${file.name}" şəkil və ya video deyil.`); return; }
    if (isVideo && file.size > 50 * 1024 * 1024) { alert(`"${file.name}" çox böyükdür (maks 50MB).`); return; }
    const url = URL.createObjectURL(file);
    currentMediaList.push({ type: isImage ? 'image' : 'video', url, file, name: file.name });
    renderMediaGallery();
  });
}

function renderMediaGallery() {
  if (!currentMediaList.length) {
    mediaGallery.classList.add('hidden');
    mediaGallery.innerHTML = '';
    return;
  }
  mediaGallery.classList.remove('hidden');
  mediaGallery.innerHTML = currentMediaList.map((m, i) => {
    const thumb = m.type === 'video' ? `<video src="${m.url}" muted></video>` : `<img src="${m.url}" alt="media ${i+1}" />`;
    return `<div class="media-item">
      ${thumb}
      <span class="media-type-tag">${m.type === 'video' ? ICONS.video : ICONS.image}</span>
      <button type="button" class="media-remove" data-idx="${i}" title="Sil">${ICONS.close}</button>
    </div>`;
  }).join('');
  mediaGallery.querySelectorAll('.media-remove').forEach(btn => {
    btn.addEventListener('click', () => {
      const idx = Number(btn.dataset.idx);
      const m   = currentMediaList[idx];
      if (m.id) deletedMediaIds.push(m.id);
      if (m.file) URL.revokeObjectURL(m.url);
      currentMediaList.splice(idx, 1);
      renderMediaGallery();
    });
  });
}

postForm?.addEventListener('submit', async e => {
  e.preventDefault();
  const btn = postForm.querySelector('[type=submit]');
  btn.disabled = true;

  try {
    const payload = {
      date:        fDate.value,
      company:     fCompany.value.trim(),
      description: fDesc.value.trim(),
      color:       currentColor,
      status:      fStatus.value,
    };

    let post;
    const existingId = fId.value;

    if (existingId) {
      post = await api('PUT', `/posts/${existingId}`, payload);
    } else {
      post = await api('POST', '/posts', payload);
    }

    // Silinəcək media-ları sil
    for (const mid of deletedMediaIds) {
      await api('DELETE', `/media/${mid}`).catch(() => {});
    }

    // Yeni faylları yüklə
    const newFiles = currentMediaList.filter(m => m.file);
    if (newFiles.length) {
      const fd = new FormData();
      newFiles.forEach(m => fd.append('files[]', m.file));
      await api('POST', `/posts/${post.id}/media`, fd, true);
    }

    showToast(existingId ? 'Yeniləndi' : 'Yaradıldı');

    // Cache-i yenilə
    const updated = await api('GET', `/posts/${post.id}`);
    const dateStr = updated.date;
    if (!postsCache[dateStr]) postsCache[dateStr] = [];
    if (existingId) {
      const idx = postsCache[dateStr].findIndex(p => String(p.id) === String(existingId));
      if (idx !== -1) postsCache[dateStr][idx] = updated;
      else postsCache[dateStr].push(updated);
    } else {
      postsCache[dateStr].push(updated);
    }

    renderMonth();
    openDay(dateStr);
  } catch(err) {
    showToast(err.message || 'Xəta baş verdi', 'error');
  } finally {
    btn.disabled = false;
  }
});

// ====== DETAL ======
async function openDetail(id, onBack) {
  let post = Object.values(postsCache).flat().find(p => String(p.id) === String(id));
  if (!post) {
    try { post = await api('GET', `/posts/${id}`); } catch { return; }
  }

  const container = document.getElementById('detail-content');
  const goBack    = onBack || (() => openDay(post.date));
  const d         = new Date(post.date + 'T00:00:00');
  const dateLabel = `${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}, ${DAYS[d.getDay()]}`;
  const mediaList = post.media || [];
  const color     = post.color || DEFAULT_COLOR;
  const s         = statusInfo(post.status);

  let mediaHtml;
  if (mediaList.length) {
    const items = mediaList.map((m, i) => {
      const view = m.type === 'video'
        ? `<video src="${m.url}" controls></video>`
        : `<img src="${m.url}" alt="${escapeHtml(post.company)} ${i+1}" />`;
      return `<figure class="detail-media">
        ${view}
        <figcaption class="detail-media-bar">
          <span class="detail-media-meta">${m.type === 'video' ? ICONS.video : ICONS.image} ${m.type === 'video' ? 'Video' : 'Şəkil'} ${i+1}</span>
          <a class="ghost-btn small dl-btn" href="${m.url}" download="${escapeHtml(m.original_name||'media')}">${ICONS.download} Yüklə</a>
        </figcaption>
      </figure>`;
    }).join('');
    mediaHtml = `<div class="detail-section-title">Media (${mediaList.length})</div><div class="detail-gallery">${items}</div>`;
  } else {
    mediaHtml = `<div class="no-media">${ICONS.inbox} Bu paylaşım üçün şəkil/video əlavə edilməyib</div>`;
  }

  container.innerHTML = `
    <button class="back-link" id="detail-back">‹ Geri</button>
    <div class="detail-head">
      <div>
        <div class="detail-company"><span class="detail-dot" style="background:${color}"></span>${escapeHtml(post.company)}</div>
        <div class="detail-date">${ICONS.calendar} ${dateLabel}</div>
        <span class="status-badge" style="color:${s.color};background:${s.bg};margin-top:10px">${s.label}</span>
      </div>
      <div class="detail-actions">
        <button class="ghost-btn" id="detail-edit">Redaktə</button>
        <button class="btn-danger" id="detail-delete">Sil</button>
      </div>
    </div>
    ${mediaHtml}
    <div class="detail-section-title">Paylaşılacaq mətn</div>
    ${post.description ? `<div class="detail-text">${escapeHtml(post.description)}</div>` : `<div class="detail-text detail-empty-text">Mətn əlavə edilməyib</div>`}`;

  document.getElementById('detail-back').addEventListener('click', goBack);
  document.getElementById('detail-edit').addEventListener('click', () => openAddForm(post.date, post));
  document.getElementById('detail-delete').addEventListener('click', async () => {
    if (!confirm(`"${post.company}" paylaşımını silmək istəyirsiniz?`)) return;
    try {
      await api('DELETE', `/posts/${post.id}`);
      if (postsCache[post.date]) {
        postsCache[post.date] = postsCache[post.date].filter(p => String(p.id) !== String(post.id));
      }
      showToast('Silindi');
      renderMonth();
      goBack();
    } catch(err) { showToast(err.message, 'error'); }
  });

  showView('detail');
}
