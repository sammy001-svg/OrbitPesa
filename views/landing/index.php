<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="OrbitPesa — Kenya's most developer-friendly payment gateway. Accept M-Pesa, cards, wallet payments and more with one simple API.">
  <title>OrbitPesa — Kenya's Leading Payment Gateway</title>
  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/landing.css">
</head>
<body>

<!-- ============================================================ NAVBAR ============================================================ -->
<nav class="lp-nav">
  <a href="<?= APP_URL ?>/" class="lp-logo">
    <div class="mark">OP</div>
    <span class="brand">Orbit<span>Pesa</span></span>
  </a>

  <ul class="lp-nav-links">
    <li><a href="#features">Features</a></li>
    <li><a href="#channels">Payment Channels</a></li>
    <li><a href="#pricing">Pricing</a></li>
    <li><a href="<?= APP_URL ?>/developers">Developers</a></li>
    <li><a href="<?= APP_URL ?>/developers/docs">Documentation</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><a href="<?= APP_URL ?>/wallet" style="color:#158347;font-weight:700"><i class="fas fa-wallet"></i> Wallet</a></li>
  </ul>

  <div class="lp-nav-cta">
    <a href="<?= APP_URL ?>/login" class="btn-nav-login">Log In</a>
    <a href="<?= APP_URL ?>/register" class="btn-nav-signup">Get Started Free</a>
  </div>

  <div class="hamburger" id="hamburger" aria-label="Open menu">
    <span></span><span></span><span></span>
  </div>
</nav>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
  <div class="mobile-menu-header">
    <a href="<?= APP_URL ?>/" class="lp-logo">
      <div class="mark">OP</div>
      <span class="brand">Orbit<span>Pesa</span></span>
    </a>
    <button id="mobileClose" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--navy)">
      <i class="fas fa-times"></i>
    </button>
  </div>
  <ul>
    <li><a href="#features">Features</a></li>
    <li><a href="#channels">Payment Channels</a></li>
    <li><a href="#pricing">Pricing</a></li>
    <li><a href="<?= APP_URL ?>/developers">Developers</a></li>
    <li><a href="<?= APP_URL ?>/developers/docs">Documentation</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
  <div class="mobile-menu-footer">
    <a href="<?= APP_URL ?>/login" class="btn-nav-login" style="text-align:center">Log In</a>
    <a href="<?= APP_URL ?>/register" class="btn-nav-signup" style="text-align:center;display:block;padding:12px">Get Started Free</a>
  </div>
</div>

<!-- ============================================================ HERO ============================================================ -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-badge">
      <i class="fas fa-bolt"></i> Trusted by 5,000+ Kenyan Businesses
    </div>
    <h1>Accept Payments<br>the <span class="accent">Smart Way</span></h1>
    <p>
      OrbitPesa is Kenya's most powerful payment gateway. One API, multiple payment channels — M-Pesa STK Push, cards, wallets, payment links, and more. Go live in minutes.
    </p>
    <div class="hero-actions">
      <a href="<?= APP_URL ?>/register" class="btn-hero-primary">
        <i class="fas fa-rocket"></i> Create Free Account
      </a>
      <a href="<?= APP_URL ?>/developers/docs" class="btn-hero-outline">
        <i class="fas fa-code"></i> View API Docs
      </a>
    </div>
  </div>

  <div class="hero-visual">
    <div class="dashboard-preview">
      <div class="preview-bar">
        <div class="preview-dot"></div>
        <div class="preview-dot"></div>
        <div class="preview-dot"></div>
        <span style="font-size:.75rem;color:rgba(255,255,255,.35);margin-left:8px">OrbitPesa Dashboard</span>
      </div>
      <div class="preview-stats">
        <div class="preview-stat">
          <div class="preview-stat-val" data-target="1420500" data-suffix="">KES 1.4M</div>
          <div class="preview-stat-lbl">Total Received</div>
        </div>
        <div class="preview-stat">
          <div class="preview-stat-val">KES 24,850</div>
          <div class="preview-stat-lbl">Wallet Balance</div>
        </div>
      </div>
      <div style="font-size:.75rem;color:rgba(255,255,255,.4);margin-bottom:8px;padding-left:2px">Recent Transactions</div>
      <div class="preview-txn-list">
        <div class="preview-txn">
          <div>
            <div class="preview-txn-name">John Doe — M-Pesa</div>
            <div style="font-size:.7rem;color:rgba(255,255,255,.3)">2 mins ago</div>
          </div>
          <div style="text-align:right">
            <div class="preview-txn-amount">+KES 3,500</div>
            <div class="preview-txn-badge">Completed</div>
          </div>
        </div>
        <div class="preview-txn">
          <div>
            <div class="preview-txn-name">Sarah W. — Card</div>
            <div style="font-size:.7rem;color:rgba(255,255,255,.3)">15 mins ago</div>
          </div>
          <div style="text-align:right">
            <div class="preview-txn-amount">+KES 12,000</div>
            <div class="preview-txn-badge">Completed</div>
          </div>
        </div>
        <div class="preview-txn">
          <div>
            <div class="preview-txn-name">Payment Link — Web</div>
            <div style="font-size:.7rem;color:rgba(255,255,255,.3)">1 hr ago</div>
          </div>
          <div style="text-align:right">
            <div class="preview-txn-amount">+KES 850</div>
            <div class="preview-txn-badge">Completed</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================ STATS BAR ============================================================ -->
