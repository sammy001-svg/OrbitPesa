<?php
$page   = max(1,(int)($_GET['page']??1));
$limit  = 20;
$offset = ($page-1)*$limit;
$status = $_GET['status'] ?? 'open';

$where  = $status ? "WHERE d.status = ?" : "WHERE 1=1";
$params = $status ? [$status] : [];

$totalRow = DB::fetch("SELECT COUNT(*) as c FROM disputes d $where", $params);
$total    = $totalRow['c'] ?? 0;
$pages    = (int)ceil($total/$limit);
$disputes = DB::fetchAll(
    "SELECT d.*, u.business_name, u.email,
            t.amount as txn_amount, t.channel, t.phone as txn_phone
     FROM disputes d
     JOIN users u ON d.user_id=u.id
     LEFT JOIN transactions t ON d.transaction_ref=t.reference
     $where ORDER BY d.created_at DESC LIMIT ? OFFSET ?",
    array_merge($params,[$limit,$offset])
);

$openCount       = DB::fetch("SELECT COUNT(*) as c FROM disputes WHERE status='open'")['c'] ?? 0;
$inReviewCount   = DB::fetch("SELECT COUNT(*) as c FROM disputes WHERE status='under_review'")['c'] ?? 0;
$resolvedCount   = DB::fetch("SELECT COUNT(*) as c FROM disputes WHERE status='resolved'")['c'] ?? 0;
?>

<div class="section-hd">
  <div>
    <h2>Disputes</h2>
    <p>Review and resolve merchant transaction disputes.</p>
  </div>
</div>

<!-- Stats -->
<div class="admin-stats" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
  <div class="admin-stat red">
    <div class="admin-stat-icon red"><i class="fas fa-exclamation-circle"></i></div>
    <div>
      <div class="admin-stat-val"><?= $openCount ?></div>
      <div class="admin-stat-lbl">Open Disputes</div>
    </div>
  </div>
  <div class="admin-stat orange">
    <div class="admin-stat-icon orange"><i class="fas fa-search"></i></div>
    <div>
      <div class="admin-stat-val"><?= $inReviewCount ?></div>
      <div class="admin-stat-lbl">Under Review</div>
    </div>
  </div>
  <div class="admin-stat green">
    <div class="admin-stat-icon green"><i class="fas fa-handshake"></i></div>
    <div>
      <div class="admin-stat-val"><?= $resolvedCount ?></div>
      <div class="admin-stat-lbl">Resolved</div>
    </div>
  </div>
</div>

<!-- Tabs -->
<div class="tabs mb-6">
  <?php foreach(['open'=>'danger','under_review'=>'warning','resolved'=>'success','closed'=>'secondary'] as $s=>$bc): ?>
  <a href="?status=<?=$s?>" class="tab <?=$status===$s?'active':''?>">
    <?=ucwords(str_replace('_',' ',$s))?>
    <span class="badge badge-<?=$bc?>"><?= DB::fetch("SELECT COUNT(*) as c FROM disputes WHERE status=?",[$s])['c']??0 ?></span>
  </a>
  <?php endforeach; ?>
  <a href="?status=" class="tab <?=$status===''?'active':''?>">All</a>
</div>

