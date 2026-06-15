<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OrbitPesa Wallet — Send, Receive & Pay with ease</title>
  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/wallet.css">
</head>
<body class="wallet-landing-body">

  <!-- Hero -->
  <section class="wl-hero">
    <div class="wl-logo">Orbit<span>Pesa</span> Wallet</div>
    <div class="wl-tagline">Your money,<br>always moving.</div>
    <p class="wl-sub">Send money, buy airtime, pay bills and transfer to any bank — all from one simple wallet.</p>
    <div class="wl-ctas">
      <a href="<?= APP_URL ?>/wallet/register" class="wl-cta-primary">
        <i class="fas fa-wallet"></i> Create Wallet
      </a>
      <a href="<?= APP_URL ?>/wallet/login" class="wl-cta-ghost">
        Log In
      </a>
    </div>
  </section>

  <!-- Features -->
  <section class="wl-features">
    <h2 class="wl-features-title">Everything you need</h2>
    <p class="wl-features-sub">Built for Kenya, designed for everyone</p>
    <div class="wl-feature-grid">
      <div class="wl-feature-card">
        <div class="wl-feature-icon" style="background:#f97316"><i class="fas fa-paper-plane"></i></div>
        <div class="wl-feature-title">Send Money</div>
        <div class="wl-feature-desc">Instantly transfer to any OrbitPesa wallet using their ID, phone, or email.</div>
      </div>
      <div class="wl-feature-card">
        <div class="wl-feature-icon" style="background:#158347"><i class="fas fa-arrow-down"></i></div>
        <div class="wl-feature-title">Receive Money</div>
        <div class="wl-feature-desc">Share your unique wallet ID to receive payments from anyone, anywhere.</div>
      </div>
      <div class="wl-feature-card">
        <div class="wl-feature-icon" style="background:#3b82f6"><i class="fas fa-mobile-alt"></i></div>
        <div class="wl-feature-title">Buy Airtime & Data</div>
        <div class="wl-feature-desc">Top up Safaricom, Airtel, Telkom, and Faiba instantly from your wallet.</div>
      </div>
      <div class="wl-feature-card">
        <div class="wl-feature-icon" style="background:#f59e0b"><i class="fas fa-file-invoice"></i></div>
        <div class="wl-feature-title">Pay Paybills</div>
        <div class="wl-feature-desc">Pay KPLC, Nairobi Water, DSTV, and thousands of other paybills.</div>
      </div>
      <div class="wl-feature-card">
        <div class="wl-feature-icon" style="background:#158347"><i class="fas fa-money-bill-wave"></i></div>
        <div class="wl-feature-title">M-Pesa Transfer</div>
        <div class="wl-feature-desc">Send money directly to any M-Pesa number with a simple transaction.</div>
      </div>
      <div class="wl-feature-card">
        <div class="wl-feature-icon" style="background:#0D1B3E"><i class="fas fa-university"></i></div>
        <div class="wl-feature-title">Bank Transfer</div>
        <div class="wl-feature-desc">Transfer funds to any Kenyan bank account quickly and securely.</div>
      </div>
    </div>
  </section>

  <!-- How it works -->
  <section style="background:#0D1B3E;padding:60px 24px;text-align:center">
    <h2 style="font-size:1.5rem;font-weight:800;color:white;margin-bottom:8px">Get started in minutes</h2>
    <p style="color:rgba(255,255,255,.5);margin-bottom:40px;font-size:.9rem">No paperwork. No queues. Just sign up and go.</p>
    <div style="display:flex;gap:24px;justify-content:center;flex-wrap:wrap;max-width:700px;margin:0 auto">
      <div style="text-align:center;flex:1;min-width:160px">
        <div style="width:56px;height:56px;border-radius:50%;background:rgba(21,131,71,.2);border:2px solid #158347;color:#7dce9d;font-size:1.3rem;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">1</div>
        <div style="font-weight:700;color:white;margin-bottom:6px;font-size:.9rem">Create Account</div>
        <div style="font-size:.78rem;color:rgba(255,255,255,.4)">Sign up with your National ID or email address</div>
      </div>
      <div style="text-align:center;flex:1;min-width:160px">
        <div style="width:56px;height:56px;border-radius:50%;background:rgba(21,131,71,.2);border:2px solid #158347;color:#7dce9d;font-size:1.3rem;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">2</div>
        <div style="font-weight:700;color:white;margin-bottom:6px;font-size:.9rem">Get Your Wallet</div>
        <div style="font-size:.78rem;color:rgba(255,255,255,.4)">Receive a unique OrbitPesa wallet ID instantly</div>
      </div>
      <div style="text-align:center;flex:1;min-width:160px">
        <div style="width:56px;height:56px;border-radius:50%;background:rgba(21,131,71,.2);border:2px solid #158347;color:#7dce9d;font-size:1.3rem;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">3</div>
        <div style="font-weight:700;color:white;margin-bottom:6px;font-size:.9rem">Start Transacting</div>
        <div style="font-size:.78rem;color:rgba(255,255,255,.4)">Send, receive and pay from anywhere</div>
      </div>
    </div>
    <div style="margin-top:40px">
      <a href="<?= APP_URL ?>/wallet/register" style="display:inline-block;padding:16px 40px;background:#158347;color:white;border-radius:14px;text-decoration:none;font-weight:700;font-size:1rem;transition:background .15s">
        Open Your Free Wallet
      </a>
    </div>
  </section>

  <footer class="wl-footer">
    <p style="margin-bottom:12px">
      <a href="<?= APP_URL ?>/">OrbitPesa Merchant</a> &nbsp;·&nbsp;
      <a href="<?= APP_URL ?>/apply-mpesa">Apply for Till/Paybill</a> &nbsp;·&nbsp;
      <a href="<?= APP_URL ?>/developers/docs">API Docs</a>
    </p>
    <p>&copy; <?= date('Y') ?> OrbitPesa Ltd. All rights reserved. &nbsp;|&nbsp; Sandbox / Demo Environment</p>
  </footer>

</body>
</html>
