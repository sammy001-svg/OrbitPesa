<?php
$wallet = Wallet::getOrCreate(auth_user()['id']);
$recentWalletTxns = DB::fetchAll(
    "SELECT * FROM transactions WHERE user_id = ? AND channel = 'wallet' ORDER BY created_at DESC LIMIT 8",
    [auth_user()['id']]
);
$ledger = Wallet::getLedger(auth_user()['id'], 5);
?>

<div class="section-hd">
  <div>
    <h2>Wallet Pay</h2>
    <p>Send payments directly from your OrbitPesa wallet balance.</p>
  </div>
</div>

<!-- Balance Banner -->
<div style="background:var(--navy);border-radius:var(--radius-lg);padding:24px 28px;display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:14px">
  <div>
    <div style="font-size:.78rem;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px">Available Balance</div>
    <div style="font-size:2.2rem;font-weight:800;color:#fff"><?= format_amount($wallet['balance']) ?></div>
  </div>
  <div style="display:flex;gap:10px">
    <a href="<?= APP_URL ?>/dashboard/wallet" class="btn btn-ghost" style="border-color:rgba(255,255,255,.25);color:rgba(255,255,255,.8)">
      <i class="fas fa-wallet"></i> Wallet Details
    </a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

  <!-- Pay Form -->
  <div class="card">
    <div class="card-header">
      <h4><i class="fas fa-paper-plane" style="color:var(--green);margin-right:6px"></i> Send Payment</h4>
    </div>
    <div class="card-body">
      <div id="walletResult" class="alert" style="display:none"></div>

      <?php if ($wallet['balance'] < 1): ?>
      <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <div>Your wallet balance is too low to make a payment. <a href="<?= APP_URL ?>/dashboard/wallet">Fund your wallet</a> first.</div>
      </div>
      <?php else: ?>

      <form id="walletPayForm">
        <div class="form-group">
          <label class="form-label">Recipient Email or Phone <span style="color:var(--danger)">*</span></label>
          <div class="input-group">
            <span class="input-addon"><i class="fas fa-user"></i></span>
            <input type="text" id="wpRecipient" class="form-control" placeholder="email@example.com or 07XX XXX XXX" required>
            <button type="button" class="input-addon btn-lookup" id="lookupBtn" onclick="lookupRecipient()"
                    style="cursor:pointer;background:var(--green);border:none;color:#fff;padding:0 12px;border-radius:0 var(--radius) var(--radius) 0;font-size:.8rem">
              Verify
            </button>
          </div>
        </div>

        <!-- Recipient card (shown after lookup) -->
        <div id="recipientCard" style="display:none;background:var(--bg);border-radius:var(--radius);padding:12px 14px;border:1px solid var(--green);margin-bottom:16px">
          <div style="display:flex;align-items:center;gap:10px">
            <div style="width:36px;height:36px;background:var(--green);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;font-size:.9rem" id="rcptAvatar">?</div>
            <div>
              <div id="rcptName" style="font-weight:700;font-size:.875rem;color:var(--navy)"></div>
              <div id="rcptEmail" style="font-size:.74rem;color:var(--text-muted)"></div>
            </div>
            <i class="fas fa-check-circle" style="color:var(--green);margin-left:auto;font-size:1.1rem"></i>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Amount (KES) <span style="color:var(--danger)">*</span></label>
          <div class="input-group">
            <span class="input-addon">KES</span>
            <input type="number" id="wpAmount" class="form-control" placeholder="100"
                   min="1" max="<?= floor($wallet['balance']) ?>" step="1" required>
          </div>
          <div class="form-hint">Max: <?= format_amount($wallet['balance']) ?> (your balance)</div>
        </div>

        <div class="form-group">
          <label class="form-label">Note <span style="color:var(--text-muted);font-weight:400">(optional)</span></label>
          <input type="text" id="wpNote" class="form-control" placeholder="e.g. Invoice payment, thank you...">
        </div>

        <!-- Summary -->
        <div id="wpSummary" style="display:none;background:var(--bg);border-radius:var(--radius);padding:14px;margin-bottom:16px">
          <div style="display:flex;justify-content:space-between;font-size:.84rem;margin-bottom:5px">
            <span style="color:var(--text-muted)">Amount</span><span id="wpSumAmt" style="font-weight:600"></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:.84rem;margin-bottom:5px">
            <span style="color:var(--text-muted)">Fee</span><span style="color:var(--text-muted)">KES 0.00 (Free)</span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:.875rem;border-top:1px solid var(--border);padding-top:8px;margin-top:4px">
            <span style="font-weight:700;color:var(--navy)">New Balance</span>
            <span id="wpNewBal" style="font-weight:800;color:var(--navy)"></span>
          </div>
        </div>

        <button type="submit" id="wpBtn" class="btn btn-primary btn-block btn-lg" disabled>
          <i class="fas fa-paper-plane"></i> Send Payment
        </button>
      </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right: recent transactions + ledger -->
  <div style="display:flex;flex-direction:column;gap:20px">

    <!-- Success state -->
    <div id="wpSuccess" class="card" style="display:none;border-left:4px solid var(--green)">
      <div class="card-body" style="text-align:center;padding:28px">
        <div style="width:56px;height:56px;background:var(--green-light);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:1.4rem;color:var(--green)">
          <i class="fas fa-check"></i>
        </div>
        <h3 style="color:var(--green);margin-bottom:6px">Payment Sent!</h3>
        <p id="wpSuccessMsg" style="color:var(--text-muted);font-size:.9rem;margin-bottom:4px"></p>
        <p style="font-size:.75rem;color:var(--text-muted)">Ref: <code id="wpSuccessRef"></code></p>
        <button class="btn btn-outline btn-sm" style="margin-top:14px" onclick="resetWalletForm()">
          <i class="fas fa-plus"></i> Send Another
        </button>
      </div>
    </div>

    <div class="card" style="flex:1">
      <div class="card-header">
        <h4><i class="fas fa-history" style="color:var(--green);margin-right:6px"></i> Recent Wallet Payments</h4>
        <a href="<?= APP_URL ?>/dashboard/wallet" class="btn btn-ghost btn-sm">Ledger</a>
      </div>
      <div class="p-0">
        <?php if (empty($recentWalletTxns)): ?>
          <div class="empty-state" style="padding:30px">
            <i class="fas fa-wallet" style="color:var(--text-muted)"></i>
            <p>No wallet payments yet.</p>
          </div>
        <?php else: ?>
        <div class="table-wrap">
          <table class="orb-table">
            <thead><tr><th>Description</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
              <?php foreach ($recentWalletTxns as $t): ?>
              <tr>
                <td style="font-size:.82rem;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                  <?= sanitize($t['description'] ?: 'Wallet payment') ?>
                </td>
                <td style="font-weight:700"><?= format_amount($t['amount']) ?></td>
                <td><?= transaction_status_badge($t['status']) ?></td>
                <td style="font-size:.75rem;color:var(--text-muted)"><?= date('d M H:i', strtotime($t['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<script>
const apiBase  = '<?= APP_URL ?>/api/v1';
const apiKey   = '<?= DB::fetch("SELECT key_value FROM api_keys WHERE user_id=? AND environment=? AND status='active' LIMIT 1", [auth_user()['id'], $_SESSION['user']['env'] ?? 'test'])['key_value'] ?? '' ?>';
const balance  = <?= (float)$wallet['balance'] ?>;
let recipientId = null;

async function lookupRecipient() {
  const val = document.getElementById('wpRecipient').value.trim();
  if (!val) return;
  document.getElementById('lookupBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

  try {
    const res  = await fetch('<?= APP_URL ?>/api/v1/wallet/lookup?q=' + encodeURIComponent(val), {
      headers: { 'X-API-Key': apiKey }
    });
    const data = await res.json();
    if (data.success && data.data) {
      const u = data.data;
      recipientId = u.id;
      document.getElementById('rcptName').textContent   = u.business_name;
      document.getElementById('rcptEmail').textContent  = u.email;
      document.getElementById('rcptAvatar').textContent = u.business_name.charAt(0).toUpperCase();
      document.getElementById('recipientCard').style.display = 'block';
      document.getElementById('wpBtn').disabled = false;
    } else {
      showWalletResult('danger', 'No OrbitPesa account found for that email or phone.');
      document.getElementById('recipientCard').style.display = 'none';
      recipientId = null;
      document.getElementById('wpBtn').disabled = true;
    }
  } catch(e) {
    showWalletResult('danger', 'Lookup failed. Please try again.');
  }
  document.getElementById('lookupBtn').textContent = 'Verify';
}

document.getElementById('wpAmount')?.addEventListener('input', function() {
  const amt = parseFloat(this.value) || 0;
  if (!amt || amt > balance) { document.getElementById('wpSummary').style.display='none'; return; }
  document.getElementById('wpSummary').style.display = 'block';
  document.getElementById('wpSumAmt').textContent   = 'KES ' + amt.toLocaleString('en-KE',{minimumFractionDigits:2});
  document.getElementById('wpNewBal').textContent   = 'KES ' + (balance - amt).toLocaleString('en-KE',{minimumFractionDigits:2});
});

document.getElementById('walletPayForm')?.addEventListener('submit', async function(e) {
  e.preventDefault();
  if (!recipientId) { showWalletResult('warning', 'Please verify a recipient first.'); return; }
  const btn = document.getElementById('wpBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending…';
  showWalletResult('','');

  try {
    const res = await fetch(apiBase + '/payments/wallet/pay', {
      method: 'POST',
      headers: { 'Content-Type':'application/json','X-API-Key':apiKey },
      body: JSON.stringify({
        recipient_id: recipientId,
        amount      : parseFloat(document.getElementById('wpAmount').value),
        description : document.getElementById('wpNote').value || 'Wallet payment',
      })
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('walletPayForm').style.display = 'none';
      document.getElementById('wpSuccess').style.display     = 'block';
      document.getElementById('wpSuccessMsg').textContent = 'KES ' + parseFloat(data.data.amount).toLocaleString() + ' sent successfully.';
      document.getElementById('wpSuccessRef').textContent = data.data.reference;
    } else {
      showWalletResult('danger', data.error || 'Payment failed.');
    }
  } catch(err) {
    showWalletResult('danger', 'Network error — please try again.');
  }
  btn.disabled = false;
  btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Payment';
});

function resetWalletForm() {
  document.getElementById('walletPayForm').style.display = 'block';
  document.getElementById('wpSuccess').style.display     = 'none';
  document.getElementById('walletPayForm').reset();
  document.getElementById('recipientCard').style.display = 'none';
  document.getElementById('wpSummary').style.display     = 'none';
  document.getElementById('wpBtn').disabled = true;
  recipientId = null;
}

function showWalletResult(type, msg) {
  const el = document.getElementById('walletResult');
  if (!msg) { el.style.display='none'; return; }
  el.className = 'alert alert-' + type;
  el.textContent = msg;
  el.style.display = 'block';
}
</script>
