<?php
function render_layout(string $title, string $pageKey, callable $content, array $user): void {
    $base = BASE_PATH;
    $nav = [
        ['href' => '',                 'label' => 'Overview',        'icon' => 'bi-speedometer2'],
        ['href' => '/live-monitor',    'label' => 'Live Monitor',    'icon' => 'bi-broadcast'],
        ['href' => '/extensions',      'label' => 'Extensions',      'icon' => 'bi-telephone'],
        ['href' => '/call-queues',     'label' => 'Call Queues',     'icon' => 'bi-list-task'],
        ['href' => '/ring-groups',     'label' => 'Ring Groups',     'icon' => 'bi-diagram-3'],
        ['href' => '/ivr-menus',       'label' => 'IVR Menus',       'icon' => 'bi-signpost-split'],
        ['href' => '/sla-rules',       'label' => 'SLA Rules',       'icon' => 'bi-shield-check'],
        ['href' => '/time-conditions', 'label' => 'Time Rules',      'icon' => 'bi-clock'],
        ['href' => '/call-logs',       'label' => 'Call Logs',       'icon' => 'bi-journal-text'],
        ['href' => '/reports',         'label' => 'Reports',         'icon' => 'bi-bar-chart-line'],
        ['href' => '/sip-trunks',      'label' => 'SIP Trunks',      'icon' => 'bi-hdd-network'],
        ['href' => '/pbx-agents',      'label' => 'PBX Agents',      'icon' => 'bi-headset'],
        ['href' => '/dashboard-users', 'label' => 'User Management', 'icon' => 'bi-people'],
        ['href' => '/whatsapp',        'label' => 'WhatsApp Inbox',  'icon' => 'bi-whatsapp'],
    ];
    ?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="base-path" content="<?= $base ?>">
<title><?= h($title) ?> — <?= APP_NAME ?></title>
<script>/* Prevent theme flash — must run before any stylesheet */(function(){var t=localStorage.getItem('pbx-theme')||'dark';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="<?= $base ?>/assets/js/app.js"></script>
</head>
<body>

<div class="d-flex" style="min-height:100vh">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <i class="bi bi-telephone-fill brand-icon"></i>
      <span class="brand-name"><?= APP_NAME ?></span>
    </div>

    <nav class="sidebar-nav">
      <?php foreach ($nav as $item):
        $href   = $base . $item['href'];
        $active = $pageKey === ltrim($item['href'], '/') || ($item['href'] === '' && $pageKey === 'dashboard');
      ?>
      <a href="<?= $href ?: $base . '/' ?>" class="nav-item <?= $active ? 'active' : '' ?>">
        <i class="bi <?= $item['icon'] ?> nav-icon"></i>
        <?= h($item['label']) ?>
      </a>
      <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
      <div class="connection-status mb-2">
        <span class="status-dot status-dot--green status-dot--pulse"></span>
        <span class="text-muted small ms-1">System Online</span>
      </div>
      <a href="<?= $base ?>/settings" class="nav-item <?= ($pageKey === 'settings' || $pageKey === 'freepbx-settings') ? 'active' : '' ?>">
        <i class="bi bi-gear nav-icon"></i>
        System Settings
      </a>
      <div class="user-row mt-2">
        <i class="bi bi-person-circle nav-icon"></i>
        <span class="user-name"><?= h($user['name']) ?></span>
        <a href="<?= $base ?>/logout" class="logout-btn ms-auto" title="Logout">
          <i class="bi bi-box-arrow-right"></i>
        </a>
      </div>
    </div>
  </aside>

  <!-- Main content wrapper -->
  <div class="flex-grow-1 d-flex flex-column overflow-hidden">

    <!-- Topbar -->
    <header class="topbar d-flex align-items-center justify-content-between px-4">
      <div class="d-flex align-items-center gap-2">
        <span class="status-dot status-dot--green status-dot--pulse"></span>
        <span class="text-muted small">System Online</span>
      </div>
      <div class="d-flex align-items-center gap-3 text-muted small">
        Node: <code>NGA-Lagos-01</code>
        <span class="badge bg-primary"><?= h(ucfirst($user['role'])) ?></span>
        <button id="theme-toggle" title="Toggle dark / light mode" onclick="toggleTheme()">
          <i class="bi bi-sun-fill" id="theme-icon"></i>
        </button>
      </div>
    </header>

    <!-- Page content -->
    <main class="page-content flex-grow-1 p-4 overflow-auto">
      <?php $content(); ?>
    </main>

  </div>
</div>

</body>
</html>
<?php
}
