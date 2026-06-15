<?php
$page   = max(1,(int)($_GET['page']??1));
$limit  = 20;
$offset = ($page-1)*$limit;
$status = $_GET['status']??'pending';

$where  = $status ? "WHERE w.status = ?" : "WHERE 1=1";
$params = $status ? [$status] : [];

$totalRow = DB::fetch("SELECT COUNT(*) as c FROM withdrawals w $where", $params);
$total    = $totalRow['c'] ?? 0;
$pages    = (int)ceil($total/$limit);
$wds      = DB::fetchAll(
    "SELECT w.*, u.business_name, u.email, u.phone as merchant_phone
     FROM withdrawals w JOIN users u ON w.user_id=u.id
     $where ORDER BY w.created_at DESC LIMIT ? OFFSET ?",
    array_merge($params,[$limit,$offset])
);

$pendingVol = DB::fetch("SELECT COALESCE(SUM(amount),0) as vol FROM withdrawals WHERE status='pending'");
$processedToday = DB::fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(amount),0) as vol FROM withdrawals WHERE status='completed' AND DATE(updated_at)=CURDATE()");
?>

<div class="section-hd">
  <div>
    <h2>Withdrawals Queue</h2>
    <p>Review and process merchant withdrawal requests.</p>
  </div>
</div>

<!-- Summary -->
<div class="admin-stats" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
  <div class="admin-stat orange">
    <div class="admin-stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
    <div>
      <div class="admin-stat-val"><?= format_amount($pendingVol['vol']) ?></div>
      <div class="admin-stat-lbl">Pending Volume</div>
      <div class="admin-stat-sub"><?= DB::fetch("SELECT COUNT(*) as c FROM withdrawals WHERE status='pending'")['c'] ?? 0 ?> requests</div>
    </div>
  </div>
  <div class="admin-stat green">
    <div class="admin-stat-icon green"><i class="fas fa-check-circle"></i></div>
    <div>
      <div class="admin-stat-val"><?= format_amount($processedToday['vol']) ?></div>
      <div class="admin-stat-lbl">Processed Today</div>
      <div class="admin-stat-sub"><?= $processedToday['cnt'] ?> withdrawals</div>
    </div>
  </div>
  <div class="admin-stat navy">
    <div class="admin-stat-icon navy"><i class="fas fa-coins"></i></div>
    <div>
      <?php $allTime = DB::fetch("SELECT COALESCE(SUM(amount),0) as v FROM withdrawals WHERE status='completed'"); ?>
      <div class="admin-stat-val"><?= format_amount($allTime['v']) ?></div>
      <div class="admin-stat-lbl">All-Time Processed</div>
    </div>
  </div>
</div>

<!-- Status Tabs -->
<div class="tabs mb-6">
  <?php foreach(['pending','processing','completed','failed'] as $s): ?>
    <?php $cnt = DB::fetch("SELECT COUNT(*) as c FROM withdrawals WHERE status=?",[$s])['c']??0; ?>
    <a href="?status=<?=$s?>" class="tab <?=$status===$s?'active':''?>">
      <?=ucfirst($s)?> <span class="badge badge-<?=$s==='pending'?'warning':($s==='completed'?'success':($s==='failed'?'danger':'info'))?>">
        <?=$cnt?></span>
    </a>
  <?php endforeach; ?>
  <a href="?status=" class="tab <?=$status===''?'active':''?>">All</a>
</div>

<div class="card">
  <div class="p-0">
    <?php if (empty($wds)): ?>
      <div class="empty-state">
        <i class="fas fa-check-circle" style="color:var(--success)"></i>
        <h4>No <?= $status ?> withdrawals</h4>
        <p><?= $status === 'pending' ? 'All withdrawal requests have been processed.' : 'Nothing to show.' ?></p>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="orb-table">
          <thead>
            <tr>
              <th>Merchant</th>
              <th>Reference</th>
              <th>Channel</th>
              <th>Destination</th>
              <th>Amount</th>
              <th>Fee</th>
              <th>Status</th>
              <th>Requested</th>
              <?php if ($status === 'pending'): ?><th>Actions</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($wds as $wd): ?>
            <tr>
              <td>
                <div style="font-weight:600;font-size:.875rem"><?= sanitize($wd['business_name']) ?></div>
                <div style="font-size:.75rem;color:var(--text-muted)"><?= sanitize($wd['email']) ?></div>
              </td>
              <td><code style="font-size:.78rem"><?= sanitize($wd['reference']) ?></code></td>
              <td>
                <span class="chip <?= $wd['channel']==='mpesa'?'green':'navy' ?>">
                  <i class="fas fa-<?= $wd['channel']==='mpesa'?'mobile-alt':'university' ?>"></i>
                  <?= ucfirst($wd['channel']) ?>
                </span>
              </td>
              <td style="font-size:.84rem"><?= sanitize($wd['destination']) ?></td>
              <td style="font-weight:700"><?= format_amount($wd['amount']) ?></td>
              <td style="font-size:.82rem;color:var(--text-muted)"><?= format_amount($wd['fee']) ?></td>
              <td>
                <?php
                $sc = ['pending'=>'badge-warning','processing'=>'badge-info','completed'=>'badge-success','failed'=>'badge-danger'];
                ?>
                <span class="badge <?= $sc[$wd['status']]??'badge-secondary' ?>"><?= ucfirst($wd['status']) ?></span>
              </td>
              <td style="font-size:.78rem;color:var(--text-muted)"><?= date('d M Y H:i',strtotime($wd['created_at'])) ?></td>
              <?php if ($status === 'pending'): ?>
              <td>
                <div style="display:flex;gap:6px">
                  <form method="POST" action="<?= APP_URL ?>/admin/withdrawals/approve" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= sanitize($wd['id']) ?>">
                    <button type="submit" class="btn btn-primary btn-sm" data-confirm="Approve and process this withdrawal of <?= format_amount($wd['amount']) ?>?">
                      <i class="fas fa-check"></i> Approve
                    </button>
                  </form>
                  <form method="POST" action="<?= APP_URL ?>/admin/withdrawals/reject" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= sanitize($wd['id']) ?>">
                    <button type="submit" class="btn btn-danger btn-sm" data-confirm="Reject this withdrawal? Funds will be returned to wallet.">
                      <i class="fas fa-times"></i>
                    </button>
                  </form>
                </div>
              </td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($pages > 1): ?>
      <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:.82rem;color:var(--text-muted)">Showing <?=$offset+1?>–<?=min($offset+$limit,$total)?> of <?=number_format($total)?></span>
        <div class="pagination">
          <?php for($i=max(1,$page-2);$i<=min($pages,$page+2);$i++): ?>
            <a href="?status=<?=$status?>&page=<?=$i?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a>
          <?php endfor; ?>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
