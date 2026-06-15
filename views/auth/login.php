<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log In — OrbitPesa</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
  <style>
    body { display: flex; min-height: 100vh; background: var(--bg); }
    .auth-panel { display: flex; width: 100%; }
    .auth-left {
      width: 440px;
      flex-shrink: 0;
      background: var(--navy);
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 60px 48px;
      color: #fff;
    }
    .auth-left .logo { display:flex; align-items:center; gap:12px; margin-bottom:52px; }
    .auth-left .logo .mark { width:38px;height:38px;background:var(--green);border-radius:9px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;color:#fff; }
    .auth-left .logo span { font-size:1.3rem;font-weight:800;color:#fff; }
    .auth-left .logo span em { color:var(--green);font-style:normal; }
    .auth-left h2 { font-size:1.7rem;font-weight:800;color:#fff;margin-bottom:14px; }
    .auth-left p { color:rgba(255,255,255,.6);font-size:.9rem;line-height:1.7;margin-bottom:36px; }
    .auth-feature { display:flex;align-items:center;gap:12px;margin-bottom:16px; }
    .auth-feature .ic { width:36px;height:36px;background:rgba(21,131,71,.2);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#7dce9d;flex-shrink:0; }
    .auth-feature p { color:rgba(255,255,255,.7);font-size:.85rem;margin:0; }
    .auth-right { flex:1;display:flex;align-items:center;justify-content:center;padding:40px 20px; }
    .auth-form-wrap { width:100%;max-width:420px; }
    .auth-form-wrap h1 { font-size:1.7rem;font-weight:800;color:var(--navy);margin-bottom:6px; }
    .auth-form-wrap .subtitle { color:var(--text-muted);font-size:.9rem;margin-bottom:32px; }
    .auth-form-wrap .subtitle a { color:var(--green);font-weight:600; }
    .divider { display:flex;align-items:center;gap:12px;margin:24px 0;color:var(--text-light);font-size:.8rem; }
    .divider::before,.divider::after { content:'';flex:1;height:1px;background:var(--border); }
    @media(max-width:860px){ .auth-left{display:none} }
    @media(max-width:480px){ .auth-right{padding:24px 16px} }
  </style>
</head>
<body>
<div class="auth-panel">
  <!-- Left branding panel -->
  <div class="auth-left">
    <div class="logo">
      <div class="mark">OP</div>
      <span>Orbit<em>Pesa</em></span>
    </div>
    <h2>Welcome back to OrbitPesa</h2>
    <p>Kenya's leading payment gateway. Accept M-Pesa, cards, wallets and more with one API.</p>
    <div class="auth-feature">
      <div class="ic"><i class="fas fa-bolt"></i></div>
      <p>Real-time M-Pesa STK Push with instant webhook callbacks</p>
    </div>
    <div class="auth-feature">
      <div class="ic"><i class="fas fa-shield-alt"></i></div>
      <p>Bank-grade security with 256-bit TLS encryption</p>
    </div>
    <div class="auth-feature">
      <div class="ic"><i class="fas fa-chart-line"></i></div>
      <p>Live dashboard with real-time transaction analytics</p>
    </div>
  </div>

  <!-- Right form panel -->
  <div class="auth-right">
    <div class="auth-form-wrap">
      <h1>Log in to your account</h1>
      <p class="subtitle">Don't have an account? <a href="<?= APP_URL ?>/register">Create one free</a></p>

      <?php if ($error = flash('error')): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= sanitize($error) ?></div>
      <?php endif; ?>
      <?php if ($success = flash('success')): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= sanitize($success) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= APP_URL ?>/login" id="loginForm" novalidate>
        <?= csrf_field() ?>

        <div class="form-group">
          <label class="form-label" for="email">Email Address</label>
          <input type="email" class="form-control" id="email" name="email"
                 placeholder="you@business.com"
                 value="<?= sanitize($_POST['email'] ?? '') ?>"
                 required autocomplete="email">
        </div>

        <div class="form-group">
          <label class="form-label" for="password" style="display:flex;justify-content:space-between">
            Password
            <a href="<?= APP_URL ?>/forgot-password" style="font-weight:500;font-size:.8rem">Forgot password?</a>
          </label>
          <div style="position:relative">
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="Your password"
                   required autocomplete="current-password" style="padding-right:42px">
            <button type="button" data-reveal="password"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <div style="display:flex;align-items:center;gap:8px;margin-bottom:24px">
          <input type="checkbox" id="remember" name="remember" style="accent-color:var(--green)">
          <label for="remember" style="font-size:.875rem;color:var(--text-muted);cursor:pointer">Remember me for 30 days</label>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">
          <i class="fas fa-sign-in-alt"></i> Log In
        </button>
      </form>

      <div class="divider">or continue with</div>

      <a href="<?= APP_URL ?>/register" class="btn btn-outline-navy btn-block">
        <i class="fas fa-user-plus"></i> Create New Account
      </a>

      <p style="text-align:center;margin-top:28px;font-size:.78rem;color:var(--text-light)">
        By logging in you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
      </p>
    </div>
  </div>
</div>

<script src="<?= APP_URL ?>/assets/js/app.js"></script>
</body>
</html>
