<?php
$backUrl = APP_URL . '/wallet/pockets';
$pct     = WalletPocket::progressPercent($pocket);

$recentPocketTxns = DB::fetchAll(
    "SELECT * FROM wallet_transactions
     WHERE wallet_user_id = ? AND counterparty = ? AND type IN ('pocket_in','pocket_out')
     ORDER BY created_at DESC LIMIT 15",
    [$walletUser['id'], $pocket['id']]
);
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

  <!-- Pocket balance card -->
  <div style="background:#0D1B3E;border-radius:20px;padding:20px;margin-bottom:14px;text-align:center">
    <div style="font-size:2.6rem;margin-bottom:6px"><?= htmlspecialchars($pocket['emoji']) ?></div>
    <div style="color:rgba(255,255,255,.7);font-size:.78rem;letter-spacing:.06em;text-transform:uppercase">
      <?= htmlspecialchars($pocket['name']) ?>
    </div>
    <div style="color:#fff;font-size:2rem;font-weight:700;margin-top:4px">
      KES <?= number_format((float)$pocket['balance'], 2) ?>
    </div>
    <?php if ($pocket['target_amount']): ?>
    <div style="color:rgba(255,255,255,.5);font-size:.75rem;margin-top:4px">
      Goal: KES <?= number_format((float)$pocket['target_amount'], 2) ?> &nbsp;·&nbsp; <?= $pct ?>% saved
    </div>
    <div style="margin:12px auto 0;max-width:220px;height:6px;background:rgba(255,255,255,.15);border-radius:99px;overflow:hidden">
      <div style="height:100%;width:<?= $pct ?>%;background:<?= $pct >= 100 ? '#22c55e' : '#a78bfa' ?>;border-radius:99px"></div>
    </div>
    <?php if ($pct >= 100): ?>
    <div style="color:#22c55e;font-size:.78rem;margin-top:6px;font-weight:600">
      <i class="fas fa-trophy"></i> Goal reached!
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>

  <!-- Action buttons -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
    <button onclick="showPanel('deposit')" id="btnDeposit"
            style="background:#7c3aed;color:#fff;border:none;border-radius:14px;padding:13px;font-size:.88rem;font-weight:600;cursor:pointer">
      <i class="fas fa-arrow-down"></i> Add Money
    </button>
    <button onclick="showPanel('withdraw')" id="btnWithdraw"
            style="background:#0D1B3E;color:#fff;border:none;border-radius:14px;padding:13px;font-size:.88rem;font-weight:600;cursor:pointer">
      <i class="fas fa-arrow-up"></i> Withdraw
    </button>
  </div>

  <!-- Deposit panel -->
  <div id="panelDeposit" class="wform-card" style="display:none;margin-bottom:14px">
    <div style="font-weight:600;font-size:.88rem;color:#7c3aed;margin-bottom:12px">
      <i class="fas fa-arrow-down"></i> Add Money to Pocket
    </div>
    <form method="POST" action="<?= APP_URL ?>/wallet/pockets/deposit">
      <?= csrf_field() ?>
      <input type="hidden" name="pocket_id" value="<?= htmlspecialchars($pocket['id']) ?>">

      <div class="wform-group">
        <label class="wform-label">Amount (KES)</label>
        <input type="number" name="amount" class="wform-control" placeholder="0.00" min="1" step="1" required>
        <div class="wform-hint">Your wallet balance: KES <?= number_format((float)$walletUser['balance'], 2) ?></div>
        <div class="amount-picks" style="margin-top:8px">
          <?php foreach ([100, 500, 1000, 2500, 5000] as $v): ?>
          <button type="button" class="amount-pick"
                  onclick="this.closest('form').querySelector('[name=amount]').value=<?= $v ?>">
            <?= number_format($v) ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="wform-group">
        <label class="wform-label">Enter PIN to confirm</label>
        <input type="password" name="pin" class="wform-control pin-input"
               inputmode="numeric" maxlength="4" placeholder="····" required autocomplete="off">
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
        <button type="button" onclick="hidePanel()" class="wbtn" style="background:#f1f5f9;color:#475569">
          Cancel
        </button>
        <button type="submit" class="wbtn wbtn-primary" style="background:#7c3aed">
          <i class="fas fa-piggy-bank"></i> Save
        </button>
      </div>
    </form>
  </div>

  <!-- Withdraw panel -->
  <div id="panelWithdraw" class="wform-card" style="display:none;margin-bottom:14px">
    <div style="font-weight:600;font-size:.88rem;color:#0D1B3E;margin-bottom:12px">
      <i class="fas fa-arrow-up"></i> Withdraw from Pocket
    </div>
    <form method="POST" action="<?= APP_URL ?>/wallet/pockets/withdraw">
      <?= csrf_field() ?>
      <input type="hidden" name="pocket_id" value="<?= htmlspecialchars($pocket['id']) ?>">

      <div class="wform-group">
        <label class="wform-label">Amount (KES)</label>
        <input type="number" name="amount" class="wform-control" placeholder="0.00"
               min="1" max="<?= (float)$pocket['balance'] ?>" step="1" required>
        <div class="wform-hint">Pocket balance: KES <?= number_format((float)$pocket['balance'], 2) ?></div>
      </div>

      <div class="wform-group">
        <label class="wform-label">Enter PIN to confirm</label>
        <input type="password" name="pin" class="wform-control pin-input"
               inputmode="numeric" maxlength="4" placeholder="····" required autocomplete="off">
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
        <button type="button" onclick="hidePanel()" class="wbtn" style="background:#f1f5f9;color:#475569">
          Cancel
        </button>
        <button type="submit" class="wbtn wbtn-primary">
          <i class="fas fa-arrow-up"></i> Withdraw
        </button>
      </div>
    </form>
  </div>

  <!-- Transaction history -->
  <div class="wsection-hd" style="margin-top:4px">
    <span class="wsection-title">Pocket Activity</span>
  </div>

  <div style="background:white;border-radius:20px;margin:0;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06)">
    <?php if (empty($recentPocketTxns)): ?>
    <div class="wempty">
      <i class="fas fa-piggy-bank"></i>
      <p>No activity yet</p>
      <p style="font-size:.75rem;margin-top:4px">Add money to start saving</p>
    </div>
    <?php else: ?>
    <div class="txn-list">
      <?php foreach ($recentPocketTxns as $t):
        $isCredit = $t['type'] === 'pocket_out';
        $icon     = 'fa-piggy-bank';
        $label    = $t['type'] === 'pocket_in' ? 'Saved' : 'Withdrawn';
        $color    = '#7c3aed';
      ?>
      <div class="txn-item">
        <div class="txn-icon" style="background:<?= $color ?>"><i class="fas <?= $icon ?>"></i></div>
        <div class="txn-body">
          <div class="txn-title"><?= $label ?></div>
          <div class="txn-sub"><?= date('d M Y, H:i', strtotime($t['created_at'])) ?></div>
        </div>
        <div class="txn-right">
          <div class="txn-amount <?= $isCredit ? 'credit' : 'debit' ?>">
            <?= $isCredit ? '+' : '-' ?>KES <?= number_format((float)$t['amount'], 2) ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Delete pocket -->
  <div style="margin-top:20px;text-align:center">
    <button onclick="document.getElementById('deleteModal').style.display='flex'"
            style="background:none;border:none;color:#dc2626;font-size:.8rem;cursor:pointer;text-decoration:underline">
      <i class="fas fa-trash-alt"></i> Delete this pocket
    </button>
  </div>

