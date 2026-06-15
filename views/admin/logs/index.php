<?php
$page    = max(1,(int)($_GET['page']??1));
$limit   = 50;
$offset  = ($page-1)*$limit;
$admin   = $_GET['admin'] ?? '';
$action  = $_GET['action'] ?? '';
$dateFrom= $_GET['date_from'] ?? '';
$dateTo  = $_GET['date_to'] ?? '';

$where  = "WHERE 1=1";
$params = [];
if ($admin)    { $where .= " AND l.admin_id = ?"; $params[] = $admin; }
if ($action)   { $where .= " AND l.action LIKE ?"; $params[] = "%$action%"; }
if ($dateFrom) { $where .= " AND DATE(l.created_at) >= ?"; $params[] = $dateFrom; }
if ($dateTo)   { $where .= " AND DATE(l.created_at) <= ?"; $params[] = $dateTo; }

$totalRow = DB::fetch("SELECT COUNT(*) as c FROM admin_logs l $where", $params);
$total    = $totalRow['c'] ?? 0;
$pages    = (int)ceil($total/$limit);
$logs = DB::fetchAll(
    "SELECT l.*, a.name as admin_name, a.email as admin_email, a.role as admin_role
     FROM admin_logs l JOIN admins a ON l.admin_id=a.id
     $where ORDER BY l.created_at DESC LIMIT ? OFFSET ?",
    array_merge($params,[$limit,$offset])
);

$admins = DB::fetchAll("SELECT id, name, email, role FROM admins ORDER BY name");
?>

<div class="section-hd">
  <div>
    <h2>Activity Logs</h2>
    <p>Full audit trail of all administrative actions on the platform.</p>
  </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:20px">
  <div class="card-body" style="padding:14px 18px">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
      <div>
        <label class="form-label" style="font-size:.78rem">Admin</label>
        <select name="admin" class="form-control form-select" style="min-width:160px">
          <option value="">All Admins</option>
          <?php foreach ($admins as $a): ?>
            <option value="<?= sanitize($a['id']) ?>" <?= $admin==$a['id']?'selected':'' ?>>
              <?= sanitize($a['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label" style="font-size:.78rem">Action Contains</label>
        <input type="text" class="form-control" name="action" placeholder="e.g. suspend, approve..." value="<?= sanitize($action) ?>" style="min-width:160px">
      </div>
      <div>
        <label class="form-label" style="font-size:.78rem">Date From</label>
        <input type="date" class="form-control" name="date_from" value="<?= sanitize($dateFrom) ?>">
      </div>
      <div>
        <label class="form-label" style="font-size:.78rem">Date To</label>
        <input type="date" class="form-control" name="date_to" value="<?= sanitize($dateTo) ?>">
      </div>
      <div style="display:flex;gap:8px">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
        <a href="<?= APP_URL ?>/admin/logs" class="btn btn-ghost btn-sm">Clear</a>
      </div>
    </form>
  </div>
</div>

<!-- Log Count -->
<div style="font-size:.82rem;color:var(--text-muted);margin-bottom:10px">
  <?= number_format($total) ?> log entr<?= $total===1?'y':'ies' ?> found
</div>

<div class="card">
  <div class="p-0">
    <?php if (empty($logs)): ?>
      <div class="empty-state">
        <i class="fas fa-clipboard-list" style="color:var(--text-muted)"></i>
        <h4>No logs found</h4>
        <p>Try adjusting your filters.</p>
      </div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="orb-table">
        <thead>
          <tr>
            <th style="width:160px">Admin</th>
            <th>Action</th>
            <th>Target</th>
            <th style="width:160px">IP Address</th>
            <th style="width:140px">Time</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logs as $log): ?>
          <?php
          $roleColors = ['super_admin'=>'green','admin'=>'navy','support'=>'info'];
          $roleColor  = $roleColors[$log['admin_role']] ?? 'navy';
          $actionIcon = 'circle';
          if (str_contains($log['action'],'suspend'))  $actionIcon='ban';
          elseif(str_contains($log['action'],'activate')) $actionIcon='check-circle';
          elseif(str_contains($log['action'],'approve')) $actionIcon='thumbs-up';
          elseif(str_contains($log['action'],'reject'))  $actionIcon='thumbs-down';
          elseif(str_contains($log['action'],'credit'))  $actionIcon='plus-circle';
          elseif(str_contains($log['action'],'login'))   $actionIcon='sign-in-alt';
          elseif(str_contains($log['action'],'fee'))     $actionIcon='percentage';
          elseif(str_contains($log['action'],'setting')) $actionIcon='cog';
          elseif(str_contains($log['action'],'withdraw'))$actionIcon='money-bill-wave';
          ?>
          <tr>
            <td>
              <div style="font-weight:600;font-size:.84rem;color:var(--navy)"><?= sanitize($log['admin_name']) ?></div>
              <div style="display:flex;gap:5px;align-items:center;margin-top:2px">
                <span class="badge badge-<?= $roleColor ?>" style="font-size:.65rem"><?= ucfirst(str_replace('_',' ',$log['admin_role'])) ?></span>
              </div>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:7px">
                <i class="fas fa-<?= $actionIcon ?>" style="color:var(--green);font-size:.82rem;width:14px"></i>
                <span style="font-size:.875rem"><?= sanitize($log['action']) ?></span>
              </div>
            </td>
            <td>
              <?php if ($log['target_type'] && $log['target_id']): ?>
              <code style="font-size:.76rem;color:var(--text-muted)">
                <?= sanitize($log['target_type']) ?> #<?= sanitize($log['target_id']) ?>
              </code>
              <?php else: ?>
              <span style="color:var(--text-muted);font-size:.82rem">—</span>
              <?php endif; ?>
              <?php if (!empty($log['description'])): ?>
              <div style="font-size:.74rem;color:var(--text-muted);margin-top:2px"><?= sanitize(mb_strimwidth($log['description'],0,60,'...')) ?></div>
              <?php endif; ?>
            </td>
            <td style="font-size:.78rem;color:var(--text-muted);font-family:monospace"><?= sanitize($log['ip_address'] ?? '—') ?></td>
            <td>
              <div style="font-size:.78rem;color:var(--navy)"><?= date('d M Y',strtotime($log['created_at'])) ?></div>
              <div style="font-size:.72rem;color:var(--text-muted)"><?= date('H:i:s',strtotime($log['created_at'])) ?></div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between">
      <span style="font-size:.82rem;color:var(--text-muted)">Showing <?=$offset+1?>–<?=min($offset+$limit,$total)?> of <?=number_format($total)?></span>
      <div class="pagination">
        <?php
        $base = APP_URL.'/admin/logs?'.http_build_query(array_filter(compact('admin','action','dateFrom','dateTo')));
        for($i=max(1,$page-2);$i<=min($pages,$page+2);$i++):
        ?>
          <a href="<?=$base?>&page=<?=$i?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a>
        <?php endfor; ?>
      </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
