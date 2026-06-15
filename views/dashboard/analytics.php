<?php
// Date range defaults — last 30 days
$dateTo   = $_GET['date_to']   ?? date('Y-m-d');
$dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-29 days'));
$channel  = $_GET['channel']   ?? '';

$userId = auth_user()['id'];
$data   = Transaction::getAnalytics($userId, $dateFrom, $dateTo);

$summary   = $data['summary'];
$byDay     = $data['byDay'];
$byChannel = $data['byChannel'];
$byStatus  = $data['byStatus'];
$topDays   = $data['topDays'];

$total    = (int)$summary['total'];
$completed= (int)$summary['completed'];
$successRate = $total > 0 ? round($completed / $total * 100, 1) : 0;
$avgTxn   = $completed > 0 ? (float)$summary['volume'] / $completed : 0;

// Preset ranges
$presets = [
    '7d'  => ['label' => 'Last 7 days',  'from' => date('Y-m-d', strtotime('-6 days')),  'to' => date('Y-m-d')],
    '30d' => ['label' => 'Last 30 days', 'from' => date('Y-m-d', strtotime('-29 days')), 'to' => date('Y-m-d')],
    '90d' => ['label' => 'Last 90 days', 'from' => date('Y-m-d', strtotime('-89 days')), 'to' => date('Y-m-d')],
    'mtd' => ['label' => 'This month',   'from' => date('Y-m-01'),                        'to' => date('Y-m-d')],
    'ytd' => ['label' => 'This year',    'from' => date('Y-01-01'),                        'to' => date('Y-m-d')],
];

// Identify active preset
$activePreset = null;
foreach ($presets as $key => $p) {
    if ($p['from'] === $dateFrom && $p['to'] === $dateTo) { $activePreset = $key; break; }
}

// Build chart data
$dayLabels  = [];
$dayVolumes = [];
$allDays    = [];
$dt = new DateTime($dateFrom);
$end = new DateTime($dateTo);
while ($dt <= $end) {
    $allDays[$dt->format('Y-m-d')] = 0;
    $dt->modify('+1 day');
}
foreach ($byDay as $row) $allDays[$row['day']] = (float)$row['volume'];
foreach ($allDays as $d => $v) { $dayLabels[] = date('d M', strtotime($d)); $dayVolumes[] = $v; }

$channelColors = [
    'mpesa'        => '#158347',
    'card'         => '#0D1B3E',
    'wallet'       => '#059669',
    'payment_link' => '#2563eb',
    'bank'         => '#7c3aed',
];
$channelLabels = []; $channelVols = []; $channelBgs = [];
foreach ($byChannel as $row) {
    $channelLabels[] = ucwords(str_replace('_', ' ', $row['channel']));
    $channelVols[]   = (float)$row['volume'];
    $channelBgs[]    = $channelColors[$row['channel']] ?? '#94a3b8';
}

$statusMap = [];
foreach ($byStatus as $row) $statusMap[$row['status']] = (int)$row['count'];
?>

<div class="section-hd">
  <div>
    <h2>Analytics & Reports</h2>
    <p><?= date('d M Y', strtotime($dateFrom)) ?> — <?= date('d M Y', strtotime($dateTo)) ?></p>
  </div>
  <a href="<?= APP_URL ?>/dashboard/analytics/export?date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>&channel=<?= urlencode($channel) ?>"
     class="btn btn-outline btn-sm">
    <i class="fas fa-download"></i> Export CSV
  </a>
</div>

<!-- Date Range Controls -->
<div class="card" style="margin-bottom:20px">
  <div class="card-body" style="padding:12px 18px">
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
      <span style="font-size:.78rem;color:var(--text-muted);font-weight:600;margin-right:4px">Range:</span>
      <?php foreach ($presets as $key => $p): ?>
      <a href="?date_from=<?= $p['from'] ?>&date_to=<?= $p['to'] ?>"
         class="btn btn-sm <?= $activePreset === $key ? 'btn-primary' : 'btn-ghost' ?>"
         style="padding:4px 12px;font-size:.78rem">
        <?= $p['label'] ?>
      </a>
      <?php endforeach; ?>
      <span style="width:1px;height:20px;background:var(--border);margin:0 4px"></span>
      <form method="GET" style="display:flex;gap:8px;align-items:center">
        <input type="date" name="date_from" class="form-control" value="<?= $dateFrom ?>" style="width:140px;font-size:.8rem;padding:5px 8px">
        <span style="color:var(--text-muted);font-size:.8rem">to</span>
        <input type="date" name="date_to" class="form-control" value="<?= $dateTo ?>" style="width:140px;font-size:.8rem;padding:5px 8px">
        <button type="submit" class="btn btn-primary btn-sm" style="padding:5px 12px"><i class="fas fa-search"></i></button>
      </form>
    </div>
  </div>
</div>