<div class="stats-bar">
  <div class="stat-item fade-in">
    <div class="value" data-target="5000" data-suffix="+">5000+</div>
    <div class="label">Active Merchants</div>
  </div>
  <div class="stat-item fade-in">
    <div class="value" data-target="2.5" data-suffix="B+">KES 2.5B+</div>
    <div class="label">Processed Monthly</div>
  </div>
  <div class="stat-item fade-in">
    <div class="value" data-target="99.9" data-suffix="%">99.9%</div>
    <div class="label">Uptime SLA</div>
  </div>
  <div class="stat-item fade-in">
    <div class="value" data-target="3" data-suffix="s">3s</div>
    <div class="label">M-Pesa Push Speed</div>
  </div>
</div>

<!-- ============================================================ FEATURES ============================================================ -->
<section class="section" id="features">
  <div class="section-center">
    <div class="section-tag"><i class="fas fa-star"></i> Features</div>
    <h2 class="section-title">Everything You Need to<br>Collect Payments in Kenya</h2>
    <p class="section-subtitle">Built for developers, loved by businesses. OrbitPesa gives you the tools to accept any payment, anywhere, anytime.</p>
  </div>
  <div class="features-grid">
    <div class="feature-card fade-in">
      <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
      <h3>M-Pesa STK Push</h3>
      <p>Trigger M-Pesa payment prompts directly to your customers' phones. Real-time confirmation with callback webhooks.</p>
    </div>
    <div class="feature-card fade-in">
      <div class="feature-icon"><i class="fas fa-credit-card"></i></div>
      <h3>Card Payments</h3>
      <p>Accept Visa and Mastercard payments with PCI-DSS compliant processing. 3D Secure authentication built in.</p>
    </div>
    <div class="feature-card fade-in">
      <div class="feature-icon"><i class="fas fa-wallet"></i></div>
      <h3>Wallet System</h3>
      <p>Embedded wallet for merchants. Hold funds, split payments, instant wallet-to-wallet transfers.</p>
    </div>
    <div class="feature-card fade-in">
      <div class="feature-icon"><i class="fas fa-link"></i></div>
      <h3>Payment Links</h3>
      <p>Create shareable payment links in seconds. No code needed — perfect for invoicing and social commerce.</p>
    </div>
    <div class="feature-card fade-in">
      <div class="feature-icon"><i class="fas fa-code"></i></div>
      <h3>Developer-First API</h3>
      <p>RESTful API with comprehensive docs, sandbox environment, code examples in PHP, JS, Python and more.</p>
    </div>
    <div class="feature-card fade-in">
      <div class="feature-icon"><i class="fas fa-bell"></i></div>
      <h3>Instant Webhooks</h3>
      <p>Get notified the moment a payment lands. Configure webhook endpoints and retry logic from your dashboard.</p>
    </div>
    <div class="feature-card fade-in">
      <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
      <h3>Bank-Grade Security</h3>
      <p>256-bit TLS, tokenized card data, HMAC webhook signatures, and IP allowlisting keep your funds safe.</p>
    </div>
    <div class="feature-card fade-in">
      <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
      <h3>Real-Time Analytics</h3>
      <p>Live transaction dashboard, revenue charts, daily/weekly/monthly reports. Export to CSV any time.</p>
    </div>
    <div class="feature-card fade-in">
      <div class="feature-icon"><i class="fas fa-money-bill-wave"></i></div>
      <h3>Instant Withdrawals</h3>
      <p>Withdraw to M-Pesa or bank account instantly. Automated settlement with full audit trail.</p>
    </div>
  </div>
