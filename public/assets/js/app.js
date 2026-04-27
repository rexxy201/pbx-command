// ────────────── Utilities ──────────────
const BASE = (document.querySelector('meta[name="base-path"]') || {content:''}).content;

function api(path, opts={}) {
  return fetch(BASE + '/pbx-api' + path, {
    headers: {'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest'},
    credentials: 'same-origin',
    ...opts
  }).then(async r => {
    const data = await r.json().catch(() => ({}));
    if (!r.ok) throw new Error(data.error || 'Request failed');
    return data;
  });
}

function toast(msg, type='success') {
  const c = document.getElementById('toast-container') || (() => {
    const el = document.createElement('div');
    el.id = 'toast-container';
    document.body.appendChild(el);
    return el;
  })();
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.textContent = msg;
  c.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

function formatDuration(sec) {
  if (!sec || sec <= 0) return '-';
  const m = Math.floor(sec / 60), s = sec % 60;
  return m ? `${m}m ${s}s` : `${s}s`;
}

function fmtDate(dt) {
  if (!dt) return '-';
  return new Date(dt).toLocaleString('en-GB', {day:'numeric',month:'short',hour:'2-digit',minute:'2-digit',second:'2-digit'});
}

// ────────────── Bootstrap Modal helpers ──────────────
function openModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  bootstrap.Modal.getOrCreateInstance(el).show();
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  const m = bootstrap.Modal.getInstance(el);
  if (m) m.hide();
}

// Keep data-close-modal attribute support
document.addEventListener('click', e => {
  const closer = e.target.closest('[data-close-modal]');
  if (closer) { closeModal(closer.dataset.closeModal); return; }
  const opener = e.target.closest('[data-open-modal]');
  if (opener) { openModal(opener.dataset.openModal); }
});

// ────────────── Confirm dialog ──────────────
let _confirmResolve = null;

function confirm(msg, btnLabel='Delete') {
  return new Promise(resolve => {
    _confirmResolve = resolve;
    const d = document.getElementById('confirm-modal');
    if (!d) { resolve(window.confirm(msg)); return; }
    d.querySelector('.confirm-desc').textContent = msg;
    d.querySelector('#confirm-ok').textContent = btnLabel;
    openModal('confirm-modal');
  });
}

document.addEventListener('click', e => {
  if (e.target.id === 'confirm-ok' && _confirmResolve) {
    closeModal('confirm-modal');
    const res = _confirmResolve;
    _confirmResolve = null;
    res(true);
  }
  if (e.target.id === 'confirm-cancel' && _confirmResolve) {
    closeModal('confirm-modal');
    const res = _confirmResolve;
    _confirmResolve = null;
    res(false);
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const cm = document.getElementById('confirm-modal');
  if (cm) {
    cm.addEventListener('hidden.bs.modal', () => {
      if (_confirmResolve) { _confirmResolve(false); _confirmResolve = null; }
    });
  }
});

// ────────────── Chart.js theme defaults ──────────────
if (typeof Chart !== 'undefined') {
  Chart.defaults.color = '#64748b';
  Chart.defaults.borderColor = '#1e2535';
  Chart.defaults.font.family = "-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
  Chart.defaults.font.size = 12;
}

// ────────────── Live Monitor polling ──────────────
let _pollTimer = null;
function startLivePolling(callback, interval=10000) {
  callback();
  _pollTimer = setInterval(callback, interval);
}
function stopLivePolling() {
  if (_pollTimer) clearInterval(_pollTimer);
}
