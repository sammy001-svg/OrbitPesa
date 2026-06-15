<?php
// $walletUser injected by renderWallet()
$wu        = $walletUser;
$firstName = explode(' ', $wu['full_name'])[0];
$initial   = strtoupper(substr($wu['full_name'], 0, 1));
$isHome    = ($activeWalletNav ?? '') === 'home';
$appUrl    = APP_URL;
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>OrbitPesa Wallet</title>
  <link rel="icon" type="image/svg+xml" href="<?= $appUrl ?>/assets/img/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= $appUrl ?>/assets/css/wallet.css">
</head>
<body class="wallet-body">
<div class="wallet-app">

  <!-- Header -->
  <div class="wallet-header">
<?php if ($isHome): ?>
    <div class="wh-home">
      <div class="wh-toprow">
        <div>
          <div class="wh-wordmark">Orbit<span>Pesa</span></div>
          <div class="wh-greeting">Hi, <?= htmlspecialchars($firstName) ?> 👋</div>
        </div>
        <a href="<?= $appUrl ?>/wallet/profile" class="wh-avatar" title="Profile"><?= $initial ?></a>
      </div>
      <div class="wh-balance-block">
        <div class="wh-balance-label">Available Balance</div>
        <div class="wh-balance-amount">
          <span class="currency">KES </span><?= number_format((float)$wu['balance'], 2) ?>
        </div>
        <div class="wh-wallet-id"><?= htmlspecialchars($wu['wallet_id']) ?></div>
      </div>
    </div>
<?php else: ?>
    <div class="wh-sub">
      <a href="<?= $backUrl ?? $appUrl . '/wallet/home' ?>" class="wh-back">
        <i class="fas fa-arrow-left"></i>
      </a>
      <span class="wh-sub-title"><?= htmlspecialchars($pageTitle ?? '') ?></span>
      <div class="wh-sub-bal">
        <div class="wh-sub-bal-label">Balance</div>
        <div class="wh-sub-bal-amount">KES <?= number_format((float)$wu['balance'], 2) ?></div>
      </div>
    </div>
<?php endif; ?>
  </div>

  <!-- Main -->
  <main class="wallet-main">

    <?php if ($msg = flash('wallet_success')): ?>
      <div class="walert walert-success mt-8"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash('wallet_error')): ?>
      <div class="walert walert-error mt-8"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php $content(); ?>

  </main>

  <!-- Bottom Nav -->
  <nav class="wallet-nav">
    <a href="<?= $appUrl ?>/wallet/home" class="wnav-item <?= ($activeWalletNav ?? '') === 'home' ? 'active' : '' ?>">
      <i class="fas fa-home"></i>
      <span>Home</span>
    </a>
    <a href="<?= $appUrl ?>/wallet/send" class="wnav-item <?= ($activeWalletNav ?? '') === 'send' ? 'active' : '' ?>">
      <i class="fas fa-paper-plane"></i>
      <span>Send</span>
    </a>
    <a href="<?= $appUrl ?>/wallet/receive" class="wnav-center <?= ($activeWalletNav ?? '') === 'receive' ? 'active' : '' ?>">
      <div class="wnav-center-btn"><i class="fas fa-arrow-down"></i></div>
      <span>Receive</span>
    </a>
    <a href="<?= $appUrl ?>/wallet/transactions" class="wnav-item <?= ($activeWalletNav ?? '') === 'history' ? 'active' : '' ?>">
      <i class="fas fa-history"></i>
      <span>History</span>
    </a>
    <a href="<?= $appUrl ?>/wallet/profile" class="wnav-item <?= ($activeWalletNav ?? '') === 'profile' ? 'active' : '' ?>">
      <i class="fas fa-user"></i>
      <span>Profile</span>
    </a>
  </nav>

</div>
<script>
window.APP_URL = '<?= $appUrl ?>';
</script>
</body>
</html>
