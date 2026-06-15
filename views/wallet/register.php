<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title>Create Wallet — OrbitPesa</title>
  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/wallet.css">
</head>
<body class="wallet-auth-body">
<div class="wauth-wrap">

  <a href="<?= APP_URL ?>/wallet" class="wauth-logo">Orbit<span>Pesa</span> Wallet</a>

  <?php if ($err = flash('wallet_error')): ?>
    <div class="walert walert-error" style="margin-bottom:16px;width:100%">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($err) ?>
    </div>
  <?php endif; ?>

  <div class="wauth-card">
    <div class="wauth-title">Create your wallet</div>
    <div class="wauth-subtitle">A unique OrbitPesa ID is generated for you instantly</div>

    <form method="POST" action="<?= APP_URL ?>/wallet/register" novalidate>
      <?= csrf_field() ?>

      <!-- Personal info -->
      <div class="wauth-section-label" style="margin-top:0;border-top:none;padding-top:0">Personal Details</div>

      <div class="wform-group">
        <label class="wform-label">Full Name</label>
        <input type="text" name="full_name" class="wform-control" placeholder="e.g. Jane Mwangi" required autofocus
               value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
      </div>

      <div class="wform-row">
        <div class="wform-group">
          <label class="wform-label">Phone Number</label>
          <input type="tel" name="phone" class="wform-control" placeholder="0712 345 678" required
                 value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>
        <div class="wform-group">
          <label class="wform-label">National ID <span style="font-weight:400">(opt.)</span></label>
          <input type="text" name="national_id" class="wform-control" placeholder="12345678"
                 value="<?= htmlspecialchars($_POST['national_id'] ?? '') ?>">
        </div>
      </div>

      <div class="wform-group">
        <label class="wform-label">Email Address</label>
        <input type="email" name="email" class="wform-control" placeholder="jane@example.com" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <!-- Security -->
      <div class="wauth-section-label">Security</div>

      <div class="wform-row">
        <div class="wform-group">
          <label class="wform-label">Password</label>
          <input type="password" name="password" class="wform-control" placeholder="Min 8 characters" required>
        </div>
        <div class="wform-group">
          <label class="wform-label">Confirm Password</label>
          <input type="password" name="password_confirm" class="wform-control" placeholder="Repeat password" required>
        </div>
      </div>

      <div class="wform-row">
        <div class="wform-group">
          <label class="wform-label">4-Digit PIN</label>
          <input type="password" name="pin" class="wform-control pin-input" inputmode="numeric" maxlength="4" placeholder="····" required>
          <div class="wform-hint">Used to authorise transactions</div>
        </div>
        <div class="wform-group">
          <label class="wform-label">Confirm PIN</label>
          <input type="password" name="pin_confirm" class="wform-control pin-input" inputmode="numeric" maxlength="4" placeholder="····" required>
        </div>
      </div>

      <div class="mt-14">
        <button type="submit" class="wbtn wbtn-primary">Create Wallet <i class="fas fa-arrow-right"></i></button>
      </div>

      <p style="font-size:.72rem;color:#94a3b8;text-align:center;margin-top:12px">
        By signing up you agree to our Terms of Service and Privacy Policy.<br>
        <strong>Demo environment</strong> — no real money involved.
      </p>
    </form>
  </div>

  <div class="wauth-footer">
    Already have a wallet? <a href="<?= APP_URL ?>/wallet/login">Log in</a>
  </div>
  <div class="wauth-footer" style="margin-top:8px">
    <a href="<?= APP_URL ?>/wallet" style="color:rgba(255,255,255,.4)">← Back to wallet home</a>
  </div>

</div>
</body>
</html>
