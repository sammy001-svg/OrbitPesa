<?php
$recentCards = DB::fetchAll(
    "SELECT * FROM transactions WHERE user_id = ? AND channel = 'card' ORDER BY created_at DESC LIMIT 8",
    [auth_user()['id']]
);
$env = $_SESSION['user']['env'] ?? 'test';
?>

<div class="section-hd">
  <div>
    <h2>Card Payment</h2>
    <p>Charge a customer's debit or credit card.</p>
  </div>
  <span class="badge <?= $env === 'live' ? 'badge-success' : 'badge-warning' ?>" style="font-size:.8rem">
    <?= strtoupper($env) ?> MODE
  </span>
</div>

<?php if ($env === 'test'): ?>
<div class="alert alert-info" style="margin-bottom:20px">
  <i class="fas fa-flask"></i>
  <div><strong>Test Mode:</strong> Use card number <code>4242 4242 4242 4242</code>, any future expiry (e.g. 12/26), any 3-digit CVV, and any name. No real charges are made.</div>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

  <!-- Card Form -->
  <div class="card">
    <div class="card-header">
      <h4><i class="fas fa-credit-card" style="color:var(--navy);margin-right:6px"></i> Charge a Card</h4>
      <div style="display:flex;gap:6px">
        <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Visa" style="height:18px;object-fit:contain">
        <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="MC" style="height:18px;object-fit:contain">
      </div>
    </div>
    <div class="card-body">
      <div id="cardResult" class="alert" style="display:none"></div>

      <form id="cardForm">
        <!-- Amount & Description -->
        <div class="grid-2" style="gap:12px;margin-bottom:0">
          <div class="form-group">
            <label class="form-label">Amount (KES) <span style="color:var(--danger)">*</span></label>
            <div class="input-group">
              <span class="input-addon">KES</span>
              <input type="number" id="cardAmount" class="form-control" placeholder="500" min="50" step="1" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <input type="text" id="cardDesc" class="form-control" placeholder="Invoice #, Order...">
          </div>
        </div>

        <!-- Card visual -->
        <div class="card-visual" id="cardVisual">
          <div class="card-visual-chip"><i class="fas fa-microchip"></i></div>
          <div class="card-visual-number" id="visNumber">•••• •••• •••• ••••</div>
          <div class="card-visual-footer">
            <div>
              <div style="font-size:.6rem;opacity:.7;text-transform:uppercase;letter-spacing:.05em">Card Holder</div>
              <div id="visName" style="font-size:.8rem;font-weight:600;letter-spacing:.05em">FULL NAME</div>
            </div>
            <div style="text-align:right">
              <div style="font-size:.6rem;opacity:.7;text-transform:uppercase;letter-spacing:.05em">Expires</div>
              <div id="visExpiry" style="font-size:.8rem;font-weight:600">MM/YY</div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Card Number <span style="color:var(--danger)">*</span></label>
          <div class="input-group">
            <span class="input-addon" id="cardTypeIcon"><i class="fas fa-credit-card"></i></span>
            <input type="text" id="cardNumber" class="form-control" placeholder="0000 0000 0000 0000"
                   maxlength="19" inputmode="numeric" autocomplete="cc-number" required>
          </div>
        </div>

        <div class="grid-2" style="gap:12px;margin-bottom:0">
          <div class="form-group">
            <label class="form-label">Expiry Date <span style="color:var(--danger)">*</span></label>
            <input type="text" id="cardExpiry" class="form-control" placeholder="MM/YY"
                   maxlength="5" inputmode="numeric" autocomplete="cc-exp" required>
          </div>
          <div class="form-group">
            <label class="form-label">CVV <span style="color:var(--danger)">*</span></label>
            <div class="input-group">
              <input type="text" id="cardCvv" class="form-control" placeholder="•••"
                     maxlength="4" inputmode="numeric" autocomplete="cc-csc" required>
              <span class="input-addon" title="3–4 digit security code on back of card">
                <i class="fas fa-question-circle" style="cursor:help"></i>
              </span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Card Holder Name <span style="color:var(--danger)">*</span></label>
          <input type="text" id="cardName" class="form-control" placeholder="As printed on card"
                 autocomplete="cc-name" required>
        </div>

        <!-- Fee preview -->
        <div id="cardFeePreview" style="background:var(--bg);border-radius:var(--radius);padding:14px;margin-bottom:16px;display:none">
          <div style="display:flex;justify-content:space-between;font-size:.84rem;margin-bottom:5px">
            <span style="color:var(--text-muted)">Amount</span><span id="cpAmt" style="font-weight:600"></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:.84rem;margin-bottom:5px">
            <span style="color:var(--text-muted)">Card Fee (2.9%)</span><span id="cpFee" style="color:var(--danger)"></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:.875rem;border-top:1px solid var(--border);padding-top:8px;margin-top:4px">
            <span style="font-weight:700;color:var(--navy)">You Receive</span>
            <span id="cpNet" style="font-weight:800;color:var(--green)"></span>
          </div>
        </div>

        <button type="submit" id="cardBtn" class="btn btn-primary btn-block btn-lg">
          <i class="fas fa-lock"></i> Charge Card Securely
        </button>
        <p style="text-align:center;font-size:.72rem;color:var(--text-muted);margin-top:10px">
          <i class="fas fa-shield-alt" style="color:var(--green)"></i> 256-bit SSL encrypted · PCI DSS compliant
        </p>
      </form>
    </div>
  </div>

  <!-- Right: success state + recent -->
  <div style="display:flex;flex-direction:column;gap:20px">
    <div id="chargeSuccess" class="card" style="display:none;border-left:4px solid var(--green)">
      <div class="card-body" style="text-align:center;padding:32px">
        <div style="width:64px;height:64px;background:var(--green-light);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.5rem;color:var(--green)">
          <i class="fas fa-check"></i>
        </div>
        <h3 style="color:var(--green);margin-bottom:6px">Payment Successful!</h3>
        <p id="successMsg" style="color:var(--text-muted);font-size:.9rem"></p>
        <p style="font-size:.78rem;color:var(--text-muted)">Ref: <code id="successRef"></code></p>
        <button class="btn btn-outline btn-sm" style="margin-top:16px" onclick="resetCardForm()">
          <i class="fas fa-plus"></i> New Charge
        </button>
      </div>
    </div>

    <div class="card" style="flex:1">
      <div class="card-header">
        <h4><i class="fas fa-history" style="color:var(--navy);margin-right:6px"></i> Recent Card Charges</h4>
        <a href="<?= APP_URL ?>/dashboard/transactions?channel=card" class="btn btn-ghost btn-sm">View All</a>
      </div>
      <div class="p-0">
        <?php if (empty($recentCards)): ?>
          <div class="empty-state" style="padding:30px">
            <i class="fas fa-credit-card" style="color:var(--text-muted)"></i>
            <p>No card charges yet.</p>
          </div>
        <?php else: ?>
        <div class="table-wrap">
          <table class="orb-table">
            <thead><tr><th>Card</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
              <?php foreach ($recentCards as $t): ?>
              <tr>
                <td style="font-size:.82rem">
                  <i class="fas fa-credit-card" style="color:var(--navy);margin-right:4px"></i>
                  •••• <?= $t['card_last4'] ?? '••••' ?>
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