</section>

<!-- ============================================================ HOW IT WORKS ============================================================ -->
<section class="section section-alt">
  <div class="section-center">
    <div class="section-tag"><i class="fas fa-list-ol"></i> How It Works</div>
    <h2 class="section-title">Go Live in 4 Simple Steps</h2>
    <p class="section-subtitle">From signup to processing your first payment in under 10 minutes.</p>
  </div>
  <div class="steps">
    <div class="step-item fade-in">
      <div class="step-num">1</div>
      <h4>Create Account</h4>
      <p>Sign up with your business email. No paperwork, no hidden fees.</p>
    </div>
    <div class="step-item fade-in">
      <div class="step-num" style="background:var(--green)">2</div>
      <h4>Get API Keys</h4>
      <p>Grab your test and live API keys from the developer console.</p>
    </div>
    <div class="step-item fade-in">
      <div class="step-num">3</div>
      <h4>Integrate</h4>
      <p>Use our documented API or SDK to add payments to your app. Takes minutes.</p>
    </div>
    <div class="step-item fade-in">
      <div class="step-num" style="background:var(--green)">4</div>
      <h4>Go Live</h4>
      <p>Switch to live mode and start collecting real payments from day one.</p>
    </div>
  </div>
</section>

<!-- ============================================================ PAYMENT CHANNELS ============================================================ -->
<section class="section" id="channels">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center">
    <div>
      <div class="section-tag"><i class="fas fa-exchange-alt"></i> Payment Channels</div>
      <h2 class="section-title">One Platform,<br>Every Payment Method</h2>
      <p style="color:var(--text-muted);font-size:1rem;line-height:1.7;margin-bottom:32px">
        Your customers pay how they want. OrbitPesa supports all major payment methods used in Kenya — from M-Pesa to international cards.
      </p>
      <a href="<?= APP_URL ?>/register" class="btn-nav-signup" style="padding:12px 28px;font-size:.95rem;border-radius:8px">
        Start Accepting Payments
      </a>
    </div>
    <div class="channels-grid">
      <div class="lp-channel fade-in">
        <div class="lp-channel-icon green"><i class="fas fa-mobile-alt"></i></div>
        <div>
          <h4>M-Pesa STK Push</h4>
          <p>Direct push to customer phone. Instant confirmation.</p>
        </div>
      </div>
      <div class="lp-channel fade-in">
        <div class="lp-channel-icon"><i class="fas fa-credit-card"></i></div>
        <div>
          <h4>Visa / Mastercard</h4>
          <p>Debit and credit cards, international payments.</p>
        </div>
      </div>
      <div class="lp-channel fade-in">
        <div class="lp-channel-icon green"><i class="fas fa-wallet"></i></div>
        <div>
          <h4>OrbitPesa Wallet</h4>
          <p>Instant wallet payments. Zero transaction fee.</p>
        </div>
      </div>
      <div class="lp-channel fade-in">
        <div class="lp-channel-icon"><i class="fas fa-link"></i></div>
        <div>
          <h4>Payment Links</h4>
          <p>Shareable links. No code required.</p>
        </div>
      </div>
      <div class="lp-channel fade-in">
        <div class="lp-channel-icon green"><i class="fas fa-university"></i></div>
        <div>
          <h4>Bank Transfer</h4>
          <p>Direct bank payments via Pesalink and EFT.</p>
        </div>
      </div>
      <div class="lp-channel fade-in">
        <div class="lp-channel-icon"><i class="fas fa-store"></i></div>
        <div>
          <h4>USSD Payments</h4>
          <p>Reach customers on any phone, anywhere.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================ PRICING ============================================================ -->
