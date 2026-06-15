<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title>Log In — OrbitPesa Wallet</title>
  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/wallet.css">
</head>
<body class="wallet-auth-body">
<div class="wauth-wrap" style="justify-content:center">

  <a href="<?= APP_URL ?>/wallet" class="wauth-logo">Orbit<span>Pesa</span> Wallet</a>

  <?php if ($err = flash('wallet_error')): ?>
    <div class="walert walert-error" style="margin-bottom:16px;width:100%">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($err) ?>
    </div>
  <?php endif; ?>

  <div class="wauth-card">
    <div class="wauth-title">Welcome back</div>
    <div class="wauth-subtitle">Log in with your email, phone, or wallet ID</div>

    <form method="POST" action="<?= APP_URL ?>/wallet/login" novalidate>
      <?= csrf_field() ?>

      <div class="wform-group">
        <label class="wform-label">Email / Phone / Wallet ID</label>
        <input type="text" name="identifier" class="wform-control" placeholder="jane@example.com or 0712345678"
               autofocus required value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>">
      </div>

      <div class="wform-group">
        <label class="wform-label">Password</label>
        <input type="password" name="password" class="wform-control" placeholder="Your password" required>
      </div>

      <div class="mt-14">
        <button type="submit" class="wbtn wbtn-primary">Log In <i class="fas fa-arrow-right"></i></button>
      </div>
    </form>
  </div>

  <div class="wauth-footer" style="margin-top:20px">
    Don't have a wallet? <a href="<?= APP_URL ?>/wallet/register">Create one free</a>
  </div>
  <div class="wauth-footer" style="margin-top:8px">
    <a href="<?= APP_URL ?>/wallet" style="color:rgba(255,255,255,.4)">← Back to wallet home</a>
  </div>

</div>
</body>
</html>
