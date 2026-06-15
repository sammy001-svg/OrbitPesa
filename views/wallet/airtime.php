<?php
$typeParam = $_GET['type'] ?? 'airtime';
$isData    = $typeParam === 'data';
$pageTitle = $isData ? 'Buy Data Bundle' : 'Buy Airtime';
$backUrl   = APP_URL . '/wallet/home';
?>
<div style="padding-top:6px">
  <div class="wform-card">
    <form method="POST" action="<?= APP_URL ?>/wallet/airtime">
      <?= csrf_field() ?>

      <!-- Airtime / Data toggle -->
      <div class="wform-group">
        <label class="wform-label">Type</label>
        <div class="type-toggle">
          <div class="type-opt">
            <input type="radio" name="type" id="type_airtime" value="airtime" <?= !$isData ? 'checked' : '' ?>>
            <label class="type-label" for="type_airtime"><i class="fas fa-mobile-alt"></i> Airtime</label>
          </div>
          <div class="type-opt">
            <input type="radio" name="type" id="type_data" value="data" <?= $isData ? 'checked' : '' ?>>
            <label class="type-label" for="type_data"><i class="fas fa-wifi"></i> Data Bundle</label>
          </div>
        </div>
      </div>

      <!-- Network -->
      <div class="wform-group">
        <label class="wform-label">Network</label>
        <div class="network-grid">
          <?php
          $nets = [
            'safaricom' => ['#158347', 'fas fa-sim-card'],
            'airtel'    => ['#ef4444', 'fas fa-signal'],
            'telkom'    => ['#3b82f6', 'fas fa-broadcast-tower'],
            'faiba'     => ['#f97316', 'fas fa-wifi'],
          ];
          foreach ($nets as $val => [$bg, $icon]):
          ?>
          <div class="network-opt">
            <input type="radio" name="network" id="net_<?= $val ?>" value="<?= $val ?>"
                   <?= $val === 'safaricom' ? 'checked' : '' ?>>
            <label class="network-label" for="net_<?= $val ?>">
              <div class="net-icon" style="background:<?= $bg ?>"><i class="<?= $icon ?>"></i></div>
              <?= ucfirst($val) ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Phone -->
      <div class="wform-group">
        <label class="wform-label">Phone Number</label>
        <input type="tel" name="phone" class="wform-control" placeholder="0712 345 678" required
               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        <div class="wform-hint">Number to receive the airtime or data</div>
      </div>

      <!-- Amount -->
      <div class="wform-group">
        <label class="wform-label">Amount (KES)</label>
        <div class="amount-picks">
          <?php foreach ([10,20,50,100,200,500] as $v): ?>
          <button type="button" class="amount-pick" onclick="document.getElementById('airAmt').value=<?= $v ?>">
            <?= number_format($v) ?>
          </button>
          <?php endforeach; ?>
        </div>
        <input type="number" name="amount" id="airAmt" class="wform-control" style="margin-top:10px"
               placeholder="Enter amount" min="5" max="10000" step="1" required>
      </div>

      <!-- PIN -->
      <div class="wform-group">
        <label class="wform-label">PIN</label>
        <input type="password" name="pin" class="wform-control pin-input"
               inputmode="numeric" maxlength="4" placeholder="····" required autocomplete="off">
      </div>

      <div style="background:#eff6ff;border-radius:12px;padding:11px 14px;margin-bottom:12px;font-size:.78rem;color:#1e40af">
        <i class="fas fa-info-circle"></i> Balance: <strong>KES <?= number_format((float)$walletUser['balance'], 2) ?></strong> · No fees applied.
      </div>

      <button type="submit" class="wbtn wbtn-primary">
        <i class="fas fa-bolt"></i> <span id="btnLabel"><?= $isData ? 'Buy Data Bundle' : 'Buy Airtime' ?></span>
      </button>
    </form>
  </div>
</div>

<script>
document.querySelectorAll('input[name="type"]').forEach(r => {
  r.addEventListener('change', () => {
    document.getElementById('btnLabel').textContent = r.value === 'data' ? 'Buy Data Bundle' : 'Buy Airtime';
    document.title = (r.value === 'data' ? 'Buy Data Bundle' : 'Buy Airtime') + ' — OrbitPesa Wallet';
  });
});
</script>