<div class="card">
  <div class="p-0">
    <?php if (empty($disputes)): ?>
      <div class="empty-state">
        <i class="fas fa-handshake" style="color:var(--success)"></i>
        <h4>No <?=str_replace('_',' ',$status)?> disputes</h4>
        <p><?=$status==='open'?'No open disputes at the moment.':'Nothing to show.'?></p>
      </div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="orb-table">
        <thead>
          <tr>
            <th>Merchant</th>
            <th>Transaction Ref</th>
            <th>Reason</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Opened</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($disputes as $d): ?>
          <tr>
            <td>
              <a href="<?= APP_URL ?>/admin/merchants/<?= urlencode($d['user_id']) ?>"
                 style="font-weight:700;font-size:.875rem;color:var(--green);text-decoration:none">
                <?= sanitize($d['business_name']) ?>
              </a>
              <div style="font-size:.74rem;color:var(--text-muted)"><?= sanitize($d['email']) ?></div>
            </td>
            <td>
              <?php if ($d['transaction_ref']): ?>
                <code style="font-size:.76rem"><?= sanitize($d['transaction_ref']) ?></code>
                <?php if ($d['channel']): ?>
                <div style="font-size:.72rem;color:var(--text-muted)"><?= ucfirst(str_replace('_',' ',$d['channel'])) ?></div>
                <?php endif; ?>
              <?php else: ?>
                <span style="color:var(--text-muted);font-size:.82rem">—</span>
              <?php endif; ?>
            </td>
            <td>
              <div style="font-size:.84rem;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                   title="<?= sanitize($d['reason'] ?? '') ?>">
                <?= sanitize(mb_strimwidth($d['reason']??'',0,60,'...')) ?>
              </div>
            </td>
            <td style="font-weight:700"><?= $d['txn_amount'] ? format_amount($d['txn_amount']) : '—' ?></td>
            <td>
              <?php $sc = ['open'=>'badge-danger','under_review'=>'badge-warning','resolved'=>'badge-success','closed'=>'badge-secondary']; ?>
              <span class="badge <?= $sc[$d['status']]??'badge-secondary' ?>"><?= ucwords(str_replace('_',' ',$d['status'])) ?></span>
            </td>
            <td style="font-size:.78rem;color:var(--text-muted)"><?= date('d M Y',strtotime($d['created_at'])) ?></td>
            <td>
              <div class="dropdown">
                <button class="btn btn-ghost btn-sm" data-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                <div class="dropdown-menu">
                  <a href="#" class="dropdown-item"
                     onclick="openDisputeModal(<?= $d['id'] ?>, '<?= addslashes(sanitize($d['reason']??'')) ?>', '<?= $d['status'] ?>'); return false">
                    <i class="fas fa-eye"></i> View Details
                  </a>
                  <?php if (in_array($d['status'],['open','under_review'])): ?>
                  <form method="POST" action="<?= APP_URL ?>/admin/disputes/update">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                    <input type="hidden" name="status" value="under_review">
                    <button type="submit" class="dropdown-item" style="width:100%;text-align:left;background:none;border:none;cursor:pointer">
                      <i class="fas fa-search"></i> Mark Under Review
                    </button>
                  </form>
                  <form method="POST" action="<?= APP_URL ?>/admin/disputes/update">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                    <input type="hidden" name="status" value="resolved">
                    <button type="submit" class="dropdown-item" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;color:var(--success)">
                      <i class="fas fa-check"></i> Mark Resolved
                    </button>
                  </form>
                  <form method="POST" action="<?= APP_URL ?>/admin/disputes/update">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                    <input type="hidden" name="status" value="closed">
                    <button type="submit" class="dropdown-item danger" style="width:100%;text-align:left;background:none;border:none;cursor:pointer">
                      <i class="fas fa-times"></i> Close Dispute
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

<!-- Detail Modal -->
<div class="modal-overlay" id="disputeModal">
  <div class="modal" style="max-width:480px">
    <div class="modal-header">
      <h4><i class="fas fa-exclamation-circle" style="color:var(--danger)"></i> Dispute Details</h4>
      <button class="modal-close" onclick="document.getElementById('disputeModal').classList.remove('open')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <p id="disputeStatusLine" style="font-size:.82rem;color:var(--text-muted);margin-bottom:12px"></p>
      <div style="background:var(--bg);border-radius:var(--radius);padding:14px;font-size:.875rem;line-height:1.7;color:var(--text)" id="disputeDesc"></div>
      <form method="POST" action="<?= APP_URL ?>/admin/disputes/update" style="margin-top:16px">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="disputeId">
        <div class="form-group">
          <label class="form-label">Resolution Notes</label>
          <textarea class="form-control" name="resolution_notes" rows="3" placeholder="Optional notes about resolution..."></textarea>
        </div>
        <div style="display:flex;gap:8px">
          <input type="hidden" name="status" value="resolved">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Mark Resolved</button>
          <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('disputeModal').classList.remove('open')">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
function openDisputeModal(id, reason, status) {
  document.getElementById('disputeId').value = id;
  document.getElementById('disputeDesc').textContent = reason || 'No reason provided.';
  document.getElementById('disputeStatusLine').textContent = 'Current status: ' + status.replace(/_/g,' ');
  document.getElementById('disputeModal').classList.add('open');
}
</script>
