<?php
$stats     = Admin::getSystemStats();
$recentTxn = Admin::getRecentTransactions(8);
$recentMer = Admin::getRecentMerchants(5);
$byChannel = Admin::getVolumeByChannel();
$byDay     = Admin::getVolumeByDay(14);
$wStats    = WalletUser::stats();
$wTxnStats = DB::fetch("SELECT COUNT(*) as total, COALESCE(SUM(amount),0) as volume, COALESCE(SUM(fee),0) as fees FROM wallet_transactions WHERE status='completed'") ?? [];
$wToday    = DB::fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(amount),0) as vol FROM wallet_transactions WHERE status='completed' AND DATE(created_at)=CURDATE()") ?? [];
?>

<div class="section-hd">
  <div>
    <h2>System Overview</h2>
    <p>Real-time summary of all merchant activity — <?= date('l, d F Y H:i') ?></p>
  </div>
  <div style="display:flex;gap:10px">
    <a href="<?= APP_URL ?>/admin/transactions?export=csv" class="btn btn-outline btn-sm">
      <i class="fas fa-download"></i> Export Report
    </a>
    <a href="<?= APP_URL ?>/admin/merchants" class="btn btn-primary btn-sm">
      <i class="fas fa-store"></i> All Merchants
    </a>
  </div>
</div>

<!-- System Stats Row 1 -->
<div class="admin-stats">
  <div class="admin-stat green">
    <div class="admin-stat-icon green"><i class="fas fa-coins"></i></div>
    <div>
      <div class="admin-stat-val"><?= format_amount($stats['volume_all_time']) ?></div>
      <div class="admin-stat-lbl">Total Volume Processed</div>
      <div class="admin-stat-sub"><?= number_format($stats['txn_count']) ?> completed transactions</div>
    </div>
  </div>
  <div class="admin-stat navy">
    <div class="admin-stat-icon navy"><i class="fas fa-store"></i></div>
    <div>
      <div class="admin-stat-val"><?= number_format($stats['merchants_total']) ?></div>
      <div class="admin-stat-lbl">Total Merchants</div>
      <div class="admin-stat-sub"><?= $stats['merchants_active'] ?> active · <?= $stats['merchants_suspended'] ?> suspended</div>
    </div>
  </div>
  <div class="admin-stat green">
    <div class="admin-stat-icon green"><i class="fas fa-chart-line"></i></div>
    <div>
      <div class="admin-stat-val"><?= format_amount($stats['volume_today']) ?></div>
      <div class="admin-stat-lbl">Volume Today</div>
      <div class="admin-stat-sub"><?= format_amount($stats['volume_month']) ?> this month</div>
    </div>
  </div>
  <div class="admin-stat orange">
    <div class="admin-stat-icon orange"><i class="fas fa-percentage"></i></div>
    <div>
      <div class="admin-stat-val"><?= format_amount($stats['total_fees']) ?></div>
      <div class="admin-stat-lbl">Total Fees Earned</div>
      <div class="admin-stat-sub">Across all channels</div>
    </div>
  </div>
</div>

<!-- Consumer Wallet Summary -->
<div class="card" style="margin-bottom:24px">
  <div class="card-header" style="border-bottom:2px solid var(--green)">
    <h4><i class="fas fa-wallet" style="color:var(--green);margin-right:6px"></i> Consumer Wallet Overview</h4>
    <a href="<?= APP_URL ?>/admin/wallet-users" class="btn btn-ghost btn-sm">Manage Users</a>
  </div>
  <div class="card-body" style="padding:16px 20px">
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:20px">
      <div style="text-align:center">
        <div style="font-size:1.6rem;font-weight:800;color:var(--navy)"><?= number_format($wStats['total'] ?? 0) ?></div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px">Total Users</div>
      </div>
      <div style="text-align:center;border-left:1px solid var(--border)">
        <div style="font-size:1.6rem;font-weight:800;color:var(--green)"><?= number_format($wStats['active'] ?? 0) ?></div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px">Active</div>
      </div>
      <div style="text-align:center;border-left:1px solid var(--border)">
        <div style="font-size:1.6rem;font-weight:800;color:var(--navy)"><?= format_amount($wStats['total_balance'] ?? 0) ?></div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px">Total Balance</div>
      </div>
      <div style="text-align:center;border-left:1px solid var(--border)">
        <div style="font-size:1.6rem;font-weight:800;color:var(--navy)"><?= format_amount($wTxnStats['volume'] ?? 0) ?></div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px">Total Volume</div>
      </div>
      <div style="text-align:center;border-left:1px solid var(--border)">
        <div style="font-size:1.6rem;font-weight:800;color:#d97706"><?= number_format($wStats['new_this_week'] ?? 0) ?></div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px">New This Week</div>
      </div>
    </div>
  </div>
