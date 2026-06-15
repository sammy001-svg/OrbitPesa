<?php
$pageTitle = 'Pay Business';
$backUrl   = APP_URL . '/wallet/home';
?>
<div style="padding-top:6px">

  <?php if ($flash = flash('wallet_success')): ?>
  <div class="walert walert-success" style="margin:0 0 12px">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flash) ?>
  </div>
  <?php endif; ?>
  <?php if ($flash = flash('wallet_error')): ?>
  <div class="walert walert-error" style="margin:0 0 12px">
    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($flash) ?>
  </div>
  <?php endif; ?>

  <div class="wform-card">
    <form method="POST" action="<?= APP_URL ?>/wallet/pay-merchant" id="payBizForm">
      <?= csrf_field() ?>

      <!-- Merchant search -->
      <div class="wform-group">
        <label class="wform-label">Search Business</label>
        <input type="text" id="merchantSearch" class="wform-control"
               placeholder="Business name, email, or phone" autocomplete="off">
        <div class="wform-hint">Type at least 3 characters to search</div>

        <div id="merchantCard" class="recipient-card" style="display:none">
          <div class="recipient-avatar" style="background:#0D1B3E" id="bizInitial"></div>
          <div class="recipient-info">
            <div class="recipient-name" id="bizName"></div>
            <div class="recipient-wid" id="bizEmail"></div>
          </div>
          <div class="recipient-check"><i class="fas fa-check-circle"></i></div>
        </div>
        <div id="merchantNotFound" class="recipient-notfound" style="display:none">
          <i class="fas fa-exclamation-circle"></i> No active business found with that name or email.
        </div>

        <input type="hidden" name="merchant_id" id="merchantId">
      </div>

      <!-- Payment fields (shown after merchant found) -->
      <div id="payExtras" style="display:none">
        <div class="wform-group">
          <label class="wform-label">Amount (KES)</label>
          <input type="number" name="amount" id="amountInput" class="wform-control"
                 placeholder="0.00" min="1" step="1" required>
          <div class="amount-picks" style="margin-top:8px">
            <?php foreach ([100, 500, 1000, 2500, 5000] as $v): ?>
            <button type="button" class="amount-pick" onclick="document.getElementById('amountInput').value=<?= $v ?>">
              <?= number_format($v) ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="wform-group">
          <label class="wform-label">Note / Reference <span style="font-weight:400">(optional)</span></label>
          <input type="text" name="note" class="wform-control"
                 placeholder="e.g. Invoice #1234, School fees, Order #56">
        </div>

        <div class="wform-group">
          <label class="wform-label">Enter PIN to confirm</label>
          <input type="password" name="pin" class="wform-control pin-input"
                 inputmode="numeric" maxlength="4" placeholder="····" required autocomplete="off">
        </div>

        <div style="background:#f0f4ff;border-radius:12px;padding:12px 14px;margin-bottom:14px;font-size:.78rem;color:#1e40af">
          <i class="fas fa-store"></i> Payments to businesses are <strong>instant and free</strong>.
          The merchant will be notified immediately.
        </div>

        <button type="submit" class="wbtn wbtn-primary">
          <i class="fas fa-store"></i> Pay Business
        </button>
      </div>

    </form>
  </div>

</div>

<script>
(function() {
  const search    = document.getElementById('merchantSearch');
  const card      = document.getElementById('merchantCard');
  const notFound  = document.getElementById('merchantNotFound');
  const extras    = document.getElementById('payExtras');
  const hiddenId  = document.getElementById('merchantId');
  const bizInit   = document.getElementById('bizInitial');
  const bizName   = document.getElementById('bizName');
  const bizEmail  = document.getElementById('bizEmail');

  let timer;
  search.addEventListener('input', () => {
    clearTimeout(timer);
    const q = search.value.trim();
    card.style.display     = 'none';
    notFound.style.display = 'none';
    extras.style.display   = 'none';
    hiddenId.value         = '';

    if (q.length < 3) return;

    timer = setTimeout(() => {
      fetch(window.APP_URL + '/wallet/find-merchant?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
          if (data.found) {
            bizInit.textContent  = data.name.charAt(0).toUpperCase();
            bizName.textContent  = data.name;
            bizEmail.textContent = data.email;
            hiddenId.value       = data.id;
            card.style.display   = 'flex';
            extras.style.display = 'block';
            notFound.style.display = 'none';
          } else {
            card.style.display     = 'none';
            extras.style.display   = 'none';
            notFound.style.display = 'flex';
          }
        })
        .catch(() => { notFound.style.display = 'flex'; });
    }, 500);
  });

  document.getElementById('payBizForm').addEventListener('submit', function(e) {
    if (!hiddenId.value) {
      e.preventDefault();
      alert('Please search for and select a business first.');
    }
  });
})();
</script>
