<?php
$pageTitle = 'Transaction History';
$backUrl   = APP_URL . '/wallet/home';

$filterType = $_GET['type'] ?? '';
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 20;
$offset     = ($page - 1) * $perPage;

// Build query with optional type filter
if ($filterType && $filterType !== 'all') {
    $txns  = DB::fetchAll(
        "SELECT * FROM wallet_transactions WHERE wallet_user_id=? AND type=? ORDER BY created_at DESC LIMIT ? OFFSET ?",
        [$walletUser['id'], $filterType, $perPage, $offset]
    );
    $total = (int)(DB::fetch(
        "SELECT COUNT(*) as c FROM wallet_transactions WHERE wallet_user_id=? AND type=?",
        [$walletUser['id'], $filterType]
    )['c'] ?? 0);
} else {
    $txns  = WalletTransaction::getForUser($walletUser['id'], $perPage, $offset);
    $total = WalletTransaction::countForUser($walletUser['id']);
}

$totalPages = (int)ceil($total / $perPage);

$typeFilters = [
    ''              => 'All',
    'send'          => 'Sent',
    'receive'       => 'Received',
    'airtime'       => 'Airtime',
    'data'          => 'Data',
    'paybill'       => 'Paybill',
    'mpesa_out'     => 'M-Pesa',
    'bank_transfer' => 'Bank',
    'deposit'       => 'Top Up',
];
?>

<!-- Filters -->
<div class="history-filters">
  <?php foreach ($typeFilters as $val => $label): ?>
  <a href="<?= APP_URL ?>/wallet/transactions<?= $val ? '?type=' . $val : '' ?>"
     class="hf-chip <?= $filterType === $val ? 'active' : '' ?>">
    <?= htmlspecialchars($label) ?>
  </a>
  <?php endforeach; ?>
</div>

<!-- Transaction list -->
<?php if (empty($txns)): ?>
  <div class="wempty" style="padding-top:60px">
    <i class="fas fa-receipt"></i>
    <p>No transactions found</p>
    <?php if ($filterType): ?>
    <p style="margin-top:8px"><a href="<?= APP_URL ?>/wallet/transactions" style="color:#158347;font-size:.82rem">Clear filter</a></p>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div style="background:white;margin:8px 14px;border-radius:20px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06)">
    <div class="txn-list">
      <?php foreach ($txns as $t):
        $isCredit = WalletTransaction::isCredit($t['type']);
        $color    = WalletTransaction::typeColor($t['type']);
        $icon     = WalletTransaction::typeIcon($t['type']);
        $label    = WalletTransaction::typeLabel($t['type']);
        $name     = $t['counterparty_name'] ?: $t['counterparty'] ?: $label;
        $date     = date('d M Y · H:i', strtotime($t['created_at']));
      ?>
      <div class="txn-item" style="cursor:default">
        <div class="txn-icon" style="background:<?= $color ?>"><i class="fas <?= $icon ?>"></i></div>
        <div class="txn-body">
          <div class="txn-title"><?= htmlspecialchars($label) ?></div>
          <div class="txn-sub"><?= htmlspecialchars($name) ?></div>
          <?php if ($t['description']): ?>
          <div class="txn-sub" style="font-style:italic"><?= htmlspecialchars(mb_substr($t['description'], 0, 50)) ?></div>
          <?php endif; ?>
        </div>
        <div class="txn-right">
          <div class="txn-amount <?= $isCredit ? 'credit' : 'debit' ?>">
            <?= $isCredit ? '+' : '-' ?>KES <?= number_format((float)$t['amount'], 2) ?>
          </div>
          <?php if ((float)$t['fee'] > 0): ?>
          <div class="txn-date">Fee: KES <?= number_format((float)$t['fee'], 2) ?></div>
          <?php endif; ?>
          <div class="txn-date"><?= $date ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="wpagination">
    <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?><?= $filterType ? '&type=' . $filterType : '' ?>"><i class="fas fa-chevron-left"></i></a>
    <?php endif; ?>

    <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
      <?php if ($p === $page): ?>
        <span class="current"><?= $p ?></span>
      <?php else: ?>
        <a href="?page=<?= $p ?><?= $filterType ? '&type=' . $filterType : '' ?>"><?= $p ?></a>
      <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page + 1 ?><?= $filterType ? '&type=' . $filterType : '' ?>"><i class="fas fa-chevron-right"></i></a>
    <?php endif; ?>
  </div>
  <div style="text-align:center;font-size:.75rem;color:#94a3b8;padding-bottom:8px"><?= $total ?> total transactions</div>
  <?php endif; ?>
<?php endif; ?>