</div>

<!-- Alert Row -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px">
  <a href="<?= APP_URL ?>/admin/kyc" class="card" style="text-decoration:none;padding:14px 18px;display:flex;align-items:center;gap:12px;border-left:4px solid <?= $stats['kyc_pending'] > 0 ? '#d97706' : 'var(--border)' ?>">
    <i class="fas fa-id-card" style="font-size:1.3rem;color:<?= $stats['kyc_pending'] > 0 ? '#d97706' : 'var(--text-light)' ?>"></i>
    <div>
      <div style="font-weight:700;font-size:1.1rem;color:var(--navy)"><?= $stats['kyc_pending'] ?></div>
      <div style="font-size:.75rem;color:var(--text-muted)">KYC Pending</div>
    </div>
  </a>
  <a href="<?= APP_URL ?>/admin/withdrawals" class="card" style="text-decoration:none;padding:14px 18px;display:flex;align-items:center;gap:12px;border-left:4px solid <?= $stats['pending_withdrawals'] > 0 ? 'var(--green)' : 'var(--border)' ?>">
    <i class="fas fa-money-bill-wave" style="font-size:1.3rem;color:<?= $stats['pending_withdrawals'] > 0 ? 'var(--green)' : 'var(--text-light)' ?>"></i>
    <div>
      <div style="font-weight:700;font-size:1.1rem;color:var(--navy)"><?= $stats['pending_withdrawals'] ?></div>
      <div style="font-size:.75rem;color:var(--text-muted)">Pending Withdrawals</div>
    </div>
  </a>
  <a href="<?= APP_URL ?>/admin/transactions?status=pending" class="card" style="text-decoration:none;padding:14px 18px;display:flex;align-items:center;gap:12px;border-left:4px solid <?= $stats['pending_txns'] > 0 ? '#2563eb' : 'var(--border)' ?>">
    <i class="fas fa-clock" style="font-size:1.3rem;color:<?= $stats['pending_txns'] > 0 ? '#2563eb' : 'var(--text-light)' ?>"></i>
    <div>
      <div style="font-weight:700;font-size:1.1rem;color:var(--navy)"><?= $stats['pending_txns'] ?></div>
      <div style="font-size:.75rem;color:var(--text-muted)">Pending Transactions</div>
    </div>
  </a>
  <a href="<?= APP_URL ?>/admin/transactions?status=failed" class="card" style="text-decoration:none;padding:14px 18px;display:flex;align-items:center;gap:12px;border-left:4px solid <?= $stats['failed_today'] > 0 ? 'var(--danger)' : 'var(--border)' ?>">
    <i class="fas fa-times-circle" style="font-size:1.3rem;color:<?= $stats['failed_today'] > 0 ? 'var(--danger)' : 'var(--text-light)' ?>"></i>
    <div>
      <div style="font-weight:700;font-size:1.1rem;color:var(--navy)"><?= $stats['failed_today'] ?></div>
      <div style="font-size:.75rem;color:var(--text-muted)">Failed Today</div>
    </div>
  </a>
</div>

<!-- Charts Row -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:24px">
  <!-- Revenue Chart -->
  <div class="card">
    <div class="card-header">
      <h4><i class="fas fa-chart-area" style="color:var(--green);margin-right:6px"></i> Transaction Volume (14 Days)</h4>
    </div>
    <div class="card-body">
      <div class="chart-wrap">
        <canvas id="volumeChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Channel Breakdown -->
  <div class="card">
    <div class="card-header">
      <h4><i class="fas fa-chart-pie" style="color:var(--green);margin-right:6px"></i> By Channel</h4>
    </div>
    <div class="card-body" style="padding:16px">
      <?php if (empty($byChannel)): ?>
        <div class="empty-state" style="padding:30px 0"><i class="fas fa-chart-pie"></i><h4>No data yet</h4></div>
      <?php else:
        $totalVol = array_sum(array_column($byChannel, 'volume'));
        $channelColors = ['mpesa'=>'#158347','card'=>'#0D1B3E','wallet'=>'#2563eb','payment_link'=>'#d97706','bank'=>'#6b7280'];
        foreach ($byChannel as $ch):
          $pct = $totalVol > 0 ? round($ch['volume'] / $totalVol * 100) : 0;
          $icons = ['mpesa'=>'mobile-alt','card'=>'credit-card','wallet'=>'wallet','payment_link'=>'link','bank'=>'university'];
          $col = $channelColors[$ch['channel']] ?? '#888';
      ?>
        <div style="margin-bottom:14px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
            <div style="display:flex;align-items:center;gap:7px;font-size:.84rem;font-weight:600">
              <i class="fas fa-<?= $icons[$ch['channel']] ?? 'circle' ?>" style="color:<?= $col ?>;width:14px"></i>
              <?= ucfirst(str_replace('_',' ', $ch['channel'])) ?>
            </div>
            <div style="text-align:right">
              <span style="font-weight:700;font-size:.84rem"><?= format_amount($ch['volume']) ?></span>
              <span style="font-size:.72rem;color:var(--text-muted);margin-left:5px"><?= $pct ?>%</span>
            </div>
          </div>
          <div class="progress-bar">
            <div class="progress-fill" style="width:<?= $pct ?>%;background:<?= $col ?>"></div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<!-- Bottom Tables -->
