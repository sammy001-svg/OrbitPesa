<?php
$recentMpesa = DB::fetchAll(
    "SELECT * FROM transactions WHERE user_id = ? AND channel = 'mpesa' ORDER BY created_at DESC LIMIT 8",
    [auth_user()['id']]
);
$env = $_SESSION['user']['env'] ?? 'test';
?>

<div class="section-hd">
  <div>
    <h2>M-Pesa STK Push</h2>
    <p>Send a payment prompt directly to a customer's phone via Safaricom.</p>
  </div>
  <span class="badge <?= $env === 'live' ? 'badge-success' : 'badge-warning' ?>" style="font-size:.8rem">
    <?= strtoupper($env) ?> MODE
  </span>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

  <!-- Push Form -->
  <div class="card">
    <div class="card-header">
      <h4><i class="fas fa-mobile-alt" style="color:var(--green);margin-right:6px"></i> Initiate STK Push</h4>
    </div>
    <div class="card-body">
      <div id="stkResult" class="alert" style="display:none"></div>

      <form id="stkForm">
        <div class="form-group">
          <label class="form-label">Customer Phone Number <span style="color:var(--danger)">*</span></label>
          <div class="input-group">
            <span class="input-addon"><i class="fas fa-phone"></i></span>
            <input type="tel" id="stkPhone" class="form-control" placeholder="07XX XXX XXX or 2547XX XXX XXX"
                   maxlength="13" required>
          </div>
          <div class="form-hint">Kenyan number starting with 07, 01, or 254.</div>
        </div>

        <div class="form-group">
          <label class="form-label">Amount (KES) <span style="color:var(--danger)">*</span></label>
          <div class="input-group">
            <span class="input-addon">KES</span>
            <input type="number" id="stkAmount" class="form-control" placeholder="100" min="1" max="150000" step="1" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Description <span style="color:var(--text-muted);font-weight:400">(optional)</span></label>
          <input type="text" id="stkDesc" class="form-control" placeholder="e.g. Invoice #001, Order payment...">
        </div>

        <!-- Fee Preview -->
        <div id="feePreview" style="background:var(--bg);border-radius:var(--radius);padding:14px;margin-bottom:16px;display:none">
          <div style="display:flex;justify-content:space-between;font-size:.84rem;margin-bottom:6px">
            <span style="color:var(--text-muted)">Amount</span>
            <span id="prevAmount" style="font-weight:600"></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:.84rem;margin-bottom:6px">
            <span style="color:var(--text-muted)">M-Pesa Fee</span>
            <span id="prevFee" style="color:var(--danger)"></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:.875rem;border-top:1px solid var(--border);padding-top:8px;margin-top:4px">
            <span style="font-weight:700;color:var(--navy)">You Receive</span>
            <span id="prevNet" style="font-weight:800;color:var(--green)"></span>
          </div>
        </div>

        <button type="submit" id="stkBtn" class="btn btn-primary btn-block btn-lg">
          <i class="fas fa-paper-plane"></i> Send STK Push
        </button>
      </form>

      <!-- Polling Status -->
      <div id="stkPolling" style="display:none;text-align:center;padding:24px 0">
        <div style="width:56px;height:56px;border:4px solid var(--green-light);border-top-color:var(--green);border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 16px"></div>
        <p style="font-weight:600;color:var(--navy);margin-bottom:4px">Waiting for payment…</p>
        <p style="font-size:.84rem;color:var(--text-muted)">Prompt sent — customer should check their phone</p>
        <p style="font-size:.78rem;color:var(--text-muted);margin-top:8px">Reference: <code id="pollRef"></code></p>
        <button type="button" class="btn btn-ghost btn-sm" style="margin-top:12px" onclick="cancelPolling()">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Right column: info + recent -->
  <div style="display:flex;flex-direction:column;gap:20px">

    <!-- How it works -->
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-info-circle" style="color:var(--green);margin-right:6px"></i> How STK Push Works</h4></div>
      <div class="card-body">
        <div style="display:flex;flex-direction:column;gap:12px">
          <?php foreach([
            ['1','Enter phone & amount','Fill in the customer details and the amount to collect.'],
            ['2','Push sent to phone','Safaricom sends a PIN prompt directly to the customer\'s phone.'],
            ['3','Customer approves','Customer enters their M-Pesa PIN to authorise the payment.'],
            ['4','Funds to wallet','Payment is confirmed and credited to your OrbitPesa wallet.'],
          ] as [$n,$title,$desc]): ?>
          <div style="display:flex;gap:12px;align-items:flex-start">
            <div style="width:28px;height:28px;background:var(--green);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.78rem;color:#fff;flex-shrink:0"><?=$n?></div>
            <div>
              <div style="font-weight:600;font-size:.875rem;color:var(--navy)"><?=$title?></div>
              <div style="font-size:.8rem;color:var(--text-muted)"><?=$desc?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Recent M-Pesa transactions -->
    <div class="card" style="flex:1">
      <div class="card-header">
        <h4><i class="fas fa-history" style="color:var(--green);margin-right:6px"></i> Recent M-Pesa</h4>
        <a href="<?= APP_URL ?>/dashboard/transactions?channel=mpesa" class="btn btn-ghost btn-sm">View All</a>
      </div>
      <div class="p-0">
        <?php if (empty($recentMpesa)): ?>
          <div class="empty-state" style="padding:30px">
            <i class="fas fa-mobile-alt" style="color:var(--text-muted)"></i>
            <p>No M-Pesa transactions yet.</p>
          </div>
        <?php else: ?>
        <div class="table-wrap">
          <table class="orb-table">
            <thead><tr><th>Phone</th><th>Amount</th><th>Status</th><th>Time</th></tr></thead>
            <tbody>
              <?php foreach ($recentMpesa as $t): ?>
              <tr>
                <td style="font-size:.82rem"><?= mask_phone($t['phone'] ?? '—') ?></td>
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

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
const apiBase  = '<?= APP_URL ?>/api/v1';
const apiKey   = '<?= DB::fetch("SELECT key_value FROM api_keys WHERE user_id=? AND environment=? AND status='active' LIMIT 1", [auth_user()['id'], $env])['key_value'] ?? '' ?>';
let pollTimer  = null;
let pollCount  = 0;