</div>

<!-- Delete confirmation modal -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;align-items:flex-end">
  <div style="background:#fff;border-radius:24px 24px 0 0;padding:24px;width:100%;max-width:430px;margin:0 auto">
    <div style="text-align:center;margin-bottom:16px">
      <div style="font-size:2rem;margin-bottom:8px">⚠️</div>
      <div style="font-weight:700;font-size:1rem;color:#0D1B3E">Delete "<?= htmlspecialchars($pocket['name']) ?>"?</div>
      <?php if ((float)$pocket['balance'] > 0): ?>
      <div style="color:#64748b;font-size:.82rem;margin-top:6px">
        KES <?= number_format((float)$pocket['balance'], 2) ?> will be returned to your wallet.
      </div>
      <?php else: ?>
      <div style="color:#64748b;font-size:.82rem;margin-top:6px">This pocket is empty and will be permanently removed.</div>
      <?php endif; ?>
    </div>
    <form method="POST" action="<?= APP_URL ?>/wallet/pockets/delete">
      <?= csrf_field() ?>
      <input type="hidden" name="pocket_id" value="<?= htmlspecialchars($pocket['id']) ?>">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <button type="button" onclick="document.getElementById('deleteModal').style.display='none'"
                class="wbtn" style="background:#f1f5f9;color:#475569">Keep it</button>
        <button type="submit" class="wbtn" style="background:#dc2626;color:#fff">Delete</button>
      </div>
    </form>
  </div>
</div>

<script>
function showPanel(type) {
  document.getElementById('panelDeposit').style.display  = type === 'deposit'  ? 'block' : 'none';
  document.getElementById('panelWithdraw').style.display = type === 'withdraw' ? 'block' : 'none';
  document.getElementById('btn' + type.charAt(0).toUpperCase() + type.slice(1)).style.opacity = '0.6';
}
function hidePanel() {
  document.getElementById('panelDeposit').style.display  = 'none';
  document.getElementById('panelWithdraw').style.display = 'none';
  document.getElementById('btnDeposit').style.opacity  = '1';
  document.getElementById('btnWithdraw').style.opacity = '1';
}
<?php if (flash('wallet_error') || flash('wallet_success')): ?>
// Auto-open the relevant panel if redirected back after error
<?php endif; ?>
</script>
