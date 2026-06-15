<?php
$pageTitle  = 'Send Money';
$backUrl    = APP_URL . '/wallet/home';
$prefillWid = trim($_GET['wid'] ?? '');
?>
<div style="padding-top:6px">

  <div class="wform-card">
    <form method="POST" action="<?= APP_URL ?>/wallet/send" id="sendForm">
      <?= csrf_field() ?>

      <!-- Recipient search -->
      <div class="wform-group">
        <label class="wform-label">Send to</label>
        <input type="text" id="recipientSearch" class="wform-control"
               placeholder="Wallet ID, phone, or email" autocomplete="off">
        <div class="wform-hint">e.g. OP1234567890 · 0712345678 · jane@example.com</div>

        <div id="recipientCard" class="recipient-card" style="display:none">
          <div class="recipient-avatar" id="rcpInitial"></div>
          <div class="recipient-info">
            <div class="recipient-name" id="rcpName"></div>
            <div class="recipient-wid" id="rcpWid"></div>
          </div>
          <div class="recipient-check"><i class="fas fa-check-circle"></i></div>
        </div>
        <div id="recipientNotFound" class="recipient-notfound" style="display:none">
          <i class="fas fa-exclamation-circle"></i> No user found with that ID, phone, or email.
        </div>

        <input type="hidden" name="recipient_id" id="recipientId">
      </div>

      <!-- Amount and note (hidden until recipient found) -->
      <div id="sendExtras" style="display:none">
        <div class="wform-group">
          <label class="wform-label">Amount (KES)</label>
          <input type="number" name="amount" id="amountInput" class="wform-control"
                 placeholder="0.00" min="10" step="1" required>
          <div class="amount-picks" style="margin-top:8px">
            <?php foreach ([100,500,1000,2000,5000] as $v): ?>
            <button type="button" class="amount-pick" onclick="document.getElementById('amountInput').value=<?= $v ?>">
              <?= number_format($v) ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="wform-group">
          <label class="wform-label">Note <span style="font-weight:400">(optional)</span></label>
          <input type="text" name="note" class="wform-control" placeholder="What's this for?">
        </div>

        <div class="wform-group">
          <label class="wform-label">Enter PIN to confirm</label>
          <input type="password" name="pin" class="wform-control pin-input"
                 inputmode="numeric" maxlength="4" placeholder="····" required autocomplete="off">
        </div>

        <div style="background:#f0fdf4;border-radius:12px;padding:12px 14px;margin-bottom:12px;font-size:.78rem;color:#166534">
          <i class="fas fa-info-circle"></i> Wallet-to-wallet transfers are <strong>instant and free</strong>.
        </div>

        <button type="submit" class="wbtn wbtn-primary">
          <i class="fas fa-paper-plane"></i> Send Money
        </button>
      </div>

    </form>
  </div>

</div>

<script>
(function() {
  const search   = document.getElementById('recipientSearch');
  const card     = document.getElementById('recipientCard');
  const notFound = document.getElementById('recipientNotFound');
  const extras   = document.getElementById('sendExtras');
  const hiddenId = document.getElementById('recipientId');
  const rcpInit  = document.getElementById('rcpInitial');
  const rcpName  = document.getElementById('rcpName');
  const rcpWid   = document.getElementById('rcpWid');

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
      fetch(window.APP_URL + '/wallet/find-user?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
          if (data.found) {
            rcpInit.textContent = data.name.charAt(0).toUpperCase();
            rcpName.textContent = data.name;
            rcpWid.textContent  = data.wallet_id;
            hiddenId.value      = data.id;
            card.style.display  = 'flex';
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

  document.getElementById('sendForm').addEventListener('submit', function(e) {
    if (!hiddenId.value) {
      e.preventDefault();
      alert('Please search for and select a recipient first.');
    }
  });

  // Pre-fill from QR scan (?wid=)
  const prefill = '<?= addslashes($prefillWid) ?>';
  if (prefill) {
    search.value = prefill;
    search.dispatchEvent(new Event('input'));
  }
})();
</script>
