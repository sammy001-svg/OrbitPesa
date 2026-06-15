<?php
$search  = trim($_GET['search'] ?? '');
$type    = $_GET['type'] ?? '';
$userId  = $_GET['user_id'] ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 30;
$offset  = ($page - 1) * $limit;

$where  = ['1=1'];
$params = [];

if ($search) {
    $where[]  = '(wt.reference LIKE ? OR wu.full_name LIKE ? OR wu.email LIKE ? OR wu.wallet_id LIKE ?)';
    $s = '%' . $search . '%';
    $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s;
}
if ($type) {
    $where[]  = 'wt.type = ?';
    $params[] = $type;
}
if ($userId) {
    $where[]  = 'wt.wallet_user_id = ?';
    $params[] = $userId;
}

$whereStr = implode(' AND ', $where);

$total = DB::fetch(
    "SELECT COUNT(*) as c FROM wallet_transactions wt
     JOIN wallet_users wu ON wu.id = wt.wallet_user_id
     WHERE $whereStr",
    $params
)['c'] ?? 0;

$txns = DB::fetchAll(
    "SELECT wt.*, wu.full_name, wu.wallet_id, wu.email
     FROM wallet_transactions wt
     JOIN wallet_users wu ON wu.id = wt.wallet_user_id
     WHERE $whereStr
     ORDER BY wt.created_at DESC LIMIT ? OFFSET ?",
    [...$params, $limit, $offset]
);

$pages  = (int)ceil($total / $limit);
$volRow = DB::fetch(
    "SELECT COALESCE(SUM(wt.amount),0) as vol, COALESCE(SUM(wt.fee),0) as fees, COUNT(*) as cnt
     FROM wallet_transactions wt
     JOIN wallet_users wu ON wu.id = wt.wallet_user_id
     WHERE $whereStr",
    $params
) ?? [];

$filterUser = $userId ? WalletUser::find($userId) : null;

$typeOptions = [
    'send'       => ['label' => 'Sent',       'icon' => 'paper-plane',     'color' => '#dc2626'],
    'receive'    => ['label' => 'Received',    'icon' => 'arrow-down',      'color' => '#158347'],
    'deposit'    => ['label' => 'Deposit',     'icon' => 'plus-circle',     'color' => '#2563eb'],
    'withdrawal' => ['label' => 'Withdrawal',  'icon' => 'minus-circle',    'color' => '#d97706'],
    'airtime'    => ['label' => 'Airtime',     'icon' => 'mobile-alt',      'color' => '#7c3aed'],
    'paybill'    => ['label' => 'Paybill',     'icon' => 'file-invoice',    'color' => '#0891b2'],
    'transfer'   => ['label' => 'Transfer',    'icon' => 'university',      'color' => '#64748b'],
    'cashback'   => ['label' => 'Cashback',    'icon' => 'gift',            'color' => '#158347'],
];
?>

<div class="section-hd">
  <div>
    <h2>Wallet Transactions</h2>
    <p>
      <?php if ($filterUser): ?>
        Showing transactions for <strong><?= sanitize($filterUser['full_name']) ?></strong>
        (<a href="<?= APP_URL ?>/admin/wallet-users/<?= urlencode($filterUser['id']) ?>">view profile</a>)
        — <a href="?<?= http_build_query(['search'=>$search,'type'=>$type]) ?>">clear filter</a>
      <?php else: ?>
        <?= number_format($total) ?> transaction<?= $total !== 1 ? 's' : '' ?> found
      <?php endif; ?>
    </p>
  </div>
  <a href="<?= APP_URL ?>/admin/wallet-users" class="btn btn-outline btn-sm">
    <i class="fas fa-users"></i> Wallet Users
  </a>
</div>

<!-- Summary Strip -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
  <div class="card" style="padding:14px 18px;display:flex;align-items:center;gap:12px;border-left:4px solid var(--navy)">
    <i class="fas fa-exchange-alt" style="font-size:1.3rem;color:var(--navy)"></i>
    <div>
      <div style="font-size:1.2rem;font-weight:800;color:var(--navy)"><?= number_format($volRow['cnt'] ?? 0) ?></div>
      <div style="font-size:.73rem;color:var(--text-muted)">Transactions</div>
    </div>
  </div>
  <div class="card" style="padding:14px 18px;display:flex;align-items:center;gap:12px;border-left:4px solid var(--green)">
    <i class="fas fa-coins" style="font-size:1.3rem;color:var(--green)"></i>
    <div>
      <div style="font-size:1.2rem;font-weight:800;color:var(--navy)"><?= format_amount($volRow['vol'] ?? 0) ?></div>
      <div style="font-size:.73rem;color:var(--text-muted)">Total Volume</div>
    </div>
  </div>
  <div class="card" style="padding:14px 18px;display:flex;align-items:center;gap:12px;border-left:4px solid #d97706">
    <i class="fas fa-percentage" style="font-size:1.3rem;color:#d97706"></i>
    <div>
      <div style="font-size:1.2rem;font-weight:800;color:var(--navy)"><?= format_amount($volRow['fees'] ?? 0) ?></div>
      <div style="font-size:.73rem;color:var(--text-muted)">Fees Collected</div>
    </div>
  </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:20px">
  <div class="card-body" style="padding:14px 18px">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
      <?php if ($userId): ?>
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId) ?>">
      <?php endif; ?>
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control"
             style="max-width:280px" placeholder="Reference, name, email, wallet ID…">
      <select name="type" class="form-control" style="max-width:180px">
        <option value="">All Types</option>
        <?php foreach ($typeOptions as $val => $opt): ?>
          <option value="<?= $val ?>" <?= $type === $val ? 'selected' : '' ?>><?= $opt['label'] ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
      <?php if ($search || $type): ?>
        <a href="?<?= $userId ? 'user_id=' . urlencode($userId) : '' ?>" class="btn btn-ghost btn-sm">Clear</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- Type Filter Chips -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
  <a href="?<?= http_build_query(['search'=>$search,'user_id'=>$userId]) ?>"
     class="chip <?= !$type ? 'green' : '' ?>" style="text-decoration:none;cursor:pointer">All</a>
  <?php foreach ($typeOptions as $val => $opt): ?>
    <a href="?<?= http_build_query(['search'=>$search,'user_id'=>$userId,'type'=>$val]) ?>"
       class="chip" style="text-decoration:none;cursor:pointer;background:<?= $type===$val ? $opt['color'] : $opt['color'].'1a' ?>;color:<?= $type===$val ? '#fff' : $opt['color'] ?>;border:1px solid <?= $opt['color'] ?>33">
      <i class="fas fa-<?= $opt['icon'] ?>"></i> <?= $opt['label'] ?>
    </a>
  <?php endforeach; ?>
