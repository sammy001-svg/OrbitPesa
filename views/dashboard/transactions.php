<?php
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 20;
$offset  = ($page - 1) * $limit;
$filters = [
    'status'    => $_GET['status'] ?? '',
    'channel'   => $_GET['channel'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to'   => $_GET['date_to'] ?? '',
];
$txns  = Transaction::getForUser($_SESSION['user_id'], $limit, $offset, $filters);
$total = Transaction::countForUser($_SESSION['user_id'], $filters);
$pages = (int)ceil($total / $limit);
?>

<div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px">
  <div>
    <h2>Transactions</h2>
    <p>Complete history of all payments processed through your account.</p>
  </div>
  <a href="<?= APP_URL ?>/dashboard/transactions?export=csv<?= http_build_query(array_filter($filters)) ?>"
     class="btn btn-outline btn-sm">
    <i class="fas fa-download"></i> Export CSV
  </a>
</div>

<!-- Filters -->
<div class="card mb-6">
  <div class="card-body" style="padding:16px 20px">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
      <div>
        <label class="form-label" style="font-size:.78rem">Status</label>
        <select name="status" class="form-control form-select" style="min-width:130px">
          <option value="">All Statuses</option>
          <?php foreach (['completed','pending','failed','processing','reversed'] as $s): ?>
            <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label" style="font-size:.78rem">Channel</label>
        <select name="channel" class="form-control form-select" style="min-width:140px">
          <option value="">All Channels</option>
          <option value="mpesa" <?= $filters['channel'] === 'mpesa' ? 'selected' : '' ?>>M-Pesa</option>
          <option value="card" <?= $filters['channel'] === 'card' ? 'selected' : '' ?>>Card</option>
          <option value="wallet" <?= $filters['channel'] === 'wallet' ? 'selected' : '' ?>>Wallet</option>
          <option value="payment_link" <?= $filters['channel'] === 'payment_link' ? 'selected' : '' ?>>Payment Link</option>
        </select>
      </div>
      <div>
        <label class="form-label" style="font-size:.78rem">Date From</label>
        <input type="date" name="date_from" class="form-control" value="<?= $filters['date_from'] ?>" style="min-width:140px">
      </div>
      <div>
        <label class="form-label" style="font-size:.78rem">Date To</label>
        <input type="date" name="date_to" class="form-control" value="<?= $filters['date_to'] ?>" style="min-width:140px">
      </div>
      <div style="display:flex;gap:8px">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
        <a href="<?= APP_URL ?>/dashboard/transactions" class="btn btn-ghost btn-sm">Clear</a>
      </div>
    </form>
  </div>
</div>

<!-- Summary -->
<?php if (!empty($txns)): ?>
<div class="grid-4 mb-6" style="grid-template-columns:repeat(4,1fr)">
  <?php
  $summary = DB::fetch(
    "SELECT COALESCE(SUM(amount),0) as vol, COUNT(*) as cnt,
            SUM(CASE WHEN status='completed' THEN amount ELSE 0 END) as completed_vol,
            SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending_cnt
     FROM transactions WHERE user_id = ?",
    [$_SESSION['user_id']]
  );
  ?>
  <div class="stat-card"><div class="stat-icon green"><i class="fas fa-coins"></i></div><div><div class="stat-value" style="font-size:1.2rem"><?= format_amount($summary['vol']) ?></div><div class="stat-label">Total Volume</div></div></div>
  <div class="stat-card"><div class="stat-icon navy"><i class="fas fa-check"></i></div><div><div class="stat-value" style="font-size:1.2rem"><?= format_amount($summary['completed_vol']) ?></div><div class="stat-label">Completed</div></div></div>
  <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-clock"></i></div><div><div class="stat-value" style="font-size:1.2rem"><?= $summary['pending_cnt'] ?></div><div class="stat-label">Pending</div></div></div>
  <div class="stat-card"><div class="stat-icon green"><i class="fas fa-hashtag"></i></div><div><div class="stat-value" style="font-size:1.2rem"><?= number_format($summary['cnt']) ?></div><div class="stat-label">Total Count</div></div></div>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h4>
      <i class="fas fa-list" style="color:var(--green);margin-right:6px"></i>
      <?= number_format($total) ?> Transaction<?= $total !== 1 ? 's' : '' ?>
    </h4>
  </div>
  <div class="p-0">
    <?php if (empty($txns)): ?>
      <div class="empty-state">
        <i class="fas fa-search"></i>
        <h4>No transactions found</h4>
        <p>Try adjusting your filters or start accepting payments.</p>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="orb-table">
          <thead>
            <tr>
              <th>Reference</th>
              <th>Description</th>
              <th>Channel</th>
              <th>Phone / Card</th>
              <th>Amount</th>
              <th>Fee</th>
              <th>Status</th>
              <th>Date</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($txns as $txn): ?>
            <tr>
              <td><code style="font-size:.8rem"><?= sanitize($txn['reference']) ?></code></td>
              <td style="max-width:200px"><div class="truncate" style="font-size:.84rem"><?= sanitize($txn['description'] ?: '—') ?></div></td>
              <td>
                <?php
                $icons = ['mpesa'=>'mobile-alt','card'=>'credit-card','wallet'=>'wallet','bank'=>'university','payment_link'=>'link'];
                $icon = $icons[$txn['channel']] ?? 'exchange-alt';
                ?>
                <span class="chip <?= in_array($txn['channel'],['mpesa','wallet','payment_link']) ? 'green' : 'navy' ?>">
                  <i class="fas fa-<?= $icon ?>"></i>
                  <?= ucfirst(str_replace('_',' ',$txn['channel'])) ?>
                </span>
              </td>
              <td style="font-size:.82rem;color:var(--text-muted)">
                <?= $txn['phone'] ? mask_phone($txn['phone']) : ($txn['card_last4'] ? '**** '.$txn['card_last4'] : '—') ?>
              </td>
              <td style="font-weight:700;color:var(--navy)"><?= format_amount($txn['amount'], $txn['currency']) ?></td>
              <td style="font-size:.82rem;color:var(--text-muted)"><?= format_amount($txn['fee'], $txn['currency']) ?></td>
              <td><?= transaction_status_badge($txn['status']) ?></td>
              <td style="font-size:.8rem;color:var(--text-muted);white-space:nowrap">
                <?= date('d M Y', strtotime($txn['created_at'])) ?><br>
                <span style="font-size:.73rem"><?= date('H:i', strtotime($txn['created_at'])) ?></span>
              </td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-ghost btn-sm" data-toggle="dropdown" style="padding:4px 8px">
                    <i class="fas fa-ellipsis-v"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a href="#" class="dropdown-item" data-copy="<?= sanitize($txn['reference']) ?>">
                      <i class="fas fa-copy"></i> Copy Ref
                    </a>
                    <?php if ($txn['provider_ref']): ?>
                    <a href="#" class="dropdown-item" data-copy="<?= sanitize($txn['provider_ref']) ?>">
                      <i class="fas fa-receipt"></i> Copy Provider Ref
                    </a>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($pages > 1): ?>
      <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:.82rem;color:var(--text-muted)">
          Showing <?= $offset + 1 ?>&ndash;<?= min($offset + $limit, $total) ?> of <?= number_format($total) ?>
        </span>
        <div class="pagination">
          <?php
          $base = APP_URL . '/dashboard/transactions?' . http_build_query(array_filter($filters));
          if ($page > 1): ?>
            <a href="<?= $base ?>&page=<?= $page - 1 ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a>
          <?php endif;
          for ($i = max(1,$page-2); $i <= min($pages,$page+2); $i++): ?>
            <a href="<?= $base ?>&page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
          <?php endfor;
          if ($page < $pages): ?>
            <a href="<?= $base ?>&page=<?= $page + 1 ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
