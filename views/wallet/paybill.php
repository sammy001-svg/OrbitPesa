<?php
$pageTitle = 'Pay Paybill';
$backUrl   = APP_URL . '/wallet/home';

$commonPaybills = [
  ['number' => '888880', 'name' => 'KPLC Prepaid',    'icon' => 'fa-bolt',        'color' => '#f59e0b'],
  ['number' => '888882', 'name' => 'KPLC Postpaid',   'icon' => 'fa-bolt',        'color' => '#f97316'],
  ['number' => '303030', 'name' => 'Nairobi Water',   'icon' => 'fa-tint',        'color' => '#3b82f6'],
  ['number' => '400200', 'name' => 'DSTV',            'icon' => 'fa-tv',          'color' => '#0D1B3E'],
  ['number' => '620200', 'name' => 'Safaricom Home',  'icon' => 'fa-wifi',        'color' => '#158347'],
  ['number' => '200000', 'name' => 'KRA',             'icon' => 'fa-landmark',    'color' => '#7c3aed'],
];
?>
<div style="padding-top:6px">

  <!-- Quick paybills -->
  <div style="padding:0 14px 4px">
    <div class="wdivider" style="padding-left:0;padding-top:12px">Popular Paybills</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:4px">
      <?php foreach ($commonPaybills as $pb): ?>
      <button type="button" class="quick-item" onclick="selectPaybill('<?= $pb['number'] ?>','<?= htmlspecialchars($pb['name']) ?>')"
              style="padding:12px 6px;font-family:inherit;background:white;box-shadow:0 1px 6px rgba(0,0,0,.06)">
        <div class="quick-icon" style="background:<?= $pb['color'] ?>;width:40px;height:40px;font-size:.9rem">
          <i class="fas <?= $pb['icon'] ?>"></i>
        </div>
        <span class="quick-label" style="font-size:.65rem"><?= htmlspecialchars($pb['name']) ?></span>
      </button>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="wform-card">
    <form method="POST" action="<?= APP_URL ?>/wallet/paybill">
      <?= csrf_field() ?>

      <div class="wform-group">
        <label class="wform-label">Paybill Number</label>
        <input type="tel" name="paybill_number" id="paybillNumber" class="wform-control"
               placeholder="e.g. 888880" required inputmode="numeric"
               value="<?= htmlspecialchars($_POST['paybill_number'] ?? '') ?>">
        <div id="paybillName" class="wform-hint" style="font-weight:600;color:#158347"></div>
      </div>

      <div class="wform-group">
        <label class="wform-label">Account Number</label>
        <input type="text" name="account_number" id="accountNumber" class="wform-control"
               placeholder="Your account / meter number" required
               value="<?= htmlspecialchars($_POST['account_number'] ?? '') ?>">
      </div>

      <div class="wform-group">
        <label class="wform-label">Amount (KES)</label>
        <div class="amount-picks">
          <?php foreach ([50,100,200,500,1000,2000] as $v): ?>
          <button type="button" class="amount-pick" onclick="document.getElementById('pbAmt').value=<?= $v ?>">
            <?= number_format($v) ?>
          </button>
          <?php endforeach; ?>
        </div>
        <input type="number" name="amount" id="pbAmt" class="wform-control" style="margin-top:10px"
               placeholder="Enter amount" min="1" step="1" required>
      </div>

      <div class="wform-group">
        <label class="wform-label">PIN</label>
        <input type="password" name="pin" class="wform-control pin-input"
               inputmode="numeric" maxlength="4" placeholder="····" required autocomplete="off">
      </div>

      <div style="background:#eff6ff;border-radius:12px;padding:11px 14px;margin-bottom:12px;font-size:.78rem;color:#1e40af">
        <i class="fas fa-info-circle"></i> Balance: <strong>KES <?= number_format((float)$walletUser['balance'], 2) ?></strong>
      </div>

      <button type="submit" class="wbtn wbtn-primary">
        <i class="fas fa-file-invoice"></i> Pay Now
      </button>
    </form>
  </div>
</div>

<script>
<?php $map = []; foreach ($commonPaybills as $pb) { $map[$pb['number']] = $pb['name']; } ?>
const PAYBILL_MAP = <?= json_encode($map) ?>;

function selectPaybill(number, name) {
  document.getElementById('paybillNumber').value = number;
  document.getElementById('paybillName').textContent = name;
  document.getElementById('accountNumber').focus();
}

document.getElementById('paybillNumber').addEventListener('input', function() {
  const n = this.value.trim();
  const nameEl = document.getElementById('paybillName');
  nameEl.textContent = PAYBILL_MAP[n] ? '✓ ' + PAYBILL_MAP[n] : '';
});
</script>
