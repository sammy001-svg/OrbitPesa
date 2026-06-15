<?php
$pageTitle     = 'Transfer Money';
$backUrl       = APP_URL . '/wallet/home';
$defaultType   = $_GET['type'] ?? 'mpesa';

$banks = [
  'KCB'     => 'KCB Bank',
  'EQUITY'  => 'Equity Bank',
  'COOP'    => 'Co-op Bank',
  'ABSA'    => 'Absa Bank',
  'DTB'     => 'Diamond Trust Bank',
  'NCBA'    => 'NCBA Bank',
  'STANBIC' => 'Stanbic Bank',
  'FAMILY'  => 'Family Bank',
  'NIC'     => 'NIC Bank',
  'OTHER'   => 'Other Bank',
];
?>
<div style="padding-top:6px">
  <div class="wform-card">
    <form method="POST" action="<?= APP_URL ?>/wallet/transfer" id="transferForm">
      <?= csrf_field() ?>

      <!-- Type toggle -->
      <div class="wform-group">
        <label class="wform-label">Transfer to</label>
        <div class="transfer-tabs">
          <div class="transfer-tab-opt">
            <input type="radio" name="transfer_type" id="tt_mpesa" value="mpesa"
                   <?= $defaultType === 'mpesa' ? 'checked' : '' ?> onchange="switchType('mpesa')">
            <label class="transfer-tab-label" for="tt_mpesa">
              <i class="fas fa-money-bill-wave"></i> M-Pesa
            </label>
          </div>
          <div class="transfer-tab-opt">
            <input type="radio" name="transfer_type" id="tt_bank" value="bank"
                   <?= $defaultType === 'bank' ? 'checked' : '' ?> onchange="switchType('bank')">
            <label class="transfer-tab-label" for="tt_bank">
              <i class="fas fa-university"></i> Bank
            </label>
          </div>
        </div>
      </div>

      <!-- M-Pesa fields -->
      <div id="mpesaFields" <?= $defaultType !== 'mpesa' ? 'style="display:none"' : '' ?>>
        <div class="wform-group">
          <label class="wform-label">M-Pesa Phone Number</label>
          <input type="tel" name="destination" id="mpesaPhone" class="wform-control"
                 placeholder="0712 345 678" inputmode="tel"
                 value="<?= htmlspecialchars($_POST['destination'] ?? '') ?>">
          <div class="wform-hint">Recipient's Safaricom number</div>
        </div>
      </div>

      <!-- Bank fields -->
      <div id="bankFields" <?= $defaultType !== 'bank' ? 'style="display:none"' : '' ?>>
        <div class="wform-group">
          <label class="wform-label">Bank</label>
          <select name="bank_code" class="wform-control">
            <option value="">Select bank…</option>
            <?php foreach ($banks as $code => $name): ?>
            <option value="<?= $code ?>"><?= htmlspecialchars($name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="wform-group">
          <label class="wform-label">Account Number</label>
          <input type="text" name="account_number" class="wform-control" placeholder="Bank account number"
                 value="<?= htmlspecialchars($_POST['account_number'] ?? '') ?>">
        </div>
        <input type="hidden" name="destination" id="bankDestHidden"
               value="<?= htmlspecialchars($_POST['destination'] ?? '') ?>">
      </div>

      <!-- Amount -->
      <div class="wform-group">
        <label class="wform-label">Amount (KES)</label>
        <div class="amount-picks">
          <?php foreach ([500,1000,2000,5000,10000] as $v): ?>
          <button type="button" class="amount-pick" onclick="document.getElementById('trAmt').value=<?= $v ?>">
            <?= number_format($v) ?>
          </button>
          <?php endforeach; ?>
        </div>
        <input type="number" name="amount" id="trAmt" class="wform-control" style="margin-top:10px"
               placeholder="Min KES 100" min="100" step="1" required>
      </div>

      <!-- Fee notice -->
      <div id="feeNotice" style="background:#fff7ed;border-radius:12px;padding:11px 14px;margin-bottom:4px;font-size:.78rem;color:#9a3412">
        <i class="fas fa-info-circle"></i> <span id="feeText"><?= $defaultType === 'bank' ? 'Bank transfer fee: KES 50' : 'M-Pesa send fee: KES 25' ?></span>
      </div>

      <div class="wform-group" style="margin-top:12px">
        <label class="wform-label">Balance</label>
        <div style="font-size:.95rem;font-weight:700;color:#0f172a;padding:10px 0">
          KES <?= number_format((float)$walletUser['balance'], 2) ?>
        </div>
      </div>

      <!-- PIN -->
      <div class="wform-group">
        <label class="wform-label">PIN</label>
        <input type="password" name="pin" class="wform-control pin-input"
               inputmode="numeric" maxlength="4" placeholder="····" required autocomplete="off">
      </div>

      <button type="submit" class="wbtn wbtn-primary" id="submitBtn">
        <i class="fas fa-paper-plane"></i>
        <span id="submitLabel"><?= $defaultType === 'bank' ? 'Send to Bank' : 'Send via M-Pesa' ?></span>
      </button>
    </form>
  </div>

  <div style="margin:12px 14px;padding:12px 14px;background:#f8fafc;border-radius:14px;font-size:.75rem;color:#64748b">
    <strong style="color:#0f172a">Sandbox mode:</strong> All transfers are simulated. M-Pesa fee: KES 25 · Bank fee: KES 50.
  </div>
</div>

<script>
function switchType(type) {
  const mpesaFields = document.getElementById('mpesaFields');
  const bankFields  = document.getElementById('bankFields');
  const feeText     = document.getElementById('feeText');
  const submitLabel = document.getElementById('submitLabel');

  if (type === 'bank') {
    mpesaFields.style.display = 'none';
    bankFields.style.display  = 'block';
    feeText.textContent       = 'Bank transfer fee: KES 50';
    submitLabel.textContent   = 'Send to Bank';
    // Copy account number to destination hidden field
    document.querySelector('select[name="bank_code"]').addEventListener('change', updateBankDest);
    document.querySelector('input[name="account_number"]').addEventListener('input', updateBankDest);
  } else {
    mpesaFields.style.display = 'block';
    bankFields.style.display  = 'none';
    feeText.textContent       = 'M-Pesa send fee: KES 25';
    submitLabel.textContent   = 'Send via M-Pesa';
  }
}

function updateBankDest() {
  const bank    = document.querySelector('select[name="bank_code"]').value;
  const acctNum = document.querySelector('input[name="account_number"]').value;
  document.getElementById('bankDestHidden').value = [bank, acctNum].filter(Boolean).join(' - ');
}

// Wire bank field listeners
document.querySelector('select[name="bank_code"]').addEventListener('change', updateBankDest);
document.querySelector('input[name="account_number"]').addEventListener('input', updateBankDest);

// On submit, if bank, ensure destination is populated
document.getElementById('transferForm').addEventListener('submit', function(e) {
  const type = document.querySelector('input[name="transfer_type"]:checked').value;
  if (type === 'bank') {
    updateBankDest();
    const dest = document.getElementById('bankDestHidden').value;
    // rename mpesa destination field so it doesn't override
    const mpesaPhone = document.getElementById('mpesaPhone');
    if (mpesaPhone) mpesaPhone.removeAttribute('name');
  } else {
    // disable bank account_number to avoid duplicate names
    const bankAcct = document.querySelector('#bankFields input[name="account_number"]');
    if (bankAcct) bankAcct.removeAttribute('name');
    const bankHidden = document.getElementById('bankDestHidden');
    if (bankHidden) bankHidden.removeAttribute('name');
  }
});
</script>
