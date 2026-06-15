<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' : '' ?>Admin · OrbitPesa</title>
  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
  <?php if (isset($extraCss)) echo $extraCss; ?>
</head>
<body class="admin-body">

<div id="adminOverlay" class="admin-overlay"></div>

<!-- Admin Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
  <div class="admin-sidebar-logo">
    <a href="<?= APP_URL ?>/admin" class="admin-logo-link">
      <div class="logo-mark">OP</div>
      <div>
        <div class="logo-text">OrbitPesa</div>
        <div class="logo-sub">Admin Console</div>
      </div>
    </a>
  </div>

  <nav class="admin-nav">
    <div class="admin-nav-section">Overview</div>
    <a href="<?= APP_URL ?>/admin" class="admin-nav-item <?= $activeAdmin === 'dashboard' ? 'active' : '' ?>">
      <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="<?= APP_URL ?>/admin/analytics" class="admin-nav-item <?= $activeAdmin === 'analytics' ? 'active' : '' ?>">
      <i class="fas fa-chart-line"></i> Analytics
    </a>

    <div class="admin-nav-section">Management</div>
    <a href="<?= APP_URL ?>/admin/merchants" class="admin-nav-item <?= $activeAdmin === 'merchants' ? 'active' : '' ?>">
      <i class="fas fa-store"></i> Merchants
      <?php
      $kycCount = DB::fetch("SELECT COUNT(*) as c FROM users WHERE kyc_status = 'pending'");
      if (($kycCount['c'] ?? 0) > 0):
      ?>
        <span class="nav-badge"><?= $kycCount['c'] ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= APP_URL ?>/admin/transactions" class="admin-nav-item <?= $activeAdmin === 'transactions' ? 'active' : '' ?>">
      <i class="fas fa-exchange-alt"></i> Transactions
    </a>
    <a href="<?= APP_URL ?>/admin/withdrawals" class="admin-nav-item <?= $activeAdmin === 'withdrawals' ? 'active' : '' ?>">
      <i class="fas fa-money-bill-wave"></i> Withdrawals
      <?php
      $wdCount = DB::fetch("SELECT COUNT(*) as c FROM withdrawals WHERE status = 'pending'");
      if (($wdCount['c'] ?? 0) > 0):
      ?>
        <span class="nav-badge"><?= $wdCount['c'] ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= APP_URL ?>/admin/kyc" class="admin-nav-item <?= $activeAdmin === 'kyc' ? 'active' : '' ?>">
      <i class="fas fa-id-card"></i> KYC Queue
      <?php
      $kycDocs = DB::fetch("SELECT COUNT(*) as c FROM kyc_documents WHERE status = 'pending'");
      if (($kycDocs['c'] ?? 0) > 0):
      ?>
        <span class="nav-badge"><?= $kycDocs['c'] ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= APP_URL ?>/admin/disputes" class="admin-nav-item <?= $activeAdmin === 'disputes' ? 'active' : '' ?>">
      <i class="fas fa-gavel"></i> Disputes
    </a>
    <a href="<?= APP_URL ?>/admin/mpesa-accounts" class="admin-nav-item <?= $activeAdmin === 'mpesa-accounts' ? 'active' : '' ?>">
      <i class="fas fa-cash-register"></i> M-Pesa Accounts
      <?php
      $mpesaPending = DB::fetch("SELECT COUNT(*) as c FROM mpesa_accounts WHERE status='pending'");
      if (($mpesaPending['c'] ?? 0) > 0):
      ?>
        <span class="nav-badge"><?= $mpesaPending['c'] ?></span>
      <?php endif; ?>
    </a>

    <div class="admin-nav-section">Consumer Wallet</div>
    <a href="<?= APP_URL ?>/admin/wallet-users" class="admin-nav-item <?= $activeAdmin === 'wallet-users' ? 'active' : '' ?>">
      <i class="fas fa-wallet"></i> Wallet Users
      <?php
      $newWalletUsers = DB::fetch("SELECT COUNT(*) as c FROM wallet_users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
      if (($newWalletUsers['c'] ?? 0) > 0):
      ?>
        <span class="nav-badge" style="background:var(--green)"><?= $newWalletUsers['c'] ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= APP_URL ?>/admin/wallet-transactions" class="admin-nav-item <?= $activeAdmin === 'wallet-transactions' ? 'active' : '' ?>">
      <i class="fas fa-paper-plane"></i> Wallet Txns
    </a>

    <div class="admin-nav-section">Configuration</div>
    <a href="<?= APP_URL ?>/admin/fees" class="admin-nav-item <?= $activeAdmin === 'fees' ? 'active' : '' ?>">
      <i class="fas fa-percentage"></i> Fee Config
    </a>
    <a href="<?= APP_URL ?>/admin/settings" class="admin-nav-item <?= $activeAdmin === 'settings' ? 'active' : '' ?>">
      <i class="fas fa-cog"></i> System Settings
    </a>
    <a href="<?= APP_URL ?>/admin/logs" class="admin-nav-item <?= $activeAdmin === 'logs' ? 'active' : '' ?>">
      <i class="fas fa-clipboard-list"></i> Activity Logs
    </a>

    <div class="admin-nav-section">Links</div>
    <a href="<?= APP_URL ?>/dashboard" class="admin-nav-item" target="_blank">
      <i class="fas fa-external-link-alt"></i> Merchant View
    </a>
    <a href="<?= APP_URL ?>/admin/logout" class="admin-nav-item" style="color:rgba(255,100,100,.75)">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </nav>

  <?php $admin = AdminAuthMiddleware::admin(); ?>
  <div class="admin-sidebar-footer">
    <div class="admin-user-pill">
      <div class="admin-avatar"><?= strtoupper(substr($admin['name'] ?? 'A', 0, 1)) ?></div>
      <div>
        <div class="admin-name"><?= sanitize($admin['name'] ?? '') ?></div>
        <div class="admin-role"><?= ucwords(str_replace('_',' ', $admin['role'] ?? '')) ?></div>
      </div>
    </div>
  </div>
</aside>

<!-- Main -->
<div class="admin-main">
  <header class="admin-topbar">
    <div style="display:flex;align-items:center;gap:14px">
      <button class="admin-menu-btn" id="adminMenuBtn"><i class="fas fa-bars"></i></button>
      <div>
        <span class="admin-topbar-title"><?= $pageTitle ?? 'Admin' ?></span>
        <?php if (isset($breadcrumb)): ?>
        <span style="color:var(--text-muted);margin-left:6px;font-size:.82rem">/ <?= sanitize($breadcrumb) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
      <span class="admin-env-badge">ADMIN</span>
      <a href="<?= APP_URL ?>/" target="_blank" class="admin-topbar-btn" title="View Site">
        <i class="fas fa-globe"></i>
      </a>
      <div class="dropdown">
        <button class="admin-topbar-btn" data-toggle="dropdown">
          <i class="fas fa-bell"></i>
        </button>
        <div class="dropdown-menu" style="min-width:280px">
          <?php
          $pendingWd  = DB::fetch("SELECT COUNT(*) as c FROM withdrawals WHERE status='pending'");
          $pendingKyc = DB::fetch("SELECT COUNT(*) as c FROM users WHERE kyc_status='pending'");
          $openDis    = DB::fetch("SELECT COUNT(*) as c FROM disputes WHERE status='open'");
          ?>
          <?php if ($pendingWd['c'] > 0): ?>
          <a href="<?= APP_URL ?>/admin/withdrawals" class="dropdown-item">
            <i class="fas fa-money-bill-wave" style="color:var(--warning)"></i>
            <?= $pendingWd['c'] ?> pending withdrawal<?= $pendingWd['c'] > 1 ? 's' : '' ?>
          </a>
          <?php endif; ?>
          <?php if ($pendingKyc['c'] > 0): ?>
          <a href="<?= APP_URL ?>/admin/merchants?kyc=pending" class="dropdown-item">
            <i class="fas fa-id-card" style="color:var(--info)"></i>
            <?= $pendingKyc['c'] ?> KYC pending review
          </a>
          <?php endif; ?>
          <?php if ($openDis['c'] > 0): ?>
          <a href="<?= APP_URL ?>/admin/disputes" class="dropdown-item">
            <i class="fas fa-gavel" style="color:var(--danger)"></i>
            <?= $openDis['c'] ?> open dispute<?= $openDis['c'] > 1 ? 's' : '' ?>
          </a>
          <?php endif; ?>
          <?php if (!$pendingWd['c'] && !$pendingKyc['c'] && !$openDis['c']): ?>
          <div class="dropdown-item" style="color:var(--text-muted);cursor:default">
            <i class="fas fa-check-circle" style="color:var(--success)"></i> All clear
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="dropdown">
        <button class="admin-topbar-btn" data-toggle="dropdown">
          <i class="fas fa-user-shield"></i>
        </button>
        <div class="dropdown-menu">
          <div style="padding:10px 14px;border-bottom:1px solid var(--border)">
            <div style="font-weight:700;font-size:.875rem"><?= sanitize($admin['name'] ?? '') ?></div>
            <div style="font-size:.75rem;color:var(--text-muted)"><?= sanitize($admin['email'] ?? '') ?></div>
          </div>
          <a href="<?= APP_URL ?>/admin/logout" class="dropdown-item danger">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- Flash -->
  <div style="padding:0 28px">
    <?php if ($success = flash('success')): ?>
      <div class="alert alert-success" data-dismiss="4000" style="margin-top:16px">
        <i class="fas fa-check-circle"></i> <?= sanitize($success) ?>
      </div>
    <?php endif; ?>
    <?php if ($error = flash('error')): ?>
      <div class="alert alert-danger" data-dismiss="5000" style="margin-top:16px">
        <i class="fas fa-exclamation-circle"></i> <?= sanitize($error) ?>
      </div>
    <?php endif; ?>
  </div>

  <main class="admin-content">
    <?php $content(); ?>
  </main>

  <footer class="admin-footer">
    <span>&copy; <?= date('Y') ?> OrbitPesa Ltd — Admin Console v<?= APP_VERSION ?></span>
    <span style="display:flex;align-items:center;gap:8px">
      <span style="width:8px;height:8px;background:var(--success);border-radius:50%;display:inline-block;animation:pulse 2s infinite"></span>
      All systems operational
    </span>
  </footer>
</div>

<script src="<?= APP_URL ?>/assets/js/app.js"></script>
<script>
const adminBtn     = document.getElementById('adminMenuBtn');
const adminSidebar = document.getElementById('adminSidebar');
const adminOverlay = document.getElementById('adminOverlay');
if (adminBtn) {
  adminBtn.addEventListener('click', () => {
    adminSidebar.classList.toggle('open');
    adminOverlay.classList.toggle('open');
  });
}
if (adminOverlay) {
  adminOverlay.addEventListener('click', () => {
    adminSidebar.classList.remove('open');
    adminOverlay.classList.remove('open');
  });
}
</script>
<?php if (isset($extraJs)) echo $extraJs; ?>
</body>
</html>