<!-- KPI Cards -->
<div class="stat-cards" style="margin-bottom:24px">
  <div class="stat-card">
    <div class="stat-card-icon" style="background:var(--green-light);color:var(--green)"><i class="fas fa-coins"></i></div>
    <div class="stat-card-body">
      <div class="stat-card-val"><?= format_amount($summary['volume']) ?></div>
      <div class="stat-card-lbl">Total Volume</div>
      <div class="stat-card-sub">Net: <?= format_amount($summary['net']) ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:var(--navy-lighter);color:var(--navy)"><i class="fas fa-exchange-alt"></i></div>
    <div class="stat-card-body">
      <div class="stat-card-val"><?= number_format($total) ?></div>
      <div class="stat-card-lbl">Transactions</div>
      <div class="stat-card-sub"><?= $completed ?> completed</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#dcfce7;color:#16a34a"><i class="fas fa-check-circle"></i></div>
    <div class="stat-card-body">
      <div class="stat-card-val"><?= $successRate ?>%</div>
      <div class="stat-card-lbl">Success Rate</div>
      <div class="stat-card-sub"><?= $statusMap['failed'] ?? 0 ?> failed</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:#eff6ff;color:#2563eb"><i class="fas fa-chart-bar"></i></div>
    <div class="stat-card-body">
      <div class="stat-card-val"><?= format_amount($avgTxn) ?></div>
      <div class="stat-card-lbl">Avg Transaction</div>
      <div class="stat-card-sub">Fees: <?= format_amount($summary['fees']) ?></div>
    </div>
  </div>
</div>

<!-- Volume Chart -->
<div class="card" style="margin-bottom:20px">
  <div class="card-header">
    <h4><i class="fas fa-chart-area" style="color:var(--green);margin-right:6px"></i> Volume Over Time</h4>
    <span style="font-size:.78rem;color:var(--text-muted)"><?= count($allDays) ?> days</span>
  </div>
  <div class="card-body">
    <div style="position:relative;height:260px">
      <canvas id="volumeChart"></canvas>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

  <!-- Channel Breakdown -->
  <div class="card">
    <div class="card-header"><h4><i class="fas fa-chart-pie" style="color:var(--green);margin-right:6px"></i> By Channel</h4></div>
    <div class="card-body">
      <?php if (empty($byChannel)): ?>
        <div class="empty-state" style="padding:20px"><p>No data</p></div>
      <?php else: ?>
        <div style="position:relative;height:200px;margin-bottom:20px">
          <canvas id="channelChart"></canvas>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
          <?php
          $totalVol = array_sum(array_column($byChannel, 'volume')) ?: 1;
          foreach ($byChannel as $row):
            $pct = round($row['volume'] / $totalVol * 100, 1);
            $color = $channelColors[$row['channel']] ?? '#94a3b8';
          ?>
          <div style="display:flex;align-items:center;gap:8px">
            <div style="width:10px;height:10px;border-radius:2px;background:<?= $color ?>;flex-shrink:0"></div>
            <span style="font-size:.82rem;flex:1"><?= ucwords(str_replace('_',' ',$row['channel'])) ?></span>
            <span style="font-size:.8rem;color:var(--text-muted)"><?= $row['count'] ?> txns</span>
            <span style="font-size:.82rem;font-weight:700;min-width:60px;text-align:right"><?= format_amount($row['volume']) ?></span>
            <span style="font-size:.72rem;color:var(--text-muted);min-width:36px;text-align:right"><?= $pct ?>%</span>
          </div>
          <div style="height:4px;background:var(--bg);border-radius:2px">
            <div style="height:4px;width:<?= $pct ?>%;background:<?= $color ?>;border-radius:2px;transition:width .4s"></div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Status + Top Days -->
  <div style="display:flex;flex-direction:column;gap:20px">
    <!-- Status Breakdown -->
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-chart-donut" style="color:var(--green);margin-right:6px"></i> By Status</h4></div>
      <div class="card-body">
        <?php
        $statuses = ['completed'=>['#158347','fas fa-check-circle'], 'pending'=>['#d97706','fas fa-clock'], 'failed'=>['#dc2626','fas fa-times-circle'], 'processing'=>['#2563eb','fas fa-spinner']];
        foreach ($statuses as $st => [$color, $icon]):
          $cnt = $statusMap[$st] ?? 0;
          if (!$cnt && $st !== 'completed') continue;
          $pct = $total > 0 ? round($cnt / $total * 100, 1) : 0;
        ?>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
          <i class="<?= $icon ?>" style="color:<?= $color ?>;width:14px"></i>
          <span style="font-size:.84rem;flex:1;text-transform:capitalize"><?= $st ?></span>
          <span style="font-size:.82rem;font-weight:700;color:var(--navy)"><?= number_format($cnt) ?></span>
          <span style="font-size:.74rem;color:var(--text-muted);min-width:36px;text-align:right"><?= $pct ?>%</span>
        </div>
        <div style="height:5px;background:var(--bg);border-radius:3px;margin-bottom:10px">
          <div style="height:5px;width:<?= $pct ?>%;background:<?= $color ?>;border-radius:3px"></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Top Days -->
    <div class="card" style="flex:1">
      <div class="card-header"><h4><i class="fas fa-trophy" style="color:var(--green);margin-right:6px"></i> Best Days</h4></div>
      <div class="card-body">
        <?php if (empty($topDays)): ?>
          <div class="empty-state" style="padding:16px"><p>No data</p></div>
        <?php else: ?>
          <?php foreach ($topDays as $i => $row): ?>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
            <div style="width:24px;height:24px;border-radius:50%;background:<?= $i===0?'var(--green)':($i===1?'#94a3b8':'#d97706') ?>;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;color:#fff;flex-shrink:0">
              <?= $i + 1 ?>
            </div>
            <span style="font-size:.84rem;flex:1"><?= date('d M Y', strtotime($row['day'])) ?></span>
            <span style="font-weight:700;font-size:.875rem;color:var(--green)"><?= format_amount($row['volume']) ?></span>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<!-- Channel Performance Table -->
