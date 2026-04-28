<?php
require_once dirname(dirname(__DIR__)) . '/src/Settings.php';

render_layout('WhatsApp Inbox', 'whatsapp', function() use ($user) {
    $agents      = Database::query("SELECT name FROM pbx_users WHERE is_active ORDER BY name");
    $currentUser = $user['name'] ?? '';
    $currentRole = $user['role'] ?? 'employee';
?>

<style>
/* ── WhatsApp Inbox layout ─────────────────────────────────────────── */
.wa-inbox {
  display: flex;
  height: calc(100vh - 110px);
  min-height: 480px;
  background: var(--card-bg, #0d1526);
  border: 1px solid var(--card-border, #1a2540);
  border-radius: 8px;
  overflow: hidden;
}

/* Conversation list */
.wa-sidebar {
  width: 300px;
  min-width: 260px;
  flex-shrink: 0;
  border-right: 1px solid var(--card-border, #1a2540);
  display: flex;
  flex-direction: column;
}
.wa-sidebar-header {
  padding: 10px 14px;
  border-bottom: 1px solid var(--card-border, #1a2540);
  display: flex;
  align-items: center;
  gap: 8px;
}
.wa-sidebar-header h6 { margin:0; font-size:.85rem; font-weight:600; }
.wa-conv-search {
  padding: 8px 12px;
  border-bottom: 1px solid var(--card-border, #1a2540);
}
.wa-conv-list { flex:1; overflow-y:auto; }
.wa-conv-item {
  padding: 10px 14px;
  cursor: pointer;
  border-bottom: 1px solid var(--table-border, #111827);
  transition: background 0.1s;
  position: relative;
  border-left: 3px solid transparent;
}
.wa-conv-item:hover { background: var(--nav-hover-bg, #111827); }
.wa-conv-item.active { background: var(--nav-active-bg, #1e3a5f); }
.wa-conv-item.mine   { border-left-color: #22c55e; }
.wa-conv-item.theirs { border-left-color: #38bdf8; }
.wa-conv-item.unassigned { border-left-color: #f59e0b; }
.wa-conv-name    { font-size:.84rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.wa-conv-preview { font-size:.75rem; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px; }
.wa-conv-meta    { display:flex; align-items:center; justify-content:space-between; margin-top:3px; gap:4px; flex-wrap:wrap; }
.wa-conv-time    { font-size:.7rem; color:#475569; }
.wa-badge        { background:#22c55e; color:#fff; border-radius:99px; font-size:.65rem; font-weight:700; padding:1px 6px; min-width:18px; text-align:center; flex-shrink:0; }
.wa-assign-dot   { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.wa-assign-dot.mine      { background:#22c55e; }
.wa-assign-dot.theirs    { background:#38bdf8; }
.wa-assign-dot.unassigned { background:#f59e0b; }

/* Queue tabs */
.wa-queue-tabs {
  display: flex;
  gap: 0;
  border-bottom: 1px solid var(--card-border, #1a2540);
}
.wa-queue-tab {
  flex: 1;
  padding: 6px 4px;
  font-size: .72rem;
  font-weight: 500;
  text-align: center;
  background: none;
  border: none;
  color: var(--nav-color, #94a3b8);
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: color 0.15s, border-color 0.15s;
}
.wa-queue-tab:hover { color: var(--nav-hover-color, #e2e8f0); }
.wa-queue-tab.active { color: var(--accent, #3b82f6); border-bottom-color: var(--accent, #3b82f6); }
.wa-queue-tab .wa-tab-count {
  display: inline-block;
  background: var(--nav-active-bg, #1e3a5f);
  color: var(--accent, #60a5fa);
  border-radius: 99px;
  padding: 0 5px;
  font-size: .65rem;
  margin-left: 3px;
  min-width: 16px;
  text-align: center;
}
.wa-queue-tab.active .wa-tab-count { background: var(--accent, #3b82f6); color: #fff; }

/* Chat area */
.wa-chat { flex:1; display:flex; flex-direction:column; min-width:0; }
.wa-chat-header {
  padding: 10px 14px;
  border-bottom: 1px solid var(--card-border, #1a2540);
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.wa-chat-name { font-size:.9rem; font-weight:600; }
.wa-chat-sub  { font-size:.75rem; color:#64748b; }
.wa-messages  { flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:8px; }
.wa-empty     { flex:1; display:flex; align-items:center; justify-content:center; color:#475569; font-size:.85rem; flex-direction:column; gap:8px; }

/* Bubbles */
.wa-bubble-wrap { display:flex; }
.wa-bubble-wrap.out { justify-content:flex-end; }
.wa-bubble {
  max-width: 70%;
  padding: 8px 12px;
  border-radius: 10px;
  font-size: .83rem;
  line-height: 1.45;
  word-break: break-word;
}
.wa-bubble.in  { background: var(--modal-bg, #1e2535); border-bottom-left-radius:2px; }
.wa-bubble.out { background:#1a4731; color:#d1fae5; border-bottom-right-radius:2px; }
.wa-bubble-time { font-size:.65rem; opacity:.5; margin-top:4px; }
.wa-bubble .wa-sender { font-size:.7rem; color:#64748b; margin-bottom:2px; }

/* Reply area */
.wa-reply {
  padding: 10px 14px;
  border-top: 1px solid var(--card-border, #1a2540);
  display: flex;
  gap: 8px;
  align-items: flex-end;
}
.wa-reply textarea {
  flex: 1;
  resize: none;
  min-height: 38px;
  max-height: 120px;
  border-radius: 6px;
  font-size: .84rem;
  line-height: 1.4;
}
.wa-status-badge { display:inline-block; width:8px; height:8px; border-radius:50%; }
.wa-status-badge.sent      { background:#64748b; }
.wa-status-badge.delivered { background:#38bdf8; }
.wa-status-badge.read      { background:#22c55e; }

/* Claim/takeover banner */
.wa-claim-banner {
  padding: 8px 14px;
  background: rgba(245,158,11,.1);
  border-bottom: 1px solid rgba(245,158,11,.2);
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: .8rem;
  color: #f59e0b;
  flex-shrink: 0;
}
</style>

<div class="page-header">
  <div>
    <h1><i class="bi bi-whatsapp text-success me-2"></i>WhatsApp Inbox</h1>
    <p class="page-subtitle">Shared team inbox — claim, reply and hand off customer conversations.</p>
  </div>
  <div class="page-header-actions">
    <select id="wa-status-filter" class="form-select form-select-sm" style="width:130px">
      <option value="">All statuses</option>
      <option value="open">Open</option>
      <option value="pending">Pending</option>
      <option value="closed">Closed</option>
    </select>
    <button class="btn btn-outline-secondary btn-sm" onclick="loadConversations()">
      <i class="bi bi-arrow-clockwise"></i>
    </button>
  </div>
</div>

<div class="wa-inbox">
  <!-- Conversation list -->
  <div class="wa-sidebar">
    <div class="wa-sidebar-header">
      <i class="bi bi-whatsapp text-success" style="font-size:1.1rem"></i>
      <h6>Conversations</h6>
      <span class="badge bg-secondary ms-auto" id="wa-conv-count">0</span>
    </div>

    <!-- Queue tabs -->
    <div class="wa-queue-tabs">
      <button class="wa-queue-tab active" data-queue="all">
        All <span class="wa-tab-count" id="tab-count-all">0</span>
      </button>
      <button class="wa-queue-tab" data-queue="mine">
        My Queue <span class="wa-tab-count" id="tab-count-mine">0</span>
      </button>
      <button class="wa-queue-tab" data-queue="unassigned">
        Unassigned <span class="wa-tab-count" id="tab-count-unassigned">0</span>
      </button>
    </div>

    <div class="wa-conv-search">
      <input type="text" class="form-control form-control-sm" id="wa-search" placeholder="Search contacts...">
    </div>
    <div class="wa-conv-list" id="wa-conv-list">
      <div class="text-center text-muted p-4 small">Loading conversations...</div>
    </div>
  </div>

  <!-- Chat area -->
  <div class="wa-chat" id="wa-chat-area">
    <div class="wa-empty">
      <i class="bi bi-chat-left-dots" style="font-size:2.5rem; opacity:.15"></i>
      <span>Select a conversation to start</span>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../_confirm-modal.php'; ?>

<!-- Assign modal (admin/manager only) -->
<div class="modal fade" id="assign-modal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reassign Conversation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Assign to Agent</label>
        <select class="form-select form-select-sm" id="assign-agent">
          <option value="">— Unassigned —</option>
          <?php foreach ($agents as $a): ?>
          <option value="<?= h($a['name']) ?>"><?= h($a['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="assign-save">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
let convs = [], activeConvId = null;
let activeQueue = 'all';
const CURRENT_USER = <?= json_encode($currentUser) ?>;
const CURRENT_ROLE = <?= json_encode($currentRole) ?>;

function fmtConvTime(ts) {
  if (!ts) return '';
  const d = new Date(ts), now = new Date();
  if (d.toDateString() === now.toDateString()) return d.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
  return d.toLocaleDateString('en-GB',{day:'numeric',month:'short'});
}
function h2(s) { const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }

// ── Queue tabs ─────────────────────────────────────────────────────────────
document.querySelectorAll('.wa-queue-tab').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.wa-queue-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeQueue = btn.dataset.queue;
    renderConvList();
  });
});

// ── Load conversation list ─────────────────────────────────────────────────
async function loadConversations() {
  try {
    convs = await api('/whatsapp-conversations');
    renderConvList();
    if (activeConvId) {
      const still = convs.find(c => c.id == activeConvId);
      if (still) renderChatHeader(still);
    }
  } catch(e) { console.error(e); }
}

function assignmentClass(conv) {
  if (!conv.assigned_agent)                          return 'unassigned';
  if (conv.assigned_agent === CURRENT_USER)          return 'mine';
  return 'theirs';
}

function renderConvList() {
  const q      = document.getElementById('wa-search').value.toLowerCase();
  const status = document.getElementById('wa-status-filter').value;

  const all        = convs.filter(c => !status || c.status === status);
  const mine       = all.filter(c => c.assigned_agent === CURRENT_USER);
  const unassigned = all.filter(c => !c.assigned_agent);

  document.getElementById('tab-count-all').textContent        = all.length;
  document.getElementById('tab-count-mine').textContent       = mine.length;
  document.getElementById('tab-count-unassigned').textContent = unassigned.length;

  let list = activeQueue === 'mine'       ? mine
           : activeQueue === 'unassigned' ? unassigned
           : all;

  if (q) list = list.filter(c =>
    (c.contact_number||'').includes(q) || (c.contact_name||'').toLowerCase().includes(q)
  );

  document.getElementById('wa-conv-count').textContent = list.length;

  const el = document.getElementById('wa-conv-list');
  if (!list.length) {
    el.innerHTML = '<div class="text-center text-muted p-4 small">No conversations</div>';
    return;
  }

  el.innerHTML = list.map(c => {
    const ac  = assignmentClass(c);
    const agentLabel = c.assigned_agent
      ? (c.assigned_agent === CURRENT_USER ? 'You' : h2(c.assigned_agent))
      : '<span style="color:#f59e0b">Unassigned</span>';
    return `<div class="wa-conv-item ${ac} ${c.id == activeConvId ? 'active' : ''}" onclick="openConv(${c.id})">
      <div class="d-flex align-items-center gap-2">
        <span class="wa-assign-dot ${ac}"></span>
        <div style="flex:1;min-width:0">
          <div class="wa-conv-name">${c.contact_name ? h2(c.contact_name) : h2(c.contact_number)}</div>
          <div class="wa-conv-preview">${c.last_message_body ? h2(c.last_message_body) : '<em class="text-muted">No messages</em>'}</div>
        </div>
        ${c.unread_count > 0 ? `<span class="wa-badge">${c.unread_count}</span>` : ''}
      </div>
      <div class="wa-conv-meta">
        <span class="wa-conv-time">${fmtConvTime(c.last_message_at)}</span>
        <span style="font-size:.68rem;color:#64748b">${agentLabel}</span>
        <span class="badge ${c.status==='open'?'bg-success':c.status==='pending'?'bg-warning text-dark':'bg-secondary'}" style="font-size:.6rem">${c.status}</span>
      </div>
    </div>`;
  }).join('');
}

// ── Open a conversation ────────────────────────────────────────────────────
async function openConv(id) {
  activeConvId = id;
  renderConvList();
  const conv = convs.find(c => c.id == id);
  if (!conv) return;
  renderChatHeader(conv);
  await loadMessages(id);
  // Mark unread as read
  if (conv.unread_count > 0) {
    api('/whatsapp-conversations?id=' + id, { method:'PUT', body: JSON.stringify({ unreadCount: 0 }) }).catch(()=>{});
    conv.unread_count = 0;
    renderConvList();
  }
}

function renderChatHeader(conv) {
  const area = document.getElementById('wa-chat-area');
  const ac   = assignmentClass(conv);
  const isMine    = ac === 'mine';
  const isTheirs  = ac === 'theirs';
  const isUnassigned = ac === 'unassigned';
  const isClosed  = conv.status === 'closed';
  const canAdmin  = CURRENT_ROLE === 'admin' || CURRENT_ROLE === 'manager';

  // Primary action button
  let primaryBtn = '';
  if (!isClosed) {
    if (isUnassigned) {
      primaryBtn = `<button class="btn btn-success btn-sm" onclick="claimConv(${conv.id})">
        <i class="bi bi-hand-index me-1"></i>Claim
      </button>`;
    } else if (isMine) {
      primaryBtn = `<button class="btn btn-outline-secondary btn-sm" onclick="releaseConv(${conv.id})" title="Put back in unassigned pool">
        <i class="bi bi-arrow-left-circle me-1"></i>Release
      </button>`;
    } else if (isTheirs) {
      primaryBtn = `<button class="btn btn-warning btn-sm text-dark" onclick="takeOverConv(${conv.id})">
        <i class="bi bi-lightning-charge me-1"></i>Take Over
      </button>`;
    }
  }

  // Admin/manager can always reassign via modal
  const reassignBtn = canAdmin
    ? `<button class="btn btn-outline-secondary btn-sm" onclick="openAssign(${conv.id})" title="Reassign to specific agent">
        <i class="bi bi-person-check"></i>
       </button>`
    : '';

  const statusBtn = `<button class="btn btn-outline-${isClosed?'success':'secondary'} btn-sm" onclick="toggleStatus(${conv.id})">
    ${isClosed ? '<i class="bi bi-envelope-open me-1"></i>Reopen' : '<i class="bi bi-check2-all me-1"></i>Close'}
  </button>`;

  const agentInfo = conv.assigned_agent
    ? `<span class="badge bg-secondary ms-1" style="font-size:.7rem">
        <i class="bi bi-headset me-1"></i>${h2(conv.assigned_agent)}${isMine ? ' (you)' : ''}
       </span>`
    : `<span class="badge ms-1" style="background:#f59e0b22;color:#f59e0b;font-size:.7rem">
        <i class="bi bi-inbox me-1"></i>Unassigned
       </span>`;

  area.innerHTML = `
    <div class="wa-chat-header">
      <div style="flex:1;min-width:0">
        <div class="wa-chat-name">${h2(conv.contact_name || conv.contact_number)}</div>
        <div class="wa-chat-sub d-flex align-items-center flex-wrap gap-1">
          ${h2(conv.contact_number)}
          ${agentInfo}
        </div>
      </div>
      <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-end">
        ${primaryBtn}
        ${reassignBtn}
        ${statusBtn}
      </div>
    </div>
    <div class="wa-messages" id="wa-msgs-${conv.id}">
      <div class="text-center text-muted small">Loading messages...</div>
    </div>
    <div class="wa-reply">
      <textarea class="form-control form-control-sm" id="wa-reply-input" rows="1"
        placeholder="${isClosed ? 'Conversation closed' : isUnassigned ? 'Claim this conversation to reply…' : 'Type a message… (Enter to send, Shift+Enter for new line)'}"
        ${isClosed ? 'disabled' : ''}></textarea>
      <button class="btn btn-success btn-sm" id="wa-send-btn" onclick="sendMessage(${conv.id})" ${isClosed ? 'disabled' : ''}>
        <i class="bi bi-send-fill"></i>
      </button>
    </div>
  `;

  const ta = document.getElementById('wa-reply-input');
  if (ta && !isClosed) {
    ta.addEventListener('input', () => { ta.style.height='auto'; ta.style.height=Math.min(ta.scrollHeight,120)+'px'; });
    ta.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(conv.id); }
    });
  }
}

// ── Messages ───────────────────────────────────────────────────────────────
async function loadMessages(convId) {
  try {
    const msgs = await api('/whatsapp-messages?conversation_id=' + convId);
    const el = document.getElementById('wa-msgs-' + convId);
    if (!el) return;
    if (!msgs.length) {
      el.innerHTML = '<div class="text-center text-muted small py-4">No messages yet</div>';
      return;
    }
    el.innerHTML = msgs.map(m => {
      const isOut = m.direction === 'outbound';
      const statusIcon = isOut ? `<span class="wa-status-badge ${m.status}" title="${m.status}"></span>` : '';
      return `<div class="wa-bubble-wrap ${isOut ? 'out' : ''}">
        <div class="wa-bubble ${isOut ? 'out' : 'in'}">
          ${isOut && m.sender_name ? `<div class="wa-sender">${h2(m.sender_name)}</div>` : ''}
          ${!isOut && m.sender_name ? `<div class="wa-sender">${h2(m.sender_name)}</div>` : ''}
          ${h2(m.body || '')}
          <div class="wa-bubble-time d-flex align-items-center gap-1 justify-content-end">
            ${new Date(m.created_at).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'})}
            ${statusIcon}
          </div>
        </div>
      </div>`;
    }).join('');
    el.scrollTop = el.scrollHeight;
  } catch(e) { console.error(e); }
}

// ── Send message ───────────────────────────────────────────────────────────
async function sendMessage(convId) {
  const conv  = convs.find(c => c.id == convId);
  if (!conv || conv.status === 'closed') return;

  const input = document.getElementById('wa-reply-input');
  const body  = input?.value.trim();
  if (!body) return;

  // Auto-claim if unassigned
  if (!conv.assigned_agent) {
    await claimConv(convId, true); // silent=true
  }

  const btn = document.getElementById('wa-send-btn');
  if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>'; }
  try {
    await api('/whatsapp-messages?conversation_id=' + convId, {
      method: 'POST',
      body: JSON.stringify({ body, conversationId: convId, senderName: CURRENT_USER })
    });
    if (input) { input.value = ''; input.style.height = 'auto'; }
    const el = document.getElementById('wa-msgs-' + convId);
    if (el) {
      const div = document.createElement('div');
      div.className = 'wa-bubble-wrap out';
      div.innerHTML = `<div class="wa-bubble out">
        <div class="wa-sender">${h2(CURRENT_USER)}</div>
        ${h2(body)}
        <div class="wa-bubble-time">just now <span class="wa-status-badge sent"></span></div>
      </div>`;
      el.appendChild(div);
      el.scrollTop = el.scrollHeight;
    }
  } catch(e) { toast(e.message, 'error'); }
  if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-send-fill"></i>'; }
}

// ── Assignment actions ─────────────────────────────────────────────────────
async function claimConv(convId, silent = false) {
  try {
    await api('/whatsapp-conversations?id=' + convId, {
      method: 'PUT', body: JSON.stringify({ assignedAgent: CURRENT_USER })
    });
    const c = convs.find(x => x.id == convId);
    if (c) { c.assigned_agent = CURRENT_USER; renderConvList(); renderChatHeader(c); }
    if (!silent) toast('Conversation claimed');
  } catch(e) { if (!silent) toast(e.message, 'error'); }
}

async function releaseConv(convId) {
  if (!await confirm('Release this conversation back to the unassigned pool?', 'Release')) return;
  try {
    await api('/whatsapp-conversations?id=' + convId, {
      method: 'PUT', body: JSON.stringify({ assignedAgent: '' })
    });
    const c = convs.find(x => x.id == convId);
    if (c) { c.assigned_agent = ''; renderConvList(); renderChatHeader(c); }
    toast('Released to unassigned pool');
  } catch(e) { toast(e.message, 'error'); }
}

async function takeOverConv(convId) {
  const c = convs.find(x => x.id == convId);
  const prev = c?.assigned_agent || 'the current agent';
  if (!await confirm(`Take over from ${prev}?`, 'Take Over')) return;
  try {
    await api('/whatsapp-conversations?id=' + convId, {
      method: 'PUT', body: JSON.stringify({ assignedAgent: CURRENT_USER })
    });
    if (c) { c.assigned_agent = CURRENT_USER; renderConvList(); renderChatHeader(c); }
    toast('Conversation taken over');
  } catch(e) { toast(e.message, 'error'); }
}

async function toggleStatus(convId) {
  const conv = convs.find(c => c.id == convId);
  if (!conv) return;
  const newStatus = conv.status === 'closed' ? 'open' : 'closed';
  try {
    await api('/whatsapp-conversations?id=' + convId, { method:'PUT', body: JSON.stringify({ status: newStatus }) });
    conv.status = newStatus;
    renderConvList();
    renderChatHeader(conv);
    toast(`Conversation ${newStatus}`);
  } catch(e) { toast(e.message, 'error'); }
}

// ── Reassign modal (admin / manager) ──────────────────────────────────────
function openAssign(convId) {
  const conv = convs.find(c => c.id == convId);
  const sel  = document.getElementById('assign-agent');
  sel.value  = conv?.assigned_agent || '';
  document.getElementById('assign-save').onclick = async () => {
    const agent = sel.value;
    try {
      await api('/whatsapp-conversations?id=' + convId, { method:'PUT', body: JSON.stringify({ assignedAgent: agent }) });
      const c = convs.find(x => x.id == convId);
      if (c) { c.assigned_agent = agent; renderConvList(); renderChatHeader(c); }
      closeModal('assign-modal');
      toast(agent ? `Assigned to ${agent}` : 'Unassigned');
    } catch(e) { toast(e.message, 'error'); }
  };
  openModal('assign-modal');
}

// ── Event bindings + boot ──────────────────────────────────────────────────
document.getElementById('wa-search').addEventListener('input', renderConvList);
document.getElementById('wa-status-filter').addEventListener('change', renderConvList);

loadConversations();
setInterval(loadConversations, 15000);
</script>

<?php }, $user ?? []); ?>
