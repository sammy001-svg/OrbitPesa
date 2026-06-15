<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Apply for M-Pesa Business Till / Paybill — OrbitPesa</title>
  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/landing.css">
  <style>
    .apply-hero{background:#0D1B3E;padding:80px 0 60px;text-align:center;color:#fff}
    .apply-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(21,131,71,.18);border:1px solid rgba(21,131,71,.35);color:#7dce9d;font-size:.78rem;font-weight:700;padding:6px 16px;border-radius:20px;margin-bottom:20px;letter-spacing:.04em}
    .apply-hero h1{font-size:2.4rem;font-weight:900;margin:0 0 12px;letter-spacing:-.03em;line-height:1.15}
    .apply-hero h1 span{color:#158347}
    .apply-hero p{font-size:1.05rem;color:rgba(255,255,255,.72);max-width:560px;margin:0 auto}
    .apply-section{padding:60px 0;background:#f8fafc}
    .apply-grid{display:grid;grid-template-columns:1fr 420px;gap:48px;align-items:start}
    @media(max-width:900px){.apply-grid{grid-template-columns:1fr}}
    .apply-benefits h2{font-size:1.5rem;font-weight:800;color:#0D1B3E;margin:0 0 8px}
    .apply-benefits p{color:#64748b;margin:0 0 28px;font-size:.95rem}
    .benefit-card{display:flex;gap:14px;margin-bottom:20px;align-items:flex-start}
    .benefit-icon{width:44px;height:44px;border-radius:12px;background:rgba(21,131,71,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#158347;font-size:1.1rem}
    .benefit-card h4{font-size:.95rem;font-weight:700;color:#0D1B3E;margin:0 0 3px}
    .benefit-card p{font-size:.83rem;color:#64748b;margin:0;line-height:1.5}
    .apply-form-card{background:#fff;border-radius:16px;padding:36px;box-shadow:0 4px 32px rgba(13,27,62,.08);border:1px solid #e8ecf4}
    .apply-form-card h3{font-size:1.2rem;font-weight:800;color:#0D1B3E;margin:0 0 4px}
    .apply-form-card .subtitle{font-size:.82rem;color:#64748b;margin:0 0 24px}
    .form-section-title{font-size:.7rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin:0 0 14px;padding-bottom:6px;border-bottom:1px solid #f1f5f9}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .form-group{margin-bottom:14px}
    .form-group label{display:block;font-size:.78rem;font-weight:700;color:#475569;margin-bottom:5px}
    .form-group input,.form-group select,.form-group textarea{width:100%;box-sizing:border-box;padding:10px 12px;border:1.5px solid #dde2ec;border-radius:8px;font-size:.9rem;font-family:inherit;color:#1e293b;background:#fff;transition:border .2s}
    .form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:#158347;box-shadow:0 0 0 3px rgba(21,131,71,.08)}
    .form-group textarea{resize:vertical;min-height:80px}
    .type-selector{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
    .type-option{position:relative;cursor:pointer}
    .type-option input{position:absolute;opacity:0;width:0;height:0}
    .type-option-label{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;padding:14px 10px;border:2px solid #dde2ec;border-radius:10px;font-size:.82rem;font-weight:700;color:#475569;transition:all .2s;text-align:center}
    .type-option-label i{font-size:1.4rem;color:#94a3b8;transition:color .2s}
    .type-option input:checked + .type-option-label{border-color:#158347;background:rgba(21,131,71,.06);color:#158347}
    .type-option input:checked + .type-option-label i{color:#158347}
    .apply-submit{width:100%;padding:13px;background:#158347;color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;font-family:inherit;margin-top:8px;transition:background .2s}
    .apply-submit:hover{background:#126d3c}
    .apply-alert{padding:12px 16px;border-radius:8px;font-size:.87rem;margin-bottom:16px;display:flex;align-items:center;gap:10px}
    .apply-alert.success{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
    .apply-alert.error{background:#fff1f2;border:1px solid #fecdd3;color:#9f1239}
    .steps-strip{background:#fff;border-top:1px solid #e8ecf4;border-bottom:1px solid #e8ecf4;padding:32px 0}
    .steps-inner{display:grid;grid-template-columns:repeat(4,1fr);gap:24px;text-align:center}
    @media(max-width:700px){.steps-inner{grid-template-columns:1fr 1fr}}
    .step-num{width:36px;height:36px;border-radius:50%;background:#158347;color:#fff;font-size:.9rem;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 10px}
    .step-title{font-size:.88rem;font-weight:700;color:#0D1B3E;margin:0 0 3px}
    .step-desc{font-size:.78rem;color:#64748b;margin:0}
    .required-note{font-size:.75rem;color:#94a3b8;margin:0 0 20px;text-align:right}
    .login-note{background:#f8fafc;border:1px solid #e8ecf4;border-radius:8px;padding:12px 14px;font-size:.8rem;color:#64748b;margin-bottom:16px;text-align:center}
    .login-note a{color:#158347;font-weight:700;text-decoration:none}
  </style>
</head>
<body>

<!-- Nav -->
<nav class="nav">
  <div class="container nav-inner">
    <a href="<?= APP_URL ?>/" class="nav-logo">Orbit<span>Pesa</span></a>
    <div class="nav-links">
      <a href="<?= APP_URL ?>/#features">Features</a>
      <a href="<?= APP_URL ?>/#pricing">Pricing</a>
      <a href="<?= APP_URL ?>/developers">Developers</a>
      <a href="<?= APP_URL ?>/#contact">Contact</a>
    </div>
    <div class="nav-cta">
      <?php if (is_logged_in()): ?>
        <a href="<?= APP_URL ?>/dashboard/mpesa-account" class="btn btn-outline">My Application</a>
        <a href="<?= APP_URL ?>/dashboard" class="btn btn-primary">Dashboard</a>
      <?php else: ?>
        <a href="<?= APP_URL ?>/login" class="btn btn-outline">Sign In</a>
        <a href="<?= APP_URL ?>/register" class="btn btn-primary">Get Started</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Hero -->
<section class="apply-hero">
  <div class="container">
    <div class="apply-hero-badge"><i class="fas fa-mobile-alt"></i> M-Pesa Business Account</div>
    <h1>Get Your Business <span>Till or Paybill</span></h1>
    <p>Apply for a dedicated M-Pesa till or paybill number and start collecting customer payments directly into your OrbitPesa wallet.</p>
  </div>
</section>

<!-- How it works -->
<div class="steps-strip">
  <div class="container">
    <div class="steps-inner">
      <div>
        <div class="step-num">1</div>
        <div class="step-title">Apply Online</div>
        <div class="step-desc">Fill the form with your business details</div>
      </div>
      <div>
        <div class="step-num">2</div>
        <div class="step-title">Verification</div>
        <div class="step-desc">Our team reviews your application within 2 days</div>
      </div>
      <div>
        <div class="step-num">3</div>
        <div class="step-title">Get Your Number</div>
        <div class="step-desc">Receive your unique till or paybill number</div>
      </div>
      <div>
        <div class="step-num">4</div>
        <div class="step-title">Collect Payments</div>
        <div class="step-desc">Funds land in your wallet instantly</div>
      </div>
    </div>
  </div>
</div>

<!-- Main -->
<section class="apply-section">
  <div class="container">
    <div class="apply-grid">

      <!-- Benefits -->
      <div class="apply-benefits">
        <h2>Why apply through OrbitPesa?</h2>
        <p>Get a fully managed M-Pesa business account with instant settlement to your wallet.</p>

        <div class="benefit-card">
          <div class="benefit-icon"><i class="fas fa-bolt"></i></div>
          <div>
            <h4>Instant Wallet Settlement</h4>
            <p>Every payment made to your till or paybill is credited to your OrbitPesa wallet immediately — no waiting periods.</p>
          </div>
        </div>
        <div class="benefit-card">
          <div class="benefit-icon"><i class="fas fa-chart-bar"></i></div>
          <div>
            <h4>Real-Time Analytics</h4>
            <p>Track every transaction in your dashboard. Filter by date, amount, or customer phone number.</p>
          </div>
        </div>
        <div class="benefit-card">
          <div class="benefit-icon"><i class="fas fa-satellite-dish"></i></div>
          <div>
            <h4>Webhook Notifications</h4>
            <p>Get instant payment callbacks to your server whenever a customer pays — perfect for automating order fulfilment.</p>
          </div>
        </div>
        <div class="benefit-card">
          <div class="benefit-icon"><i class="fas fa-exchange-alt"></i></div>
          <div>
            <h4>Flexible Withdrawals</h4>
            <p>Withdraw your balance to M-Pesa or bank account at any time, with same-day processing.</p>
          </div>
        </div>
        <div class="benefit-card">
          <div class="benefit-icon"><i class="fas fa-shield-alt"></i></div>
          <div>
            <h4>Fully Compliant</h4>
            <p>All accounts are registered and compliant with CBK and Safaricom regulations. Your business is protected.</p>
          </div>
        </div>

        <div style="margin-top:28px;padding:16px 20px;background:#fff;border:1px solid #e8ecf4;border-radius:12px">
          <div style="font-size:.78rem;font-weight:700;color:#0D1B3E;margin-bottom:8px">Processing fees</div>
          <div style="display:flex;justify-content:space-between;font-size:.82rem;color:#64748b;margin-bottom:4px">
            <span>Till number (Buy Goods)</span><span style="font-weight:700;color:#158347">0.5%</span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:.82rem;color:#64748b">
            <span>Paybill (Pay Bill)</span><span style="font-weight:700;color:#158347">0.5%</span>
          </div>
          <div style="font-size:.72rem;color:#94a3b8;margin-top:8px">Fee capped at KES 300 per transaction</div>
        </div>
      </div>

      <!-- Form -->
      <div class="apply-form-card">
        <h3>Business Account Application</h3>
        <p class="subtitle">Takes about 3 minutes. We'll review within 1–2 business days.</p>

        <?php if ($success = flash('mpesa_success')): ?>
          <div class="apply-alert success"><i class="fas fa-check-circle"></i> <?= sanitize($success) ?></div>
        <?php endif; ?>
        <?php if ($error = flash('mpesa_error')): ?>
          <div class="apply-alert error"><i class="fas fa-exclamation-circle"></i> <?= sanitize($error) ?></div>
        <?php endif; ?>

        <?php if (is_logged_in()): ?>
          <div class="login-note">Applying as <strong><?= sanitize(auth_user()['business_name'] ?? '') ?></strong> · <a href="<?= APP_URL ?>/dashboard/mpesa-account">View my application →</a></div>
        <?php else: ?>
          <div class="login-note">Already have an account? <a href="<?= APP_URL ?>/login">Sign in</a> to track your application</div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/apply-mpesa">
          <?= csrf_field() ?>

          <div class="form-section-title">Account Type</div>
          <div class="type-selector">
            <label class="type-option">
              <input type="radio" name="application_type" value="till" checked>
              <span class="type-option-label">
                <i class="fas fa-cash-register"></i>
                Till Number<br><span style="font-size:.7rem;font-weight:400;color:#94a3b8">Buy Goods</span>
              </span>
            </label>
            <label class="type-option">
              <input type="radio" name="application_type" value="paybill">
              <span class="type-option-label">
                <i class="fas fa-receipt"></i>
                Paybill<br><span style="font-size:.7rem;font-weight:400;color:#94a3b8">Pay Bill</span>
              </span>
            </label>
          </div>

          <div class="form-section-title">Business Details</div>
          <div class="form-group">
            <label>Business Name *</label>
            <input type="text" name="business_name" value="<?= sanitize(is_logged_in() ? (auth_user()['business_name'] ?? '') : '') ?>" placeholder="Your registered business name" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Business Type *</label>
              <select name="business_type" required>
                <option value="">Select type</option>
                <option value="retail">Retail / Shop</option>
                <option value="restaurant">Restaurant / Food</option>
                <option value="services">Services</option>
                <option value="transport">Transport</option>
                <option value="education">Education</option>
                <option value="healthcare">Healthcare</option>
                <option value="ngo">NGO / Church</option>
                <option value="ecommerce">E-Commerce</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="form-group">
              <label>Expected Monthly Volume</label>
              <select name="monthly_volume">
                <option value="under_50k">Under KES 50K</option>
                <option value="50k_200k">KES 50K – 200K</option>
                <option value="200k_1m" selected>KES 200K – 1M</option>
                <option value="over_1m">Over KES 1M</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Business Registration No. (optional)</label>
            <input type="text" name="business_reg_no" placeholder="e.g. BN/2023/001234">
          </div>

          <div class="form-section-title" style="margin-top:4px">Contact Information</div>
          <div class="form-row">
            <div class="form-group">
              <label>Contact Name *</label>
              <input type="text" name="contact_name" placeholder="Your full name" required>
            </div>
            <div class="form-group">
              <label>Contact Phone *</label>
              <input type="tel" name="contact_phone" value="<?= sanitize(is_logged_in() ? (auth_user()['phone'] ?? '') : '') ?>" placeholder="07XXXXXXXX" required>
            </div>
          </div>
          <div class="form-group">
            <label>Contact Email *</label>
            <input type="email" name="contact_email" value="<?= sanitize(is_logged_in() ? (auth_user()['email'] ?? '') : '') ?>" placeholder="you@business.com" required>
          </div>
          <div class="form-group">
            <label>Brief Business Description (optional)</label>
            <textarea name="description" placeholder="What products or services do you offer?"></textarea>
          </div>

          <button type="submit" class="apply-submit">
            <i class="fas fa-paper-plane"></i> Submit Application
          </button>
          <div style="font-size:.72rem;color:#94a3b8;text-align:center;margin-top:10px">
            By submitting you agree to our <a href="<?= APP_URL ?>/#terms" style="color:#158347">Terms of Service</a>
          </div>
        </form>
      </div>

    </div>
  </div>
</section>

<!-- Footer -->
<footer class="footer">
  <div class="container footer-inner">
    <div>
      <div class="footer-logo">Orbit<span>Pesa</span></div>
      <p style="color:rgba(255,255,255,.45);font-size:.85rem;margin-top:8px">Kenya's modern payment gateway</p>
    </div>
    <div style="font-size:.82rem;color:rgba(255,255,255,.45)">
      &copy; <?= date('Y') ?> OrbitPesa Ltd. All rights reserved.
    </div>
  </div>
</footer>

<script>
document.querySelectorAll('.type-option input').forEach(radio => {
  radio.addEventListener('change', () => {
    document.querySelectorAll('.type-option-label').forEach(l => {
      l.style.borderColor = '';
      l.style.background  = '';
    });
  });
});
</script>
</body>
</html>
