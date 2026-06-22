<?php $notifUnread = Notification::unreadCount($_SESSION['user_id']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
  <?php if (isset($extraCss)) echo $extraCss; ?>
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:99" class=""></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <a href="<?= APP_URL ?>/dashboard" class="sidebar-logo">
    <div class="logo-mark">OP</div>
    <span class="logo-text">Orbit<span>Pesa</span></span>
  </a>

  <nav class="sidebar-nav">
    <div class="nav-section">Main</div>
    <a href="<?= APP_URL ?>/dashboard" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
      <i class="fas fa-chart-pie"></i> Overview
    </a>
    <a href="<?= APP_URL ?>/dashboard/transactions" class="nav-item <?= $activeNav === 'transactions' ? 'active' : '' ?>">
      <i class="fas fa-exchange-alt"></i> Transactions
    </a>
    <a href="<?= APP_URL ?>/dashboard/analytics" class="nav-item <?= $activeNav === 'analytics' ? 'active' : '' ?>">
      <i class="fas fa-chart-line"></i> Analytics
    </a>
    <a href="<?= APP_URL ?>/dashboard/payment-links" class="nav-item <?= $activeNav === 'payment-links' ? 'active' : '' ?>">
      <i class="fas fa-link"></i> Payment Links
    </a>
    <a href="<?= APP_URL ?>/dashboard/wallet" class="nav-item <?= $activeNav === 'wallet' ? 'active' : '' ?>">
      <i class="fas fa-wallet"></i> Wallet
    </a>

    <div class="nav-section" style="margin-top:8px">Payments</div>
    <a href="<?= APP_URL ?>/dashboard/mpesa" class="nav-item <?= $activeNav === 'mpesa' ? 'active' : '' ?>">
      <i class="fas fa-mobile-alt"></i> M-Pesa Push
    </a>
    <a href="<?= APP_URL ?>/dashboard/card" class="nav-item <?= $activeNav === 'card' ? 'active' : '' ?>">
      <i class="fas fa-credit-card"></i> Card Payment
    </a>
    <a href="<?= APP_URL ?>/dashboard/wallet-pay" class="nav-item <?= $activeNav === 'wallet-pay' ? 'active' : '' ?>">
      <i class="fas fa-coins"></i> Wallet Pay
    </a>
    <?php
    $mpesaAcct = MpesaAccount::findByUserId($_SESSION['user_id']);
    $mpesaBadge = match($mpesaAcct['status'] ?? '') {
        'approved'     => ['OK',      'background:rgba(21,131,71,.25);color:#7dce9d'],
        'pending'      => ['Pending', 'background:rgba(245,158,11,.2);color:#fbbf24'],
        'under_review' => ['Review',  'background:rgba(59,130,246,.2);color:#93c5fd'],
        'rejected'     => ['!',       'background:rgba(239,68,68,.2);color:#f87171'],
        default        => ['New',     'background:rgba(255,255,255,.12);color:rgba(255,255,255,.5)'],
    };
    ?>
    <a href="<?= APP_URL ?>/dashboard/mpesa-account" class="nav-item <?= $activeNav === 'mpesa-account' ? 'active' : '' ?>" style="display:flex;align-items:center;justify-content:space-between">
      <span><i class="fas fa-cash-register"></i> Business Account</span>
      <span style="font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:10px;<?= $mpesaBadge[1] ?>"><?= $mpesaBadge[0] ?></span>
    </a>

    <div class="nav-section" style="margin-top:8px">Developer</div>
    <a href="<?= APP_URL ?>/developers" class="nav-item <?= $activeNav === 'developers' ? 'active' : '' ?>">
      <i class="fas fa-code"></i> Console
    </a>
    <a href="<?= APP_URL ?>/dashboard/api-keys" class="nav-item <?= $activeNav === 'api-keys' ? 'active' : '' ?>">
      <i class="fas fa-key"></i> API Keys
    </a>
    <a href="<?= APP_URL ?>/dashboard/webhooks" class="nav-item <?= $activeNav === 'webhooks' ? 'active' : '' ?>">
      <i class="fas fa-satellite-dish"></i> Webhooks
    </a>
    <a href="<?= APP_URL ?>/developers/docs" class="nav-item <?= $activeNav === 'docs' ? 'active' : '' ?>">
      <i class="fas fa-book"></i> Documentation
    </a>

    <div class="nav-section" style="margin-top:8px">Account</div>
    <a href="<?= APP_URL ?>/dashboard/notifications" class="nav-item <?= $activeNav === 'notifications' ? 'active' : '' ?>" style="display:flex;align-items:center;justify-content:space-between">
      <span><i class="fas fa-bell"></i> Notifications</span>
      <?php if ($notifUnread > 0): ?>
        <span style="background:#ef4444;color:#fff;font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:10px"><?= $notifUnread > 99 ? '99+' : $notifUnread ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= APP_URL ?>/dashboard/disputes" class="nav-item <?= $activeNav === 'disputes' ? 'active' : '' ?>">
      <i class="fas fa-scale-balanced"></i> Disputes
    </a>
    <?php $kycUser = auth_user(); $kycBadge = $kycUser['kyc_status'] ?? 'unverified'; ?>
    <a href="<?= APP_URL ?>/dashboard/kyc" class="nav-item <?= $activeNav === 'kyc' ? 'active' : '' ?>" style="display:flex;align-items:center;justify-content:space-between">
      <span><i class="fas fa-id-card"></i> KYC Verification</span>
      <?php if ($kycBadge === 'verified'): ?>
        <span style="background:rgba(21,131,71,.25);color:#7dce9d;font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:10px">OK</span>
      <?php elseif ($kycBadge === 'pending'): ?>
        <span style="background:rgba(234,179,8,.2);color:#fbbf24;font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:10px">Review</span>
      <?php elseif ($kycBadge === 'rejected'): ?>
        <span style="background:rgba(239,68,68,.2);color:#f87171;font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:10px">!</span>
      <?php else: ?>
        <span style="background:rgba(255,255,255,.12);color:rgba(255,255,255,.5);font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:10px">New</span>
      <?php endif; ?>
    </a>
    <a href="<?= APP_URL ?>/dashboard/settings" class="nav-item <?= $activeNav === 'settings' ? 'active' : '' ?>">
      <i class="fas fa-cog"></i> Settings
    </a>
    <a href="<?= APP_URL ?>/logout" class="nav-item" style="color:rgba(255,100,100,.7)">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </nav>

  <?php $u = auth_user(); ?>
  <div class="sidebar-footer">
    <div class="user-pill">
      <div class="user-avatar"><?= strtoupper(substr($u['business_name'] ?? 'U', 0, 1)) ?></div>
      <div>
        <div class="user-name"><?= sanitize($u['business_name'] ?? '') ?></div>
        <div class="user-role"><?= ucfirst($u['account_type'] ?? 'Business') ?></div>
      </div>
    </div>
  </div>
</aside>

<!-- Main Area -->
<div class="main-area">
  <!-- Topbar -->
  <header class="topbar">
    <div style="display:flex;align-items:center;gap:14px">
      <button class="topbar-icon-btn" id="menuToggle" style="border:none">
        <i class="fas fa-bars"></i>
      </button>
      <span class="topbar-title"><?= $pageTitle ?? APP_NAME ?></span>
    </div>
    <div class="topbar-actions">
      <?php $env = $_SESSION['user']['env'] ?? 'test'; ?>
      <span class="badge <?= $env === 'live' ? 'badge-success' : 'badge-warning' ?>" style="font-size:.75rem;padding:5px 12px">
        <?= strtoupper($env) ?> MODE
      </span>
      <?php $notifItems = Notification::getForUser($_SESSION['user_id'], 8); ?>
      <div class="notif-wrap" id="notifWrap">
        <button class="topbar-icon-btn" id="notifToggle" title="Notifications" aria-label="Notifications">
          <i class="fas fa-bell"></i>
          <?php if ($notifUnread > 0): ?>
            <span class="notif-badge" id="notifBadge"><?= $notifUnread > 99 ? '99+' : $notifUnread ?></span>
          <?php else: ?>
            <span class="notif-badge" id="notifBadge" style="display:none">0</span>
          <?php endif; ?>
        </button>
        <div class="notif-dropdown" id="notifDropdown">
          <div class="notif-hd">
            <span class="notif-hd-title">Notifications</span>
            <?php if ($notifUnread > 0): ?>
              <form method="POST" action="<?= APP_URL ?>/dashboard/notifications/read-all" style="margin:0">
                <?= csrf_field() ?>
                <button type="submit" class="notif-mark-all">Mark all read</button>
              </form>
            <?php endif; ?>
          </div>
          <div class="notif-list">
            <?php if (empty($notifItems)): ?>
              <div class="notif-empty">
                <i class="fas fa-bell-slash"></i>
                <p>No notifications yet</p>
              </div>
            <?php else: ?>
              <?php foreach ($notifItems as $n): ?>
                <a href="<?= APP_URL ?>/dashboard/notifications/goto?id=<?= urlencode($n['id']) ?>"
                   class="notif-item <?= $n['is_read'] ? '' : 'notif-unread' ?>">
                  <div class="notif-icon notif-type-<?= htmlspecialchars($n['type']) ?>">
                    <i class="fas <?= Notification::typeIcon($n['type']) ?>"></i>
                  </div>
                  <div class="notif-content">
                    <div class="notif-title"><?= sanitize($n['title']) ?></div>
                    <div class="notif-body-text"><?= sanitize($n['body']) ?></div>
                    <div class="notif-time"><?= Notification::timeAgo($n['created_at']) ?></div>
                  </div>
                  <?php if (!$n['is_read']): ?><span class="notif-dot"></span><?php endif; ?>
                </a>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <a href="<?= APP_URL ?>/dashboard/notifications" class="notif-footer-link">
            View all notifications <i class="fas fa-arrow-right" style="font-size:.7rem"></i>
          </a>
        </div>
      </div>
      <div class="dropdown">
        <button class="topbar-icon-btn" data-toggle="dropdown">
          <i class="fas fa-user-circle"></i>
        </button>
        <div class="dropdown-menu">
          <a href="<?= APP_URL ?>/dashboard/settings" class="dropdown-item">
            <i class="fas fa-cog"></i> Settings
          </a>
          <a href="<?= APP_URL ?>/developers" class="dropdown-item">
            <i class="fas fa-code"></i> Developer
          </a>
          <div class="dropdown-divider"></div>
          <a href="<?= APP_URL ?>/logout" class="dropdown-item danger">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- Flash Messages -->
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

  <!-- Page Content -->
  <main class="page-content">
    <?php $content(); ?>
  </main>

  <footer style="padding:16px 28px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;font-size:.78rem;color:var(--text-muted)">
    <span>&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</span>
    <span>v<?= APP_VERSION ?></span>
  </footer>
</div>

<script>window.APP_URL = '<?= APP_URL ?>';</script>
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
<?php if (isset($extraJs)) echo $extraJs; ?>
</body>
</html>
