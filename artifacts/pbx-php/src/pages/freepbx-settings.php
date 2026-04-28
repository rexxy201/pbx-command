<?php
require_once dirname(dirname(__DIR__)) . '/src/Settings.php';

render_layout('System Settings', 'settings', function() use ($user) {
    // Get the server's base URL for the webhook hint
    $proto     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host      = $_SERVER['HTTP_HOST'] ?? 'yourdomain.com';
    $webhookUrl = $proto . '://' . $host . BASE_PATH . '/pbx-api/whatsapp-webhook';
?>
<div class="page-header">
  <div>
    <h1>System Settings</h1>
    <p class="page-subtitle">Configure all API integrations and system-wide options.</p>
  </div>
</div>

<div id="settings-toast" class="d-none">
  <!-- Inline save confirmation for this page -->
</div>

<!-- Tab navigation -->
<ul class="nav nav-tabs mb-4" id="settings-tabs">
  <li class="nav-item"><button class="nav-link active" data-tab="general"><i class="bi bi-sliders me-1"></i>General</button></li>
  <li class="nav-item"><button class="nav-link" data-tab="appearance"><i class="bi bi-palette me-1"></i>Appearance</button></li>
  <li class="nav-item"><button class="nav-link" data-tab="freepbx"><i class="bi bi-hdd-network me-1"></i>FreePBX / AMI</button></li>
  <li class="nav-item"><button class="nav-link" data-tab="whatsapp"><i class="bi bi-whatsapp me-1"></i>WhatsApp</button></li>
  <li class="nav-item"><button class="nav-link" data-tab="smtp"><i class="bi bi-envelope me-1"></i>Email / SMTP</button></li>
</ul>

<!-- ── General ───────────────────────────────────────────────────────────── -->
<div class="tab-content" id="tab-general">
  <div class="card" style="max-width:660px">
    <div class="card-header"><div class="card-title">General</div><div class="card-desc">Application identity and regional settings.</div></div>
    <div class="card-body">
      <div class="mb-3">
        <label class="form-label">Company / ISP Name</label>
        <input type="text" class="form-control form-control-sm" id="g-company" placeholder="Lagos Fibre Networks">
      </div>
      <div class="row g-3 mb-3">
        <div class="col-6">
          <label class="form-label">Dashboard Title</label>
          <input type="text" class="form-control form-control-sm" id="g-title" placeholder="PBX Command">
        </div>
        <div class="col-6">
          <label class="form-label">Timezone</label>
          <select class="form-select form-select-sm" id="g-tz">
            <option value="Africa/Lagos">Africa/Lagos (WAT)</option>
            <option value="UTC">UTC</option>
            <option value="Europe/London">Europe/London</option>
            <option value="America/New_York">America/New_York</option>
          </select>
        </div>
      </div>
      <div class="mb-0">
        <label class="form-label">Support Email</label>
        <input type="email" class="form-control form-control-sm" id="g-email" placeholder="support@company.com">
      </div>
    </div>
    <div class="card-footer text-end border-top border-dark">
      <button class="btn btn-primary btn-sm" onclick="saveGroup('general')">
        <i class="bi bi-floppy me-1"></i>Save General Settings
      </button>
    </div>
  </div>
</div>

<!-- ── Appearance ─────────────────────────────────────────────────────────── -->
<div class="tab-content d-none" id="tab-appearance">
  <div class="card" style="max-width:660px">
    <div class="card-header">
      <div class="card-title">Appearance</div>
      <div class="card-desc">Set the accent colour and default theme mode for all users of this dashboard.</div>
    </div>
    <div class="card-body">

      <div class="mb-4">
        <label class="form-label fw-medium mb-2">Accent Colour</label>
        <div class="d-flex gap-2 flex-wrap align-items-center" id="accent-swatches">
          <button class="accent-swatch" data-color="blue"   title="Blue"   style="background:#3b82f6"></button>
          <button class="accent-swatch" data-color="indigo" title="Indigo" style="background:#6366f1"></button>
          <button class="accent-swatch" data-color="purple" title="Purple" style="background:#8b5cf6"></button>
          <button class="accent-swatch" data-color="teal"   title="Teal"   style="background:#14b8a6"></button>
          <button class="accent-swatch" data-color="green"  title="Green"  style="background:#22c55e"></button>
          <button class="accent-swatch" data-color="orange" title="Orange" style="background:#f97316"></button>
          <button class="accent-swatch" data-color="red"    title="Red"    style="background:#ef4444"></button>
          <button class="accent-swatch" data-color="rose"   title="Rose"   style="background:#f43f5e"></button>
        </div>
        <div class="text-muted small mt-2">
          Selected: <strong id="accent-selected-name">Blue</strong>
          <span class="ms-2 text-muted">(changes are previewed live — click Save to persist)</span>
        </div>
      </div>

      <div class="mb-0">
        <label class="form-label fw-medium mb-2">Default Theme Mode</label>
        <div class="d-flex gap-4">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="default-mode" id="mode-dark" value="dark" checked>
            <label class="form-check-label" for="mode-dark">
              <i class="bi bi-moon-fill me-1 text-muted"></i>Dark
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="default-mode" id="mode-light" value="light">
            <label class="form-check-label" for="mode-light">
              <i class="bi bi-sun-fill me-1 text-warning"></i>Light
            </label>
          </div>
        </div>
        <div class="text-muted small mt-1">This is the default for new browser sessions. Users can still toggle their own preference.</div>
      </div>

    </div>
    <div class="card-footer text-end border-top border-dark">
      <button class="btn btn-primary btn-sm" onclick="saveGroup('appearance')">
        <i class="bi bi-floppy me-1"></i>Save Appearance
      </button>
    </div>
  </div>
</div>

<!-- ── FreePBX / AMI ─────────────────────────────────────────────────────── -->
<div class="tab-content d-none" id="tab-freepbx">
  <div class="card mb-3" style="max-width:660px">
    <div class="card-header"><div class="card-title">FreePBX REST API</div><div class="card-desc">Credentials for provisioning sync with your FreePBX server.</div></div>
    <div class="card-body">
      <div class="mb-3">
        <label class="form-label">FreePBX Base URL</label>
        <input type="url" class="form-control form-control-sm" id="f-url" placeholder="https://pbx.yourdomain.com">
      </div>
      <div class="row g-3">
        <div class="col-6">
          <label class="form-label">API Key</label>
          <input type="text" class="form-control form-control-sm" id="f-apikey" placeholder="From FreePBX Admin panel">
        </div>
        <div class="col-6">
          <label class="form-label">API Secret</label>
          <input type="password" class="form-control form-control-sm" id="f-apisecret" placeholder="Leave blank to keep existing" autocomplete="new-password">
        </div>
      </div>
    </div>
  </div>

  <div class="card" style="max-width:660px">
    <div class="card-header"><div class="card-title">Asterisk AMI</div><div class="card-desc">AMI manager credentials for live call monitoring.</div></div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-8">
          <label class="form-label">AMI Host / IP</label>
          <input type="text" class="form-control form-control-sm" id="f-ami-host" placeholder="192.168.1.10 or pbx.host">
        </div>
        <div class="col-4">
          <label class="form-label">Port</label>
          <input type="number" class="form-control form-control-sm" id="f-ami-port" placeholder="5038" value="5038">
        </div>
      </div>
      <div class="row g-3">
        <div class="col-6">
          <label class="form-label">AMI Username</label>
          <input type="text" class="form-control form-control-sm" id="f-ami-user" placeholder="ami_manager">
        </div>
        <div class="col-6">
          <label class="form-label">AMI Password</label>
          <input type="password" class="form-control form-control-sm" id="f-ami-pass" placeholder="Leave blank to keep existing" autocomplete="new-password">
        </div>
      </div>
    </div>
    <div class="card-footer text-end border-top border-dark">
      <button class="btn btn-primary btn-sm" onclick="saveFreepbx()">
        <i class="bi bi-floppy me-1"></i>Save FreePBX / AMI Settings
      </button>
    </div>
  </div>
</div>

<!-- ── WhatsApp ───────────────────────────────────────────────────────────── -->
<div class="tab-content d-none" id="tab-whatsapp">
  <div class="card mb-3" style="max-width:660px">
    <div class="card-header">
      <div class="card-title"><i class="bi bi-whatsapp text-success me-1"></i>WhatsApp Cloud API</div>
      <div class="card-desc">Connect your WhatsApp Business number via Meta's Cloud API. <a href="https://developers.facebook.com/docs/whatsapp/cloud-api/get-started" target="_blank" class="text-info small">Setup guide <i class="bi bi-box-arrow-up-right"></i></a></div>
    </div>
    <div class="card-body">

      <div class="alert alert-secondary py-2 mb-3 small">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Webhook URL</strong> — paste this into your Meta App's webhook configuration:
        <br>
        <code id="webhook-url-display" class="user-select-all"><?= h($webhookUrl) ?></code>
        <button class="btn btn-sm btn-outline-secondary ms-2 py-0" onclick="navigator.clipboard.writeText('<?= h($webhookUrl) ?>');toast('Copied!')">
          <i class="bi bi-clipboard"></i>
        </button>
      </div>

      <div class="mb-3">
        <label class="form-label">Phone Number ID</label>
        <input type="text" class="form-control form-control-sm font-mono" id="wa-phone-id" placeholder="1234567890123456">
        <div class="form-text text-muted">Found in Meta Developer Console → WhatsApp → Getting Started</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Permanent Access Token</label>
        <input type="password" class="form-control form-control-sm font-mono" id="wa-token" placeholder="EAAxxxxxxx... (leave blank to keep existing)" autocomplete="new-password">
        <div class="form-text text-muted">Generate a permanent token in Meta Business Suite → System Users</div>
      </div>
      <div class="row g-3 mb-0">
        <div class="col-8">
          <label class="form-label">Webhook Verify Token</label>
          <input type="text" class="form-control form-control-sm font-mono" id="wa-verify" placeholder="my_secret_verify_token">
          <div class="form-text text-muted">Choose any secret string — you'll enter it in Meta's webhook setup</div>
        </div>
        <div class="col-4">
          <label class="form-label">API Version</label>
          <input type="text" class="form-control form-control-sm font-mono" id="wa-version" placeholder="v19.0" value="v19.0">
        </div>
      </div>
    </div>
    <div class="card-footer d-flex align-items-center gap-2 border-top border-dark">
      <button class="btn btn-outline-secondary btn-sm" onclick="testWhatsapp()">
        <i class="bi bi-patch-check me-1"></i>Test Connection
      </button>
      <div class="flex-grow-1"></div>
      <button class="btn btn-success btn-sm" onclick="saveGroup('whatsapp')">
        <i class="bi bi-floppy me-1"></i>Save WhatsApp Settings
      </button>
    </div>
  </div>

  <div class="card" style="max-width:660px">
    <div class="card-header"><div class="card-title">WhatsApp Status</div></div>
    <div class="card-body" id="wa-status-area">
      <div class="text-muted small">Save settings then click Test Connection to verify.</div>
    </div>
  </div>
</div>

<!-- ── SMTP / Email ──────────────────────────────────────────────────────── -->
<div class="tab-content d-none" id="tab-smtp">
  <div class="card" style="max-width:660px">
    <div class="card-header"><div class="card-title">Email / SMTP</div><div class="card-desc">For notification emails and alert delivery.</div></div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-8">
          <label class="form-label">SMTP Host</label>
          <input type="text" class="form-control form-control-sm" id="s-host" placeholder="mail.yourdomain.com">
        </div>
        <div class="col-4">
          <label class="form-label">Port</label>
          <input type="number" class="form-control form-control-sm" id="s-port" placeholder="587" value="587">
        </div>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-6">
          <label class="form-label">SMTP Username</label>
          <input type="text" class="form-control form-control-sm" id="s-user" placeholder="noreply@company.com">
        </div>
        <div class="col-6">
          <label class="form-label">SMTP Password</label>
          <input type="password" class="form-control form-control-sm" id="s-pass" placeholder="Leave blank to keep existing" autocomplete="new-password">
        </div>
      </div>
      <div class="row g-3">
        <div class="col-6">
          <label class="form-label">From Email</label>
          <input type="email" class="form-control form-control-sm" id="s-from-email" placeholder="noreply@company.com">
        </div>
        <div class="col-6">
          <label class="form-label">From Name</label>
          <input type="text" class="form-control form-control-sm" id="s-from-name" placeholder="PBX Command">
        </div>
      </div>
    </div>
    <div class="card-footer text-end border-top border-dark">
      <button class="btn btn-primary btn-sm" onclick="saveGroup('smtp')">
        <i class="bi bi-floppy me-1"></i>Save Email Settings
      </button>
    </div>
  </div>
</div>

<style>
.nav-tabs { border-color: var(--card-border, #1a2540); }
.nav-tabs .nav-link { color: var(--nav-color, #94a3b8); border-color: transparent; background: none; }
.nav-tabs .nav-link:hover { color: var(--nav-hover-color, #e2e8f0); border-color: transparent; background: var(--nav-hover-bg, #111827); }
.nav-tabs .nav-link.active { color: var(--accent, #60a5fa); border-color: var(--card-border, #1a2540) var(--card-border, #1a2540) var(--body-bg, #0a0f1e); background: var(--body-bg, #0a0f1e); }
.card-footer { background: var(--card-bg, #0d1526) !important; }
</style>

<script>
const tabMap = {
  general:    {load: loadGeneral},
  appearance: {load: loadAppearance},
  freepbx:    {load: loadFreepbx},
  whatsapp:   {load: loadWhatsapp},
  smtp:       {load: loadSmtp},
};

// Tab switching
document.querySelectorAll('[data-tab]').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.add('d-none'));
    btn.classList.add('active');
    const t = btn.dataset.tab;
    document.getElementById('tab-' + t).classList.remove('d-none');
    tabMap[t]?.load?.();
  });
});

// ── General ───────────────────────────────────────────────────────────────
async function loadGeneral() {
  try {
    const d = await api('/settings?group=general');
    document.getElementById('g-company').value = d['general.company'] || '';
    document.getElementById('g-title').value   = d['general.title']   || '';
    document.getElementById('g-email').value   = d['general.email']   || '';
    const tz = document.getElementById('g-tz');
    if (d['general.timezone']) tz.value = d['general.timezone'];
  } catch(e) {}
}

// ── FreePBX ───────────────────────────────────────────────────────────────
async function loadFreepbx() {
  try {
    const d = await api('/freepbx');
    document.getElementById('f-url').value      = d.url         || '';
    document.getElementById('f-apikey').value   = d.api_key     || '';
    document.getElementById('f-ami-host').value = d.ami_host    || '';
    document.getElementById('f-ami-port').value = d.ami_port    || '5038';
    document.getElementById('f-ami-user').value = d.ami_username|| '';
  } catch(e) {}
}
async function saveFreepbx() {
  try {
    await api('/freepbx', { method:'POST', body: JSON.stringify({
      url:         document.getElementById('f-url').value,
      apiKey:      document.getElementById('f-apikey').value,
      apiSecret:   document.getElementById('f-apisecret').value,
      amiHost:     document.getElementById('f-ami-host').value,
      amiPort:     document.getElementById('f-ami-port').value,
      amiUsername: document.getElementById('f-ami-user').value,
      amiPassword: document.getElementById('f-ami-pass').value,
    })});
    document.getElementById('f-apisecret').value = '';
    document.getElementById('f-ami-pass').value  = '';
    toast('FreePBX / AMI settings saved');
  } catch(e) { toast(e.message, 'error'); }
}

// ── WhatsApp ──────────────────────────────────────────────────────────────
async function loadWhatsapp() {
  try {
    const d = await api('/settings?group=whatsapp');
    document.getElementById('wa-phone-id').value = d['whatsapp.phone_number_id'] || '';
    document.getElementById('wa-verify').value   = d['whatsapp.verify_token']    || '';
    document.getElementById('wa-version').value  = d['whatsapp.api_version']     || 'v19.0';
    // access_token is sensitive — never prefilled
  } catch(e) {}
}
async function testWhatsapp() {
  const area = document.getElementById('wa-status-area');
  area.innerHTML = '<div class="text-muted small"><span class="spinner-border spinner-border-sm me-1"></span>Testing...</div>';
  try {
    const d = await api('/settings?group=whatsapp');
    const phoneId = d['whatsapp.phone_number_id'] || '';
    if (!phoneId) { area.innerHTML = '<div class="text-warning small"><i class="bi bi-exclamation-triangle me-1"></i>Phone Number ID not set.</div>'; return; }
    area.innerHTML = `<div class="text-success small"><i class="bi bi-check-circle me-1"></i>
      Settings saved. Phone Number ID: <code>${phoneId}</code><br>
      Webhook: <code>${document.getElementById('webhook-url-display').textContent}</code><br>
      Make sure this URL is registered in your Meta App dashboard under <strong>WhatsApp → Configuration → Webhook</strong>.
    </div>`;
  } catch(e) { area.innerHTML = `<div class="text-danger small"><i class="bi bi-x-circle me-1"></i>${e.message}</div>`; }
}

// ── SMTP ──────────────────────────────────────────────────────────────────
async function loadSmtp() {
  try {
    const d = await api('/settings?group=smtp');
    document.getElementById('s-host').value       = d['smtp.host']       || '';
    document.getElementById('s-port').value       = d['smtp.port']       || '587';
    document.getElementById('s-user').value       = d['smtp.username']   || '';
    document.getElementById('s-from-email').value = d['smtp.from_email'] || '';
    document.getElementById('s-from-name').value  = d['smtp.from_name']  || '';
  } catch(e) {}
}

// ── Generic group save ─────────────────────────────────────────────────────
async function saveGroup(group) {
  let settings = {};
  if (group === 'general') {
    settings = {
      'general.company':  document.getElementById('g-company').value,
      'general.title':    document.getElementById('g-title').value,
      'general.timezone': document.getElementById('g-tz').value,
      'general.email':    document.getElementById('g-email').value,
    };
  } else if (group === 'whatsapp') {
    settings = {
      'whatsapp.phone_number_id': document.getElementById('wa-phone-id').value,
      'whatsapp.verify_token':    document.getElementById('wa-verify').value,
      'whatsapp.api_version':     document.getElementById('wa-version').value,
    };
    const tok = document.getElementById('wa-token').value;
    if (tok && tok !== '••••••••') settings['whatsapp.access_token'] = tok;
  } else if (group === 'appearance') {
    const mode = document.querySelector('input[name="default-mode"]:checked')?.value || 'dark';
    settings = {
      'general.accent_color': _accentColor,
      'general.default_mode': mode,
    };
  } else if (group === 'smtp') {
    settings = {
      'smtp.host':       document.getElementById('s-host').value,
      'smtp.port':       document.getElementById('s-port').value,
      'smtp.username':   document.getElementById('s-user').value,
      'smtp.from_email': document.getElementById('s-from-email').value,
      'smtp.from_name':  document.getElementById('s-from-name').value,
    };
    const pw = document.getElementById('s-pass').value;
    if (pw) settings['smtp.password'] = pw;
  }
  try {
    await api('/settings', { method:'POST', body: JSON.stringify({ group, settings }) });
    if (group === 'whatsapp') document.getElementById('wa-token').value = '';
    if (group === 'smtp')     document.getElementById('s-pass').value  = '';
    toast('Settings saved');
  } catch(e) { toast(e.message, 'error'); }
}

// ── Appearance ─────────────────────────────────────────────────────────────
let _accentColor = 'blue';

function _applyAccent(color) {
  document.documentElement.setAttribute('data-accent', color);
}
function _selectSwatch(color) {
  _accentColor = color;
  document.querySelectorAll('.accent-swatch').forEach(s => {
    s.classList.toggle('selected', s.dataset.color === color);
  });
  const names = {blue:'Blue',indigo:'Indigo',purple:'Purple',teal:'Teal',green:'Green',orange:'Orange',red:'Red',rose:'Rose'};
  const el = document.getElementById('accent-selected-name');
  if (el) el.textContent = names[color] || color.charAt(0).toUpperCase()+color.slice(1);
  _applyAccent(color);
}
async function loadAppearance() {
  try {
    const d = await api('/settings?group=general');
    _selectSwatch(d['general.accent_color'] || 'blue');
    const mode = d['general.default_mode'] || 'dark';
    const r = document.querySelector(`input[name="default-mode"][value="${mode}"]`);
    if (r) r.checked = true;
  } catch(e) {}
}
// Swatch click handlers — attached once after DOM ready
document.querySelectorAll('.accent-swatch').forEach(s => {
  s.addEventListener('click', () => _selectSwatch(s.dataset.color));
});

// Load first tab on page load
loadGeneral();
</script>
<?php }, $user ?? []); ?>
