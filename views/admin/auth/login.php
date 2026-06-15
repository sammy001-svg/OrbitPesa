<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login — <?= APP_NAME ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
  <style>
    body { background: var(--bg); display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
    .admin-login-wrap {
      display:grid; grid-template-columns:1fr 1fr; max-width:900px; width:100%;
      border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(13,27,62,.18);
    }
    .admin-login-left {
      background:var(--navy); color:#fff; padding:48px 40px; display:flex; flex-direction:column; justify-content:space-between;
    }
    .admin-login-right { background:#fff; padding:48px 40px; }
    .admin-brand { display:flex; align-items:center; gap:12px; }
    .admin-brand-mark {
      width:44px; height:44px; background:var(--green); border-radius:10px;
      display:flex; align-items:center; justify-content:center;
      font-weight:800; font-size:1rem; color:#fff; letter-spacing:-.5px;
    }
    .admin-brand-name { font-size:1.25rem; font-weight:800; }
    .admin-brand-sub  { font-size:.72rem; color:rgba(255,255,255,.5); letter-spacing:.08em; text-transform:uppercase; }
    .admin-login-tagline { font-size:1.4rem; font-weight:700; margin-bottom:10px; line-height:1.35; }
    .admin-login-desc { color:rgba(255,255,255,.65); font-size:.875rem; line-height:1.6; }
    .admin-warning {
      background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1);
      border-radius:10px; padding:14px 16px; font-size:.8rem; color:rgba(255,255,255,.7);
    }
    .admin-warning i { color:var(--green); margin-right:6px; }
    .login-title { font-size:1.35rem; font-weight:800; color:var(--navy); margin-bottom:4px; }
    .login-sub   { font-size:.875rem; color:var(--text-muted); margin-bottom:28px; }
    @media(max-width:650px){
      .admin-login-wrap { grid-template-columns:1fr; }
      .admin-login-left { display:none; }
    }
  </style>
</head>
<body>
<div class="admin-login-wrap">
  <!-- Left branding -->
  <div class="admin-login-left">
    <div>
      <div class="admin-brand" style="margin-bottom:40px">
        <div class="admin-brand-mark">OP</div>
        <div>
          <div class="admin-brand-name"><?= APP_NAME ?></div>
          <div class="admin-brand-sub">Admin Console</div>
        </div>
      </div>
      <div class="admin-login-tagline">Platform Administration &amp; Control Centre</div>
      <p class="admin-login-desc">
        Manage merchants, verify KYC documents, configure fees, process withdrawals, and oversee all system activity from one place.
      </p>
    </div>
    <div class="admin-warning">
      <i class="fas fa-shield-alt"></i>
      Restricted area. This portal is for authorised OrbitPesa staff only. Unauthorised access attempts are logged.
    </div>
  </div>

  <!-- Right form -->
  <div class="admin-login-right">
    <?php if ($msg = flash('success')): ?>
      <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= sanitize($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash('error')): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= sanitize($msg) ?></div>
    <?php endif; ?>
    <p class="login-title">Sign in as Admin</p>
    <p class="login-sub">Enter your administrator credentials below.</p>

    <form method="POST" action="<?= APP_URL ?>/admin/login" autocomplete="on">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-group">
          <span class="input-addon"><i class="fas fa-at"></i></span>
          <input type="email" id="email" name="email" class="form-control"
                 placeholder="admin@orbitpesa.com" required autocomplete="username"
                 value="<?= sanitize($_POST['email'] ?? '') ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-group">
          <span class="input-addon"><i class="fas fa-lock"></i></span>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="••••••••" required autocomplete="current-password">
          <button type="button" class="input-addon btn-password-toggle" data-target="password"
                  style="cursor:pointer;background:none;border:none;color:var(--text-muted)">
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px;font-size:.95rem">
        <i class="fas fa-sign-in-alt"></i> Sign In to Admin Console
      </button>
    </form>

    <div style="margin-top:24px;text-align:center">
      <a href="<?= APP_URL ?>/" style="font-size:.82rem;color:var(--text-muted);text-decoration:none">
        <i class="fas fa-arrow-left"></i> Back to merchant portal
      </a>
    </div>
  </div>
</div>
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
</body>
</html>