<section class="section section-alt" id="pricing">
  <div class="section-center">
    <div class="section-tag"><i class="fas fa-tag"></i> Pricing</div>
    <h2 class="section-title">Simple, Transparent Pricing</h2>
    <p class="section-subtitle">No monthly fees. No hidden charges. Pay only for what you process.</p>
  </div>
  <div class="pricing-grid">
    <div class="pricing-card fade-in">
      <div class="pricing-name">Starter</div>
      <div class="pricing-rate">1.5%<span> + KES 5</span></div>
      <div class="pricing-desc">Per M-Pesa transaction</div>
      <ul class="pricing-features">
        <li><i class="fas fa-check"></i> M-Pesa STK Push</li>
        <li><i class="fas fa-check"></i> Payment Links</li>
        <li><i class="fas fa-check"></i> Wallet Payments</li>
        <li><i class="fas fa-check"></i> Basic Dashboard</li>
        <li><i class="fas fa-check"></i> Email Support</li>
      </ul>
      <a href="<?= APP_URL ?>/register" class="btn-nav-signup" style="display:block;text-align:center;padding:12px 20px;border-radius:8px">Get Started</a>
    </div>

    <div class="pricing-card featured fade-in">
      <div class="pricing-badge">Most Popular</div>
      <div class="pricing-name">Business</div>
      <div class="pricing-rate">1.2%<span> + KES 3</span></div>
      <div class="pricing-desc">Per M-Pesa + card transaction</div>
      <ul class="pricing-features">
        <li><i class="fas fa-check"></i> All Starter features</li>
        <li><i class="fas fa-check"></i> Visa / Mastercard (2.9%)</li>
        <li><i class="fas fa-check"></i> Advanced Analytics</li>
        <li><i class="fas fa-check"></i> Webhooks & API</li>
        <li><i class="fas fa-check"></i> Priority Support</li>
        <li><i class="fas fa-check"></i> Custom Payment Pages</li>
      </ul>
      <a href="<?= APP_URL ?>/register" class="btn-nav-signup" style="display:block;text-align:center;padding:12px 20px;border-radius:8px">Get Started</a>
    </div>

    <div class="pricing-card fade-in">
      <div class="pricing-name">Enterprise</div>
      <div class="pricing-rate" style="font-size:2rem">Custom</div>
      <div class="pricing-desc">Volume-based pricing</div>
      <ul class="pricing-features">
        <li><i class="fas fa-check"></i> All Business features</li>
        <li><i class="fas fa-check"></i> Negotiated rates</li>
        <li><i class="fas fa-check"></i> Dedicated manager</li>
        <li><i class="fas fa-check"></i> SLA guarantee</li>
        <li><i class="fas fa-check"></i> White-label option</li>
        <li><i class="fas fa-check"></i> 24/7 phone support</li>
      </ul>
      <a href="mailto:enterprise@orbitpesa.com" class="btn-nav-login" style="display:block;text-align:center;padding:12px 20px;border-radius:8px">Contact Sales</a>
    </div>
  </div>
</section>