<div style="display:grid;grid-template-columns:3fr 2fr;gap:20px">
  <!-- Recent Transactions -->
  <div class="card">
    <div class="card-header">
      <h4><i class="fas fa-exchange-alt" style="color:var(--green);margin-right:6px"></i> Recent Transactions</h4>
      <a href="<?= APP_URL ?>/admin/transactions" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <div class="p-0">
      <?php if (empty($recentTxn)): ?>
        <div class="empty-state"><i class="fas fa-inbox"></i><h4>No transactions yet</h4></div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="orb-table">
            <thead>
              <tr><th>Merchant</th><th>Reference</th><th>Channel</th><th>Amount</th><th>Status</th><th>Time</th></tr>
            </thead>
            <tbody>
              <?php foreach ($recentTxn as $t): ?>
              <tr class="merchant-row">
                <td style="font-weight:600;font-size:.84rem"><?= sanitize($t['business_name']) ?></td>
                <td><code style="font-size:.77rem"><?= sanitize($t['reference']) ?></code></td>
                <td>
                  <?php $icons=['mpesa'=>'mobile-alt','card'=>'credit-card','wallet'=>'wallet','payment_link'=>'link','bank'=>'university']; ?>
                  <span class="chip <?= in_array($t['channel'],['mpesa','wallet','payment_link'])?'green':'navy' ?>">
                    <i class="fas fa-<?= $icons[$t['channel']] ?? 'exchange-alt' ?>"></i>
                    <?= ucfirst(str_replace('_',' ',$t['channel'])) ?>
                  </span>
                </td>
                <td style="font-weight:700"><?= format_amount($t['amount'],$t['currency']) ?></td>
                <td><?= transaction_status_badge($t['status']) ?></td>
                <td style="font-size:.78rem;color:var(--text-muted)"><?= time_ago($t['created_at']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- New Merchants -->
  <div class="card">
    <div class="card-header">
      <h4><i class="fas fa-user-plus" style="color:var(--green);margin-right:6px"></i> New Merchants</h4>
      <a href="<?= APP_URL ?>/admin/merchants" class="btn btn-ghost btn-sm">All</a>
    </div>
    <div class="p-0">
      <?php if (empty($recentMer)): ?>
        <div class="empty-state"><i class="fas fa-store"></i><h4>No merchants yet</h4></div>
      <?php else: ?>
        <?php foreach ($recentMer as $m): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid var(--border)">
          <div style="width:36px;height:36px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.875rem;color:#fff;flex-shrink:0">
            <?= strtoupper(substr($m['business_name'],0,1)) ?>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              <?= sanitize($m['business_name']) ?>
            </div>
            <div style="font-size:.75rem;color:var(--text-muted)"><?= sanitize($m['email']) ?></div>
          </div>
          <div style="text-align:right;flex-shrink:0">
            <span class="kyc-badge kyc-<?= $m['kyc_status'] ?>"><?= ucfirst($m['kyc_status']) ?></span>
            <div style="font-size:.72rem;color:var(--text-muted);margin-top:3px"><?= time_ago($m['created_at']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
$labels  = array_column($byDay, 'date');
$volumes = array_column($byDay, 'volume');
$labelsJson  = json_encode(array_map(fn($d) => date('d M', strtotime($d)), $labels));
$volumesJson = json_encode(array_map('floatval', $volumes));
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
  const ctx = document.getElementById('volumeChart');
  if (!ctx) return;
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= $labelsJson ?>,
      datasets: [{
        label: 'Volume (KES)',
        data: <?= $volumesJson ?>,
        backgroundColor: 'rgba(21,131,71,.18)',
        borderColor: '#158347',
        borderWidth: 2,
        borderRadius: 4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => 'KES ' + parseFloat(ctx.raw).toLocaleString('en-KE', { minimumFractionDigits: 2 })
          }
        }
      },
      scales: {
        y: {
          grid: { color: '#f0f0f0' },
          ticks: { callback: v => 'KES ' + (v >= 1000 ? (v/1000).toFixed(0) + 'K' : v) }
        },
        x: { grid: { display: false } }
      }
    }
  });
})();
</script>