</div>

<!-- Transactions Table -->
<div class="card">
  <?php if (empty($txns)): ?>
    <div class="empty-state" style="padding:60px 0">
      <i class="fas fa-paper-plane"></i>
      <h4>No transactions found</h4>
      <p style="color:var(--text-muted)">Try adjusting your search or filters</p>
    </div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="orb-table">
      <thead>
        <tr>
          <th>User</th>
          <th>Reference</th>
          <th>Type</th>
          <th>Amount</th>
          <th>Fee</th>
          <th>Balance After</th>
          <th>Counterparty</th>
          <th>Time</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($txns as $t):
          $isCredit  = WalletTransaction::isCredit($t['type']);
          $typeLabel = WalletTransaction::typeLabel($t['type']);
          $typeIcon  = WalletTransaction::typeIcon($t['type']);
          $typeColor = WalletTransaction::typeColor($t['type']);
        ?>
        <tr>
          <td>
            <a href="<?= APP_URL ?>/admin/wallet-users/<?= urlencode($t['wallet_user_id']) ?>"
               style="text-decoration:none;color:var(--navy);font-weight:600;font-size:.84rem">
              <?= sanitize($t['full_name']) ?>
            </a>
            <div style="font-size:.73rem;color:var(--text-muted)"><?= sanitize($t['wallet_id']) ?></div>
          </td>
          <td><code style="font-size:.76rem;background:#f4f6f8;padding:2px 6px;border-radius:4px"><?= sanitize($t['reference']) ?></code></td>
          <td>
            <span class="chip" style="background:<?= $typeColor ?>1a;color:<?= $typeColor ?>;border:1px solid <?= $typeColor ?>33;white-space:nowrap">
              <i class="fas fa-<?= $typeIcon ?>"></i> <?= $typeLabel ?>
            </span>
          </td>
          <td style="font-weight:700;color:<?= $isCredit ? 'var(--green)' : '#dc2626' ?>;white-space:nowrap">
            <?= $isCredit ? '+' : '-' ?><?= format_amount($t['amount']) ?>
          </td>
          <td style="font-size:.82rem;color:var(--text-muted)"><?= $t['fee'] > 0 ? format_amount($t['fee']) : '—' ?></td>
          <td style="font-weight:600;color:var(--navy);white-space:nowrap"><?= format_amount($t['balance_after']) ?></td>
          <td style="font-size:.8rem;color:var(--text-muted);max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <?= sanitize($t['counterparty_name'] ?? $t['counterparty'] ?? '—') ?>
          </td>
          <td style="font-size:.78rem;color:var(--text-muted);white-space:nowrap"><?= time_ago($t['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-top:1px solid var(--border)">
    <div style="font-size:.82rem;color:var(--text-muted)">
      Showing <?= $offset + 1 ?>–<?= min($offset + $limit, $total) ?> of <?= number_format($total) ?>
    </div>
    <div style="display:flex;gap:6px">
      <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(['search'=>$search,'type'=>$type,'user_id'=>$userId,'page'=>$page-1]) ?>" class="btn btn-ghost btn-sm">← Prev</a>
      <?php endif; ?>
      <?php for ($p = max(1,$page-2); $p <= min($pages,$page+2); $p++): ?>
        <a href="?<?= http_build_query(['search'=>$search,'type'=>$type,'user_id'=>$userId,'page'=>$p]) ?>"
           class="btn btn-sm <?= $p===$page ? 'btn-primary' : 'btn-ghost' ?>"><?= $p ?></a>
      <?php endfor; ?>
      <?php if ($page < $pages): ?>
        <a href="?<?= http_build_query(['search'=>$search,'type'=>$type,'user_id'=>$userId,'page'=>$page+1]) ?>" class="btn btn-ghost btn-sm">Next →</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>
