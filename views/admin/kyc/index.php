<?php
$page   = max(1,(int)($_GET['page']??1));
$limit  = 20;
$offset = ($page-1)*$limit;
$filter = $_GET['status'] ?? 'pending';

$where  = $filter ? "WHERE k.status = ?" : "WHERE 1=1";
$params = $filter ? [$filter] : [];

$totalRow = DB::fetch("SELECT COUNT(*) as c FROM kyc_documents k $where", $params);
$total    = $totalRow['c'] ?? 0;
$pages    = (int)ceil($total/$limit);
$docs = DB::fetchAll(
    "SELECT k.*, u.business_name, u.email, u.phone, u.account_type, u.kyc_status as merchant_kyc_status
     FROM kyc_documents k JOIN users u ON k.user_id=u.id
     $where ORDER BY k.created_at DESC LIMIT ? OFFSET ?",
    array_merge($params,[$limit,$offset])
);

$pendingCount   = DB::fetch("SELECT COUNT(*) as c FROM kyc_documents WHERE status='pending'")['c'] ?? 0;
$approvedCount  = DB::fetch("SELECT COUNT(*) as c FROM kyc_documents WHERE status='approved'")['c'] ?? 0;
$rejectedCount  = DB::fetch("SELECT COUNT(*) as c FROM kyc_documents WHERE status='rejected'")['c'] ?? 0;
?>

<div class="section-hd">
  <div>
    <h2>KYC Verification Queue</h2>
    <p>Review merchant identity documents and approve or reject KYC submissions.</p>
  </div>
</div>

<!-- Stats -->
<div class="admin-stats" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
  <div class="admin-stat orange">
    <div class="admin-stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
    <div>
      <div class="admin-stat-val"><?= $pendingCount ?></div>
      <div class="admin-stat-lbl">Pending Review</div>
    </div>
  </div>
  <div class="admin-stat green">
    <div class="admin-stat-icon green"><i class="fas fa-check-shield"></i></div>
    <div>
      <div class="admin-stat-val"><?= $approvedCount ?></div>
      <div class="admin-stat-lbl">Approved</div>
    </div>
  </div>
  <div class="admin-stat red">
    <div class="admin-stat-icon red"><i class="fas fa-times-circle"></i></div>
    <div>
      <div class="admin-stat-val"><?= $rejectedCount ?></div>
      <div class="admin-stat-lbl">Rejected</div>
    </div>
  </div>
</div>

<!-- Tabs -->
<div class="tabs mb-6">
  <?php foreach(['pending'=>'warning','approved'=>'success','rejected'=>'danger'] as $s=>$bc): ?>
  <a href="?status=<?=$s?>" class="tab <?=$filter===$s?'active':''?>">
    <?=ucfirst($s)?> <span class="badge badge-<?=$bc?>"><?= DB::fetch("SELECT COUNT(*) as c FROM kyc_documents WHERE status=?",[$s])['c']??0 ?></span>
  </a>
  <?php endforeach; ?>
  <a href="?status=" class="tab <?=$filter===''?'active':''?>">All</a>
</div>

<div class="card">
  <div class="p-0">
    <?php if (empty($docs)): ?>
      <div class="empty-state">
        <i class="fas fa-folder-open" style="color:var(--text-muted)"></i>
        <h4>No <?=$filter?> documents</h4>
        <p><?=$filter==='pending'?'All KYC submissions have been reviewed.':'Nothing to display.'?></p>
      </div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="orb-table">
        <thead>
          <tr>
            <th>Merchant</th>
            <th>Document Type</th>
            <th>KYC Status</th>
            <th>Account</th>
            <th>Submitted</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($docs as $doc): ?>
          <tr>
            <td>
              <a href="<?= APP_URL ?>/admin/merchants/<?= urlencode($doc['user_id']) ?>" style="font-weight:700;font-size:.875rem;color:var(--green);text-decoration:none">
                <?= sanitize($doc['business_name']) ?>
              </a>
              <div style="font-size:.75rem;color:var(--text-muted)"><?= sanitize($doc['email']) ?></div>
            </td>
            <td>
              <span style="font-weight:600;font-size:.84rem"><?= ucwords(str_replace('_',' ',$doc['doc_type'])) ?></span>
              <?php if ($doc['file_path']): ?>
              <div>
                <a href="<?= APP_URL ?>/<?= sanitize($doc['file_path']) ?>" target="_blank" rel="noopener"
                   style="font-size:.74rem;color:var(--green)">
                  <i class="fas fa-external-link-alt"></i> View File
                </a>
              </div>
              <?php endif; ?>
            </td>
            <td>
              <span class="kyc-badge kyc-<?= $doc['merchant_kyc_status'] ?>"><?= ucfirst($doc['merchant_kyc_status']) ?></span>
            </td>
            <td><span class="badge badge-navy" style="font-size:.72rem"><?= ucfirst($doc['account_type']) ?></span></td>
            <td style="font-size:.78rem;color:var(--text-muted)"><?= date('d M Y H:i',strtotime($doc['created_at'])) ?></td>
            <td>
              <?php if ($doc['status'] === 'pending'): ?>
              <div style="display:flex;gap:6px;align-items:center">
                <form method="POST" action="<?= APP_URL ?>/admin/kyc/review" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="doc_id" value="<?= sanitize($doc['id']) ?>">
                  <input type="hidden" name="user_id" value="<?= sanitize($doc['user_id']) ?>">
                  <input type="hidden" name="action" value="approve">
                  <button type="submit" class="btn btn-primary btn-sm" data-confirm="Approve this KYC document?">
                    <i class="fas fa-check"></i> Approve
                  </button>
                </form>
                <button class="btn btn-danger btn-sm" onclick="openRejectModal(<?= $doc['id'] ?>, <?= $doc['user_id'] ?>)">
                  <i class="fas fa-times"></i> Reject
                </button>
              </div>
              <?php else: ?>
              <span class="badge badge-<?= $doc['status']==='approved'?'success':'danger' ?>"><?= ucfirst($doc['status']) ?></span>
              <?php if ($doc['review_notes']): ?>
                <div style="font-size:.72rem;color:var(--text-muted);margin-top:3px" title="<?= sanitize($doc['review_notes']) ?>">
                  <i class="fas fa-comment-alt"></i> Note on file
                </div>
              <?php endif; ?>
              <?php endif; ?>
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
          <a href="?status=<?=$filter?>&page=<?=$i?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a>
        <?php endfor; ?>
      </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal-overlay" id="rejectModal">
  <div class="modal" style="max-width:420px">
    <div class="modal-header">
      <h4><i class="fas fa-times-circle" style="color:var(--danger)"></i> Reject KYC Document</h4>
      <button class="modal-close" onclick="document.getElementById('rejectModal').classList.remove('open')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/admin/kyc/review" id="rejectForm">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="reject">
      <input type="hidden" name="doc_id" id="rejectDocId">
      <input type="hidden" name="user_id" id="rejectUserId">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Rejection Reason <span style="color:var(--danger)">*</span></label>
          <textarea class="form-control" name="notes" rows="4" required
                    placeholder="Explain why this document is being rejected..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="document.getElementById('rejectModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Confirm Rejection</button>
      </div>
    </form>
  </div>
</div>
<script>
function openRejectModal(docId, userId) {
  document.getElementById('rejectDocId').value = docId;
  document.getElementById('rejectUserId').value = userId;
  document.getElementById('rejectModal').classList.add('open');
}
</script>
