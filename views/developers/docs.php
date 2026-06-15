<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Documentation — OrbitPesa</title>
  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--green:#158347;--navy:#0D1B3E;--border:#e2e8f0;--bg:#f8fafc;--text:#1e293b;--muted:#64748b;--radius:8px;--sidebar-w:272px}
    body{font-family:'Inter',sans-serif;background:#fff;color:var(--text)}

    /* Layout */
    .docs-layout{display:flex;min-height:100vh}
    .docs-sidebar{width:var(--sidebar-w);flex-shrink:0;background:var(--navy);position:sticky;top:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column}
    .docs-main{flex:1;min-width:0}

    /* Sidebar */
    .sb-logo{padding:20px 20px 16px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:10px;text-decoration:none}
    .sb-logo-mark{width:30px;height:30px;background:var(--green);border-radius:6px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.78rem;color:#fff;flex-shrink:0}
    .sb-logo span{font-size:.95rem;font-weight:700;color:#fff}
    .sb-logo span em{color:var(--green);font-style:normal}
    .sb-nav{flex:1;padding:12px 0}
    .sb-section{padding:14px 16px 4px;font-size:.65rem;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.1em}
    .sb-link{display:flex;align-items:center;gap:9px;padding:7px 20px;color:rgba(255,255,255,.55);font-size:.8rem;font-weight:500;text-decoration:none;border-left:3px solid transparent;transition:all .12s}
    .sb-link:hover,.sb-link.active{color:#fff;background:rgba(255,255,255,.06)}
    .sb-link.active{border-left-color:var(--green);background:rgba(21,131,71,.12)}
    .sb-link i{width:15px;text-align:center;font-size:.82rem;opacity:.7}
    .sb-link.active i{opacity:1}
    .sb-foot{padding:16px 20px;border-top:1px solid rgba(255,255,255,.08);margin-top:auto}
    .sb-foot a{display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.45);font-size:.78rem;text-decoration:none;margin-bottom:6px;transition:color .12s}
    .sb-foot a:hover{color:rgba(255,255,255,.8)}

    /* Topbar */
    .docs-topbar{height:54px;background:#fff;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 32px;position:sticky;top:0;z-index:50;gap:16px}
    .docs-topbar-left{display:flex;align-items:center;gap:12px}
    .version-badge{background:#dcfce7;color:#166534;font-size:.7rem;font-weight:700;padding:3px 8px;border-radius:4px}
    .status-pill{display:flex;align-items:center;gap:5px;font-size:.78rem;color:var(--muted)}
    .status-dot{width:7px;height:7px;background:#22c55e;border-radius:50%;animation:pulse 2.5s ease-in-out infinite}
    @keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.85)}}

    /* Content */
    .docs-content{max-width:860px;padding:40px 48px;margin:0 auto}
    @media(max-width:1100px){.docs-content{padding:32px 28px}}

    /* Sections */
    .doc-section{margin-bottom:64px;scroll-margin-top:70px}
    .doc-section-title{display:flex;align-items:center;gap:12px;margin-bottom:16px;padding-bottom:14px;border-bottom:2px solid var(--border)}
    .doc-section-title h2{font-size:1.4rem;font-weight:800;color:var(--navy)}
    .doc-section-title .icon{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
    .doc-section p{color:var(--muted);font-size:.9rem;line-height:1.75;margin-bottom:14px}

    /* Endpoint card */
    .endpoint{background:#fff;border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:24px}
    .endpoint-head{display:flex;align-items:center;gap:12px;padding:12px 18px;background:var(--bg);border-bottom:1px solid var(--border);cursor:pointer;user-select:none}
    .endpoint-head:hover{background:#f1f5f9}
    .endpoint-url{font-family:'JetBrains Mono',monospace;font-size:.88rem;font-weight:600;color:var(--navy)}
    .endpoint-desc{color:var(--muted);font-size:.8rem;margin-left:auto}
    .method{padding:4px 9px;border-radius:4px;font-size:.7rem;font-weight:700;font-family:'JetBrains Mono',monospace;flex-shrink:0}
    .method-get{background:#dbeafe;color:#1d4ed8}
    .method-post{background:#dcfce7;color:#166534}
    .endpoint-body{padding:20px 20px 0;display:none}
    .endpoint-body.open{display:block}

    /* Param table */
    .param-table{width:100%;border-collapse:collapse;font-size:.83rem;margin-bottom:20px}
    .param-table th{text-align:left;padding:8px 12px;background:var(--bg);font-weight:700;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);border-bottom:1px solid var(--border)}
    .param-table td{padding:10px 12px;border-bottom:1px solid #f1f5f9;vertical-align:top;line-height:1.5}
    .param-table tr:last-child td{border-bottom:none}
    .pname{font-family:'JetBrains Mono',monospace;font-weight:600;color:var(--navy);font-size:.82rem}
    .ptype{color:var(--green);font-family:'JetBrains Mono',monospace;font-size:.78rem}
    .req{background:#fef2f2;color:#991b1b;padding:2px 6px;border-radius:4px;font-size:.68rem;font-weight:700}
    .opt{background:#f0fdf4;color:#166534;padding:2px 6px;border-radius:4px;font-size:.68rem;font-weight:700}

    /* Code block with language tabs */
    .code-wrap{background:#0D1B3E;border-radius:8px;overflow:hidden;margin-bottom:20px}
    .code-tabs{display:flex;border-bottom:1px solid rgba(255,255,255,.1)}
    .code-tab{padding:8px 16px;font-size:.75rem;font-weight:600;color:rgba(255,255,255,.45);cursor:pointer;transition:color .12s;border-bottom:2px solid transparent;font-family:'Inter',sans-serif}
    .code-tab.active{color:#fff;border-bottom-color:var(--green)}
    .code-tab:hover{color:rgba(255,255,255,.8)}
    .code-pane{display:none;position:relative}
    .code-pane.active{display:block}
    .code-copy{position:absolute;top:10px;right:12px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.6);padding:4px 10px;border-radius:4px;font-size:.72rem;cursor:pointer;font-family:'Inter',sans-serif;transition:background .12s}
    .code-copy:hover{background:rgba(255,255,255,.2)}
    pre.code{padding:18px 18px 18px;color:#e2e8f0;font-family:'JetBrains Mono',monospace;font-size:.8rem;line-height:1.7;overflow-x:auto;white-space:pre;background:none;margin:0}
    .kw{color:#93c5fd}.str{color:#86efac}.num{color:#fb923c}.key{color:#c084fc}.cmt{color:#64748b}

    /* Response example */
    .resp-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:6px;padding-left:2px}
    pre.json{background:var(--bg);border:1px solid var(--border);border-radius:var(--radius);padding:16px;font-family:'JetBrains Mono',monospace;font-size:.79rem;line-height:1.7;overflow-x:auto;color:var(--text);margin-bottom:20px}

    /* Try-it panel */
    .try-it{border-top:1px solid var(--border);margin:0 -20px;padding:20px}
    .try-it-hd{display:flex;align-items:center;gap:8px;margin-bottom:14px;font-size:.85rem;font-weight:700;color:var(--navy)}
    .try-field{margin-bottom:12px}
    .try-field label{display:block;font-size:.75rem;font-weight:600;color:var(--muted);margin-bottom:4px}
    .try-field input,.try-field select,.try-field textarea{width:100%;border:1.5px solid var(--border);border-radius:var(--radius);padding:8px 10px;font-size:.84rem;font-family:'Inter',sans-serif;outline:none;transition:border-color .12s;background:#fff}
    .try-field input:focus,.try-field select:focus,.try-field textarea:focus{border-color:var(--green)}
    .try-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .try-btn{background:var(--green);color:#fff;border:none;border-radius:var(--radius);padding:9px 20px;font-size:.84rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .12s}
    .try-btn:hover{background:#117a3e}
    .try-btn:disabled{opacity:.6;cursor:not-allowed}
    .try-result{margin-top:14px;display:none}
    .try-result-status{display:inline-flex;align-items:center;gap:6px;font-size:.78rem;font-weight:700;padding:3px 10px;border-radius:4px;margin-bottom:8px}
    .try-ok{background:#dcfce7;color:#166534}
    .try-err{background:#fef2f2;color:#991b1b}
    pre.try-json{background:#0D1B3E;color:#a5f3b0;border-radius:6px;padding:14px;font-family:'JetBrains Mono',monospace;font-size:.76rem;line-height:1.65;overflow-x:auto;max-height:300px;overflow-y:auto;white-space:pre-wrap}

    /* Info alert */
    .info-box{display:flex;gap:10px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:var(--radius);padding:14px 16px;margin-bottom:20px;font-size:.85rem;color:#1e40af}
    .warn-box{display:flex;gap:10px;background:#fff7ed;border:1px solid #fed7aa;border-radius:var(--radius);padding:14px 16px;margin-bottom:20px;font-size:.85rem;color:#92400e}

    /* Event badge */
    .event-badge{background:#0D1B3E;color:#a5f3b0;font-family:'JetBrains Mono',monospace;font-size:.76rem;padding:3px 8px;border-radius:4px}

    @media(max-width:860px){.docs-sidebar{display:none}.docs-content{padding:24px 16px}}
  </style>
</head>
<body>
<div class="docs-layout">

  <!-- Sidebar -->
  <aside class="docs-sidebar">
    <a class="sb-logo" href="<?= APP_URL ?>/developers">
      <div class="sb-logo-mark">OP</div>
      <span>Orbit<em>Pesa</em> <span style="font-size:.68rem;color:rgba(255,255,255,.3);font-weight:400">API</span></span>
    </a>
    <nav class="sb-nav">
      <div class="sb-section">Getting Started</div>
      <a class="sb-link active" href="#introduction"><i class="fas fa-home"></i> Introduction</a>
      <a class="sb-link" href="#authentication"><i class="fas fa-key"></i> Authentication</a>
      <a class="sb-link" href="#errors"><i class="fas fa-exclamation-triangle"></i> Errors & Status Codes</a>

      <div class="sb-section">Payments</div>
      <a class="sb-link" href="#mpesa"><i class="fas fa-mobile-alt"></i> M-Pesa STK Push</a>
      <a class="sb-link" href="#cards"><i class="fas fa-credit-card"></i> Card Payments</a>
      <a class="sb-link" href="#wallet"><i class="fas fa-coins"></i> Wallet Payments</a>

      <div class="sb-section">Resources</div>
      <a class="sb-link" href="#transactions"><i class="fas fa-exchange-alt"></i> Transactions</a>
      <a class="sb-link" href="#payment-links"><i class="fas fa-link"></i> Payment Links</a>

      <div class="sb-section">Events</div>
      <a class="sb-link" href="#webhooks"><i class="fas fa-satellite-dish"></i> Webhooks</a>

      <div class="sb-section">SDKs</div>
      <a class="sb-link" href="#sdks"><i class="fas fa-cubes"></i> Libraries & SDKs</a>
    </nav>
    <div class="sb-foot">
      <a href="<?= APP_URL ?>/developers"><i class="fas fa-terminal"></i> Developer Console</a>
      <a href="<?= APP_URL ?>/dashboard/api-keys"><i class="fas fa-key"></i> My API Keys</a>
      <a href="<?= APP_URL ?>/"><i class="fas fa-home"></i> Back to Home</a>
    </div>
  </aside>

  <!-- Main -->
  <div class="docs-main">
    <div class="docs-topbar">
      <div class="docs-topbar-left">
        <strong style="color:var(--navy);font-size:.9rem">API Reference</strong>
        <span class="version-badge">v1.0</span>
      </div>
      <div style="display:flex;align-items:center;gap:16px">
        <div class="status-pill"><div class="status-dot"></div> All Systems Operational</div>
        <a href="<?= APP_URL ?>/developers" class="btn btn-ghost btn-sm" style="font-size:.78rem">← Console</a>
      </div>
    </div>

    <div class="docs-content">

      <!-- ─── INTRODUCTION ─────────────────────────────────── -->
      <div class="doc-section" id="introduction">
        <div class="doc-section-title">
          <div class="icon" style="background:#dcfce7;color:var(--green)"><i class="fas fa-rocket"></i></div>
          <h2>Introduction</h2>
        </div>
        <p>Welcome to the OrbitPesa API. Integrate M-Pesa, card, and wallet payments into any application using a single consistent REST API. All communication uses JSON over HTTPS.</p>

        <div class="info-box"><i class="fas fa-info-circle"></i>
          <div><strong>Base URL</strong><br>
            <code><?= APP_URL ?>/api/v1</code> — same URL for both sandbox and production. The key prefix (<code>op_test_</code> vs <code>op_live_</code>) determines which environment is used.
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:24px">
          <?php foreach ([['M-Pesa STK','fas fa-mobile-alt','#dcfce7','#166534'],['Card Payments','fas fa-credit-card','#eff6ff','#1d4ed8'],['Wallet Pay','fas fa-coins','#fef3c7','#92400e']] as [$n,$i,$bg,$c]): ?>
          <div style="background:<?=$bg?>;border-radius:8px;padding:14px;display:flex;align-items:center;gap:10px">
            <i class="<?=$i?>" style="color:<?=$c?>;font-size:1.1rem"></i>
            <span style="font-weight:600;font-size:.82rem;color:<?=$c?>"><?=$n?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- ─── AUTHENTICATION ───────────────────────────────── -->
      <div class="doc-section" id="authentication">
        <div class="doc-section-title">
          <div class="icon" style="background:#fef9c3;color:#854d0e"><i class="fas fa-key"></i></div>
          <h2>Authentication</h2>
        </div>
        <p>Pass your API key in the <code>X-API-Key</code> header on every request. Never expose live keys in client-side code.</p>

        <?php $this_key = 'op_test_your_key_here'; ?>
        <?php ob_start(); ?>
curl -X GET "<?= APP_URL ?>/api/v1/transactions" \
  -H "X-API-Key: op_test_your_key_here" \
  -H "Content-Type: application/json"<?php $curl_auth = ob_get_clean(); ?>

        <div class="code-wrap">
          <div class="code-tabs">
            <div class="code-tab active" onclick="switchTab(this,'auth')">cURL</div>
            <div class="code-tab" onclick="switchTab(this,'auth')">PHP</div>
            <div class="code-tab" onclick="switchTab(this,'auth')">Node.js</div>
            <div class="code-tab" onclick="switchTab(this,'auth')">Python</div>
          </div>
          <div class="code-pane active"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code">curl -X GET <span class="str">"<?= APP_URL ?>/api/v1/transactions"</span> \
  -H <span class="str">"X-API-Key: op_test_your_key_here"</span> \
  -H <span class="str">"Content-Type: application/json"</span></pre></div>
          <div class="code-pane"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code">$response = file_get_contents(<span class="str">'<?= APP_URL ?>/api/v1/transactions'</span>, <span class="kw">false</span>,
    stream_context_create([<span class="str">'http'</span> => [
        <span class="str">'header'</span> => <span class="str">"X-API-Key: op_test_your_key_here\r\nContent-Type: application/json"</span>
    ]])
);
$data = json_decode($response, <span class="kw">true</span>);</pre></div>
          <div class="code-pane"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code"><span class="kw">const</span> res = <span class="kw">await</span> fetch(<span class="str">'<?= APP_URL ?>/api/v1/transactions'</span>, {
  headers: {
    <span class="str">'X-API-Key'</span>: <span class="str">'op_test_your_key_here'</span>,
    <span class="str">'Content-Type'</span>: <span class="str">'application/json'</span>
  }
});
<span class="kw">const</span> data = <span class="kw">await</span> res.json();</pre></div>
          <div class="code-pane"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code"><span class="kw">import</span> requests
res = requests.get(
    <span class="str">'<?= APP_URL ?>/api/v1/transactions'</span>,
    headers={<span class="str">'X-API-Key'</span>: <span class="str">'op_test_your_key_here'</span>}
)
data = res.json()</pre></div>
        </div>

        <table class="param-table">
          <thead><tr><th>Prefix</th><th>Mode</th><th>Behaviour</th></tr></thead>
          <tbody>
            <tr><td class="pname">op_test_…</td><td><span style="background:#fef9c3;color:#854d0e;padding:2px 8px;border-radius:4px;font-size:.72rem;font-weight:700">SANDBOX</span></td><td>Simulated payments, no real money moved</td></tr>
            <tr><td class="pname">op_live_…</td><td><span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:4px;font-size:.72rem;font-weight:700">LIVE</span></td><td>Real transactions via Safaricom / card processor</td></tr>
          </tbody>
        </table>
      </div>

      <!-- ─── ERRORS ────────────────────────────────────────── -->
      <div class="doc-section" id="errors">
        <div class="doc-section-title">
          <div class="icon" style="background:#fef2f2;color:#dc2626"><i class="fas fa-exclamation-triangle"></i></div>
          <h2>Errors & Status Codes</h2>
        </div>
        <p>All error responses have <code>success: false</code> and a human-readable <code>message</code>. HTTP status codes follow REST conventions.</p>
        <table class="param-table" style="margin-bottom:20px">
          <thead><tr><th>Code</th><th>Meaning</th></tr></thead>
          <tbody>
            <tr><td><span style="font-weight:700;color:#166534">200</span></td><td>Success</td></tr>
            <tr><td><span style="font-weight:700;color:#92400e">400</span></td><td>Bad Request — missing or malformed parameter</td></tr>
            <tr><td><span style="font-weight:700;color:#dc2626">401</span></td><td>Unauthorized — API key missing or invalid</td></tr>
            <tr><td><span style="font-weight:700;color:#dc2626">403</span></td><td>Forbidden — account suspended</td></tr>
            <tr><td><span style="font-weight:700;color:#dc2626">404</span></td><td>Not Found</td></tr>
            <tr><td><span style="font-weight:700;color:#92400e">422</span></td><td>Validation Error</td></tr>
            <tr><td><span style="font-weight:700;color:#dc2626">429</span></td><td>Rate limit exceeded (100 req/min)</td></tr>
            <tr><td><span style="font-weight:700;color:#dc2626">500</span></td><td>Internal Server Error</td></tr>
          </tbody>
        </table>
        <div class="resp-label">Error Response</div>
        <pre class="json">{
  "<span style="color:var(--navy);font-weight:600">success</span>": <span style="color:#dc2626">false</span>,
  "<span style="color:var(--navy);font-weight:600">message</span>": "phone is required",
  "<span style="color:var(--navy);font-weight:600">errors</span>": { "phone": "The phone field is required" }
}</pre>
      </div>

      <!-- ─── M-PESA ─────────────────────────────────────────── -->
      <div class="doc-section" id="mpesa">
        <div class="doc-section-title">
          <div class="icon" style="background:#dcfce7;color:#166534"><i class="fas fa-mobile-alt"></i></div>
          <h2>M-Pesa STK Push</h2>
        </div>
        <p>Trigger a payment prompt on the customer's Safaricom phone. They enter their M-Pesa PIN to approve. Poll <code>/payments/status/{ref}</code> or receive a webhook when the payment completes.</p>

        <!-- POST /payments/mpesa/stk -->
        <div class="endpoint">
          <div class="endpoint-head" onclick="toggleEndpoint(this)">
            <span class="method method-post">POST</span>
            <code class="endpoint-url">/payments/mpesa/stk</code>
            <span class="endpoint-desc">Initiate STK Push</span>
            <i class="fas fa-chevron-down" style="margin-left:auto;color:var(--muted);font-size:.8rem;transition:transform .2s"></i>
          </div>
          <div class="endpoint-body open">
            <h3 style="font-size:.9rem;font-weight:700;color:var(--navy);margin-bottom:10px">Request Body</h3>
            <table class="param-table">
              <thead><tr><th>Field</th><th>Type</th><th></th><th>Description</th></tr></thead>
              <tbody>
                <tr><td class="pname">phone</td><td class="ptype">string</td><td><span class="req">required</span></td><td>Safaricom number — <code>07XXXXXXXX</code> or <code>254XXXXXXXXX</code></td></tr>
                <tr><td class="pname">amount</td><td class="ptype">integer</td><td><span class="req">required</span></td><td>Amount in KES (minimum 1)</td></tr>
                <tr><td class="pname">description</td><td class="ptype">string</td><td><span class="req">required</span></td><td>Payment description shown to customer (max 100 chars)</td></tr>
                <tr><td class="pname">callback_url</td><td class="ptype">string</td><td><span class="opt">optional</span></td><td>Override webhook URL for this transaction</td></tr>
              </tbody>
            </table>

            <div class="code-wrap">
              <div class="code-tabs">
                <div class="code-tab active" onclick="switchTab(this,'mpesa-stk')">cURL</div>
                <div class="code-tab" onclick="switchTab(this,'mpesa-stk')">PHP</div>
                <div class="code-tab" onclick="switchTab(this,'mpesa-stk')">Node.js</div>
                <div class="code-tab" onclick="switchTab(this,'mpesa-stk')">Python</div>
              </div>
              <div class="code-pane active"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code">curl -X POST <span class="str">"<?= APP_URL ?>/api/v1/payments/mpesa/stk"</span> \
  -H <span class="str">"X-API-Key: op_test_your_key"</span> \
  -H <span class="str">"Content-Type: application/json"</span> \
  -d <span class="str">'{"phone":"0712345678","amount":500,"description":"Order #1042"}'</span></pre></div>
              <div class="code-pane"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code">$ch = curl_init(<span class="str">'<?= APP_URL ?>/api/v1/payments/mpesa/stk'</span>);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => <span class="kw">true</span>,
    CURLOPT_POST           => <span class="kw">true</span>,
    CURLOPT_HTTPHEADER     => [<span class="str">'X-API-Key: op_test_your_key'</span>, <span class="str">'Content-Type: application/json'</span>],
    CURLOPT_POSTFIELDS     => json_encode([
        <span class="str">'phone'</span>       => <span class="str">'0712345678'</span>,
        <span class="str">'amount'</span>      => <span class="num">500</span>,
        <span class="str">'description'</span> => <span class="str">'Order #1042'</span>
    ])
]);
$data = json_decode(curl_exec($ch), <span class="kw">true</span>);
<span class="cmt">// Poll $data['reference'] every 3s using GET /payments/status/{ref}</span></pre></div>
              <div class="code-pane"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code"><span class="kw">const</span> res = <span class="kw">await</span> fetch(<span class="str">'<?= APP_URL ?>/api/v1/payments/mpesa/stk'</span>, {
  method: <span class="str">'POST'</span>,
  headers: { <span class="str">'X-API-Key'</span>: <span class="str">'op_test_your_key'</span>, <span class="str">'Content-Type'</span>: <span class="str">'application/json'</span> },
  body: JSON.stringify({ phone: <span class="str">'0712345678'</span>, amount: <span class="num">500</span>, description: <span class="str">'Order #1042'</span> })
});
<span class="kw">const</span> { reference } = (<span class="kw">await</span> res.json()).data;</pre></div>
              <div class="code-pane"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code">res = requests.post(
    <span class="str">'<?= APP_URL ?>/api/v1/payments/mpesa/stk'</span>,
    headers={<span class="str">'X-API-Key'</span>: <span class="str">'op_test_your_key'</span>},
    json={<span class="str">'phone'</span>: <span class="str">'0712345678'</span>, <span class="str">'amount'</span>: <span class="num">500</span>, <span class="str">'description'</span>: <span class="str">'Order #1042'</span>}
)
reference = res.json()[<span class="str">'data'</span>][<span class="str">'reference'</span>]</pre></div>
            </div>

            <div class="resp-label">Response (201 Created)</div>
            <pre class="json">{
  "<span style="color:var(--navy);font-weight:600">success</span>": true,
  "<span style="color:var(--navy);font-weight:600">message</span>": "STK Push sent. Awaiting customer confirmation.",
  "<span style="color:var(--navy);font-weight:600">data</span>": {
    "<span style="color:var(--navy)">reference</span>": "TXN-A3F9B2-20260614",
    "<span style="color:var(--navy)">checkout_request_id</span>": "ws_CO_SIM_...",
    "<span style="color:var(--navy)">status</span>": "pending"
  }
}</pre>

            <!-- Try-It -->
            <div class="try-it">
              <div class="try-it-hd"><i class="fas fa-play-circle" style="color:var(--green)"></i> Try It</div>
              <div class="try-field">
                <label>X-API-Key</label>
                <input type="text" id="mpesa-key" placeholder="op_test_your_key_here" value="<?= is_logged_in() ? '' : '' ?>">
              </div>
              <div class="try-row">
                <div class="try-field"><label>phone</label><input type="text" id="mpesa-phone" value="0712345678"></div>
                <div class="try-field"><label>amount (KES)</label><input type="number" id="mpesa-amount" value="100"></div>
              </div>
              <div class="try-field"><label>description</label><input type="text" id="mpesa-desc" value="Test payment"></div>
              <button class="try-btn" onclick="tryMpesa()"><i class="fas fa-paper-plane"></i> Send Request</button>
              <div class="try-result" id="mpesa-result"></div>
            </div>
          </div>
        </div>

        <!-- GET /payments/status/{ref} -->
        <div class="endpoint">
          <div class="endpoint-head" onclick="toggleEndpoint(this)">
            <span class="method method-get">GET</span>
            <code class="endpoint-url">/payments/status/{reference}</code>
            <span class="endpoint-desc">Poll payment status</span>
            <i class="fas fa-chevron-down" style="margin-left:auto;color:var(--muted);font-size:.8rem;transition:transform .2s"></i>
          </div>
          <div class="endpoint-body">
            <p>Poll the status of any pending transaction. In sandbox, M-Pesa payments auto-complete after ~9 seconds.</p>
            <div class="resp-label">Response</div>
            <pre class="json">{
  "<span style="color:var(--navy);font-weight:600">success</span>": true,
  "<span style="color:var(--navy);font-weight:600">data</span>": {
    "<span style="color:var(--navy)">reference</span>": "TXN-A3F9B2-20260614",
    "<span style="color:var(--navy)">status</span>": "completed",
    "<span style="color:var(--navy)">amount</span>": "500.00",
    "<span style="color:var(--navy)">channel</span>": "mpesa"
  }
}</pre>
          </div>
        </div>
      </div>

      <!-- ─── CARD PAYMENTS ──────────────────────────────────── -->
      <div class="doc-section" id="cards">
        <div class="doc-section-title">
          <div class="icon" style="background:#eff6ff;color:#1d4ed8"><i class="fas fa-credit-card"></i></div>
          <h2>Card Payments</h2>
        </div>
        <p>Accept Visa and Mastercard payments. Send card details directly to the OrbitPesa API — all card data is handled server-side within your PCI-compliant API call.</p>
        <div class="warn-box"><i class="fas fa-shield-alt"></i>
          <div><strong>Test Cards:</strong> Use <code>4242 4242 4242 4242</code> (Visa) or <code>5500 0000 0000 0004</code> (Mastercard) with any future expiry and any 3-digit CVV in sandbox mode.</div>
        </div>

        <div class="endpoint">
          <div class="endpoint-head" onclick="toggleEndpoint(this)">
            <span class="method method-post">POST</span>
            <code class="endpoint-url">/payments/card/charge</code>
            <span class="endpoint-desc">Charge a card</span>
            <i class="fas fa-chevron-down" style="margin-left:auto;color:var(--muted);font-size:.8rem;transition:transform .2s"></i>
          </div>
          <div class="endpoint-body open">
            <table class="param-table">
              <thead><tr><th>Field</th><th>Type</th><th></th><th>Description</th></tr></thead>
              <tbody>
                <tr><td class="pname">card_number</td><td class="ptype">string</td><td><span class="req">required</span></td><td>Card number (digits only, 13–19 digits)</td></tr>
                <tr><td class="pname">exp_month</td><td class="ptype">string</td><td><span class="req">required</span></td><td>Expiry month (01–12)</td></tr>
                <tr><td class="pname">exp_year</td><td class="ptype">string</td><td><span class="req">required</span></td><td>Expiry year (2-digit: 25, 26…)</td></tr>
                <tr><td class="pname">cvv</td><td class="ptype">string</td><td><span class="req">required</span></td><td>3 or 4-digit security code</td></tr>
                <tr><td class="pname">card_holder</td><td class="ptype">string</td><td><span class="req">required</span></td><td>Name as printed on card</td></tr>
                <tr><td class="pname">amount</td><td class="ptype">integer</td><td><span class="req">required</span></td><td>Amount in KES (minimum 50)</td></tr>
                <tr><td class="pname">description</td><td class="ptype">string</td><td><span class="opt">optional</span></td><td>Payment description</td></tr>
              </tbody>
            </table>

            <div class="code-wrap">
              <div class="code-tabs">
                <div class="code-tab active" onclick="switchTab(this,'card')">cURL</div>
                <div class="code-tab" onclick="switchTab(this,'card')">PHP</div>
                <div class="code-tab" onclick="switchTab(this,'card')">Node.js</div>
                <div class="code-tab" onclick="switchTab(this,'card')">Python</div>
              </div>
              <div class="code-pane active"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code">curl -X POST <span class="str">"<?= APP_URL ?>/api/v1/payments/card/charge"</span> \
  -H <span class="str">"X-API-Key: op_test_your_key"</span> \
  -H <span class="str">"Content-Type: application/json"</span> \
  -d <span class="str">'{"card_number":"4242424242424242","exp_month":"12","exp_year":"27","cvv":"123","card_holder":"Jane Doe","amount":1000}'</span></pre></div>
              <div class="code-pane"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code">$data = json_decode(file_get_contents(<span class="str">'<?= APP_URL ?>/api/v1/payments/card/charge'</span>, <span class="kw">false</span>,
    stream_context_create([<span class="str">'http'</span> => [
        <span class="str">'method'</span>  => <span class="str">'POST'</span>,
        <span class="str">'header'</span>  => <span class="str">"X-API-Key: op_test_your_key\r\nContent-Type: application/json"</span>,
        <span class="str">'content'</span> => json_encode([
            <span class="str">'card_number'</span> => <span class="str">'4242424242424242'</span>,
            <span class="str">'exp_month'</span>   => <span class="str">'12'</span>, <span class="str">'exp_year'</span> => <span class="str">'27'</span>,
            <span class="str">'cvv'</span>         => <span class="str">'123'</span>, <span class="str">'card_holder'</span> => <span class="str">'Jane Doe'</span>,
            <span class="str">'amount'</span>      => <span class="num">1000</span>
        ])
    ]])
), <span class="kw">true</span>);</pre></div>
              <div class="code-pane"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code"><span class="kw">const</span> res = <span class="kw">await</span> fetch(<span class="str">'<?= APP_URL ?>/api/v1/payments/card/charge'</span>, {
  method: <span class="str">'POST'</span>,
  headers: { <span class="str">'X-API-Key'</span>: <span class="str">'op_test_your_key'</span>, <span class="str">'Content-Type'</span>: <span class="str">'application/json'</span> },
  body: JSON.stringify({
    card_number: <span class="str">'4242424242424242'</span>, exp_month: <span class="str">'12'</span>, exp_year: <span class="str">'27'</span>,
    cvv: <span class="str">'123'</span>, card_holder: <span class="str">'Jane Doe'</span>, amount: <span class="num">1000</span>
  })
});</pre></div>
              <div class="code-pane"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code">res = requests.post(<span class="str">'<?= APP_URL ?>/api/v1/payments/card/charge'</span>,
    headers={<span class="str">'X-API-Key'</span>: <span class="str">'op_test_your_key'</span>},
    json={<span class="str">'card_number'</span>: <span class="str">'4242424242424242'</span>, <span class="str">'exp_month'</span>: <span class="str">'12'</span>,
          <span class="str">'exp_year'</span>: <span class="str">'27'</span>, <span class="str">'cvv'</span>: <span class="str">'123'</span>,
          <span class="str">'card_holder'</span>: <span class="str">'Jane Doe'</span>, <span class="str">'amount'</span>: <span class="num">1000</span>})</pre></div>
            </div>

            <div class="resp-label">Response</div>
            <pre class="json">{
  "<span style="color:var(--navy);font-weight:600">success</span>": true,
  "<span style="color:var(--navy);font-weight:600">message</span>": "Card charged successfully",
  "<span style="color:var(--navy);font-weight:600">data</span>": {
    "<span style="color:var(--navy)">reference</span>": "TXN-B9C1D3-20260614",
    "<span style="color:var(--navy)">status</span>": "completed",
    "<span style="color:var(--navy)">amount</span>": "1000.00",
    "<span style="color:var(--navy)">card_last4</span>": "4242"
  }
}</pre>
          </div>
        </div>
      </div>

      <!-- ─── WALLET PAYMENTS ───────────────────────────────── -->
      <div class="doc-section" id="wallet">
        <div class="doc-section-title">
          <div class="icon" style="background:#fef3c7;color:#92400e"><i class="fas fa-coins"></i></div>
          <h2>Wallet Payments</h2>
        </div>
        <p>Transfer funds between OrbitPesa wallets instantly at zero transaction cost. Look up a recipient by email or phone before sending.</p>

        <div class="endpoint">
          <div class="endpoint-head" onclick="toggleEndpoint(this)">
            <span class="method method-get">GET</span>
            <code class="endpoint-url">/wallet/lookup?q={email_or_phone}</code>
            <span class="endpoint-desc">Find a wallet recipient</span>
            <i class="fas fa-chevron-down" style="margin-left:auto;color:var(--muted);font-size:.8rem;transition:transform .2s"></i>
          </div>
          <div class="endpoint-body">
            <p>Search for a merchant wallet by email address or phone number. Returns masked details for confirmation.</p>
            <div class="resp-label">Response</div>
            <pre class="json">{
  "<span style="color:var(--navy);font-weight:600">success</span>": true,
  "<span style="color:var(--navy);font-weight:600">data</span>": {
    "<span style="color:var(--navy)">id</span>": "abc123-...",
    "<span style="color:var(--navy)">business_name</span>": "Acme Ltd",
    "<span style="color:var(--navy)">phone_masked</span>": "0712****678"
  }
}</pre>
          </div>
        </div>

        <div class="endpoint">
          <div class="endpoint-head" onclick="toggleEndpoint(this)">
            <span class="method method-post">POST</span>
            <code class="endpoint-url">/payments/wallet/pay</code>
            <span class="endpoint-desc">Send wallet payment</span>
            <i class="fas fa-chevron-down" style="margin-left:auto;color:var(--muted);font-size:.8rem;transition:transform .2s"></i>
          </div>
          <div class="endpoint-body open">
            <table class="param-table">
              <thead><tr><th>Field</th><th>Type</th><th></th><th>Description</th></tr></thead>
              <tbody>
                <tr><td class="pname">recipient_id</td><td class="ptype">string</td><td><span class="req">required</span></td><td>Recipient user ID from <code>/wallet/lookup</code></td></tr>
                <tr><td class="pname">amount</td><td class="ptype">number</td><td><span class="req">required</span></td><td>Amount in KES (minimum 1)</td></tr>
                <tr><td class="pname">description</td><td class="ptype">string</td><td><span class="opt">optional</span></td><td>Transfer note</td></tr>
              </tbody>
            </table>

            <div class="code-wrap">
              <div class="code-tabs">
                <div class="code-tab active" onclick="switchTab(this,'wallet')">cURL</div>
                <div class="code-tab" onclick="switchTab(this,'wallet')">PHP</div>
              </div>
              <div class="code-pane active"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code">curl -X POST <span class="str">"<?= APP_URL ?>/api/v1/payments/wallet/pay"</span> \
  -H <span class="str">"X-API-Key: op_test_your_key"</span> \
  -H <span class="str">"Content-Type: application/json"</span> \
  -d <span class="str">'{"recipient_id":"user-uuid","amount":250,"description":"Invoice #88"}'</span></pre></div>
              <div class="code-pane"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code">$payload = [<span class="str">'recipient_id'</span> => <span class="str">'user-uuid'</span>, <span class="str">'amount'</span> => <span class="num">250</span>, <span class="str">'description'</span> => <span class="str">'Invoice #88'</span>];
$ch = curl_init(<span class="str">'<?= APP_URL ?>/api/v1/payments/wallet/pay'</span>);
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=><span class="kw">true</span>,CURLOPT_POST=><span class="kw">true</span>,
    CURLOPT_HTTPHEADER=>[<span class="str">'X-API-Key: op_test_your_key'</span>,<span class="str">'Content-Type: application/json'</span>],
    CURLOPT_POSTFIELDS=>json_encode($payload)]);
$data = json_decode(curl_exec($ch), <span class="kw">true</span>);</pre></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ─── TRANSACTIONS ──────────────────────────────────── -->
      <div class="doc-section" id="transactions">
        <div class="doc-section-title">
          <div class="icon" style="background:#f0fdf4;color:var(--green)"><i class="fas fa-exchange-alt"></i></div>
          <h2>Transactions</h2>
        </div>

        <div class="endpoint">
          <div class="endpoint-head" onclick="toggleEndpoint(this)">
            <span class="method method-get">GET</span>
            <code class="endpoint-url">/transactions</code>
            <span class="endpoint-desc">List transactions</span>
            <i class="fas fa-chevron-down" style="margin-left:auto;color:var(--muted);font-size:.8rem;transition:transform .2s"></i>
          </div>
          <div class="endpoint-body open">
            <table class="param-table">
              <thead><tr><th>Query Param</th><th>Type</th><th>Description</th></tr></thead>
              <tbody>
                <tr><td class="pname">status</td><td class="ptype">string</td><td><code>pending</code> | <code>completed</code> | <code>failed</code></td></tr>
                <tr><td class="pname">channel</td><td class="ptype">string</td><td><code>mpesa</code> | <code>card</code> | <code>wallet</code></td></tr>
                <tr><td class="pname">date_from</td><td class="ptype">string</td><td>YYYY-MM-DD</td></tr>
                <tr><td class="pname">date_to</td><td class="ptype">string</td><td>YYYY-MM-DD</td></tr>
                <tr><td class="pname">limit</td><td class="ptype">integer</td><td>Default 20, max 100</td></tr>
                <tr><td class="pname">page</td><td class="ptype">integer</td><td>Default 1</td></tr>
              </tbody>
            </table>

            <div class="resp-label">Response</div>
            <pre class="json">{
  "<span style="color:var(--navy);font-weight:600">success</span>": true,
  "<span style="color:var(--navy);font-weight:600">data</span>": {
    "<span style="color:var(--navy)">transactions</span>": [
      {
        "<span style="color:var(--navy)">reference</span>": "TXN-A3F9B2-20260614",
        "<span style="color:var(--navy)">amount</span>": "500.00",
        "<span style="color:var(--navy)">fee</span>": "7.50",
        "<span style="color:var(--navy)">currency</span>": "KES",
        "<span style="color:var(--navy)">channel</span>": "mpesa",
        "<span style="color:var(--navy)">phone</span>": "254712345678",
        "<span style="color:var(--navy)">status</span>": "completed",
        "<span style="color:var(--navy)">created_at</span>": "2026-06-14T12:34:56+03:00"
      }
    ],
    "<span style="color:var(--navy)">total</span>": 47,
    "<span style="color:var(--navy)">page</span>": 1,
    "<span style="color:var(--navy)">limit</span>": 20
  }
}</pre>

            <!-- Try-It -->
            <div class="try-it">
              <div class="try-it-hd"><i class="fas fa-play-circle" style="color:var(--green)"></i> Try It</div>
              <div class="try-field"><label>X-API-Key</label><input type="text" id="txn-key" placeholder="op_test_your_key_here"></div>
              <div class="try-row">
                <div class="try-field">
                  <label>status</label>
                  <select id="txn-status">
                    <option value="">All</option><option>completed</option><option>pending</option><option>failed</option>
                  </select>
                </div>
                <div class="try-field">
                  <label>channel</label>
                  <select id="txn-channel">
                    <option value="">All</option><option>mpesa</option><option>card</option><option>wallet</option>
                  </select>
                </div>
              </div>
              <button class="try-btn" onclick="tryTxns()"><i class="fas fa-play"></i> Fetch Transactions</button>
              <div class="try-result" id="txn-result"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ─── PAYMENT LINKS ─────────────────────────────────── -->
      <div class="doc-section" id="payment-links">
        <div class="doc-section-title">
          <div class="icon" style="background:#ede9fe;color:#7c3aed"><i class="fas fa-link"></i></div>
          <h2>Payment Links</h2>
        </div>
        <p>Create shareable payment pages — no coding required from the payer. Fixed-amount or open-amount links, with optional expiry and usage limits.</p>

        <div class="endpoint">
          <div class="endpoint-head" onclick="toggleEndpoint(this)">
            <span class="method method-post">POST</span>
            <code class="endpoint-url">/payment-links</code>
            <span class="endpoint-desc">Create a payment link</span>
            <i class="fas fa-chevron-down" style="margin-left:auto;color:var(--muted);font-size:.8rem;transition:transform .2s"></i>
          </div>
          <div class="endpoint-body open">
            <table class="param-table">
              <thead><tr><th>Field</th><th>Type</th><th></th><th>Description</th></tr></thead>
              <tbody>
                <tr><td class="pname">title</td><td class="ptype">string</td><td><span class="req">required</span></td><td>Name of the payment link</td></tr>
                <tr><td class="pname">amount</td><td class="ptype">number</td><td><span class="opt">optional</span></td><td>Fixed amount in KES. Omit for open-amount links.</td></tr>
                <tr><td class="pname">description</td><td class="ptype">string</td><td><span class="opt">optional</span></td><td>Details shown to payer</td></tr>
                <tr><td class="pname">expires_at</td><td class="ptype">datetime</td><td><span class="opt">optional</span></td><td>Expiry: <code>YYYY-MM-DD HH:MM:SS</code></td></tr>
                <tr><td class="pname">max_uses</td><td class="ptype">integer</td><td><span class="opt">optional</span></td><td>Max payments. Null = unlimited.</td></tr>
              </tbody>
            </table>
            <div class="resp-label">Response</div>
            <pre class="json">{
  "<span style="color:var(--navy);font-weight:600">success</span>": true,
  "<span style="color:var(--navy);font-weight:600">data</span>": {
    "<span style="color:var(--navy)">id</span>": "uuid...",
    "<span style="color:var(--navy)">slug</span>": "a3f9b2c8d1",
    "<span style="color:var(--navy)">url</span>": "<?= APP_URL ?>/pay/a3f9b2c8d1",
    "<span style="color:var(--navy)">title</span>": "Monthly Subscription",
    "<span style="color:var(--navy)">amount</span>": "500.00",
    "<span style="color:var(--navy)">status</span>": "active"
  }
}</pre>
          </div>
        </div>
      </div>

      <!-- ─── WEBHOOKS ──────────────────────────────────────── -->
      <div class="doc-section" id="webhooks">
        <div class="doc-section-title">
          <div class="icon" style="background:#f0fdf4;color:var(--green)"><i class="fas fa-satellite-dish"></i></div>
          <h2>Webhooks</h2>
        </div>
        <p>Configure webhook endpoints in your <a href="<?= APP_URL ?>/dashboard/webhooks" style="color:var(--green)">dashboard</a> to receive real-time events. Each request is signed with HMAC-SHA256.</p>

        <h3 style="font-size:.95rem;font-weight:700;color:var(--navy);margin-bottom:10px">Signature Verification</h3>
        <div class="code-wrap">
          <div class="code-tabs">
            <div class="code-tab active" onclick="switchTab(this,'wh-verify')">PHP</div>
            <div class="code-tab" onclick="switchTab(this,'wh-verify')">Node.js</div>
            <div class="code-tab" onclick="switchTab(this,'wh-verify')">Python</div>
          </div>
          <div class="code-pane active"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code">$payload   = file_get_contents(<span class="str">'php://input'</span>);
$sigHeader = $_SERVER[<span class="str">'HTTP_X_ORBITPESA_SIGNATURE'</span>] ?? <span class="str">''</span>;
$secret    = <span class="str">'your_webhook_secret'</span>; <span class="cmt">// From Dashboard → Webhooks</span>
$expected  = <span class="str">'sha256='</span> . hash_hmac(<span class="str">'sha256'</span>, $payload, $secret);

<span class="kw">if</span> (!hash_equals($expected, $sigHeader)) {
    http_response_code(<span class="num">401</span>); exit(<span class="str">'Invalid signature'</span>);
}
$event = json_decode($payload, <span class="kw">true</span>);
<span class="cmt">// Handle $event['event'] ...</span>
http_response_code(<span class="num">200</span>);</pre></div>
          <div class="code-pane"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code"><span class="kw">const</span> crypto = require(<span class="str">'crypto'</span>);
app.post(<span class="str">'/webhook'</span>, express.raw({type: <span class="str">'application/json'</span>}), (req, res) => {
  <span class="kw">const</span> sig      = req.headers[<span class="str">'x-orbitpesa-signature'</span>];
  <span class="kw">const</span> expected = <span class="str">'sha256='</span> + crypto.createHmac(<span class="str">'sha256'</span>, secret).update(req.body).digest(<span class="str">'hex'</span>);
  <span class="kw">if</span> (!crypto.timingSafeEqual(Buffer.from(sig), Buffer.from(expected))) {
    <span class="kw">return</span> res.status(<span class="num">401</span>).send(<span class="str">'Invalid signature'</span>);
  }
  <span class="kw">const</span> event = JSON.parse(req.body);
  <span class="cmt">// Handle event.event ...</span>
  res.status(<span class="num">200</span>).send(<span class="str">'OK'</span>);
});</pre></div>
          <div class="code-pane"><button class="code-copy" onclick="copyCode(this)">Copy</button>
<pre class="code"><span class="kw">import</span> hmac, hashlib
<span class="kw">def</span> verify(payload_bytes, sig_header, secret):
    expected = <span class="str">'sha256='</span> + hmac.new(secret.encode(), payload_bytes, hashlib.sha256).hexdigest()
    <span class="kw">return</span> hmac.compare_digest(expected, sig_header)

<span class="cmt"># In your Flask/Django view:</span>
<span class="kw">if not</span> verify(request.data, request.headers.get(<span class="str">'X-OrbitPesa-Signature'</span>,<span class="str">''</span>), SECRET):
    abort(<span class="num">401</span>)</pre></div>
        </div>

        <h3 style="font-size:.95rem;font-weight:700;color:var(--navy);margin:20px 0 10px">Event Reference</h3>
        <table class="param-table">
          <thead><tr><th>Event</th><th>Trigger</th></tr></thead>
          <tbody>
            <tr><td><span class="event-badge">payment.completed</span></td><td>Payment successfully received and credited to wallet</td></tr>
            <tr><td><span class="event-badge">payment.failed</span></td><td>Customer cancelled, timeout, or card declined</td></tr>
            <tr><td><span class="event-badge">payment.pending</span></td><td>M-Pesa STK Push sent, awaiting PIN</td></tr>
            <tr><td><span class="event-badge">withdrawal.created</span></td><td>Merchant requested a withdrawal</td></tr>
            <tr><td><span class="event-badge">withdrawal.done</span></td><td>Withdrawal disbursed to M-Pesa or bank</td></tr>
          </tbody>
        </table>

        <h3 style="font-size:.95rem;font-weight:700;color:var(--navy);margin:20px 0 10px">Example Payload</h3>
        <pre class="json">{
  "<span style="color:var(--navy);font-weight:600">event</span>": "payment.completed",
  "<span style="color:var(--navy);font-weight:600">timestamp</span>": "2026-06-14T12:34:56+03:00",
  "<span style="color:var(--navy);font-weight:600">data</span>": {
    "<span style="color:var(--navy)">reference</span>": "TXN-A3F9B2-20260614",
    "<span style="color:var(--navy)">amount</span>": 500,
    "<span style="color:var(--navy)">currency</span>": "KES",
    "<span style="color:var(--navy)">channel</span>": "mpesa",
    "<span style="color:var(--navy)">status</span>": "completed",
    "<span style="color:var(--navy)">phone</span>": "254712345678",
    "<span style="color:var(--navy)">card_last4</span>": null,
    "<span style="color:var(--navy)">description</span>": "Order #1042",
    "<span style="color:var(--navy)">created_at</span>": "2026-06-14T12:34:48+03:00"
  }
}</pre>
      </div>

      <!-- ─── SDKs ──────────────────────────────────────────── -->
      <div class="doc-section" id="sdks">
        <div class="doc-section-title">
          <div class="icon" style="background:#fef9c3;color:#92400e"><i class="fas fa-cubes"></i></div>
          <h2>Libraries & SDKs</h2>
        </div>
        <p>Official SDKs simplify integration. All libraries are thin wrappers around the REST API and use the same API key authentication.</p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:28px">
          <?php foreach ([
            ['PHP','fab fa-php','#7c3aed','composer require orbitpesa/php-sdk'],
            ['Node.js','fab fa-node-js','#166534','npm install orbitpesa'],
            ['Python','fab fa-python','#1d4ed8','pip install orbitpesa'],
            ['Go','fas fa-code','#64748b','go get github.com/orbitpesa/go-sdk'],
          ] as [$n,$i,$c,$cmd]): ?>
          <div class="endpoint" style="margin:0">
            <div style="padding:14px 16px">
              <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                <i class="<?=$i?>" style="color:<?=$c?>;font-size:1.1rem"></i>
                <strong style="color:var(--navy);font-size:.88rem"><?=$n?></strong>
              </div>
              <code style="font-size:.76rem;background:var(--bg);padding:4px 8px;border-radius:4px;color:var(--muted);display:block"><?=$cmd?></code>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="info-box"><i class="fas fa-info-circle"></i>
          <div>SDKs are in active development. Until released, use the REST API directly with the code examples above. Contact <strong>developers@orbitpesa.com</strong> for early access.</div>
        </div>
      </div>

      <!-- Footer -->
      <div style="padding:32px 0;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
        <p style="color:var(--muted);font-size:.82rem;margin:0">&copy; <?= date('Y') ?> OrbitPesa Ltd.</p>
        <div style="display:flex;gap:18px">
          <a href="<?= APP_URL ?>/" style="color:var(--muted);font-size:.82rem;text-decoration:none">Home</a>
          <a href="<?= APP_URL ?>/developers" style="color:var(--muted);font-size:.82rem;text-decoration:none">Console</a>
          <a href="mailto:developers@orbitpesa.com" style="color:var(--green);font-size:.82rem;text-decoration:none">Developer Support</a>
        </div>
      </div>

    </div><!-- /docs-content -->
  </div><!-- /docs-main -->
</div><!-- /docs-layout -->

<script>
// ── Sidebar active link ───────────────────────────────────
const sections = document.querySelectorAll('.doc-section');
const sbLinks  = document.querySelectorAll('.sb-link[href^="#"]');
window.addEventListener('scroll', () => {
  let cur = '';
  sections.forEach(s => { if (window.scrollY >= s.offsetTop - 80) cur = s.id; });
  sbLinks.forEach(a => a.classList.toggle('active', a.getAttribute('href') === '#' + cur));
}, { passive: true });

// ── Code language tabs ────────────────────────────────────
function switchTab(tab, group) {
  const wrap   = tab.closest('.code-wrap');
  const tabs   = wrap.querySelectorAll('.code-tab');
  const panes  = wrap.querySelectorAll('.code-pane');
  const idx    = Array.from(tabs).indexOf(tab);
  tabs.forEach((t,i)  => t.classList.toggle('active', i === idx));
  panes.forEach((p,i) => p.classList.toggle('active', i === idx));
}

// ── Copy code ─────────────────────────────────────────────
function copyCode(btn) {
  const code = btn.nextElementSibling.textContent;
  navigator.clipboard.writeText(code.trim()).then(() => {
    btn.textContent = 'Copied!';
    setTimeout(() => btn.textContent = 'Copy', 1500);
  });
}

// ── Toggle endpoint body ──────────────────────────────────
function toggleEndpoint(head) {
  const body    = head.nextElementSibling;
  const icon    = head.querySelector('.fa-chevron-down');
  const isOpen  = body.classList.toggle('open');
  if (icon) icon.style.transform = isOpen ? 'rotate(180deg)' : '';
}

// ── API try-it helpers ────────────────────────────────────
const BASE = '<?= APP_URL ?>/api/v1';

function showResult(id, data, ok) {
  const el = document.getElementById(id);
  el.style.display = 'block';
  el.innerHTML = `
    <div class="try-result-status ${ok ? 'try-ok' : 'try-err'}">
      <i class="fas fa-${ok ? 'check' : 'times'}-circle"></i> ${ok ? 'Success' : 'Error'}
    </div>
    <pre class="try-json">${JSON.stringify(data, null, 2)}</pre>`;
}

async function tryMpesa() {
  const btn = event.target.closest('button');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending…';
  try {
    const res = await fetch(BASE + '/payments/mpesa/stk', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-API-Key': document.getElementById('mpesa-key').value.trim() },
      body: JSON.stringify({
        phone: document.getElementById('mpesa-phone').value,
        amount: parseInt(document.getElementById('mpesa-amount').value),
        description: document.getElementById('mpesa-desc').value
      })
    });
    const data = await res.json();
    showResult('mpesa-result', data, data.success);
  } catch(e) { showResult('mpesa-result', {error: e.message}, false); }
  btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Request';
}

async function tryTxns() {
  const btn = event.target.closest('button');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fetching…';
  try {
    const params = new URLSearchParams();
    const status = document.getElementById('txn-status').value;
    const ch     = document.getElementById('txn-channel').value;
    if (status) params.set('status', status);
    if (ch)     params.set('channel', ch);
    const res = await fetch(BASE + '/transactions?' + params, {
      headers: { 'X-API-Key': document.getElementById('txn-key').value.trim() }
    });
    const data = await res.json();
    showResult('txn-result', data, data.success);
  } catch(e) { showResult('txn-result', {error: e.message}, false); }
  btn.disabled = false; btn.innerHTML = '<i class="fas fa-play"></i> Fetch Transactions';
}
</script>
</body>
</html>
