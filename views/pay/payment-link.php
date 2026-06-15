<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Pay — <?= sanitize($link['title']) ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --green: #158347; --navy: #0D1B3E;
      --green-light: #dcfce7; --border: #e2e8f0;
      --text: #1e293b; --muted: #64748b; --bg: #f8fafc;
      --radius: 10px; --radius-lg: 16px;
      --shadow: 0 8px 32px rgba(13,27,62,.12);
    }
    body { font-family:'Inter',sans-serif; background:var(--bg); min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:16px; }

    /* Layout */
    .pay-wrap { width:100%; max-width:440px; }
    .pay-card  { background:#fff; border-radius:var(--radius-lg); box-shadow:var(--shadow); overflow:hidden; }

    /* Header */
    .pay-hd { background:var(--navy); padding:28px 24px 24px; text-align:center; }
    .pay-hd-mark { width:48px;height:48px;background:var(--green);border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;color:#fff;margin:0 auto 12px; }
    .pay-hd h2   { color:#fff;font-size:1.05rem;font-weight:700;margin-bottom:4px; }
    .pay-hd .merch{ color:rgba(255,255,255,.55);font-size:.8rem;margin-bottom:10px; }
    .pay-hd .amt  { font-size:2rem;font-weight:900;color:#fff;line-height:1; }
    .pay-hd .desc { font-size:.78rem;color:rgba(255,255,255,.5);margin-top:6px; }

    /* Body */
    .pay-bd { padding:24px; }

    /* Custom amount */
    .pay-amount-wrap { margin-bottom:20px; }
    .pay-amount-wrap label { display:block;font-size:.8rem;font-weight:600;color:var(--muted);margin-bottom:6px; }
    .pay-amount-input { display:flex;border:1.5px solid var(--border);border-radius:var(--radius);overflow:hidden; }
    .pay-amount-input span { background:var(--bg);padding:0 12px;display:flex;align-items:center;font-size:.85rem;color:var(--muted);border-right:1.5px solid var(--border); }
    .pay-amount-input input { flex:1;border:none;outline:none;padding:10px 12px;font-size:1rem;font-weight:700;font-family:inherit; }

    /* Channels */
    .channels { display:flex;gap:10px;margin-bottom:20px; }
    .ch-btn { flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 8px;border:2px solid var(--border);border-radius:var(--radius);cursor:pointer;background:#fff;transition:all .15s;font-family:inherit; }
    .ch-btn i   { font-size:1.25rem;color:var(--muted); }
    .ch-btn span{ font-size:.75rem;font-weight:600;color:var(--muted); }
    .ch-btn.active { border-color:var(--green);background:var(--green-light); }
    .ch-btn.active i, .ch-btn.active span { color:var(--green); }

    /* Forms */
    .field { margin-bottom:14px; }
    .field label { display:block;font-size:.78rem;font-weight:600;color:var(--muted);margin-bottom:5px; }
    .field input  { width:100%;border:1.5px solid var(--border);border-radius:var(--radius);padding:10px 12px;font-size:.9rem;font-family:inherit;outline:none;transition:border-color .15s; }
    .field input:focus { border-color:var(--green); }
    .field input.error { border-color:#dc2626; }
    .row-2 { display:grid;grid-template-columns:1fr 1fr;gap:12px; }

    /* Card visual */
    .card-vis { background:linear-gradient(135deg,var(--navy) 0%,#1a3a7c 100%);border-radius:12px;padding:18px 18px 14px;color:#fff;margin-bottom:16px; }
    .card-vis.visa   { background:linear-gradient(135deg,#1a1f71,#283593); }
    .card-vis.master { background:linear-gradient(135deg,#1b2030,#c62828); }
    .card-vis-chip   { font-size:1.5rem;opacity:.6;margin-bottom:10px; }
    .card-vis-num    { font-size:.95rem;letter-spacing:.2em;font-weight:600;margin-bottom:14px; }
    .card-vis-foot   { display:flex;justify-content:space-between;font-size:.7rem; }
    .card-vis-foot .lbl { opacity:.5;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;font-size:.6rem; }
    .card-vis-foot .val { font-weight:600;font-size:.78rem; }

    /* Pay button */
    .pay-btn { width:100%;padding:14px;background:var(--green);color:#fff;border:none;border-radius:var(--radius);font-size:1rem;font-weight:700;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .15s;margin-top:4px; }
    .pay-btn:hover:not(:disabled) { background:#117a3e; }
    .pay-btn:disabled { opacity:.6;cursor:not-allowed; }

    /* Alert */
    .alert { padding:12px 14px;border-radius:var(--radius);font-size:.84rem;display:flex;gap:8px;align-items:flex-start;margin-bottom:16px; }
    .alert-danger  { background:#fef2f2;color:#991b1b;border:1px solid #fecaca; }
    .alert-success { background:#f0fdf4;color:#166534;border:1px solid #bbf7d0; }
    .alert-info    { background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe; }

    /* Polling / status screens */
    .screen { display:none;text-align:center;padding:32px 24px; }
    .screen.active { display:block; }
    .spin { width:56px;height:56px;border:4px solid #e2e8f0;border-top-color:var(--green);border-radius:50%;animation:spin .75s linear infinite;margin:0 auto 18px; }
    @keyframes spin { to { transform:rotate(360deg); } }
    .check-circle { width:64px;height:64px;background:var(--green-light);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:1.6rem;color:var(--green); }
    .x-circle     { width:64px;height:64px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:1.6rem;color:#dc2626; }
    .screen h3    { font-size:1.15rem;font-weight:800;color:var(--navy);margin-bottom:8px; }
    .screen p     { font-size:.875rem;color:var(--muted);line-height:1.6; }
    .screen .ref  { font-family:monospace;font-size:.78rem;background:var(--bg);padding:6px 12px;border-radius:6px;display:inline-block;margin-top:10px;color:var(--muted); }
    .screen .retry{ margin-top:16px;background:none;border:1.5px solid var(--border);border-radius:var(--radius);padding:8px 20px;font-size:.85rem;font-weight:600;font-family:inherit;cursor:pointer;color:var(--text); }
    .screen .retry:hover { border-color:var(--green);color:var(--green); }

    /* Footer */
    .pay-ft { padding:14px 24px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:center;gap:6px;font-size:.72rem;color:var(--muted); }
    .pay-ft i { color:var(--green); }

    /* Page footer */
    .site-ft { margin-top:18px;text-align:center;font-size:.72rem;color:var(--muted); }
    .site-ft a { color:var(--green);text-decoration:none; }

    @media(max-width:480px) {
      body { padding:0;align-items:flex-start; }
      .pay-card { border-radius:0;min-height:100vh;box-shadow:none; }
    }
  </style>
</head>
<body>
<div class="pay-wrap">
  <div class="pay-card">

    <!-- Header -->
    <div class="pay-hd">
      <div class="pay-hd-mark">OP</div>
      <h2><?= sanitize($link['title']) ?></h2>
      <div class="merch"><?= sanitize($link['business_name']) ?></div>
      <?php if ($link['amount']): ?>
        <div class="amt"><?= format_amount($link['amount']) ?></div>
      <?php endif; ?>
      <?php if ($link['description']): ?>
        <div class="desc"><?= sanitize($link['description']) ?></div>
      <?php endif; ?>
    </div>

    <!-- === CHECKOUT FORM SCREEN === -->
    <div id="screenForm" class="screen active" style="padding:0">
      <div class="pay-bd">
        <div id="alertBox" class="alert alert-danger" style="display:none"></div>

        <!-- Custom amount -->
        <?php if (!$link['is_fixed_amount']): ?>
        <div class="pay-amount-wrap">
          <label>Amount (KES)</label>
          <div class="pay-amount-input">
            <span>KES</span>
            <input type="number" id="customAmount" placeholder="Enter amount" min="1" step="1">
          </div>
        </div>
        <?php endif; ?>

        <!-- Channel selector -->
        <div class="channels">
          <button type="button" class="ch-btn active" id="chMpesa" onclick="setChannel('mpesa')">
            <i class="fas fa-mobile-alt"></i>
            <span>M-Pesa</span>
          </button>
          <button type="button" class="ch-btn" id="chCard" onclick="setChannel('card')">
            <i class="fas fa-credit-card"></i>
            <span>Card</span>
          </button>
        </div>

        <!-- M-Pesa fields -->
        <div id="fMpesa">
          <div class="field">
            <label>M-Pesa Phone Number</label>
            <input type="tel" id="mpesaPhone" placeholder="07XX XXX XXX" maxlength="13" inputmode="tel">
          </div>
          <div class="field">
            <label>Email <span style="font-weight:400;color:var(--muted)">(optional — for receipt)</span></label>
            <input type="email" id="mpesaEmail" placeholder="your@email.com">
          </div>
          <div class="alert alert-info" style="font-size:.78rem">
            <i class="fas fa-info-circle"></i>
            <span>You will receive a PIN prompt on your phone. Enter your M-Pesa PIN to approve.</span>
          </div>
        </div>

        <!-- Card fields -->
        <div id="fCard" style="display:none">
          <!-- Card visual -->
          <div class="card-vis" id="cardVis">
            <div class="card-vis-chip"><i class="fas fa-microchip"></i></div>
            <div class="card-vis-num" id="visNum">•••• •••• •••• ••••</div>
            <div class="card-vis-foot">
              <div><div class="lbl">Cardholder</div><div class="val" id="visName">FULL NAME</div></div>
              <div style="text-align:right"><div class="lbl">Expires</div><div class="val" id="visExp">MM/YY</div></div>
            </div>
          </div>
          <div class="field">
            <label>Card Number</label>
            <input type="text" id="cardNum" placeholder="0000 0000 0000 0000" maxlength="19" inputmode="numeric" autocomplete="cc-number">
          </div>
          <div class="row-2">
            <div class="field">
              <label>Expiry Date</label>
              <input type="text" id="cardExp" placeholder="MM/YY" maxlength="5" inputmode="numeric" autocomplete="cc-exp">
            </div>
            <div class="field">
              <label>CVV</label>
              <input type="text" id="cardCvv" placeholder="•••" maxlength="4" inputmode="numeric" autocomplete="cc-csc">
            </div>
          </div>
          <div class="field">
            <label>Cardholder Name</label>
            <input type="text" id="cardHolder" placeholder="As on card" autocomplete="cc-name">
          </div>
          <div class="field">
            <label>Email <span style="font-weight:400;color:var(--muted)">(optional — for receipt)</span></label>
            <input type="email" id="cardEmail" placeholder="your@email.com">
          </div>
        </div>

        <button class="pay-btn" id="payBtn" onclick="submitPayment()">
          <i class="fas fa-lock"></i>
          <span id="payBtnTxt">Pay <?= $link['amount'] ? format_amount($link['amount']) : 'Now' ?></span>
        </button>
      </div>

      <div class="pay-ft">
        <i class="fas fa-shield-alt"></i> Secured by OrbitPesa &bull; 256-bit SSL
      </div>
    </div>

    <!-- === M-PESA POLLING SCREEN === -->
    <div id="screenWait" class="screen">
      <div class="spin"></div>
      <h3>Waiting for payment…</h3>
      <p>Check your phone and enter your <strong>M-Pesa PIN</strong> to complete the payment.</p>
      <div class="ref" id="waitRef"></div>
      <br>
      <button class="retry" onclick="cancelPoll()">← Go back</button>
    </div>

    <!-- === SUCCESS SCREEN === -->
    <div id="screenOk" class="screen">
      <div class="check-circle"><i class="fas fa-check"></i></div>
      <h3>Payment Successful!</h3>
      <p id="okMsg">Your payment has been received.</p>
      <div class="ref" id="okRef"></div>
      <br>
      <p style="font-size:.78rem;color:var(--muted);margin-top:12px">You may close this page.</p>
    </div>

    <!-- === FAILURE SCREEN === -->
    <div id="screenFail" class="screen">
      <div class="x-circle"><i class="fas fa-times"></i></div>
      <h3>Payment Failed</h3>
      <p id="failMsg">The payment was not completed. Please try again.</p>
      <button class="retry" onclick="goBack()">← Try Again</button>
    </div>

  </div>

  <div class="site-ft">
    Powered by <a href="<?= APP_URL ?>">OrbitPesa</a> &bull; <a href="<?= APP_URL ?>/developers/docs">API Docs</a>
  </div>
</div>

<script>
const SLUG    = '<?= $link['slug'] ?>';
const API_URL = '<?= APP_URL ?>/api/v1';
const FIXED   = <?= $link['is_fixed_amount'] ? 'true' : 'false' ?>;
const AMT     = <?= (float)($link['amount'] ?? 0) ?>;

let channel  = 'mpesa';
let pollTimer = null;
let pollCount = 0;

// ── Channel switcher ──────────────────────────────────────
function setChannel(ch) {
  channel = ch;
  document.getElementById('chMpesa').classList.toggle('active', ch === 'mpesa');
  document.getElementById('chCard').classList.toggle('active',  ch === 'card');
  document.getElementById('fMpesa').style.display = ch === 'mpesa' ? '' : 'none';
  document.getElementById('fCard').style.display  = ch === 'card'  ? '' : 'none';
  const amt = getAmount();
  document.getElementById('payBtnTxt').textContent = 'Pay ' + (amt ? 'KES ' + amt.toLocaleString() : 'Now');
}

function getAmount() {
  if (FIXED) return AMT;
  return parseFloat(document.getElementById('customAmount')?.value) || 0;
}

// ── Card visual ───────────────────────────────────────────
document.getElementById('cardNum')?.addEventListener('input', function() {
  const v = this.value.replace(/\D/g,'').substring(0,16);
  this.value = v.match(/.{1,4}/g)?.join(' ') || v;
  document.getElementById('visNum').textContent = (v + '················').substring(0,16).match(/.{1,4}/g).join(' ');
  const vis = document.getElementById('cardVis');
  if (v.startsWith('4'))              { vis.className = 'card-vis visa'; }
  else if (/^5[1-5]|^2[2-7]/.test(v)){ vis.className = 'card-vis master'; }
  else                                { vis.className = 'card-vis'; }
});
document.getElementById('cardExp')?.addEventListener('input', function() {
  let v = this.value.replace(/\D/g,'');
  if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2,4);
  this.value = v;
  document.getElementById('visExp').textContent = this.value || 'MM/YY';
});
document.getElementById('cardHolder')?.addEventListener('input', function() {
  document.getElementById('visName').textContent = this.value.toUpperCase() || 'FULL NAME';
});
document.getElementById('customAmount')?.addEventListener('input', function() {
  const amt = parseFloat(this.value) || 0;
  document.getElementById('payBtnTxt').textContent = amt ? 'Pay KES ' + amt.toLocaleString() : 'Pay Now';
});

// ── Submit ────────────────────────────────────────────────
async function submitPayment() {
  clearAlert();
  const amt = getAmount();
  if (!FIXED && !amt) { showAlert('Please enter an amount.'); return; }

  const btn = document.getElementById('payBtn');
  btn.disabled = true;
  document.getElementById('payBtnTxt').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing…';

  const body = { channel, amount: amt };

  if (channel === 'mpesa') {
    const phone = document.getElementById('mpesaPhone').value.trim();
    if (!phone) { showAlert('Please enter your M-Pesa phone number.'); reset(); return; }
    body.phone = phone;
    const email = document.getElementById('mpesaEmail').value.trim();
    if (email) body.email = email;
  } else {
    const num    = document.getElementById('cardNum').value.replace(/\s/g,'');
    const exp    = document.getElementById('cardExp').value;
    const cvv    = document.getElementById('cardCvv').value;
    const holder = document.getElementById('cardHolder').value.trim();
    if (!num || num.length < 13)  { showAlert('Please enter a valid card number.'); reset(); return; }
    if (!exp || !exp.includes('/')){ showAlert('Please enter the expiry date (MM/YY).'); reset(); return; }
    if (!cvv)                      { showAlert('Please enter the CVV.'); reset(); return; }
    if (!holder)                   { showAlert('Please enter the cardholder name.'); reset(); return; }
    body.card_number  = num;
    body.card_expiry  = exp;
    body.cvv          = cvv;
    body.card_holder  = holder;
    const cardEmail = document.getElementById('cardEmail').value.trim();
    if (cardEmail) body.email = cardEmail;
  }

  try {
    const res  = await fetch(API_URL + '/checkout/' + SLUG + '/pay', {
      method : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body   : JSON.stringify(body),
    });
    const data = await res.json();

    if (!data.success) { showAlert(data.error || 'Payment failed. Please try again.'); reset(); return; }

    if (channel === 'card') {
      showSuccess('KES ' + parseFloat(data.data.amount).toLocaleString() + ' paid successfully via card ending ' + (data.data.card_last4 || '••••') + '.', data.data.reference);
    } else {
      startPolling(data.data.reference, amt);
    }
  } catch(e) {
    showAlert('Network error. Please check your connection and try again.');
    reset();
  }
}

// ── Polling ───────────────────────────────────────────────
function startPolling(ref, amt) {
  document.getElementById('waitRef').textContent = 'Ref: ' + ref;
  showScreen('screenWait');
  pollCount = 0;
  pollTimer = setInterval(() => poll(ref, amt), 3000);
}

async function poll(ref, amt) {
  pollCount++;
  if (pollCount > 40) {
    clearInterval(pollTimer);
    showFail('Payment timed out. Please try again.');
    return;
  }
  try {
    const res  = await fetch(API_URL + '/checkout/status/' + ref);
    const data = await res.json();
    if (!data.success) return;
    const st = data.data.status;
    if (st === 'completed') {
      clearInterval(pollTimer);
      showSuccess('KES ' + parseFloat(data.data.amount).toLocaleString() + ' received via M-Pesa. Thank you!', ref);
    } else if (st === 'failed') {
      clearInterval(pollTimer);
      showFail('Payment was cancelled or failed. Please try again.');
    }
  } catch(e) {}
}

function cancelPoll() {
  clearInterval(pollTimer);
  goBack();
}

// ── Screen helpers ────────────────────────────────────────
function showScreen(id) {
  ['screenForm','screenWait','screenOk','screenFail'].forEach(s => {
    document.getElementById(s).classList.toggle('active', s === id);
  });
}

function showSuccess(msg, ref) {
  document.getElementById('okMsg').textContent = msg;
  document.getElementById('okRef').textContent = 'Ref: ' + ref;
  showScreen('screenOk');
}

function showFail(msg) {
  document.getElementById('failMsg').textContent = msg;
  showScreen('screenFail');
}

function goBack() {
  showScreen('screenForm');
  reset();
}

function reset() {
  const btn = document.getElementById('payBtn');
  btn.disabled = false;
  document.getElementById('payBtnTxt').innerHTML = 'Pay <?= $link['amount'] ? format_amount($link['amount']) : 'Now' ?>';
}

function showAlert(msg) {
  const el = document.getElementById('alertBox');
  el.textContent = msg;
  el.style.display = 'flex';
}
function clearAlert() {
  document.getElementById('alertBox').style.display = 'none';
}
</script>
</body>
</html>