// Fee preview
document.getElementById('stkAmount').addEventListener('input', function() {
  const amt = parseFloat(this.value) || 0;
  if (!amt) { document.getElementById('feePreview').style.display='none'; return; }
  const fee = Math.min(Math.max(amt * 0.015, 5), 500);
  document.getElementById('feePreview').style.display = 'block';
  document.getElementById('prevAmount').textContent = 'KES ' + amt.toLocaleString('en-KE', {minimumFractionDigits:2});
  document.getElementById('prevFee').textContent    = '− KES ' + fee.toFixed(2);
  document.getElementById('prevNet').textContent    = 'KES ' + (amt - fee).toLocaleString('en-KE', {minimumFractionDigits:2});
});

document.getElementById('stkForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const phone  = document.getElementById('stkPhone').value.trim();
  const amount = document.getElementById('stkAmount').value.trim();
  const desc   = document.getElementById('stkDesc').value.trim();
  const btn    = document.getElementById('stkBtn');

  setResult('', '');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending…';

  try {
    const res = await fetch(apiBase + '/payments/mpesa/stk', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-API-Key': apiKey },
      body: JSON.stringify({ phone, amount: parseFloat(amount), description: desc })
    });
    const data = await res.json();

    if (data.success) {
      showPolling(data.data.reference);
    } else {
      setResult('danger', data.error || 'Failed to initiate STK push.');
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send STK Push';
    }
  } catch(err) {
    setResult('danger', 'Network error — please try again.');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send STK Push';
  }
});

function showPolling(ref) {
  document.getElementById('stkForm').style.display   = 'none';
  document.getElementById('stkPolling').style.display = 'block';
  document.getElementById('pollRef').textContent      = ref;
  pollCount = 0;
  pollTimer = setInterval(() => pollStatus(ref), 3000);
}

async function pollStatus(ref) {
  pollCount++;
  if (pollCount > 40) { cancelPolling(); setResult('warning', 'Payment timed out. Ask the customer to check their M-Pesa.'); return; }
  try {
    const res  = await fetch(apiBase + '/payments/status/' + ref, { headers: { 'X-API-Key': apiKey } });
    const data = await res.json();
    if (!data.success) return;
    const st = data.data.status;
    if (st === 'completed') {
      clearInterval(pollTimer);
      resetForm();
      setResult('success', '✓ Payment of KES ' + parseFloat(data.data.amount).toLocaleString() + ' received successfully!');
      setTimeout(() => location.reload(), 3000);
    } else if (st === 'failed') {
      clearInterval(pollTimer);
      resetForm();
      setResult('danger', 'Payment failed or was cancelled by the customer.');
    }
  } catch(e) {}
}

function cancelPolling() {
  clearInterval(pollTimer);
  resetForm();
}

function resetForm() {
  document.getElementById('stkForm').style.display    = 'block';
  document.getElementById('stkPolling').style.display = 'none';
  const btn = document.getElementById('stkBtn');
  btn.disabled = false;
  btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send STK Push';
}

function setResult(type, msg) {
  const el = document.getElementById('stkResult');
  if (!msg) { el.style.display='none'; return; }
  el.className  = 'alert alert-' + type;
  el.textContent = msg;
  el.style.display = 'block';
}
</script>
