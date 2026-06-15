<?php
$stats = User::getStats($_SESSION['user_id']);
$recentTxns = Transaction::getForUser($_SESSION['user_id'], 8);
?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
  <div>
    <h2>Good <?= (date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening')) ?>,
      <?= sanitize(explode(' ', auth_user()['business_name'])[0]) ?> 👋</h2>
    <p>Here's what's happening with your payments today, <?= date('l, d F Y') ?>.</p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a href="<?= APP_URL ?>/dashboard/payment-links" class="btn btn-outline btn-sm">
      <i class="fas fa-link"></i> New Payment Link
    </a>
    <a href="<?= APP_URL ?>/dashboard/mpesa" class="btn btn-primary btn-sm">
      <i class="fas fa-paper-plane"></i> Send STK Push
    </a>
  </div>
</div>

<!-- Stat Cards -->
<div class="grid-4 mb-6">
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-arrow-down"></i></div>
    <div>
      <div class="stat-value"><?= format_amount($stats['today_received']) ?></div>
      <div class="stat-label">Received Today</div>
      <div class="stat-trend up"><i class="fas fa-arrow-up"></i> Live</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon navy"><i class="fas fa-wallet"></i></div>
    <div>
      <div class="stat-value"><?= format_amount($stats['wallet_balance']) ?></div>
      <div class="stat-label">Wallet Balance</div>
      <div class="stat-trend"><a href="<?= APP_URL ?>/dashboard/wallet" style="font-size:.78rem;color:var(--green)">Withdraw →</a></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
    <div>
      <div class="stat-value"><?= format_amount($stats['total_received']) ?></div>
      <div class="stat-label">Total Received (All Time)</div>
      <div class="stat-trend up"><?= number_format($stats['total_count']) ?> transactions</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
    <div>
      <div class="stat-value"><?= $stats['pending_count'] ?></div>
      <div class="stat-label">Pending Transactions</div>
      <div class="stat-trend"><a href="<?= APP_URL ?>/dashboard/transactions?status=pending" style="font-size:.78rem;color:var(--green)">View →</a></div>
    </div>
  </div>
</div>

<div class="grid-2-1 mb-6">
  <!-- Recent Transactions -->
  <div class="card">
    <div class="card-header">
      <h4><i class="fas fa-exchange-alt" style="color:var(--green);margin-right:6px"></i> Recent Transactions</h4>
      <a href="<?= APP_URL ?>/dashboard/transactions" class="btn btn-ghost btn-sm">View All <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="p-0">
      <?php if (empty($recentTxns)): ?>
        <div class="empty-state">
          <i class="fas fa-inbox"></i>
          <h4>No transactions yet</h4>
          <p>Your first payment will appear here.</p>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="orb-table">
            <thead>
              <tr>
                <th>Reference</th>
                <th>Channel</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Time</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentTxns as $txn): ?>
              <tr>
                <td>
                  <span style="font-family:monospace;font-size:.82rem"><?= sanitize($txn['reference']) ?></span>
                  <?php if ($txn['description']): ?>
                    <div style="font-size:.75rem;color:var(--text-muted)"><?= sanitize(substr($txn['description'], 0, 30)) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="chip <?= $txn['channel'] === 'mpesa' ? 'green' : 'navy' ?>">
                    <?php
                    $icons = ['mpesa'=>'mobile-alt','card'=>'credit-card','wallet'=>'wallet','bank'=>'university','payment_link'=>'link'];
                    $icon = $icons[$txn['channel']] ?? 'exchange-alt';
                    ?>
                    <i class="fas fa-<?= $icon ?>"></i>
                    <?= ucfirst(str_replace('_',' ',$txn['channel'])) ?>
                  </span>
                </td>
                <td style="font-weight:700;color:var(--navy)"><?= format_amount($txn['amount'], $txn['currency']) ?></td>
                <td><?= transaction_status_badge($txn['status']) ?></td>
                <td style="color:var(--text-muted);font-size:.82rem"><?= time_ago($txn['created_at']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Quick Actions -->
  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-bolt" style="color:var(--green);margin-right:6px"></i> Quick Actions</h4></div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
        <a href="<?= APP_URL ?>/dashboard/mpesa" class="btn btn-primary btn-block">
          <i class="fas fa-mobile-alt"></i> M-Pesa STK Push
        </a>
        <a href="<?= APP_URL ?>/dashboard/payment-links" class="btn btn-outline btn-block">
          <i class="fas fa-link"></i> Create Payment Link
        </a>
        <a href="<?= APP_URL ?>/dashboard/wallet" class="btn btn-outline-navy btn-block">
          <i class="fas fa-money-bill-wave"></i> Withdraw Funds
        </a>
        <a href="<?= APP_URL ?>/dashboard/card" class="btn btn-ghost btn-block" style="border:1px solid var(--border)">
          <i class="fas fa-credit-card"></i> Charge Card
        </a>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h4><i class="fas fa-key" style="color:var(--green);margin-right:6px"></i> API Keys</h4></div>
      <div class="card-body">
        <?php $keys = ApiKey::getForUser($_SESSION['user_id']); ?>
        <?php if (empty($keys)): ?>
          <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:12px">No API keys generated yet.</p>
          <a href="<?= APP_URL ?>/dashboard/api-keys" class="btn btn-outline btn-sm">Generate Keys</a>
        <?php else: $k = $keys[0]; ?>
          <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:6px"><?= sanitize($k['label']) ?> (<?= $k['environment'] ?>)</div>
          <div class="copy-field" style="margin-bottom:10px">
            <input type="text" value="<?= sanitize($k['masked_key']) ?>" readonly>
            <button onclick="copyToClipboard('<?= sanitize($k['masked_key']) ?>', this)">Copy</button>
          </div>
          <a href="<?= APP_URL ?>/dashboard/api-keys" style="font-size:.82rem;color:var(--green)">Manage API Keys →</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Payment Channels Overview -->
<div class="card">
  <div class="card-header">
    <h4><i class="fas fa-chart-bar" style="color:var(--green);margin-right:6px"></i> Payment Channels</h4>
    <a href="<?= APP_URL ?>/dashboard/transactions" class="btn btn-ghost btn-sm">Full Report</a>
  </div>
  <div class="card-body">
    <div class="grid-4">
      <?php
      $channels = [
        ['label' => 'M-Pesa',        'icon' => 'mobile-alt',   'color' => 'green', 'key' => 'mpesa'],
        ['label' => 'Card',          'icon' => 'credit-card',  'color' => 'navy',  'key' => 'card'],
        ['label' => 'Wallet',        'icon' => 'wallet',       'color' => 'green', 'key' => 'wallet'],
        ['label' => 'Payment Links', 'icon' => 'link',         'color' => 'navy',  'key' => 'payment_link'],
      ];
      foreach ($channels as $ch):
        $row = DB::fetch(
          "SELECT COALESCE(SUM(amount),0) as total, COUNT(*) as cnt FROM transactions WHERE user_id = ? AND channel = ? AND status = 'completed'",
          [$_SESSION['user_id'], $ch['key']]
        );
      ?>
      <div style="text-align:center;padding:16px;border:1px solid var(--border);border-radius:var(--radius-lg)">
        <div class="stat-icon <?= $ch['color'] ?>" style="margin:0 auto 10px;width:44px;height:44px;font-size:1.1rem">
          <i class="fas fa-<?= $ch['icon'] ?>"></i>
        </div>
        <div style="font-weight:700;color:var(--navy);font-size:1rem"><?= format_amount($row['total']) ?></div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px"><?= $ch['label'] ?></div>
        <div style="font-size:.72rem;color:var(--text-light);margin-top:3px"><?= number_format($row['cnt']) ?> txns</div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
