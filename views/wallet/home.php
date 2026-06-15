<?php
$recentTxns = WalletTransaction::getForUser($walletUser['id'], 6);
?>

<!-- Quick Actions -->
<div style="padding:20px 0 0">
  <div class="quick-grid">
    <a href="<?= APP_URL ?>/wallet/send" class="quick-item">
      <div class="quick-icon" style="background:#f97316"><i class="fas fa-paper-plane"></i></div>
      <span class="quick-label">Send</span>
    </a>
    <a href="<?= APP_URL ?>/wallet/receive" class="quick-item">
      <div class="quick-icon" style="background:#158347"><i class="fas fa-arrow-down"></i></div>
      <span class="quick-label">Receive</span>
    </a>
    <a href="<?= APP_URL ?>/wallet/airtime" class="quick-item">
      <div class="quick-icon" style="background:#3b82f6"><i class="fas fa-mobile-alt"></i></div>
      <span class="quick-label">Airtime</span>
    </a>
    <a href="<?= APP_URL ?>/wallet/paybill" class="quick-item">
      <div class="quick-icon" style="background:#f59e0b"><i class="fas fa-file-invoice"></i></div>
      <span class="quick-label">Paybill</span>
    </a>
  </div>

  <div class="wdivider" style="margin-top:14px">More Services</div>

  <div class="quick-grid">
    <a href="<?= APP_URL ?>/wallet/transfer?type=mpesa" class="quick-item">
      <div class="quick-icon" style="background:#158347"><i class="fas fa-money-bill-wave"></i></div>
      <span class="quick-label">M-Pesa</span>
    </a>
    <a href="<?= APP_URL ?>/wallet/transfer?type=bank" class="quick-item">
      <div class="quick-icon" style="background:#0D1B3E"><i class="fas fa-university"></i></div>
      <span class="quick-label">Bank</span>
    </a>
    <a href="<?= APP_URL ?>/wallet/airtime?type=data" class="quick-item">
      <div class="quick-icon" style="background:#2563eb"><i class="fas fa-wifi"></i></div>
      <span class="quick-label">Data</span>
    </a>
    <form method="POST" action="<?= APP_URL ?>/wallet/deposit" style="margin:0">
      <?= csrf_field() ?>
      <input type="hidden" name="amount" value="1000">
      <button type="submit" class="quick-item" style="font-family:inherit">
        <div class="quick-icon" style="background:#7c3aed"><i class="fas fa-plus-circle"></i></div>
        <span class="quick-label">Top Up</span>
      </button>
    </form>
  </div>
</div>

<!-- Recent Activity -->
<div class="wsection-hd" style="margin-top:20px">
  <span class="wsection-title">Recent Activity</span>
  <a href="<?= APP_URL ?>/wallet/transactions" class="wsection-link">See all</a>
</div>

<div style="background:white;border-radius:20px;margin:0 14px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06)">
<?php if (empty($recentTxns)): ?>
  <div class="wempty">
    <i class="fas fa-receipt"></i>
    <p>No transactions yet</p>
    <p style="font-size:.75rem;margin-top:4px">Send money or buy airtime to get started</p>
  </div>
<?php else: ?>
  <div class="txn-list">
    <?php foreach ($recentTxns as $t):
      $isCredit = WalletTransaction::isCredit($t['type']);
      $color    = WalletTransaction::typeColor($t['type']);
      $icon     = WalletTransaction::typeIcon($t['type']);
      $label    = WalletTransaction::typeLabel($t['type']);
      $name     = $t['counterparty_name'] ?: $t['counterparty'] ?: $label;
    ?>
    <div class="txn-item">
      <div class="txn-icon" style="background:<?= $color ?>"><i class="fas <?= $icon ?>"></i></div>
      <div class="txn-body">
        <div class="txn-title"><?= htmlspecialchars($label) ?></div>
        <div class="txn-sub"><?= htmlspecialchars($name) ?></div>
      </div>
      <div class="txn-right">
        <div class="txn-amount <?= $isCredit ? 'credit' : 'debit' ?>">
          <?= $isCredit ? '+' : '-' ?>KES <?= number_format((float)$t['amount'], 2) ?>
        </div>
        <div class="txn-date"><?= date('d M, H:i', strtotime($t['created_at'])) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
</div>

<!-- Demo notice -->
<div style="margin:16px 14px 0;padding:12px 14px;background:#fefce8;border-radius:12px;border:1px solid #fde047;font-size:.75rem;color:#854d0e;display:flex;align-items:flex-start;gap:8px">
  <i class="fas fa-flask" style="margin-top:1px;flex-shrink:0"></i>
  <span><strong>Sandbox mode</strong> — all transactions are simulated. Tap <strong>Top Up</strong> to add KES 1,000 demo funds anytime.</span>
</div>
