<?php
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 25;
$offset = ($page - 1) * $limit;

$filters = [];
if ($search) $filters['search'] = $search;
if ($status) $filters['status'] = $status;

$users = WalletUser::getAll($filters, $limit, $offset);
$total = WalletUser::countAll($filters);
$stats = WalletUser::stats();
$pages = (int)ceil($total / $limit);
?>

<div class="section-hd">
  <div>
    <h2>Wallet Users</h2>
    <p>Manage OrbitPesa consumer wallet accounts — <?= number_format($total) ?> user<?= $total !== 1 ? 's' : '' ?> found</p>
  </div>
  <a href="<?= APP_URL ?>/admin/wallet-transactions" class="btn btn-outline btn-sm">
    <i class="fas fa-paper-plane"></i> Wallet Transactions
  </a>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px">
  <div class="card" style="padding:16px 18px;display:flex;align-items:center;gap:12px;border-left:4px solid var(--green)">
    <i class="fas fa-users" style="font-size:1.4rem;color:var(--green)"></i>
    <div>
      <div style="font-size:1.3rem;font-weight:800;color:var(--navy)"><?= number_format($stats['total'] ?? 0) ?></div>
      <div style="font-size:.75rem;color:var(--text-muted)">Total Users</div>
    </div>
  </div>
  <div class="card" style="padding:16px 18px;display:flex;align-items:center;gap:12px;border-left:4px solid var(--green)">
    <i class="fas fa-user-check" style="font-size:1.4rem;color:var(--green)"></i>
    <div>
      <div style="font-size:1.3rem;font-weight:800;color:var(--navy)"><?= number_format($stats['active'] ?? 0) ?></div>
      <div style="font-size:.75rem;color:var(--text-muted)">Active</div>
    </div>
  </div>
  <div class="card" style="padding:16px 18px;display:flex;align-items:center;gap:12px;border-left:4px solid #d97706">
    <i class="fas fa-piggy-bank" style="font-size:1.4rem;color:#d97706"></i>
    <div>
      <div style="font-size:1.3rem;font-weight:800;color:var(--navy)"><?= format_amount($stats['total_balance'] ?? 0) ?></div>
      <div style="font-size:.75rem;color:var(--text-muted)">Total Balance</div>
    </div>
  </div>
  <div class="card" style="padding:16px 18px;display:flex;align-items:center;gap:12px;border-left:4px solid #2563eb">
    <i class="fas fa-user-plus" style="font-size:1.4rem;color:#2563eb"></i>
    <div>
      <div style="font-size:1.3rem;font-weight:800;color:var(--navy)"><?= number_format($stats['new_this_week'] ?? 0) ?></div>
      <div style="font-size:.75rem;color:var(--text-muted)">New This Week</div>
    </div>
  </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:20px">
  <div class="card-body" style="padding:14px 18px">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control"
             style="max-width:300px" placeholder="Search name, email, phone or wallet ID…">
      <select name="status" class="form-control" style="max-width:160px">
        <option value="">All Status</option>
        <option value="active"    <?= $status === 'active'    ? 'selected' : '' ?>>Active</option>
        <option value="suspended" <?= $status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
      </select>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Search</button>
      <?php if ($search || $status): ?>
      <a href="<?= APP_URL ?>/admin/wallet-users" class="btn btn-ghost btn-sm">Clear</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- Users Table -->
<div class="card">
  <?php if (empty($users)): ?>
    <div class="empty-state" style="padding:60px 0">
      <i class="fas fa-wallet"></i>
      <h4>No wallet users found</h4>
      <p style="color:var(--text-muted)">Try adjusting your search or filters</p>
    </div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="orb-table">
      <thead>
        <tr>
          <th>User</th>
          <th>Wallet ID</th>
          <th>Phone</th>
          <th>Balance</th>
          <th>Status</th>
          <th>Joined</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:34px;height:34px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.82rem;color:#fff;flex-shrink:0">
                <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
              </div>
              <div>
                <div style="font-weight:600;font-size:.875rem"><?= sanitize($u['full_name']) ?></div>
                <div style="font-size:.75rem;color:var(--text-muted)"><?= sanitize($u['email']) ?></div>
              </div>
            </div>
          </td>
          <td><code style="font-size:.8rem;background:#f4f6f8;padding:2px 7px;border-radius:4px"><?= sanitize($u['wallet_id']) ?></code></td>
          <td style="font-size:.84rem"><?= sanitize($u['phone']) ?></td>
          <td style="font-weight:700;color:var(--navy)"><?= format_amount($u['balance']) ?></td>
          <td>
            <?php if ($u['status'] === 'active'): ?>
              <span class="chip green"><i class="fas fa-circle" style="font-size:.5rem"></i> Active</span>
            <?php else: ?>
              <span class="chip" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca"><i class="fas fa-ban"></i> Suspended</span>
            <?php endif; ?>
          </td>
          <td style="font-size:.78rem;color:var(--text-muted)"><?= time_ago($u['created_at']) ?></td>
          <td style="text-align:right">
            <a href="<?= APP_URL ?>/admin/wallet-users/<?= urlencode($u['id']) ?>" class="btn btn-ghost btn-sm">
              <i class="fas fa-eye"></i> View
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-top:1px solid var(--border)">
    <div style="font-size:.82rem;color:var(--text-muted)">
      Showing <?= $offset + 1 ?>–<?= min($offset + $limit, $total) ?> of <?= number_format($total) ?> users
    </div>
    <div style="display:flex;gap:6px">
      <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(['search'=>$search,'status'=>$status,'page'=>$page-1]) ?>" class="btn btn-ghost btn-sm">← Prev</a>
      <?php endif; ?>
      <?php for ($p = max(1,$page-2); $p <= min($pages,$page+2); $p++): ?>
        <a href="?<?= http_build_query(['search'=>$search,'status'=>$status,'page'=>$p]) ?>"
           class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-ghost' ?>"><?= $p ?></a>
      <?php endfor; ?>
      <?php if ($page < $pages): ?>
        <a href="?<?= http_build_query(['search'=>$search,'status'=>$status,'page'=>$page+1]) ?>" class="btn btn-ghost btn-sm">Next →</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>