<style>
.card-visual {
  background: linear-gradient(135deg, var(--navy) 0%, #1a3a7c 100%);
  border-radius: 14px;
  padding: 22px 22px 18px;
  color: #fff;
  margin-bottom: 20px;
  position: relative;
  min-height: 150px;
  box-shadow: 0 8px 24px rgba(13,27,62,.25);
}
.card-visual-chip { font-size: 1.8rem; opacity: .7; margin-bottom: 14px; }
.card-visual-number { font-size: 1.1rem; letter-spacing: .2em; font-weight: 600; margin-bottom: 18px; }
.card-visual-footer { display: flex; justify-content: space-between; }
.card-visual.visa   { background: linear-gradient(135deg, #1a1f71 0%, #283593 100%); }
.card-visual.master { background: linear-gradient(135deg, #1b2030 0%, #c62828 100%); }
</style>

<script>
const apiBase = '<?= APP_URL ?>/api/v1';
const apiKey  = '<?= DB::fetch("SELECT key_value FROM api_keys WHERE user_id=? AND environment=? AND status='active' LIMIT 1", [auth_user()['id'], $env])['key_value'] ?? '' ?>';

// Card number formatting + brand detection
document.getElementById('cardNumber').addEventListener('input', function() {
  let v = this.value.replace(/\D/g,'').substring(0,16);
  this.value = v.match(/.{1,4}/g)?.join(' ') || v;
  document.getElementById('visNumber').textContent = (v + '················').substring(0,16).match(/.{1,4}/g).join(' ');
  const vis = document.getElementById('cardVisual');
  const ico = document.getElementById('cardTypeIcon');
  if (v.startsWith('4')) { vis.className='card-visual visa'; ico.innerHTML='<i class="fab fa-cc-visa"></i>'; }
  else if (/^5[1-5]/.test(v)||/^2[2-7]/.test(v)) { vis.className='card-visual master'; ico.innerHTML='<i class="fab fa-cc-mastercard"></i>'; }
  else { vis.className='card-visual'; ico.innerHTML='<i class="fas fa-credit-card"></i>'; }
});

document.getElementById('cardExpiry').addEventListener('input', function() {
  let v = this.value.replace(/\D/g,'');
  if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2,4);
  this.value = v;
  document.getElementById('visExpiry').textContent = this.value || 'MM/YY';
});

document.getElementById('cardName').addEventListener('input', function() {
  document.getElementById('visName').textContent = this.value.toUpperCase() || 'FULL NAME';
});

document.getElementById('cardAmount').addEventListener('input', function() {
  const amt = parseFloat(this.value) || 0;
  if (!amt) { document.getElementById('cardFeePreview').style.display='none'; return; }
  const fee = Math.max(amt * 0.029, 30);
  document.getElementById('cardFeePreview').style.display = 'block';
  document.getElementById('cpAmt').textContent = 'KES ' + amt.toLocaleString('en-KE',{minimumFractionDigits:2});
  document.getElementById('cpFee').textContent = '− KES ' + fee.toFixed(2);
  document.getElementById('cpNet').textContent = 'KES ' + (amt - fee).toLocaleString('en-KE',{minimumFractionDigits:2});
});

document.getElementById('cardForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('cardBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing…';
  setCardResult('','');

  const number = document.getElementById('cardNumber').value.replace(/\s/g,'');
  const expiry = document.getElementById('cardExpiry').value;
  const [exp_month, exp_year] = expiry.split('/');

  try {
    const res = await fetch(apiBase + '/payments/card/charge', {
      method: 'POST',
      headers: { 'Content-Type':'application/json','X-API-Key':apiKey },
      body: JSON.stringify({
        amount      : parseFloat(document.getElementById('cardAmount').value),
        description : document.getElementById('cardDesc').value,
        card_number : number,
        exp_month   : exp_month,
        exp_year    : '20' + exp_year,
        cvv         : document.getElementById('cardCvv').value,
        card_holder : document.getElementById('cardName').value,
      })
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('cardForm').style.display      = 'none';
      document.getElementById('chargeSuccess').style.display = 'block';
      document.getElementById('successMsg').textContent  = 'KES ' + parseFloat(data.data.amount).toLocaleString() + ' charged successfully.';
      document.getElementById('successRef').textContent  = data.data.reference;
    } else {
      setCardResult('danger', data.error || 'Card charge failed.');
    }
  } catch(err) {
    setCardResult('danger', 'Network error — please try again.');
  }
  btn.disabled = false;
  btn.innerHTML = '<i class="fas fa-lock"></i> Charge Card Securely';
});

function resetCardForm() {
  document.getElementById('cardForm').style.display      = 'block';
  document.getElementById('chargeSuccess').style.display = 'none';
  document.getElementById('cardForm').reset();
  document.getElementById('visNumber').textContent  = '•••• •••• •••• ••••';
  document.getElementById('visName').textContent    = 'FULL NAME';
  document.getElementById('visExpiry').textContent  = 'MM/YY';
  document.getElementById('cardFeePreview').style.display = 'none';
}

function setCardResult(type, msg) {
  const el = document.getElementById('cardResult');
  if (!msg) { el.style.display='none'; return; }
  el.className = 'alert alert-' + type;
  el.textContent = msg;
  el.style.display = 'block';
}
</script>
