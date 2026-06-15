<?php
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 20;
$offset  = ($page - 1) * $limit;
$search  = trim($_GET['q'] ?? '');
$status  = $_GET['status'] ?? '';
$kyc     = $_GET['kyc'] ?? '';

$where  = "WHERE 1=1";
$params = [];
if ($search) {
    $where   .= " AND (u.business_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $like     = "%$search%";
    $params   = array_merge($params, [$like, $like, $like]);
}
if ($status) { $where .= " AND u.status = ?";     $params[] = $status; }
if ($kyc)    { $where .= " AND u.kyc_status = ?"; $params[] = $kyc; }

$totalRow  = DB::fetch("SELECT COUNT(*) as c FROM users u $where", $params);
$total     = $totalRow['c'] ?? 0;
$pages     = (int)ceil($total / $limit);
$merchants = DB::fetchAll(
    "SELECT u.*, w.balance,
            (SELECT COUNT(*) FROM transactions t WHERE t.user_id = u.id AND t.status='completed') as txn_count,
            (SELECT COALESCE(SUM(amount),0) FROM transactions t WHERE t.user_id = u.id AND t.status='completed') as txn_vol,
            (SELECT COUNT(*) FROM api_keys k WHERE k.user_id = u.id AND k.is_active=1) as active_keys
     FROM users u LEFT JOIN wallets w ON w.user_id = u.id
     $where ORDER BY u.created_at DESC LIMIT ? OFFSET ?",
    array_merge($params, [$limit, $offset])
);
?>

<div class="section-hd">
  <div>
    <h2>Merchants</h2>
    <p><?= number_format($total) ?> registered merchant<?= $total !== 1 ? 's' : '' ?></p>
  </div>
  <a href="<?= APP_URL ?>/admin/merchants?export=csv" class="btn btn-outline btn-sm">
    <i class="fas fa-download"></i> Export CSV
  </a>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:20px">
  <div class="card-body" style="padding:14px 18px">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
      <div style="flex:1;min-width:200px">
        <label class="form-label" style="font-size:.78rem">Search</label>
        <input type="text" class="form-control" name="q" placeholder="Name, email or phone..." value="<?= sanitize($search) ?>">
      </div>
      <div>
        <label class="form-label" style="font-size:.78rem">Status</label>
        <select name="status" class="form-control form-select" style="min-width:130px">
          <option value="">All Statuses</option>
          <option value="active"    <?= $status === 'active'    ? 'selected':'' ?>>Active</option>
          <option value="suspended" <?= $status === 'suspended' ? 'selected':'' ?>>Suspended</option>
          <option value="pending"   <?= $status === 'pending'   ? 'selected':'' ?>>Pending</option>
        </select>
      </div>
      <div>
        <label class="form-label" style="font-size:.78rem">KYC</label>
        <select name="kyc" class="form-control form-select" style="min-width:130px">
          <option value="">All KYC</option>
          <option value="verified"   <?= $kyc === 'verified'   ? 'selected':'' ?>>Verified</option>
          <option value="pending"    <?= $kyc === 'pending'    ? 'selected':'' ?>>Pending</option>
          <option value="unverified" <?= $kyc === 'unverified' ? 'selected':'' ?>>Unverified</option>
        </select>
      </div>
      <div style="display:flex;gap:8px">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Search</button>
        <a href="<?= APP_URL ?>/admin/merchants" class="btn btn-ghost btn-sm">Clear</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="p-0">
    <?php if (empty($merchants)): ?>
      <div class="empty-state"><i class="fas fa-store"></i><h4>No merchants found</h4><p>Try adjusting your filters.</p></div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="orb-table">
          <thead>
            <tr>
              <th>Merchant</th>
              <th>Contact</th>
              <th>Account</th>
              <th>KYC</th>
              <th>Wallet</th>
              <th>Transactions</th>
              <th>Volume</th>
              <th>Joined</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($merchants as $m): ?>
            <tr class="merchant-row">
              <td>
                <div style="display:flex;align-items:center;gap:10px">
                  <div style="width:34px;height:34px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;color:#fff;flex-shrink:0">
                    <?= strtoupper(substr($m['business_name'],0,1)) ?>
                  </div>
                  <div>
                    <div style="font-weight:600;font-size:.875rem"><?= sanitize($m['business_name']) ?></div>
                    <div style="font-size:.75rem;color:var(--text-muted)"><?= ucfirst($m['account_type']) ?></div>
                  </div>
                </div>
              </td>
              <td>
                <div style="font-size:.83rem"><?= sanitize($m['email']) ?></div>
                <div style="font-size:.75rem;color:var(--text-muted)"><?= sanitize($m['phone']) ?></div>
              </td>
              <td>
                <span class="status-<?= $m['status'] ?>"><?= ucfirst($m['status']) ?></span>
              </td>
              <td>
                <span class="kyc-badge kyc-<?= $m['kyc_status'] ?>"><?= ucfirst($m['kyc_status']) ?></span>
              </td>
              <td style="font-weight:600;font-size:.84rem"><?= format_amount($m['balance'] ?? 0) ?></td>
              <td style="font-size:.84rem"><?= number_format($m['txn_count']) ?></td>
              <td style="font-weight:600;font-size:.84rem"><?= format_amount($m['txn_vol'] ?? 0) ?></td>
              <td style="font-size:.78rem;color:var(--text-muted)"><?= date('d M Y', strtotime($m['created_at'])) ?></td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-ghost btn-sm" data-toggle="dropdown" style="padding:5px 10px">
                    <i class="fas fa-ellipsis-v"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a href="<?= APP_URL ?>/admin/merchants/<?= urlencode($m['id']) ?>" class="dropdown-item">
                      <i class="fas fa-eye"></i> View Details
                    </a>
                    <?php if ($m['kyc_status'] === 'pending'): ?>
                    <a href="<?= APP_URL ?>/admin/merchants/kyc/<?= urlencode($m['id']) ?>" class="dropdown-item">
                      <i class="fas fa-id-card" style="color:var(--warning)"></i> Review KYC
                    </a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <?php if ($m['status'] === 'active'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/merchants/suspend">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= sanitize($m['id']) ?>">
                      <button type="submit" class="dropdown-item danger" style="width:100%;text-align:left;background:none;border:none;cursor:pointer"
                              data-confirm="Suspend <?= sanitize($m['business_name']) ?>? They will be unable to process payments.">
                        <i class="fas fa-ban"></i> Suspend
                      </button>
                    </form>
                    <?php else: ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/merchants/activate">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= sanitize($m['id']) ?>">
                      <button type="submit" class="dropdown-item" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;color:var(--success)">
                        <i class="fas fa-check-circle"></i> Activate
                      </button>
                    </form>
                    <?php endif; ?>
                    <?php if ($m['kyc_status'] !== 'verified'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/merchants/kyc-approve">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= sanitize($m['id']) ?>">
                      <button type="submit" class="dropdown-item" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;color:var(--green)">
                        <i class="fas fa-check"></i> Approve KYC
                      </button>
                    </form>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($pages > 1): ?>
      <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:.82rem;color:var(--text-muted)">
          Showing <?= $offset+1 ?>–<?= min($offset+$limit,$total) ?> of <?= number_format($total) ?>
        </span>
        <div class="pagination">
          <?php
          $base = APP_URL . '/admin/merchants?' . http_build_query(array_filter(compact('search','status','kyc')));
          for ($i = max(1,$page-2); $i <= min($pages,$page+2); $i++):
          ?>
            <a href="<?= $base ?>&page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
          <?php endfor; ?>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
