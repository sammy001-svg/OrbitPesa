<?php
/**
 * OrbitPesa Wallet — Account Statement
 * Standalone page (no wallet layout) — optimised for print
 * Route: GET /wallet/statement?from=YYYY-MM-DD&to=YYYY-MM-DD
 */
if (empty($_SESSION['wallet_uid'])) {
    header('Location: ' . APP_URL . '/wallet/login');
    exit;
}

$walletUser = WalletUser::find($_SESSION['wallet_uid']);
if (!$walletUser || $walletUser['status'] === 'suspended') {
    unset($_SESSION['wallet_uid'], $_SESSION['wallet_user']);
    header('Location: ' . APP_URL . '/wallet/login');
    exit;
}

$defaultFrom = date('Y-m-d', strtotime('-30 days'));
$defaultTo   = date('Y-m-d');
$from = $_GET['from'] ?? $defaultFrom;
$to   = $_GET['to']   ?? $defaultTo;

// Sanitise dates
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = $defaultFrom;
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = $defaultTo;

// Ensure $to covers the full day
$toFull = $to . ' 23:59:59';

$txns = DB::fetchAll(
    "SELECT * FROM wallet_transactions
     WHERE wallet_user_id = ? AND created_at >= ? AND created_at <= ?
     ORDER BY created_at ASC",
    [$walletUser['id'], $from . ' 00:00:00', $toFull]
);

// Compute summary
$totalIn  = 0;
$totalOut = 0;
$fees     = 0;
foreach ($txns as $t) {
    $isCredit = WalletTransaction::isCredit($t['type']);
    if ($isCredit) {
        $totalIn += (float)$t['amount'];
    } else {
        $totalOut += (float)$t['amount'];
        $fees     += (float)$t['fee'];
    }
}

// Opening balance: balance before first txn in range
$openingRow = DB::fetch(
    "SELECT balance_before FROM wallet_transactions
     WHERE wallet_user_id = ? AND created_at >= ?
     ORDER BY created_at ASC LIMIT 1",
    [$walletUser['id'], $from . ' 00:00:00']
);
$openingBal = $openingRow ? (float)$openingRow['balance_before'] : (float)$walletUser['balance'];
$closingBal = !empty($txns) ? (float)end($txns)['balance_after'] : (float)$walletUser['balance'];

