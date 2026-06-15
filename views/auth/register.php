<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account — OrbitPesa</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
  <style>
    body { display:flex;min-height:100vh;background:var(--bg); }
    .auth-panel { display:flex;width:100%; }
    .auth-left {
      width:440px;flex-shrink:0;background:var(--navy);
      display:flex;flex-direction:column;justify-content:center;
      padding:60px 48px;color:#fff;
    }
    .auth-left .logo{display:flex;align-items:center;gap:12px;margin-bottom:48px;}
    .auth-left .logo .mark{width:38px;height:38px;background:var(--green);border-radius:9px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;color:#fff;}
    .auth-left .logo span{font-size:1.3rem;font-weight:800;color:#fff;}
    .auth-left .logo span em{color:var(--green);font-style:normal;}
    .step-list{display:flex;flex-direction:column;gap:20px;}
    .step-item-reg{display:flex;align-items:flex-start;gap:14px;}
    .step-num-reg{width:32px;height:32px;background:rgba(21,131,71,.25);border:1.5px solid rgba(21,131,71,.4);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;color:#7dce9d;flex-shrink:0;}
    .step-item-reg div h4{font-size:.9rem;font-weight:700;color:#fff;margin-bottom:3px;}
    .step-item-reg div p{font-size:.8rem;color:rgba(255,255,255,.55);margin:0;}
    .auth-right{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 20px;}
    .auth-form-wrap{width:100%;max-width:460px;}
    .auth-form-wrap h1{font-size:1.7rem;font-weight:800;color:var(--navy);margin-bottom:6px;}
    .auth-form-wrap .subtitle{color:var(--text-muted);font-size:.9rem;margin-bottom:28px;}
    .auth-form-wrap .subtitle a{color:var(--green);font-weight:600;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .strength-bar{height:4px;background:var(--border);border-radius:2px;margin-top:6px;overflow:hidden;}
    .strength-fill{height:100%;border-radius:2px;transition:width .3s,background .3s;}
    @media(max-width:860px){.auth-left{display:none}}
    @media(max-width:520px){.form-row{grid-template-columns:1fr}}
    @media(max-width:480px){.auth-right{padding:24px 16px}}
  </style>
</head>
<body>
<div class="auth-panel">
  <div class="auth-left">
    <div class="logo">
      <div class="mark">OP</div>
      <span>Orbit<em>Pesa</em></span>
    </div>
    <h2 style="font-size:1.6rem;font-weight:800;color:#fff;margin-bottom:12px">Start accepting payments today</h2>
    <p style="color:rgba(255,255,255,.6);font-size:.88rem;line-height:1.7;margin-bottom:36px">Free to start. No monthly fees. Go live in under 10 minutes.</p>
    <div class="step-list">
      <div class="step-item-reg">
        <div class="step-num-reg">1</div>
        <div>
          <h4>Create your free account</h4>
          <p>Takes less than 2 minutes. No credit card required.</p>
        </div>
      </div>
      <div class="step-item-reg">
        <div class="step-num-reg">2</div>
        <div>
          <h4>Get your API keys</h4>
          <p>Test keys available immediately. Live keys after quick KYC.</p>
        </div>
      </div>
      <div class="step-item-reg">
        <div class="step-num-reg">3</div>
        <div>
          <h4>Integrate & go live</h4>
          <p>One API, multiple channels. Full docs and sandbox included.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-form-wrap">
      <h1>Create your account</h1>
      <p class="subtitle">Already have an account? <a href="<?= APP_URL ?>/login">Log in here</a></p>

      <?php if ($error = flash('error')): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= sanitize($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= APP_URL ?>/register" id="registerForm" novalidate>
        <?= csrf_field() ?>

        <div class="form-group">
          <label class="form-label" for="business_name">Business / Personal Name</label>
          <input type="text" class="form-control" id="business_name" name="business_name"
                 placeholder="Acme Ltd or Your Name"
                 value="<?= sanitize($_POST['business_name'] ?? '') ?>"
                 required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="email">Email Address</label>
            <input type="email" class="form-control" id="email" name="email"
                   placeholder="you@business.com"
                   value="<?= sanitize($_POST['email'] ?? '') ?>"
                   required autocomplete="email">
          </div>
          <div class="form-group">
            <label class="form-label" for="phone">Phone Number</label>
            <input type="tel" class="form-control" id="phone" name="phone"
                   placeholder="0712345678"
                   value="<?= sanitize($_POST['phone'] ?? '') ?>"
                   required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="account_type">Account Type</label>
          <select class="form-control form-select" name="account_type" id="account_type">
            <option value="business" <?= ($_POST['account_type'] ?? '') === 'business' ? 'selected' : '' ?>>Business Account</option>
            <option value="personal" <?= ($_POST['account_type'] ?? '') === 'personal' ? 'selected' : '' ?>>Personal Account</option>
          </select>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div style="position:relative">
              <input type="password" class="form-control" id="password" name="password"
                     placeholder="Create password"
                     required autocomplete="new-password" style="padding-right:42px"
                     oninput="checkStrength(this.value)">
              <button type="button" data-reveal="password"
                      style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            <div class="strength-bar"><div class="strength-fill" id="strengthFill" style="width:0"></div></div>
            <div class="form-hint" id="strengthLabel">Enter a password</div>
          </div>
          <div class="form-group">
            <label class="form-label" for="password_confirm">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                   placeholder="Repeat password"
                   required autocomplete="new-password">
          </div>
        </div>

        <div style="display:flex;align-items:flex-start;gap:8px;margin-bottom:24px">
          <input type="checkbox" id="agree" name="agree" required style="accent-color:var(--green);margin-top:3px">
          <label for="agree" style="font-size:.845rem;color:var(--text-muted);cursor:pointer">
            I agree to OrbitPesa's <a href="#" style="color:var(--green)">Terms of Service</a>,
            <a href="#" style="color:var(--green)">Privacy Policy</a>, and
            <a href="#" style="color:var(--green)">AML Policy</a>.
          </label>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">
          <i class="fas fa-user-plus"></i> Create Free Account
        </button>
      </form>

      <p style="text-align:center;margin-top:20px;font-size:.8rem;color:var(--text-light)">
        <i class="fas fa-shield-alt" style="color:var(--green)"></i>
        Your data is encrypted and secure.
      </p>
    </div>
  </div>
</div>

<script src="<?= APP_URL ?>/assets/js/app.js"></script>
<script>
function checkStrength(val) {
  const fill = document.getElementById('strengthFill');
  const label = document.getElementById('strengthLabel');
  let score = 0;
  if (val.length >= 8) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const levels = [
    { w: '0%',   c: 'transparent', t: 'Enter a password' },
    { w: '25%',  c: '#dc2626',     t: 'Weak' },
    { w: '50%',  c: '#d97706',     t: 'Fair' },
    { w: '75%',  c: '#2563eb',     t: 'Good' },
    { w: '100%', c: '#158347',     t: 'Strong' },
  ];
  const l = levels[score];
  fill.style.width = l.w;
  fill.style.background = l.c;
  label.textContent = l.t;
  label.style.color = l.c;
}

document.getElementById('registerForm').addEventListener('submit', function(e) {
  const p = document.getElementById('password').value;
  const c = document.getElementById('password_confirm').value;
  if (p !== c) {
    e.preventDefault();
    alert('Passwords do not match.');
    return;
  }
  if (!document.getElementById('agree').checked) {
    e.preventDefault();
    alert('Please agree to the Terms of Service.');
    return;
  }
  document.getElementById('submitBtn').disabled = true;
  document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
});
</script>
</body>
</html>