<!-- ============================================================ DEVELOPER SECTION ============================================================ -->
<section class="section">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center">
    <div>
      <div class="section-tag"><i class="fas fa-terminal"></i> Developer-First</div>
      <h2 class="section-title">Integrate in Minutes,<br>Not Days</h2>
      <p style="color:var(--text-muted);font-size:1rem;line-height:1.7;margin-bottom:24px">
        Our RESTful API is clean, consistent, and fully documented. From sandbox to production with a single config change.
      </p>
      <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:32px">
        <div style="display:flex;align-items:center;gap:10px;font-size:.9rem">
          <i class="fas fa-check-circle" style="color:var(--green)"></i>
          <span>Comprehensive API documentation</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;font-size:.9rem">
          <i class="fas fa-check-circle" style="color:var(--green)"></i>
          <span>Sandbox environment with test cards & M-Pesa</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;font-size:.9rem">
          <i class="fas fa-check-circle" style="color:var(--green)"></i>
          <span>Code examples in PHP, JavaScript, Python</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;font-size:.9rem">
          <i class="fas fa-check-circle" style="color:var(--green)"></i>
          <span>Interactive API playground in browser</span>
        </div>
      </div>
      <a href="<?= APP_URL ?>/developers/docs" class="btn-nav-signup" style="padding:12px 28px;font-size:.95rem;border-radius:8px">
        <i class="fas fa-book" style="margin-right:6px"></i> Read the Docs
      </a>
    </div>
    <div>
      <div class="code-block" style="position:relative">
        <button class="copy-btn" onclick="navigator.clipboard.writeText(this.nextElementSibling.nextElementSibling.textContent.trim());this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',2000)">Copy</button>
        <div style="background:#0f1724;border:1px solid #1e293b;border-radius:10px;overflow:hidden">
          <div style="background:#1e293b;padding:8px 16px;display:flex;gap:8px;align-items:center">
            <span style="font-size:.75rem;color:#64748b">POST /api/v1/payments/mpesa/stk</span>
          </div>
          <pre style="margin:0;border-radius:0;border:none">{
  <span class="key">"phone"</span>: <span class="str">"0712345678"</span>,
  <span class="key">"amount"</span>: <span class="num">500</span>,
  <span class="key">"description"</span>: <span class="str">"Order #1042"</span>,
  <span class="key">"callback_url"</span>: <span class="str">"https://myapp.co.ke/callback"</span>
}</pre>
        </div>
        <div style="margin-top:10px;background:#0f1724;border:1px solid #1e293b;border-radius:10px;overflow:hidden">
          <div style="background:#1a2e1a;padding:8px 16px;font-size:.75rem;color:#7dce9d">
            <i class="fas fa-check-circle"></i> 200 OK — Response
          </div>
          <pre style="margin:0;border-radius:0;border:none">{
  <span class="key">"success"</span>: <span class="kw">true</span>,
  <span class="key">"reference"</span>: <span class="str">"TXN-A3F9B2-20260614"</span>,
  <span class="key">"message"</span>: <span class="str">"STK Push sent successfully"</span>,
  <span class="key">"checkout_request_id"</span>: <span class="str">"ws_CO_..."</span>
}</pre>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================ TESTIMONIALS ============================================================ -->
<section class="section section-alt">
  <div class="section-center">
    <div class="section-tag"><i class="fas fa-quote-left"></i> Testimonials</div>
    <h2 class="section-title">Trusted by Kenyan Businesses</h2>
    <p class="section-subtitle">Don't just take our word for it — hear from businesses using OrbitPesa every day.</p>
  </div>
  <div class="testimonials-grid">
    <div class="testimonial-card fade-in">
      <div class="testimonial-stars">★★★★★</div>
      <p class="testimonial-text">"OrbitPesa transformed how we collect payments for our e-commerce store. M-Pesa STK push is seamless and the API was easy to integrate."</p>
      <div class="testimonial-author">
        <div class="testimonial-avatar">JM</div>
        <div>
          <div class="testimonial-name">James Mwangi</div>
          <div class="testimonial-role">CEO, ShopKenya</div>
        </div>
      </div>
    </div>
    <div class="testimonial-card fade-in">
      <div class="testimonial-stars">★★★★★</div>
      <p class="testimonial-text">"The payment links feature alone saved us hours every week. We create a link, share on WhatsApp, and money hits our wallet instantly."</p>
      <div class="testimonial-author">
        <div class="testimonial-avatar green">AW</div>
        <div>
          <div class="testimonial-name">Amina Wanjiku</div>
          <div class="testimonial-role">Founder, NairobiStyles</div>
        </div>
      </div>
    </div>
    <div class="testimonial-card fade-in">
      <div class="testimonial-stars">★★★★★</div>
      <p class="testimonial-text">"As a developer, I love that the sandbox actually works like production. Moved from test to live in one afternoon. Zero surprises."</p>
      <div class="testimonial-author">
        <div class="testimonial-avatar">DK</div>
        <div>
          <div class="testimonial-name">David Kiplagat</div>
          <div class="testimonial-role">CTO, TechBridge Kenya</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================ CONTACT ============================================================ -->
