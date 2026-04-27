<?php
require_once dirname(dirname(__DIR__)) . '/src/Settings.php';

render_layout('WhatsApp Inbox', 'whatsapp', function() use ($user) {
    $agents = Database::query("SELECT name FROM pbx_users WHERE is_active = true ORDER BY name");
?>

<style>
/* ── WhatsApp Inbox layout ─────────────────────────────────────────── */
.wa-inbox {
  display: flex;
  height: calc(100vh - 110px);
  min-height: 480px;
  background: #0d1526;
  border: 1px solid #1a2540;
  border-radius: 8px;
  overflow: hidden;
}

/* Conversation list */
.wa-sidebar {
  width: 300px;
  min-width: 260px;
  flex-shrink: 0;
  border-right: 1px solid #1a2540;
  display: flex;
  flex-direction: column;
}
.wa-sidebar-header {
  padding: 12px 14px;
  border-bottom: 1px solid #1a2540;
  display: flex;
  align-items: center;
  gap: 8px;
}
.wa-sidebar-header h6 { margin:0; font-size:.85rem; font-weight:600; color:#e2e8f0; }
.wa-conv-search {
  padding: 8px 12px;
  border-bottom: 1px solid #1a2540;
}
.wa-conv-list { flex:1; overflow-y:auto; }
.wa-conv-item {
  padding: 10px 14px;
  cursor: pointer;
  border-bottom: 1px solid #111827;
  transition: background 0.1s;
  position: relative;
}
.wa-conv-item:hover { background: #111827; }
.wa-conv-item.active { background: #1e3a5f; border-left: 3px solid #22c55e; }
.wa-conv-name { font-size:.84rem; font-weight:600; color:#e2e8f0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.wa-conv-preview { font-size:.75rem; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px; }
.wa-conv-meta { display:flex; align-items:center; justify-content:space-between; margin-top:3px; }
.wa-conv-time { font-size:.7rem; color:#475569; }
.wa-badge { background:#22c55e; color:#fff; border-radius:99px; font-size:.65rem; font-weight:700; padding:1px 6px; min-width:18px; text-align:center; }

/* Chat area */
.wa-chat { flex:1; display:flex; flex-direction:column; min-width:0; }
.wa-chat-header {
  padding: 12px 16px;
  border-bottom: 1px solid #1a2540;
  display: flex;
  align-items: center;
  gap: 10px;
}
.wa-chat-name { font-size:.9rem; font-weight:600; color:#e2e8f0; }
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
.wa-bubble.in  { background:#1e2535; color:#e2e8f0; border-bottom-left-radius:2px; }
.wa-bubble.out { background:#1a4731; color:#d1fae5; border-bottom-right-radius:2px; }
.wa-bubble-time { font-size:.65rem; opacity:.5; margin-top:4px; }
.wa-bubble .wa-sender { font-size:.7rem; color:#64748b; margin-bottom:2px; }

/* Reply area */
.wa-reply {
  padding: 10px 14px;
  border-top: 1px solid #1a2540;
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
</style>

<div class="page-header">
  <div>
    <h1><i class="bi bi-whatsapp text-success me-2"></i>WhatsApp Inbox</h1>
    <p class="page-subtitle">Shared team inbox — manage all customer WhatsApp conversations.</p>
  </div>
  <div class="page-header-actions">
    <select id="wa-filter" class="form-select form-select-sm" style="width:130px">
      <option value="">All conversations</option>
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
      <i class="bi bi-chat-left-dots" style="font-size:2.5rem; color:#1e2535"></i>
      <span>Select a conversation to start</span>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../_confirm-modal.php'; ?>

<!-- Assign modal -->
<div class="modal fade" id="assign-modal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Assign Conversation</h5>
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
        <button class="btn btn-primary btn-sm" id="assign-save">Assign</button>
      </div>
    </div>
  </div>
</div>

<script>
let convs = [], activeConvId = null;

function fmtConvTime(ts) {
  if (!ts) return '';
  const d = new Date(ts);
  const now = new Date();
  if (d.toDateString() === now.toDateString()) return d.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'});
  return d.toLocaleDateString('en-GB',{day:'numeric',month:'short'});
}

// ── Load conversation list ──────────────────────────────────────────────────
async function loadConversations() {
  try {
    convs = await api('/whatsapp-conversations');
    renderConvList();
    // Reload active chat if open
    if (activeConvId) {
      const still = convs.find(c => c.id == activeConvId);
      if (still) renderChatHeader(still);
    }
  } catch(e) { console.error(e); }
}

function renderConvList() {
  const q      = document.getElementById('wa-search').value.toLowerCase();
  const filter = document.getElementById('wa-filter').value;
  const el     = document.getElementById('wa-conv-list');
  const list   = convs.filter(c => {
    const matchQ = !q || (c.contact_number||'').includes(q) || (c.contact_name||'').toLowerCase().includes(q);
    const matchF = !filter || c.status === filter;
    return matchQ && matchF;
  });
  document.getElementById('wa-conv-count').textContent = list.length;
  if (!list.length) {
    el.innerHTML = '<div class="text-center text-muted p-4 small">No conversations</div>';
    return;
  }
  el.innerHTML = list.map(c => `
    <div class="wa-conv-item ${c.id == activeConvId ? 'active' : ''}"
         onclick="openConv(${c.id})">
      <div class="d-flex align-items-center gap-2">
        <div style="flex:1;min-width:0">
          <div class="wa-conv-name">${c.contact_name ? h2(c.contact_name) : h2(c.contact_number)}</div>
          <div class="wa-conv-preview">${c.last_message_body ? h2(c.last_message_body) : '<em>No messages</em>'}</div>
        </div>
        ${c.unread_count > 0 ? `<span class="wa-badge">${c.unread_count}</span>` : ''}
      </div>
      <div class="wa-conv-meta mt-1">
        <span class="wa-conv-time">${fmtConvTime(c.last_message_at)}</span>
        ${c.assigned_agent ? `<span class="badge bg-secondary" style="font-size:.6rem">${h2(c.assigned_agent)}</span>` : ''}
        <span class="badge ${c.status==='open'?'bg-success':c.status==='pending'?'bg-warning text-dark':'bg-secondary'}" style="font-size:.6rem">${c.status}</span>
      </div>
    </div>
  `).join('');
}

function h2(s) { const d=document.createElement('div'); d.textContent=s; return d.innerHTML; }

// ── Open a conversation ────────────────────────────────────────────────────
async function openConv(id) {
  activeConvId = id;
  renderConvList();
  const conv = convs.find(c => c.id == id);
  if (!conv) return;
  renderChatHeader(conv);
  await loadMessages(id);
}

function renderChatHeader(conv) {
  const area = document.getElementById('wa-chat-area');
  area.innerHTML = `
    <div class="wa-chat-header">
      <div class="wa-status-badge bg-success"></div>
      <div style="flex:1">
        <div class="wa-chat-name">${h2(conv.contact_name || conv.contact_number)}</div>
        <div class="wa-chat-sub">${h2(conv.contact_number)}
          ${conv.assigned_agent ? ` · <i class="bi bi-headset"></i> ${h2(conv.assigned_agent)}` : ''}
        </div>
      </div>
      <button class="btn btn-outline-secondary btn-sm" onclick="openAssign(${conv.id})"><i class="bi bi-person-check me-1"></i>Assign</button>
      <button class="btn btn-outline-${conv.status==='closed'?'success':'secondary'} btn-sm" onclick="toggleStatus(${conv.id})">
        ${conv.status==='closed' ? '<i class="bi bi-envelope-open me-1"></i>Reopen' : '<i class="bi bi-check2-all me-1"></i>Close'}
      </button>
    </div>
    <div class="wa-messages" id="wa-msgs-${conv.id}">
      <div class="text-center text-muted small">Loading messages...</div>
    </div>
    <div class="wa-reply">
      <textarea class="form-control form-control-sm" id="wa-reply-input" rows="1"
        placeholder="Type a message... (Enter to send, Shift+Enter for new line)"
        ${conv.status==='closed' ? 'disabled' : ''}></textarea>
      <button class="btn btn-success btn-sm" id="wa-send-btn" onclick="sendMessage(${conv.id})" ${conv.status==='closed' ? 'disabled' : ''}>
        <i class="bi bi-send-fill"></i>
      </button>
    </div>
  `;
  // Auto-grow textarea
  const ta = document.getElementById('wa-reply-input');
  if (ta) {
    ta.addEventListener('input', () => { ta.style.height='auto'; ta.style.height=Math.min(ta.scrollHeight,120)+'px'; });
    ta.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(conv.id); }
    });
  }
}

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
    // Mark as read locally
    const conv = convs.find(c => c.id == convId);
    if (conv) conv.unread_count = 0;
    renderConvList();
  } catch(e) { console.error(e); }
}

async function sendMessage(convId) {
  const input = document.getElementById('wa-reply-input');
  const body  = input?.value.trim();
  if (!body) return;
  const btn = document.getElementById('wa-send-btn');
  if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>'; }
  try {
    const msg = await api('/whatsapp-messages?conversation_id=' + convId, {
      method: 'POST',
      body: JSON.stringify({ body, conversationId: convId, senderName: '<?= h($user['name'] ?? 'Agent') ?>' })
    });
    if (input) { input.value = ''; input.style.height = 'auto'; }
    // Append message immediately
    const el = document.getElementById('wa-msgs-' + convId);
    if (el) {
      const div = document.createElement('div');
      div.className = 'wa-bubble-wrap out';
      div.innerHTML = `<div class="wa-bubble out">${h2(body)}<div class="wa-bubble-time">just now <span class="wa-status-badge sent"></span></div></div>`;
      el.appendChild(div);
      el.scrollTop = el.scrollHeight;
    }
  } catch(e) { toast(e.message, 'error'); }
  if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-send-fill"></i>'; }
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

function openAssign(convId) {
  const conv = convs.find(c => c.id == convId);
  if (conv && conv.assigned_agent) document.getElementById('assign-agent').value = conv.assigned_agent;
  document.getElementById('assign-save').onclick = async () => {
    const agent = document.getElementById('assign-agent').value;
    try {
      await api('/whatsapp-conversations?id=' + convId, { method:'PUT', body: JSON.stringify({ assignedAgent: agent }) });
      const c = convs.find(x => x.id == convId);
      if (c) { c.assigned_agent = agent; renderConvList(); renderChatHeader(c); }
      closeModal('assign-modal');
      toast('Assigned to ' + (agent || 'nobody'));
    } catch(e) { toast(e.message, 'error'); }
  };
  openModal('assign-modal');
}

document.getElementById('wa-search').addEventListener('input', renderConvList);
document.getElementById('wa-filter').addEventListener('change', renderConvList);

// Auto-refresh every 15s
loadConversations();
setInterval(loadConversations, 15000);
</script>

<?php }, $user ?? []); ?>
