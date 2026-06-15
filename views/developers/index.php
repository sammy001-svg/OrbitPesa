<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Developer Console — OrbitPesa</title>
  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--green:#158347;--navy:#0D1B3E;--border:#e2e8f0;--bg:#f8fafc;--text:#1e293b;--muted:#64748b;--radius:8px}
    body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text)}

    .dev-layout{display:flex;min-height:100vh}

    /* Sidebar */
    .dev-sidebar{width:260px;flex-shrink:0;background:var(--navy);position:sticky;top:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column}
    .sb-logo{padding:20px 20px 16px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:10px;text-decoration:none}
    .sb-logo-mark{width:30px;height:30px;background:var(--green);border-radius:6px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.78rem;color:#fff}
    .sb-logo span{font-size:.95rem;font-weight:700;color:#fff}
    .sb-logo span em{color:var(--green);font-style:normal}
    .sb-nav{flex:1;padding:12px 0}
    .sb-section{padding:12px 16px 4px;font-size:.64rem;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.1em}
    .sb-link{display:flex;align-items:center;gap:9px;padding:8px 20px;color:rgba(255,255,255,.55);font-size:.81rem;font-weight:500;text-decoration:none;border-left:3px solid transparent;transition:all .12s}
    .sb-link:hover,.sb-link.active{color:#fff;background:rgba(255,255,255,.06)}
    .sb-link.active{border-left-color:var(--green);background:rgba(21,131,71,.12)}
    .sb-link i{width:15px;text-align:center;font-size:.82rem;opacity:.7}
    .sb-link.active i{opacity:1}
    .sb-foot{padding:14px 20px;border-top:1px solid rgba(255,255,255,.08);margin-top:auto}
    .sb-foot a{display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.4);font-size:.77rem;text-decoration:none;margin-bottom:6px;transition:color .12s}
    .sb-foot a:hover{color:rgba(255,255,255,.8)}

    /* Topbar */
    .dev-topbar{height:54px;background:#fff;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 28px;position:sticky;top:0;z-index:50}
    .status-pill{display:flex;align-items:center;gap:5px;font-size:.78rem;color:var(--muted)}
    .status-dot{width:7px;height:7px;background:#22c55e;border-radius:50%;animation:pulse 2.5s ease-in-out infinite}
    @keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.85)}}

    /* Main */
    .dev-main{flex:1;display:flex;flex-direction:column;min-width:0}
    .dev-content{padding:28px 32px;flex:1}
    @media(max-width:900px){.dev-sidebar{display:none}.dev-content{padding:18px 16px}}

    /* Hero */
    .hero{background:var(--navy);border-radius:12px;padding:28px 32px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap}
    .hero h1{font-size:1.4rem;font-weight:800;color:#fff;margin-bottom:6px}
    .hero p{font-size:.875rem;color:rgba(255,255,255,.55);line-height:1.65;max-width:520px}
    .hero-btns{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
    .hero-btn{display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius);font-size:.82rem;font-weight:700;text-decoration:none;transition:all .15s;font-family:'Inter',sans-serif;cursor:pointer;border:none}
    .hero-btn-primary{background:var(--green);color:#fff}.hero-btn-primary:hover{background:#117a3e}
    .hero-btn-outline{background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.2)}.hero-btn-outline:hover{background:rgba(255,255,255,.15)}

    /* Quick cards */
    .qgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-bottom:28px}
    .qcard{background:#fff;border:1px solid var(--border);border-radius:10px;padding:18px 16px;text-decoration:none;transition:border-color .15s,box-shadow .15s;display:block}
    .qcard:hover{border-color:var(--green);box-shadow:0 4px 16px rgba(21,131,71,.12)}
    .qcard-icon{width:40px;height:40px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1rem;margin-bottom:12px}
    .qcard h4{font-size:.85rem;font-weight:700;color:var(--navy);margin-bottom:4px}
    .qcard p{font-size:.75rem;color:var(--muted);line-height:1.5}

    /* API Tester */
    .tester-wrap{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:28px}
    .tester-head{background:var(--navy);padding:14px 20px;display:flex;align-items:center;gap:10px}
    .tester-head h3{color:#fff;font-size:.9rem;font-weight:700}
    .tester-body{display:grid;grid-template-columns:1fr 1fr;min-height:380px}
    @media(max-width:700px){.tester-body{grid-template-columns:1fr}}
    .tester-form{padding:20px;border-right:1px solid var(--border)}
    .tester-output{padding:20px;background:#0D1B3E;display:flex;flex-direction:column}

    .t-field{margin-bottom:12px}
    .t-field label{display:block;font-size:.74rem;font-weight:600;color:var(--muted);margin-bottom:4px}
    .t-field input,.t-field select,.t-field textarea{width:100%;border:1.5px solid var(--border);border-radius:6px;padding:8px 10px;font-size:.83rem;font-family:'Inter',sans-serif;outline:none;transition:border-color .12s;background:#fff}
    .t-field input:focus,.t-field select:focus{border-color:var(--green)}
    .t-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .t-send{width:100%;padding:9px;background:var(--green);color:#fff;border:none;border-radius:6px;font-size:.84rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:background .12s;margin-top:4px}
    .t-send:hover{background:#117a3e}
    .t-send:disabled{opacity:.6;cursor:not-allowed}
    .t-endpoint-select{display:flex;gap:8px;margin-bottom:14px}
    .t-endpoint-select select{flex:1;border:1.5px solid var(--border);border-radius:6px;padding:8px 10px;font-size:.83rem;font-family:'JetBrains Mono',monospace;outline:none;background:#fff;cursor:pointer}
    .t-method{padding:8px 12px;border-radius:6px;font-size:.72rem;font-weight:700;font-family:'JetBrains Mono',monospace;flex-shrink:0;display:flex;align-items:center}
    .m-get{background:#dbeafe;color:#1d4ed8}.m-post{background:#dcfce7;color:#166534}

    .t-output-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
    .t-output-hd span{font-size:.75rem;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.06em}
    .t-status-pill{font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:4px}
    .t-ok{background:#dcfce7;color:#166534}.t-err{background:#fef2f2;color:#991b1b}
    pre.t-json{color:#a5f3b0;font-family:'JetBrains Mono',monospace;font-size:.77rem;line-height:1.65;flex:1;overflow:auto;white-space:pre-wrap;margin:0}
    .t-placeholder{display:flex;flex-direction:column;align-items:center;justify-content:center;flex:1;color:rgba(255,255,255,.2);text-align:center;gap:10px}
    .t-placeholder i{font-size:2rem}
    .t-placeholder p{font-size:.8rem;line-height:1.5}

    /* Endpoint table */
    .ep-table{width:100%;border-collapse:collapse;font-size:.83rem}
    .ep-table th{text-align:left;padding:8px 14px;background:var(--bg);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);border-bottom:1px solid var(--border)}
    .ep-table td{padding:10px 14px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
    .ep-table tr:last-child td{border-bottom:none}
    .ep-url{font-family:'JetBrains Mono',monospace;font-size:.8rem;color:var(--navy)}
    .method-b{padding:3px 8px;border-radius:4px;font-size:.68rem;font-weight:700;font-family:'JetBrains Mono',monospace}
    .mb-get{background:#dbeafe;color:#1d4ed8}.mb-post{background:#dcfce7;color:#166534}

    /* Section heading */
    .section-title{font-size:1rem;font-weight:800;color:var(--navy);margin-bottom:14px;display:flex;align-items:center;gap:8px}
    .section-title i{color:var(--green)}
  </style>
</head>
<body>
<div class="dev-layout">

  <!-- Sidebar -->
  <aside class="dev-sidebar">
    <a class="sb-logo" href="<?= APP_URL ?>/developers">
      <div class="sb-logo-mark">OP</div>
      <span>Orbit<em>Pesa</em> <span style="font-size:.65rem;color:rgba(255,255,255,.3);font-weight:400">Dev</span></span>
    </a>
    <nav class="sb-nav">
      <div class="sb-section">Overview</div>
      <a class="sb-link active" href="<?= APP_URL ?>/developers"><i class="fas fa-terminal"></i> Console</a>
      <a class="sb-link" href="<?= APP_URL ?>/developers/docs"><i class="fas fa-book"></i> API Reference</a>

      <div class="sb-section">Payments</div>
      <a class="sb-link" href="<?= APP_URL ?>/developers/docs#mpesa"><i class="fas fa-mobile-alt"></i> M-Pesa STK Push</a>
      <a class="sb-link" href="<?= APP_URL ?>/developers/docs#cards"><i class="fas fa-credit-card"></i> Card Payments</a>
      <a class="sb-link" href="<?= APP_URL ?>/developers/docs#wallet"><i class="fas fa-coins"></i> Wallet Payments</a>

      <div class="sb-section">Resources</div>
      <a class="sb-link" href="<?= APP_URL ?>/developers/docs#transactions"><i class="fas fa-exchange-alt"></i> Transactions</a>
      <a class="sb-link" href="<?= APP_URL ?>/developers/docs#payment-links"><i class="fas fa-link"></i> Payment Links</a>
      <a class="sb-link" href="<?= APP_URL ?>/developers/docs#webhooks"><i class="fas fa-satellite-dish"></i> Webhooks</a>

      <div class="sb-section">Account</div>
      <a class="sb-link" href="<?= APP_URL ?>/dashboard/api-keys"><i class="fas fa-key"></i> API Keys</a>
      <a class="sb-link" href="<?= APP_URL ?>/dashboard/webhooks"><i class="fas fa-bell"></i> Webhook Endpoints</a>
    </nav>
    <div class="sb-foot">
      <a href="<?= APP_URL ?>/dashboard"><i class="fas fa-chart-pie"></i> Dashboard</a>
      <a href="<?= APP_URL ?>/"><i class="fas fa-home"></i> Back to Home</a>
    </div>
  </aside>

  <!-- Main -->
  <div class="dev-main">
    <div class="dev-topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <strong style="color:var(--navy);font-size:.9rem">Developer Console</strong>
        <span style="background:#dcfce7;color:#166534;font-size:.68rem;font-weight:700;padding:3px 7px;border-radius:4px">v1.0</span>
      </div>
      <div style="display:flex;align-items:center;gap:16px">
        <div class="status-pill"><div class="status-dot"></div> API Operational</div>
        <?php if (is_logged_in()): ?>
          <a href="<?= APP_URL ?>/dashboard" class="btn btn-ghost btn-sm" style="font-size:.78rem"><i class="fas fa-chart-pie"></i> Dashboard</a>
        <?php else: ?>
          <a href="<?= APP_URL ?>/register" class="btn btn-primary btn-sm" style="font-size:.78rem">Get API Keys</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="dev-content">

      <!-- Hero -->
      <div class="hero">
        <div>
          <h1>OrbitPesa Developer Console</h1>
          <p>One API for M-Pesa, card, and wallet payments. Test requests live, explore the reference, and manage your integration from one place.</p>
          <div class="hero-btns">
            <a href="<?= APP_URL ?>/developers/docs" class="hero-btn hero-btn-primary"><i class="fas fa-book"></i> API Reference</a>
            <a href="<?= APP_URL ?>/dashboard/api-keys" class="hero-btn hero-btn-outline"><i class="fas fa-key"></i> API Keys</a>
            <a href="<?= APP_URL ?>/register" class="hero-btn hero-btn-outline"><i class="fas fa-user-plus"></i> Create Account</a>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;min-width:210px">
          <div style="background:rgba(255,255,255,.06);border-radius:8px;padding:12px 14px">
            <div style="font-size:.65rem;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px">Base URL</div>
            <code style="font-size:.78rem;color:#a5f3b0;font-family:'JetBrains Mono',monospace"><?= APP_URL ?>/api/v1</code>
          </div>
          <div style="background:rgba(255,255,255,.06);border-radius:8px;padding:12px 14px">
            <div style="font-size:.65rem;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px">Auth Header</div>
            <code style="font-size:.78rem;color:#93c5fd;font-family:'JetBrains Mono',monospace">X-API-Key: op_test_…</code>
          </div>
        </div>
      </div>

      <!-- Quick links -->
      <div class="qgrid">
        <?php $cards = [
          [APP_URL.'/developers/docs#mpesa', 'fas fa-mobile-alt', '#dcfce7', '#166534', 'M-Pesa STK', 'Trigger phone payment prompts'],
          [APP_URL.'/developers/docs#cards', 'fas fa-credit-card', '#eff6ff', '#1d4ed8', 'Card Payments', 'Visa & Mastercard'],
          [APP_URL.'/developers/docs#wallet', 'fas fa-coins', '#fef3c7', '#92400e', 'Wallet Pay', 'Instant transfers'],
          [APP_URL.'/developers/docs#webhooks', 'fas fa-satellite-dish', '#f0fdf4', '#166534', 'Webhooks', 'Real-time event delivery'],
          [APP_URL.'/developers/docs#payment-links', 'fas fa-link', '#ede9fe', '#7c3aed', 'Payment Links', 'No-code checkout pages'],
          [APP_URL.'/developers/docs#sdks', 'fas fa-cubes', '#fff7ed', '#92400e', 'SDKs', 'PHP, Node, Python, Go'],
        ]; foreach ($cards as [$url,$ic,$bg,$c,$n,$d]): ?>
        <a href="<?=$url?>" class="qcard">
          <div class="qcard-icon" style="background:<?=$bg?>;color:<?=$c?>"><i class="<?=$ic?>"></i></div>
          <h4><?=$n?></h4><p><?=$d?></p>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- ── LIVE API TESTER ─────────────────────────────────── -->
      <div class="section-title"><i class="fas fa-play-circle"></i> Live API Tester</div>
      <div class="tester-wrap">
        <div class="tester-head">
          <i class="fas fa-terminal" style="color:var(--green)"></i>
          <h3>Make a Real Request</h3>
          <span style="margin-left:auto;font-size:.72rem;color:rgba(255,255,255,.35)">Calls your actual API — make sure you use a test key</span>
        </div>
        <div class="tester-body">
          <!-- Form -->
          <div class="tester-form">
            <div class="t-field">
              <label>API Key</label>
              <input type="text" id="t-key" placeholder="op_test_your_key_here">
              <?php if (is_logged_in()): ?>
              <div style="font-size:.72rem;color:var(--muted);margin-top:4px"><i class="fas fa-info-circle"></i> Find your key at <a href="<?= APP_URL ?>/dashboard/api-keys" style="color:var(--green)">Dashboard → API Keys</a></div>
              <?php endif; ?>
            </div>
            <div class="t-endpoint-select">
              <div class="t-method m-get" id="t-method-badge">GET</div>
              <select id="t-endpoint" onchange="onEndpointChange()">
                <optgroup label="Transactions">
                  <option value="GET|/transactions">GET /transactions — List</option>
                  <option value="GET|/transactions/{ref}">GET /transactions/{ref} — Single</option>
                </optgroup>
                <optgroup label="Payments">
                  <option value="POST|/payments/mpesa/stk">POST /payments/mpesa/stk</option>
                  <option value="POST|/payments/card/charge">POST /payments/card/charge</option>
                  <option value="POST|/payments/wallet/pay">POST /payments/wallet/pay</option>
                  <option value="GET|/payments/status/{ref}">GET /payments/status/{ref}</option>
                </optgroup>
                <optgroup label="Payment Links">
                  <option value="POST|/payment-links">POST /payment-links — Create</option>
                  <option value="GET|/payment-links">GET /payment-links — List</option>
                </optgroup>
                <optgroup label="System">
                  <option value="GET|/ping">GET /ping — Health check</option>
                </optgroup>
              </select>
            </div>

            <!-- Dynamic fields per endpoint -->
            <div id="t-fields">
              <!-- Populated by JS -->
            </div>

            <button class="t-send" id="t-send-btn" onclick="sendRequest()">
              <i class="fas fa-paper-plane"></i> Send Request
            </button>
          </div>

          <!-- Output -->
          <div class="tester-output">
            <div class="t-output-hd">
              <span>Response</span>
              <span id="t-status-label"></span>
            </div>
            <div id="t-placeholder" class="t-placeholder">
              <i class="fas fa-satellite-dish"></i>
              <p>Select an endpoint and click<br><strong style="color:rgba(255,255,255,.5)">Send Request</strong> to see the response</p>
            </div>
            <pre class="t-json" id="t-output" style="display:none"></pre>
          </div>
        </div>
      </div>

      <!-- ── ENDPOINT REFERENCE TABLE ───────────────────────── -->
      <div class="section-title" style="margin-bottom:14px"><i class="fas fa-list"></i> All Endpoints</div>
      <div class="card" style="margin-bottom:28px">
        <div class="p-0">
          <div class="table-wrap">
            <table class="ep-table">
              <thead><tr><th>Method</th><th>Endpoint</th><th>Description</th><th>Auth</th></tr></thead>
              <tbody>
                <?php $eps = [
                  ['POST','payments/mpesa/stk',      'Initiate M-Pesa STK Push',             true],
                  ['GET', 'payments/status/{ref}',   'Poll payment status',                  true],
                  ['POST','payments/mpesa/callback', 'Safaricom callback (public)',           false],
                  ['POST','payments/card/charge',    'Charge a card',                        true],
                  ['POST','payments/wallet/pay',     'Send wallet payment',                  true],
                  ['GET', 'wallet/lookup',           'Find wallet by email/phone',           true],
                  ['GET', 'transactions',            'List transactions',                    true],
                  ['GET', 'transactions/{ref}',      'Get single transaction',               true],
                  ['GET', 'payment-links',           'List payment links',                   true],
                  ['POST','payment-links',           'Create payment link',                  true],
                  ['POST','checkout/{slug}/pay',     'Public checkout (no key needed)',       false],
                  ['GET', 'checkout/status/{ref}',   'Public checkout status poll',           false],
                  ['GET', 'ping',                    'API health check',                     false],
                ]; foreach ($eps as [$m,$u,$d,$auth]): ?>
                <tr>
                  <td><span class="method-b <?= $m==='GET'?'mb-get':'mb-post' ?>"><?= $m ?></span></td>
                  <td><code class="ep-url">/api/v1/<?= $u ?></code></td>
                  <td style="font-size:.83rem;color:var(--muted)"><?= $d ?></td>
                  <td>
                    <?= $auth
                      ? '<span style="background:#fef9c3;color:#92400e;padding:2px 7px;border-radius:4px;font-size:.68rem;font-weight:700"><i class="fas fa-key"></i> API Key</span>'
                      : '<span style="background:#f1f5f9;color:#64748b;padding:2px 7px;border-radius:4px;font-size:.68rem;font-weight:700">Public</span>' ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div style="padding:14px 18px;border-top:1px solid var(--border)">
          <a href="<?= APP_URL ?>/developers/docs" class="btn btn-ghost btn-sm"><i class="fas fa-book"></i> Full Reference with Examples</a>
        </div>
      </div>

      <!-- ── QUICKSTART ─────────────────────────────────────── -->
      <div class="section-title"><i class="fas fa-rocket"></i> Quickstart — M-Pesa in 5 minutes</div>
      <div class="card" style="margin-bottom:28px">
        <div class="card-body" style="padding:0">
          <div style="background:#0D1B3E;border-radius:10px;overflow:hidden">
            <div style="display:flex;border-bottom:1px solid rgba(255,255,255,.08)">
              <div class="qs-tab" onclick="qsTab(this,'php')" style="padding:10px 18px;font-size:.75rem;font-weight:600;color:#fff;cursor:pointer;border-bottom:2px solid var(--green);transition:color .12s" data-target="qs-php">PHP</div>
              <div class="qs-tab" onclick="qsTab(this,'js')"  style="padding:10px 18px;font-size:.75rem;font-weight:600;color:rgba(255,255,255,.45);cursor:pointer;border-bottom:2px solid transparent" data-target="qs-js">Node.js</div>
              <div class="qs-tab" onclick="qsTab(this,'py')"  style="padding:10px 18px;font-size:.75rem;font-weight:600;color:rgba(255,255,255,.45);cursor:pointer;border-bottom:2px solid transparent" data-target="qs-py">Python</div>
              <button onclick="copyQs()" style="margin-left:auto;margin-right:12px;align-self:center;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.6);padding:4px 12px;border-radius:4px;font-size:.72rem;cursor:pointer" id="qs-copy-btn">Copy</button>
            </div>
            <div id="qs-php">
<pre style="color:#e2e8f0;font-family:'JetBrains Mono',monospace;font-size:.78rem;line-height:1.7;padding:20px;overflow-x:auto"><span style="color:#64748b">// Step 1: Initiate STK Push</span>
$ch = curl_init(<span style="color:#86efac">'<?= APP_URL ?>/api/v1/payments/mpesa/stk'</span>);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => <span style="color:#93c5fd">true</span>,
    CURLOPT_POST           => <span style="color:#93c5fd">true</span>,
    CURLOPT_HTTPHEADER     => [
        <span style="color:#86efac">'X-API-Key: op_test_YOUR_KEY'</span>,
        <span style="color:#86efac">'Content-Type: application/json'</span>,
    ],
    CURLOPT_POSTFIELDS => json_encode([
        <span style="color:#86efac">'phone'</span>       => <span style="color:#86efac">'0712345678'</span>,
        <span style="color:#86efac">'amount'</span>      => <span style="color:#fb923c">500</span>,
        <span style="color:#86efac">'description'</span> => <span style="color:#86efac">'Order #1042'</span>,
    ]),
]);
$response  = json_decode(curl_exec($ch), <span style="color:#93c5fd">true</span>);
$reference = $response[<span style="color:#86efac">'data'</span>][<span style="color:#86efac">'reference'</span>]; <span style="color:#64748b">// save this</span>

<span style="color:#64748b">// Step 2: Poll status every 3 seconds</span>
<span style="color:#93c5fd">do</span> {
    sleep(<span style="color:#fb923c">3</span>);
    $ch  = curl_init(<span style="color:#86efac">'<?= APP_URL ?>/api/v1/payments/status/'</span> . $reference);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=><span style="color:#93c5fd">true</span>, CURLOPT_HTTPHEADER=>[<span style="color:#86efac">'X-API-Key: op_test_YOUR_KEY'</span>]]);
    $poll = json_decode(curl_exec($ch), <span style="color:#93c5fd">true</span>);
} <span style="color:#93c5fd">while</span> ($poll[<span style="color:#86efac">'data'</span>][<span style="color:#86efac">'status'</span>] === <span style="color:#86efac">'pending'</span>);

<span style="color:#64748b">// Step 3: Handle result</span>
<span style="color:#93c5fd">if</span> ($poll[<span style="color:#86efac">'data'</span>][<span style="color:#86efac">'status'</span>] === <span style="color:#86efac">'completed'</span>) {
    echo <span style="color:#86efac">"Payment received! Ref: $reference"</span>;
} <span style="color:#93c5fd">else</span> {
    echo <span style="color:#86efac">"Payment failed or cancelled."</span>;
}</pre></div>
            <div id="qs-js" style="display:none">
<pre style="color:#e2e8f0;font-family:'JetBrains Mono',monospace;font-size:.78rem;line-height:1.7;padding:20px;overflow-x:auto"><span style="color:#64748b">// Step 1: Initiate STK Push</span>
<span style="color:#93c5fd">const</span> res = <span style="color:#93c5fd">await</span> fetch(<span style="color:#86efac">'<?= APP_URL ?>/api/v1/payments/mpesa/stk'</span>, {
  method: <span style="color:#86efac">'POST'</span>,
  headers: { <span style="color:#86efac">'X-API-Key'</span>: <span style="color:#86efac">'op_test_YOUR_KEY'</span>, <span style="color:#86efac">'Content-Type'</span>: <span style="color:#86efac">'application/json'</span> },
  body: JSON.stringify({ phone: <span style="color:#86efac">'0712345678'</span>, amount: <span style="color:#fb923c">500</span>, description: <span style="color:#86efac">'Order #1042'</span> })
});
<span style="color:#93c5fd">const</span> { reference } = (<span style="color:#93c5fd">await</span> res.json()).data;

<span style="color:#64748b">// Step 2: Poll every 3 seconds</span>
<span style="color:#93c5fd">const</span> poll = <span style="color:#93c5fd">async</span> () => {
  <span style="color:#93c5fd">const</span> r    = <span style="color:#93c5fd">await</span> fetch(`<?= APP_URL ?>/api/v1/payments/status/${reference}`,
    { headers: { <span style="color:#86efac">'X-API-Key'</span>: <span style="color:#86efac">'op_test_YOUR_KEY'</span> } });
  <span style="color:#93c5fd">const</span> data = (<span style="color:#93c5fd">await</span> r.json()).data;
  <span style="color:#93c5fd">if</span> (data.status === <span style="color:#86efac">'pending'</span>) <span style="color:#93c5fd">return</span> setTimeout(poll, <span style="color:#fb923c">3000</span>);
  console.log(data.status === <span style="color:#86efac">'completed'</span> ? <span style="color:#86efac">'Paid!'</span> : <span style="color:#86efac">'Failed'</span>, reference);
};
setTimeout(poll, <span style="color:#fb923c">3000</span>);</pre></div>
            <div id="qs-py" style="display:none">
<pre style="color:#e2e8f0;font-family:'JetBrains Mono',monospace;font-size:.78rem;line-height:1.7;padding:20px;overflow-x:auto"><span style="color:#93c5fd">import</span> requests, time

headers = {<span style="color:#86efac">'X-API-Key'</span>: <span style="color:#86efac">'op_test_YOUR_KEY'</span>}
BASE    = <span style="color:#86efac">'<?= APP_URL ?>/api/v1'</span>

<span style="color:#64748b"># Step 1: Initiate STK Push</span>
res = requests.post(f<span style="color:#86efac">'{BASE}/payments/mpesa/stk'</span>, headers=headers,
    json={<span style="color:#86efac">'phone'</span>: <span style="color:#86efac">'0712345678'</span>, <span style="color:#86efac">'amount'</span>: <span style="color:#fb923c">500</span>, <span style="color:#86efac">'description'</span>: <span style="color:#86efac">'Order #1042'</span>})
reference = res.json()[<span style="color:#86efac">'data'</span>][<span style="color:#86efac">'reference'</span>]

<span style="color:#64748b"># Step 2: Poll every 3 seconds</span>
<span style="color:#93c5fd">while True</span>:
    time.sleep(<span style="color:#fb923c">3</span>)
    poll = requests.get(f<span style="color:#86efac">'{BASE}/payments/status/{reference}'</span>, headers=headers).json()
    status = poll[<span style="color:#86efac">'data'</span>][<span style="color:#86efac">'status'</span>]
    <span style="color:#93c5fd">if</span> status != <span style="color:#86efac">'pending'</span>: <span style="color:#93c5fd">break</span>

print(<span style="color:#86efac">'Paid!'</span> <span style="color:#93c5fd">if</span> status == <span style="color:#86efac">'completed'</span> <span style="color:#93c5fd">else</span> <span style="color:#86efac">'Failed'</span>, reference)</pre></div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div style="padding:20px 0;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
        <p style="color:var(--muted);font-size:.8rem;margin:0">&copy; <?= date('Y') ?> OrbitPesa Ltd. &bull; API v1.0</p>
        <div style="display:flex;gap:16px">
          <a href="<?= APP_URL ?>/developers/docs" style="color:var(--green);font-size:.8rem;text-decoration:none">API Docs</a>
          <a href="mailto:developers@orbitpesa.com" style="color:var(--muted);font-size:.8rem;text-decoration:none">Support</a>
        </div>
      </div>

    </div><!-- /dev-content -->
  </div><!-- /dev-main -->
</div>

<script>
const BASE = '<?= APP_URL ?>/api/v1';

// ── Endpoint definitions (method, path, fields) ───────────
const ENDPOINTS = {
  'GET|/transactions': {
    method: 'GET', path: '/transactions',
    fields: [
      {id:'q-status',label:'status (optional)',type:'select',opts:['','completed','pending','failed']},
      {id:'q-channel',label:'channel (optional)',type:'select',opts:['','mpesa','card','wallet']},
    ]
  },
  'GET|/transactions/{ref}': {
    method: 'GET', path: '/transactions/{ref}',
    fields: [{id:'q-ref',label:'reference',type:'text',ph:'TXN-XXXXXX'}]
  },
  'POST|/payments/mpesa/stk': {
    method: 'POST', path: '/payments/mpesa/stk',
    fields: [
      {id:'q-phone',label:'phone',type:'text',ph:'0712345678',val:'0712345678'},
      {id:'q-amount',label:'amount (KES)',type:'number',ph:'500',val:'100'},
      {id:'q-desc',label:'description',type:'text',ph:'Order #1042',val:'Test payment'},
    ]
  },
  'POST|/payments/card/charge': {
    method: 'POST', path: '/payments/card/charge',
    fields: [
      {id:'q-cardnum',label:'card_number',type:'text',ph:'4242424242424242',val:'4242424242424242'},
      {id:'q-expm',label:'exp_month',type:'text',ph:'12',val:'12'},
      {id:'q-expy',label:'exp_year',type:'text',ph:'28',val:'28'},
      {id:'q-cvv',label:'cvv',type:'text',ph:'123',val:'123'},
      {id:'q-holder',label:'card_holder',type:'text',ph:'Jane Doe',val:'Test Cardholder'},
      {id:'q-cardamt',label:'amount (KES)',type:'number',ph:'1000',val:'1000'},
    ]
  },
  'POST|/payments/wallet/pay': {
    method: 'POST', path: '/payments/wallet/pay',
    fields: [
      {id:'q-recip',label:'recipient_id',type:'text',ph:'user-uuid-here'},
      {id:'q-wamt',label:'amount (KES)',type:'number',ph:'500',val:'500'},
      {id:'q-wdesc',label:'description (optional)',type:'text',ph:'Invoice #1'},
    ]
  },
  'GET|/payments/status/{ref}': {
    method: 'GET', path: '/payments/status/{ref}',
    fields: [{id:'q-sref',label:'reference',type:'text',ph:'TXN-XXXXXX'}]
  },
  'POST|/payment-links': {
    method: 'POST', path: '/payment-links',
    fields: [
      {id:'q-pltitle',label:'title',type:'text',ph:'Product Purchase',val:'Product Purchase'},
      {id:'q-plamt',label:'amount (KES, optional)',type:'number',ph:'0'},
      {id:'q-pldesc',label:'description (optional)',type:'text',ph:''},
    ]
  },
  'GET|/payment-links': {method:'GET',path:'/payment-links',fields:[]},
  'GET|/ping':          {method:'GET',path:'/ping',fields:[]},
};

function onEndpointChange() {
  const key  = document.getElementById('t-endpoint').value;
  const ep   = ENDPOINTS[key];
  if (!ep) return;
  const badge = document.getElementById('t-method-badge');
  badge.textContent = ep.method;
  badge.className = 't-method ' + (ep.method === 'GET' ? 'm-get' : 'm-post');

  const container = document.getElementById('t-fields');
  container.innerHTML = ep.fields.map(f => {
    if (f.type === 'select') {
      return `<div class="t-field"><label>${f.label}</label><select id="${f.id}">${f.opts.map(o=>`<option value="${o}">${o||'— any —'}</option>`).join('')}</select></div>`;
    }
    return `<div class="t-field"><label>${f.label}</label><input type="${f.type}" id="${f.id}" placeholder="${f.ph||''}" value="${f.val||''}"></div>`;
  }).join('');
}

async function sendRequest() {
  const key  = document.getElementById('t-key').value.trim();
  const epKey = document.getElementById('t-endpoint').value;
  const ep   = ENDPOINTS[epKey];
  if (!ep || !key) { alert('Enter your API key first.'); return; }

  const btn = document.getElementById('t-send-btn');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending…';

  document.getElementById('t-placeholder').style.display = 'none';
  document.getElementById('t-output').style.display = 'block';
  document.getElementById('t-output').textContent = 'Loading…';
  document.getElementById('t-status-label').innerHTML = '';

  try {
    let url = BASE + ep.path;
    const body = {};

    ep.fields.forEach(f => {
      const el = document.getElementById(f.id);
      if (!el) return;
      const v = el.value.trim();
      if (!v) return;
      // Substitute into path if needed
      const pKey = f.id.replace(/^q-/,'').replace(/-/g,'_');
      if (url.includes('{ref}') && (f.id === 'q-ref' || f.id === 'q-sref')) {
        url = url.replace('{ref}', v);
      } else if (ep.method === 'POST') {
        // Map field id → body key
        const map = {
          'q-phone':'phone','q-amount':'amount','q-desc':'description',
          'q-cardnum':'card_number','q-expm':'exp_month','q-expy':'exp_year',
          'q-cvv':'cvv','q-holder':'card_holder','q-cardamt':'amount',
          'q-recip':'recipient_id','q-wamt':'amount','q-wdesc':'description',
          'q-pltitle':'title','q-plamt':'amount','q-pldesc':'description',
        };
        if (map[f.id]) body[map[f.id]] = f.type==='number' ? parseFloat(v)||0 : v;
      } else if (ep.method === 'GET' && f.type === 'select') {
        const qKey = {'q-status':'status','q-channel':'channel'}[f.id];
        if (qKey && v) {
          url += (url.includes('?')?'&':'?') + qKey + '=' + encodeURIComponent(v);
        }
      }
    });

    const opts = { headers: { 'X-API-Key': key, 'Content-Type': 'application/json' } };
    if (ep.method === 'POST') { opts.method = 'POST'; opts.body = JSON.stringify(body); }

    const res  = await fetch(url, opts);
    const data = await res.json();

    document.getElementById('t-output').textContent = JSON.stringify(data, null, 2);
    document.getElementById('t-status-label').innerHTML = `<span class="t-status-pill ${data.success?'t-ok':'t-err'}">${res.status} ${data.success?'OK':'Error'}</span>`;
  } catch(e) {
    document.getElementById('t-output').textContent = JSON.stringify({error: e.message}, null, 2);
    document.getElementById('t-status-label').innerHTML = '<span class="t-status-pill t-err">Error</span>';
  }
  btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Request';
}

// ── Quickstart tabs ───────────────────────────────────────
function qsTab(tab, id) {
  document.querySelectorAll('.qs-tab').forEach(t => {
    t.style.color = 'rgba(255,255,255,.45)';
    t.style.borderBottom = '2px solid transparent';
  });
  tab.style.color = '#fff';
  tab.style.borderBottom = '2px solid var(--green)';
  ['php','js','py'].forEach(k => { document.getElementById('qs-'+k).style.display = k===id?'block':'none'; });
}
function copyQs() {
  const active = ['php','js','py'].find(k => document.getElementById('qs-'+k).style.display !== 'none') || 'php';
  navigator.clipboard.writeText(document.getElementById('qs-'+active).textContent.trim()).then(() => {
    const btn = document.getElementById('qs-copy-btn');
    btn.textContent = 'Copied!';
    setTimeout(()=>btn.textContent='Copy',1500);
  });
}

// Init
onEndpointChange();
</script>
</body>
</html>