<div class="card">
  <div class="card-header">
    <h4><i class="fas fa-table" style="color:var(--green);margin-right:6px"></i> Channel Performance</h4>
  </div>
  <div class="p-0">
    <?php if (empty($byChannel)): ?>
      <div class="empty-state"><p>No transactions in this period.</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="orb-table">
        <thead>
          <tr>
            <th>Channel</th>
            <th>Transactions</th>
            <th>Completed</th>
            <th>Success Rate</th>
            <th>Volume</th>
            <th>Share of Volume</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $grandVol = array_sum(array_column($byChannel, 'volume')) ?: 1;
          foreach ($byChannel as $row):
            $sr  = $row['count'] > 0 ? round($row['completed'] / $row['count'] * 100, 1) : 0;
            $sh  = round($row['volume'] / $grandVol * 100, 1);
            $col = $channelColors[$row['channel']] ?? '#94a3b8';
          ?>
          <tr>
            <td>
              <span class="chip <?= in_array($row['channel'],['mpesa','wallet'])?'green':'navy' ?>">
                <?= ucwords(str_replace('_',' ',$row['channel'])) ?>
              </span>
            </td>
            <td style="font-weight:600"><?= number_format($row['count']) ?></td>
            <td style="color:var(--success)"><?= number_format($row['completed']) ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="flex:1;height:6px;background:var(--bg);border-radius:3px;min-width:60px">
                  <div style="height:6px;width:<?= $sr ?>%;background:<?= $sr>80?'var(--success)':($sr>50?'#d97706':'var(--danger)') ?>;border-radius:3px"></div>
                </div>
                <span style="font-size:.82rem;font-weight:700"><?= $sr ?>%</span>
              </div>
            </td>
            <td style="font-weight:700"><?= format_amount($row['volume']) ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="flex:1;height:6px;background:var(--bg);border-radius:3px;min-width:60px">
                  <div style="height:6px;width:<?= $sh ?>%;background:<?= $col ?>;border-radius:3px"></div>
                </div>
                <span style="font-size:.82rem"><?= $sh ?>%</span>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#64748b';

// Volume line chart
new Chart(document.getElementById('volumeChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($dayLabels) ?>,
    datasets: [{
      label: 'Volume (KES)',
      data: <?= json_encode($dayVolumes) ?>,
      borderColor: '#158347',
      backgroundColor: 'rgba(21,131,71,.08)',
      borderWidth: 2.5,
      pointRadius: <?= count($dayLabels) > 30 ? 0 : 3 ?>,
      pointHoverRadius: 5,
      pointBackgroundColor: '#158347',
      fill: true,
      tension: 0.35,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => 'KES ' + ctx.parsed.y.toLocaleString('en-KE', {minimumFractionDigits:2})
        }
      }
    },
    scales: {
      x: { grid: { display: false }, ticks: { maxTicksLimit: 10, font: { size: 11 } } },
      y: {
        grid: { color: 'rgba(0,0,0,.05)' },
        ticks: {
          font: { size: 11 },
          callback: v => 'KES ' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v)
        }
      }
    }
  }
});

<?php if (!empty($channelLabels)): ?>
// Channel doughnut chart
new Chart(document.getElementById('channelChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($channelLabels) ?>,
    datasets: [{
      data: <?= json_encode($channelVols) ?>,
      backgroundColor: <?= json_encode($channelBgs) ?>,
      borderWidth: 2,
      borderColor: '#fff',
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '68%',
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => ' KES ' + ctx.parsed.toLocaleString('en-KE', {minimumFractionDigits:2})
        }
      }
    }
  }
});
<?php endif; ?>
</script>