$generatedAt = date('d M Y H:i');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Statement — OrbitPesa Wallet</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --green:  #158347;
      --navy:   #0D1B3E;
      --red:    #dc2626;
      --muted:  #64748b;
      --border: #e2e8f0;
      --bg:     #f8fafc;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: #0f172a;
      font-size: 13px;
      line-height: 1.5;
    }
    .page-wrapper { max-width: 780px; margin: 0 auto; padding: 0 16px 40px; }

    /* ── Print toolbar (hidden in print) ── */
    .print-bar {
      background: var(--navy);
      color: #fff;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 24px;
      border-radius: 0 0 16px 16px;
    }
    .print-bar-title { font-weight: 700; font-size: .9rem; }
    .print-bar-right { display: flex; gap: 10px; align-items: center; }
    .btn-print {
      background: var(--green);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 9px 20px;
      font-size: .82rem;
      font-weight: 700;
      cursor: pointer;
      font-family: inherit;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: background .2s;
    }
    .btn-print:hover { background: #0f6b38; }
    .btn-download {
      background: rgba(255,255,255,.12);
      color: #fff;
      border: 1px solid rgba(255,255,255,.25);
      border-radius: 10px;
      padding: 9px 16px;
      font-size: .82rem;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit;
      display: flex;
      align-items: center;
      gap: 6px;
      text-decoration: none;
    }
    .btn-back {
      color: rgba(255,255,255,.7);
      text-decoration: none;
      font-size: .82rem;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .btn-back:hover { color: #fff; }

    /* ── Date range picker bar ── */
    .filter-bar {
      background: #fff;
      border-radius: 16px;
      padding: 14px 18px;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,.05);
    }
    .filter-bar label { font-weight: 600; color: var(--muted); font-size: .78rem; }
    .filter-bar input[type=date] {
      border: 1.5px solid var(--border);
      border-radius: 9px;
      padding: 7px 11px;
      font-size: .82rem;
      font-family: inherit;
      color: #0f172a;
      outline: none;
    }
    .filter-bar input[type=date]:focus { border-color: var(--green); }
    .btn-filter {
      background: var(--navy);
      color: #fff;
      border: none;
      border-radius: 9px;
      padding: 8px 18px;
      font-size: .82rem;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit;
    }

    /* ── Statement document ── */
    .statement-doc {
      background: #fff;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 4px 24px rgba(0,0,0,.07);
    }

    /* Header band */
    .stmt-header {
      background: linear-gradient(135deg, var(--navy) 0%, #1a3a70 100%);
      color: #fff;
      padding: 28px 28px 22px;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      flex-wrap: wrap;
      gap: 16px;
    }
    .stmt-logo { font-size: 1.4rem; font-weight: 800; letter-spacing: -.5px; }
    .stmt-logo span { color: #4ade80; }
    .stmt-title { font-size: .65rem; text-transform: uppercase; letter-spacing: .12em; color: rgba(255,255,255,.6); margin-top: 3px; }
    .stmt-period { text-align: right; }
    .stmt-period-label { font-size: .65rem; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.55); }
    .stmt-period-val { font-size: .9rem; font-weight: 700; color: #fff; margin-top: 2px; }

    /* Account info */
    .stmt-acct {
      padding: 20px 28px;
      border-bottom: 1px solid var(--border);
      display: flex;
      flex-wrap: wrap;
      gap: 20px 40px;
    }
    .stmt-acct-item {}
    .stmt-acct-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); }
    .stmt-acct-val { font-size: .88rem; font-weight: 700; color: var(--navy); margin-top: 3px; }
    .stmt-acct-val.mono { font-family: 'Courier New', monospace; letter-spacing: .06em; }

    /* Summary boxes */
    .stmt-summary {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      border-bottom: 1px solid var(--border);
    }
    .stmt-stat {
      padding: 16px 20px;
      border-right: 1px solid var(--border);
    }
    .stmt-stat:last-child { border-right: none; }
    .stmt-stat-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); }
    .stmt-stat-val { font-size: 1.05rem; font-weight: 800; margin-top: 4px; color: var(--navy); }
    .stmt-stat-val.green { color: var(--green); }
    .stmt-stat-val.red   { color: var(--red); }

    /* Transactions table */
    .stmt-table-wrap { padding: 0; }
    .stmt-table {
      width: 100%;
      border-collapse: collapse;
      font-size: .8rem;
    }
    .stmt-table thead th {
      background: #f8fafc;
      padding: 11px 14px;
      text-align: left;
      font-weight: 700;
      font-size: .68rem;
      text-transform: uppercase;
      letter-spacing: .07em;
      color: var(--muted);
      border-bottom: 1px solid var(--border);
      white-space: nowrap;
    }
    .stmt-table thead th:last-child { text-align: right; }
    .stmt-table tbody tr { border-bottom: 1px solid #f1f5f9; }
    .stmt-table tbody tr:last-child { border-bottom: none; }
    .stmt-table tbody tr:hover { background: #fafcff; }
    .stmt-table td { padding: 10px 14px; vertical-align: middle; }
    .stmt-table td:last-child { text-align: right; font-weight: 700; }
    .txn-type-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: #f1f5f9;
      border-radius: 6px;
      padding: 2px 8px;
      font-size: .68rem;
      font-weight: 600;
      color: var(--muted);
    }
    .credit-row td:last-child { color: var(--green); }
    .debit-row  td:last-child { color: var(--red); }
    .bal-col { font-family: 'Courier New', monospace; font-size: .78rem; color: var(--muted); white-space: nowrap; }
    .ref-col { font-family: 'Courier New', monospace; font-size: .7rem; color: var(--muted); }
    .desc-col { color: #334155; max-width: 180px; }
    .date-col { white-space: nowrap; color: var(--muted); font-size: .75rem; }

    .empty-state {
      text-align: center;
      padding: 50px 20px;
      color: var(--muted);
    }
    .empty-state i { font-size: 2.5rem; margin-bottom: 12px; display: block; color: #cbd5e1; }

    /* Footer */
    .stmt-footer {
      padding: 18px 28px;
      border-top: 1px solid var(--border);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 8px;
      background: #fafcff;
    }
    .stmt-footer-note { font-size: .72rem; color: var(--muted); }
    .stmt-footer-brand { font-size: .75rem; font-weight: 700; color: var(--navy); }

    /* ── Print media ── */
    @media print {
      body { background: #fff; }
      .print-bar, .filter-bar { display: none !important; }
      .page-wrapper { padding: 0; max-width: 100%; }
      .statement-doc { box-shadow: none; border-radius: 0; }
      .stmt-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .stmt-stat, .stmt-table thead th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .stmt-table tbody tr:hover { background: transparent; }
      .credit-row td:last-child { color: #158347 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .debit-row  td:last-child { color: #dc2626 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }

    @media (max-width: 640px) {
      .stmt-summary { grid-template-columns: repeat(2, 1fr); }
      .stmt-table thead th:nth-child(4),
      .stmt-table td:nth-child(4) { display: none; }
      .print-bar { border-radius: 0; }
    }
  </style>
</head>
<body>

<!-- Print / nav toolbar -->
<div class="print-bar">
  <div>
    <a href="<?= APP_URL ?>/wallet/transactions" class="btn-back">
      <i class="fas fa-arrow-left"></i> Back to History
    </a>
  </div>
  <div class="print-bar-right">
    <a href="<?= APP_URL ?>/wallet/transactions/export?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>"
       class="btn-download">
      <i class="fas fa-file-csv"></i> Download CSV
    </a>
    <button class="btn-print" onclick="window.print()">
      <i class="fas fa-print"></i> Print / Save PDF
    </button>
  </div>
</div>

<div class="page-wrapper">

  <!-- Date range filter -->
  <form method="GET" action="" class="filter-bar">
    <label for="stmt-from">From</label>
    <input type="date" id="stmt-from" name="from" value="<?= htmlspecialchars($from) ?>" max="<?= date('Y-m-d') ?>">
    <label for="stmt-to">To</label>
    <input type="date" id="stmt-to"   name="to"   value="<?= htmlspecialchars($to) ?>"   max="<?= date('Y-m-d') ?>">
    <button type="submit" class="btn-filter">Generate</button>
  </form>

  <!-- Statement document -->
  <div class="statement-doc">

    <!-- Header -->
    <div class="stmt-header">
      <div>
        <div class="stmt-logo">Orbit<span>Pesa</span></div>
        <div class="stmt-title">Account Statement</div>
      </div>
      <div class="stmt-period">
        <div class="stmt-period-label">Period</div>
        <div class="stmt-period-val">
          <?= date('d M Y', strtotime($from)) ?> – <?= date('d M Y', strtotime($to)) ?>
        </div>
        <div class="stmt-period-label" style="margin-top:4px">Generated <?= $generatedAt ?></div>
      </div>
    </div>

    <!-- Account info -->
    <div class="stmt-acct">
      <div class="stmt-acct-item">
        <div class="stmt-acct-label">Account Name</div>
        <div class="stmt-acct-val"><?= htmlspecialchars($walletUser['full_name']) ?></div>
      </div>
      <div class="stmt-acct-item">
        <div class="stmt-acct-label">Wallet ID</div>
        <div class="stmt-acct-val mono"><?= htmlspecialchars($walletUser['wallet_id']) ?></div>
      </div>
      <div class="stmt-acct-item">
        <div class="stmt-acct-label">Phone</div>
        <div class="stmt-acct-val"><?= htmlspecialchars($walletUser['phone']) ?></div>
      </div>
      <div class="stmt-acct-item">
        <div class="stmt-acct-label">Current Balance</div>
        <div class="stmt-acct-val" style="color:#158347">KES <?= number_format((float)$walletUser['balance'], 2) ?></div>
      </div>
    </div>

    <!-- Summary boxes -->
    <div class="stmt-summary">
      <div class="stmt-stat">
        <div class="stmt-stat-label">Opening Balance</div>
        <div class="stmt-stat-val">KES <?= number_format($openingBal, 2) ?></div>
      </div>
      <div class="stmt-stat">
        <div class="stmt-stat-label">Total Money In</div>
        <div class="stmt-stat-val green">+KES <?= number_format($totalIn, 2) ?></div>
      </div>
      <div class="stmt-stat">
        <div class="stmt-stat-label">Total Money Out</div>
        <div class="stmt-stat-val red">-KES <?= number_format($totalOut, 2) ?></div>
      </div>
      <div class="stmt-stat">
        <div class="stmt-stat-label">Closing Balance</div>
        <div class="stmt-stat-val">KES <?= number_format($closingBal, 2) ?></div>
      </div>
    </div>

    <!-- Transactions -->
    <div class="stmt-table-wrap">
      <?php if (empty($txns)): ?>
      <div class="empty-state">
        <i class="fas fa-receipt"></i>
        <p>No transactions found for this period.</p>
        <p style="margin-top:6px;font-size:.8rem">Try adjusting the date range above.</p>
      </div>
      <?php else: ?>
      <table class="stmt-table">
        <thead>
          <tr>
            <th>Date & Time</th>
            <th>Description</th>
            <th>Reference</th>
            <th>Balance After</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($txns as $t):
          $isCredit = WalletTransaction::isCredit($t['type']);
          $label    = WalletTransaction::typeLabel($t['type']);
          $desc     = $t['description'] ?: ($t['counterparty_name'] ?: $label);
          $rowClass = $isCredit ? 'credit-row' : 'debit-row';
          $prefix   = $isCredit ? '+' : '-';
        ?>
          <tr class="<?= $rowClass ?>">
            <td class="date-col"><?= date('d M Y', strtotime($t['created_at'])) ?><br>
              <span style="font-size:.7rem"><?= date('H:i', strtotime($t['created_at'])) ?></span>
            </td>
            <td class="desc-col">
              <span class="txn-type-badge"><?= htmlspecialchars($label) ?></span>
              <div style="margin-top:3px;font-size:.78rem"><?= htmlspecialchars(mb_substr($desc, 0, 60)) ?></div>
            </td>
            <td class="ref-col"><?= htmlspecialchars($t['reference']) ?></td>
            <td class="bal-col">KES <?= number_format((float)$t['balance_after'], 2) ?></td>
            <td><?= $prefix ?>KES <?= number_format((float)$t['amount'], 2) ?>
              <?php if ((float)$t['fee'] > 0): ?>
              <div style="font-size:.7rem;font-weight:400;color:#94a3b8">Fee: KES <?= number_format((float)$t['fee'], 2) ?></div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="stmt-footer">
      <div class="stmt-footer-note">
        <?= count($txns) ?> transaction(s) &nbsp;·&nbsp; Fees: KES <?= number_format($fees, 2) ?>
        &nbsp;·&nbsp; This is an official account statement from OrbitPesa Ltd.
      </div>
      <div class="stmt-footer-brand">OrbitPesa Wallet</div>
    </div>

  </div><!-- /statement-doc -->
</div>

</body>
</html>