<section class="section" id="contact">
  <div class="section-center">
    <div class="section-tag"><i class="fas fa-envelope"></i> Contact Us</div>
    <h2 class="section-title">Have Questions? Get in Touch</h2>
    <p class="section-subtitle">Our team is ready to help you get started or answer any questions about OrbitPesa.</p>
  </div>
  <div class="contact-wrapper">

    <?php if ($contactSuccess = flash('contact_success')): ?>
      <div class="contact-alert contact-alert-success">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($contactSuccess) ?>
      </div>
    <?php endif; ?>
    <?php if ($contactError = flash('contact_error')): ?>
      <div class="contact-alert contact-alert-error">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($contactError) ?>
      </div>
    <?php endif; ?>

    <div class="contact-grid">
      <div class="contact-info">
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="fas fa-envelope"></i></div>
          <div>
            <h4>Email Support</h4>
            <p>support@orbitpesa.com</p>
          </div>
        </div>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="fas fa-headset"></i></div>
          <div>
            <h4>Phone Support</h4>
            <p>+254 700 000 000</p>
          </div>
        </div>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
          <div>
            <h4>Office</h4>
            <p>Westlands, Nairobi, Kenya</p>
          </div>
        </div>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="fas fa-clock"></i></div>
          <div>
            <h4>Business Hours</h4>
            <p>Mon–Fri, 8am – 6pm EAT</p>
          </div>
        </div>
      </div>

      <form class="contact-form" method="POST" action="<?= APP_URL ?>/contact">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
        <div class="form-row">
          <div class="form-group">
            <label for="cf_name">Full Name</label>
            <input type="text" id="cf_name" name="name" placeholder="Your name" required maxlength="100">
          </div>
          <div class="form-group">
            <label for="cf_email">Email Address</label>
            <input type="email" id="cf_email" name="email" placeholder="you@company.com" required maxlength="200">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="cf_phone">Phone <span style="font-weight:400;color:var(--text-muted)">(optional)</span></label>
            <input type="tel" id="cf_phone" name="phone" placeholder="0712 345 678" maxlength="20">
          </div>
          <div class="form-group">
            <label for="cf_subject">Subject</label>
            <select id="cf_subject" name="subject" required>
              <option value="">Select a subject</option>
              <option value="General Enquiry">General Enquiry</option>
              <option value="Technical Support">Technical Support</option>
              <option value="Enterprise Sales">Enterprise Sales</option>
              <option value="Partnership">Partnership</option>
              <option value="Billing">Billing</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="cf_message">Message</label>
          <textarea id="cf_message" name="message" rows="5" placeholder="Tell us how we can help…" required maxlength="2000"></textarea>
        </div>
        <button type="submit" class="contact-submit">
          <i class="fas fa-paper-plane"></i> Send Message
        </button>
      </form>
    </div>
  </div>
</section>

<!-- ============================================================ CTA ============================================================ -->
<section class="cta-section">
  <h2>Ready to Collect Your First Payment?</h2>
  <p>Join 5,000+ Kenyan businesses. Free to start, no monthly fees, no commitment.</p>
  <div class="cta-btns">
    <a href="<?= APP_URL ?>/register" class="btn-hero-primary">
      <i class="fas fa-rocket"></i> Create Free Account
    </a>
    <a href="<?= APP_URL ?>/developers/docs" class="btn-hero-outline">
      <i class="fas fa-book"></i> Read Documentation
    </a>
  </div>
</section>

<!-- ============================================================ FOOTER ============================================================ -->
<footer class="footer">
  <div class="footer-grid">
    <div class="footer-brand">
      <a href="<?= APP_URL ?>/" class="lp-logo" style="filter:brightness(0) invert(1)">
        <div class="mark" style="background:var(--green)">OP</div>
        <span class="brand" style="color:#fff">Orbit<span style="color:#7dce9d">Pesa</span></span>
      </a>
      <p style="margin-top:12px">Kenya's most trusted payment gateway. Secure, fast, and developer-friendly.</p>
      <div class="footer-socials" style="margin-top:16px">
        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
        <a href="#" class="social-link"><i class="fab fa-github"></i></a>
        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
      </div>
    </div>
    <div class="footer-col">
      <h4>Product</h4>
      <ul>
        <li><a href="#features">Features</a></li>
        <li><a href="#channels">Payment Channels</a></li>
        <li><a href="#pricing">Pricing</a></li>
        <li><a href="<?= APP_URL ?>/developers">Developer Console</a></li>
        <li><a href="<?= APP_URL ?>/developers/docs">API Documentation</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Company</h4>
      <ul>
        <li><a href="#">About Us</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Careers</a></li>
        <li><a href="#">Press</a></li>
        <li><a href="mailto:support@orbitpesa.com">Contact</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Legal</h4>
      <ul>
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Terms of Service</a></li>
        <li><a href="#">AML Policy</a></li>
        <li><a href="#">Security</a></li>
        <li><a href="#">Cookie Policy</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; <?= date('Y') ?> OrbitPesa Ltd. All rights reserved. Regulated by CBK.</p>
    <p style="display:flex;align-items:center;gap:6px">
      <i class="fas fa-shield-alt" style="color:var(--green)"></i> PCI DSS Compliant &nbsp;&bull;&nbsp;
      <i class="fas fa-lock" style="color:var(--green)"></i> 256-bit SSL
    </p>
  </div>
</footer>

<script src="<?= APP_URL ?>/assets/js/landing.js"></script>
</body>
</html>
