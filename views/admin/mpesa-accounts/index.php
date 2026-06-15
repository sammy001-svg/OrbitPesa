<?php
$perPage = 25;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$activeTab = $_GET['status'] ?? 'pending';
$typeFilter = $_GET['type']   ?? '';
$search     = trim($_GET['search'] ?? '');

$filters = ['status' => $activeTab, 'type' => $typeFilter, 'search' => $search];

$accounts = MpesaAccount::getAll($filters, $perPage, $offset);
$total    = MpesaAccount::countAll($filters);
$pages    = max(1, (int)ceil($total / $perPage));
$counts   = MpesaAccount::countByStatus();

$allCount = array_sum($counts);

$volumeMap = [
    'under_50k' => 'Under 50K',
    '50k_200k'  => '50K – 200K',
    '200k_1m'   => '200K – 1M',
    'over_1m'   => 'Over 1M',
];

$pageQs = array_filter(['type' => $typeFilter, 'search' => $search]);
?>

<!-- Page header -->
<div class="section-hd">
  <div>
    <h2>M-Pesa Business Accounts</h2>
    <p>Review and approve merchant applications for till and paybill numbers.</p>
  </div>
  <?php if ($counts['pending'] > 0): ?>
    <span class="badge badge-warning" style="font-size:.8rem;padding:6px 14px">
      <?= $counts['pending'] ?> pending review
    </span>
  <?php endif; ?>
</div>

<!-- Stat cards -->
<div class="admin-stats" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px">
  <div class="admin-stat orange">
    <div class="admin-stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
    <div>
      <div class="admin-stat-val"><?= number_format($counts['pending']) ?></div>
      <div class="admin-stat-lbl">Awaiting Review</div>
      <?php if ($counts['pending'] > 0): ?>
        <div class="admin-stat-sub">Needs attention</div>
      <?php endif; ?>
    </div>
  </div>
  <div class="admin-stat blue">
    <div class="admin-stat-icon blue"><i class="fas fa-search"></i></div>
    <div>
      <div class="admin-stat-val"><?= number_format($counts['under_review']) ?></div>
      <div class="admin-stat-lbl">Under Review</div>
    </div>
  </div>
  <div class="admin-stat green">
    <div class="admin-stat-icon green"><i class="fas fa-check-circle"></i></div>
    <div>
      <div class="admin-stat-val"><?= number_format($counts['approved']) ?></div>
      <div class="admin-stat-lbl">Approved</div>
      <div class="admin-stat-sub">Active accounts</div>
    </div>
  </div>
  <div class="admin-stat red">
    <div class="admin-stat-icon red"><i class="fas fa-times-circle"></i></div>
    <div>
      <div class="admin-stat-val"><?= number_format($counts['rejected']) ?></div>
      <div class="admin-stat-lbl">Rejected</div>
    </div>
  </div>
</div>

<!-- Filter toolbar -->
<div class="card" style="margin-bottom:20px">
  <div class="card-body" style="padding:14px 18px">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
      <input type="hidden" name="status" value="<?= sanitize($activeTab) ?>">
      <div style="flex:1;min-width:200px">
        <label class="form-label" style="font-size:.78rem">Search</label>
        <input type="text" class="form-control" name="search" placeholder="Business name, email, number…" value="<?= sanitize($search) ?>">
      </div>
      <div>
        <label class="form-label" style="font-size:.78rem">Account Type</label>
        <select name="type" class="form-control form-select" style="min-width:140px">
          <option value="">All Types</option>
          <option value="till"    <?= $typeFilter === 'till'    ? 'selected' : '' ?>>Till (Buy Goods)</option>
          <option value="paybill" <?= $typeFilter === 'paybill' ? 'selected' : '' ?>>Paybill</option>
        </select>
      </div>
      <div style="display:flex;gap:8px">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
        <?php if ($search || $typeFilter): ?>
          <a href="?status=<?= urlencode($activeTab) ?>" class="btn btn-ghost btn-sm">Clear</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- Status tabs -->
<div class="tabs mb-6">
  <?php
  $tabs = [
    'pending'      => ['label' => 'Pending',      'badge' => 'badge-warning', 'count' => $counts['pending']],
    'under_review' => ['label' => 'Under Review',  'badge' => 'badge-info',    'count' => $counts['under_review']],
    'approved'     => ['label' => 'Approved',      'badge' => 'badge-success', 'count' => $counts['approved']],
    'rejected'     => ['label' => 'Rejected',      'badge' => 'badge-danger',  'count' => $counts['rejected']],
  ];
  foreach ($tabs as $key => $tab):
  ?>
  <a href="?status=<?= $key ?><?= $pageQs ? '&' . http_build_query($pageQs) : '' ?>" class="tab <?= $activeTab === $key ? 'active' : '' ?>">
    <?= $tab['label'] ?>
    <span class="badge <?= $tab['badge'] ?>"><?= $tab['count'] ?></span>
  </a>
  <?php endforeach; ?>
  <a href="?<?= $pageQs ? http_build_query($pageQs) : '' ?>" class="tab <?= $activeTab === '' ? 'active' : '' ?>">
    All <span class="badge badge-secondary"><?= $allCount ?></span>
  </a>
</div>

<!-- Applications table -->
<div class="card">
  <div class="p-0">
    <?php if (empty($accounts)): ?>
      <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h4>No <?= $activeTab ? str_replace('_', ' ', $activeTab) : '' ?> applications</h4>
        <p><?= $activeTab === 'pending' ? 'All applications have been reviewed.' : 'Nothing to display for this filter.' ?></p>
      </div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="orb-table">
        <thead>
          <tr>
            <th>Applicant</th>
            <th>Type</th>
            <th>Contact</th>
            <th>Volume</th>
            <th>Number</th>
            <th>Status</th>
            <th>Applied</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($accounts as $a): ?>
          <?php
          $initials = strtoupper(substr($a['business_name'], 0, 1));
          $statusBadge = match($a['status']) {
            'pending'      => 'badge-warning',
            'under_review' => 'badge-info',
            'approved'     => 'badge-success',
            'rejected'     => 'badge-danger',
            default        => 'badge-secondary',
          };
          $statusLabel = match($a['status']) {
            'pending'      => 'Pending',
            'under_review' => 'Under Review',
            'approved'     => 'Approved',
            'rejected'     => 'Rejected',
            default        => ucfirst($a['status']),
          };
          ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:36px;height:36px;border-radius:10px;background:var(--navy-lighter);color:var(--navy);font-weight:800;font-size:.9rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                  <?= $initials ?>
                </div>
                <div>
                  <div style="font-weight:700;font-size:.875rem;color:var(--navy)"><?= sanitize($a['business_name']) ?></div>
                  <div style="font-size:.75rem;color:var(--text-muted)"><?= ucwords(str_replace('_', ' ', $a['business_type'])) ?></div>
                  <?php if (!$a['user_id']): ?>
                    <div style="font-size:.72rem;margin-top:1px"><span style="background:#f1f5f9;color:var(--text-muted);padding:1px 7px;border-radius:4px;font-weight:600">Guest</span></div>
                  <?php elseif ($a['merchant_name'] && $a['merchant_name'] !== $a['business_name']): ?>
                    <div style="font-size:.72rem;color:var(--text-light);margin-top:1px">
                      <i class="fas fa-user" style="font-size:.65rem"></i> <?= sanitize($a['merchant_name']) ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td>
              <span class="chip <?= $a['application_type'] === 'till' ? 'green' : 'navy' ?>">
                <i class="fas fa-<?= $a['application_type'] === 'till' ? 'cash-register' : 'receipt' ?>"></i>
                <?= $a['application_type'] === 'till' ? 'Till' : 'Paybill' ?>
              </span>
            </td>
            <td>
              <div style="font-size:.84rem;font-weight:600"><?= sanitize($a['contact_name']) ?></div>
              <div style="font-size:.75rem;color:var(--text-muted)"><?= sanitize($a['contact_email']) ?></div>
              <div style="font-size:.75rem;color:var(--text-muted)"><?= sanitize($a['contact_phone']) ?></div>
            </td>
            <td style="font-size:.82rem;color:var(--text-muted)">KES <?= $volumeMap[$a['monthly_volume']] ?? '—' ?></td>
            <td>
              <?php if ($a['account_number']): ?>
                <code style="font-size:.92rem;font-weight:700;color:var(--green)"><?= sanitize($a['account_number']) ?></code>
              <?php else: ?>
                <span style="color:var(--text-light)">—</span>
              <?php endif; ?>
            </td>
            <td><span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span></td>
            <td style="font-size:.78rem;color:var(--text-muted);white-space:nowrap"><?= date('d M Y', strtotime($a['created_at'])) ?></td>
            <td>
              <button class="btn btn-outline btn-sm"
                onclick="openReview(
                  '<?= $a['id'] ?>',
                  '<?= addslashes(sanitize($a['business_name'])) ?>',
                  '<?= addslashes(sanitize($a['contact_name'])) ?>',
                  '<?= addslashes(sanitize($a['contact_email'])) ?>',
                  '<?= addslashes(sanitize($a['contact_phone'])) ?>',
                  '<?= $a['application_type'] ?>',
                  '<?= $a['status'] ?>',
                  '<?= addslashes(sanitize($a['business_type'])) ?>',
                  '<?= addslashes($volumeMap[$a['monthly_volume']] ?? '') ?>',
                  '<?= addslashes(sanitize($a['business_reg_no'] ?? '')) ?>',
                  '<?= addslashes(sanitize($a['description'] ?? '')) ?>',
                  '<?= addslashes(sanitize($a['admin_notes'] ?? '')) ?>',
                  '<?= addslashes(sanitize($a['account_number'] ?? '')) ?>'
                )">
                <i class="fas fa-eye"></i> Review
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between">
      <span style="font-size:.82rem;color:var(--text-muted)">
        Showing <?= $offset + 1 ?>–<?= min($offset + $perPage, $total) ?> of <?= number_format($total) ?>
      </span>
      <div class="pagination">
        <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
          <a href="?status=<?= urlencode($activeTab) ?><?= $pageQs ? '&' . http_build_query($pageQs) : '' ?>&page=<?= $i ?>"
             class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>


<!-- ============================================================
     Review / Action Modal
     ============================================================ -->
<div class="modal-overlay" id="reviewModal">
  <div class="modal" style="max-width:600px;width:100%">

    <div class="modal-header">
      <h4 id="modalTitle" style="display:flex;align-items:center;gap:8px">
        <span id="modalTypeIcon"></span>
        Review Application
      </h4>
      <button class="modal-close" onclick="closeReview()"><i class="fas fa-times"></i></button>
    </div>

    <div class="modal-body" style="padding:0">

      <!-- Info panel -->
      <div id="modalInfoPanel" style="padding:20px 24px;border-bottom:1px solid var(--border)">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div>
            <div style="font-size:.7rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px">Business</div>
            <div id="infoBizName" style="font-weight:700;font-size:.95rem;color:var(--navy)"></div>
            <div id="infoBizType" style="font-size:.8rem;color:var(--text-muted);margin-top:2px"></div>
            <div id="infoBizReg"  style="font-size:.78rem;color:var(--text-light);margin-top:2px"></div>
          </div>
          <div>
            <div style="font-size:.7rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px">Contact</div>
            <div id="infoContactName"  style="font-weight:600;font-size:.88rem"></div>
            <div id="infoContactEmail" style="font-size:.8rem;color:var(--text-muted)"></div>
            <div id="infoContactPhone" style="font-size:.8rem;color:var(--text-muted)"></div>
          </div>
          <div>
            <div style="font-size:.7rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px">Account Type</div>
            <div id="infoType" style="font-weight:600;font-size:.88rem"></div>
          </div>
          <div>
            <div style="font-size:.7rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px">Monthly Volume</div>
            <div id="infoVolume" style="font-weight:600;font-size:.88rem"></div>
          </div>
          <div id="infoDescWrap" style="grid-column:span 2;display:none">
            <div style="font-size:.7rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px">Description</div>
            <div id="infoDesc" style="font-size:.85rem;color:var(--text-muted);line-height:1.5"></div>
          </div>
          <div id="infoPrevNotesWrap" style="grid-column:span 2;display:none">
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 12px">
              <div style="font-size:.72rem;font-weight:700;color:var(--danger);margin-bottom:3px;text-transform:uppercase;letter-spacing:.06em">Previous Rejection Note</div>
              <div id="infoPrevNotes" style="font-size:.85rem;color:#7f1d1d"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Action switcher tabs -->
      <div style="display:flex;border-bottom:1px solid var(--border);padding:0 24px;gap:0">
        <button id="tabApprove" onclick="switchTab('approve')"
          style="padding:12px 20px;border:none;background:none;font-size:.84rem;font-weight:700;cursor:pointer;border-bottom:2px solid var(--green);color:var(--green)">
          <i class="fas fa-check-circle"></i> Approve
        </button>
        <button id="tabReview" onclick="switchTab('review')"
          style="padding:12px 20px;border:none;background:none;font-size:.84rem;font-weight:600;cursor:pointer;border-bottom:2px solid transparent;color:var(--text-muted)">
          <i class="fas fa-clock"></i> Mark Under Review
        </button>
        <button id="tabReject" onclick="switchTab('reject')"
          style="padding:12px 20px;border:none;background:none;font-size:.84rem;font-weight:600;cursor:pointer;border-bottom:2px solid transparent;color:var(--text-muted)">
          <i class="fas fa-times-circle"></i> Reject
        </button>
      </div>

      <!-- Approve panel -->
      <div id="panelApprove" style="padding:20px 24px">
        <form method="POST" action="<?= APP_URL ?>/admin/mpesa-accounts/approve">
          <?= csrf_field() ?>
          <input type="hidden" name="id" id="approveId">
          <div class="form-group">
            <label class="form-label">
              Assign <span id="approveTypeLabel">Till</span> Number
              <span style="color:var(--danger)">*</span>
            </label>
            <input type="text" name="account_number" id="approveNumber" required class="form-control"
              placeholder="e.g. 123456"
              style="font-size:1.3rem;font-family:monospace;font-weight:800;letter-spacing:.08em;color:var(--green);text-align:center;padding:14px">
            <div style="font-size:.75rem;color:var(--text-muted);margin-top:6px">
              <i class="fas fa-info-circle"></i>
              Enter the unique M-Pesa till or paybill number assigned to this merchant. This cannot be changed after approval.
            </div>
          </div>
          <div class="form-group" style="margin-top:12px">
            <label class="form-label">Internal Notes <span style="color:var(--text-light);font-weight:400">(optional)</span></label>
            <textarea name="admin_notes" class="form-control" rows="2" placeholder="Optional notes for admin records only"></textarea>
          </div>
          <div style="background:var(--green-light);border:1px solid rgba(21,131,71,.2);border-radius:8px;padding:12px 14px;margin-top:4px;font-size:.82rem;color:var(--green);display:flex;gap:8px">
            <i class="fas fa-envelope" style="margin-top:2px;flex-shrink:0"></i>
            <span>The merchant will be notified by email and in-app notification with their assigned number.</span>
          </div>
          <div class="modal-footer" style="padding:16px 0 0;border-top:1px solid var(--border);margin-top:16px">
            <button type="button" class="btn btn-ghost" onclick="closeReview()">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-check-circle"></i> Approve &amp; Assign Number
            </button>
          </div>
        </form>
      </div>

      <!-- Under-review panel -->
      <div id="panelReview" style="padding:20px 24px;display:none">
        <form method="POST" action="<?= APP_URL ?>/admin/mpesa-accounts/under-review">
          <?= csrf_field() ?>
          <input type="hidden" name="id" id="reviewId">
          <div style="text-align:center;padding:12px 0 24px">
            <div style="width:56px;height:56px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:1.4rem;color:#2563eb">
              <i class="fas fa-search"></i>
            </div>
            <h4 style="color:var(--navy);margin-bottom:8px">Set to Under Review</h4>
            <p style="color:var(--text-muted);font-size:.875rem;max-width:360px;margin:0 auto">
              This signals to the merchant that their application is actively being reviewed. They will receive an in-app notification.
            </p>
          </div>
          <div class="modal-footer" style="padding:16px 0 0;border-top:1px solid var(--border)">
            <button type="button" class="btn btn-ghost" onclick="closeReview()">Cancel</button>
            <button type="submit" class="btn btn-navy">
              <i class="fas fa-search"></i> Confirm: Set Under Review
            </button>
          </div>
        </form>
      </div>

      <!-- Reject panel -->
      <div id="panelReject" style="padding:20px 24px;display:none">
        <form method="POST" action="<?= APP_URL ?>/admin/mpesa-accounts/reject">
          <?= csrf_field() ?>
          <input type="hidden" name="id" id="rejectId">
          <div class="form-group">
            <label class="form-label">
              Rejection Reason <span style="color:var(--danger)">*</span>
            </label>
            <textarea name="admin_notes" required class="form-control" rows="5"
              placeholder="Explain why this application cannot be approved. Be specific — this message is sent to the applicant by email so they can correct the issue and reapply."></textarea>
            <div style="font-size:.75rem;color:var(--text-muted);margin-top:6px">
              <i class="fas fa-envelope"></i> This reason will be emailed to the merchant and shown in their dashboard.
            </div>
          </div>
          <div class="modal-footer" style="padding:16px 0 0;border-top:1px solid var(--border);margin-top:4px">
            <button type="button" class="btn btn-ghost" onclick="closeReview()">Cancel</button>
            <button type="submit" class="btn btn-danger">
              <i class="fas fa-times-circle"></i> Confirm Rejection
            </button>
          </div>
        </form>
      </div>

    </div><!-- /.modal-body -->
  </div><!-- /.modal -->
</div><!-- /.modal-overlay -->


<script>
function openReview(id, bizName, contactName, email, phone, type, status, bizType, volume, regNo, desc, prevNotes, currentNumber) {
  // Populate info panel
  document.getElementById('modalTitle').innerHTML =
    '<span style="background:' + (type==='till'?'var(--green-light)':'var(--navy-lighter)') + ';color:' + (type==='till'?'var(--green)':'var(--navy)') +
    ';padding:3px 10px;border-radius:6px;font-size:.75rem;font-weight:700"><i class="fas fa-' + (type==='till'?'cash-register':'receipt') + '"></i> ' +
    (type==='till'?'Till':'Paybill') + '</span> Review Application';

  document.getElementById('infoBizName').textContent    = bizName;
  document.getElementById('infoBizType').textContent    = bizType;
  document.getElementById('infoBizReg').textContent     = regNo ? 'Reg: ' + regNo : '';
  document.getElementById('infoContactName').textContent  = contactName;
  document.getElementById('infoContactEmail').textContent = email;
  document.getElementById('infoContactPhone').textContent = phone;
  document.getElementById('infoType').innerHTML =
    '<span style="font-weight:700;color:' + (type==='till'?'var(--green)':'var(--navy)') + '">' +
    (type==='till'?'<i class="fas fa-cash-register"></i> Till — Buy Goods':'<i class="fas fa-receipt"></i> Paybill') + '</span>';
  document.getElementById('infoVolume').textContent = volume ? 'KES ' + volume + ' / month' : '—';

  const descWrap = document.getElementById('infoDescWrap');
  if (desc) { document.getElementById('infoDesc').textContent = desc; descWrap.style.display = ''; }
  else { descWrap.style.display = 'none'; }

  const notesWrap = document.getElementById('infoPrevNotesWrap');
  if (prevNotes) { document.getElementById('infoPrevNotes').textContent = prevNotes; notesWrap.style.display = ''; }
  else { notesWrap.style.display = 'none'; }

  // Wire hidden ID fields
  document.getElementById('approveId').value  = id;
  document.getElementById('reviewId').value   = id;
  document.getElementById('rejectId').value   = id;

  // Set type label in approve form
  document.getElementById('approveTypeLabel').textContent = type === 'till' ? 'Till' : 'Paybill';

  // Pre-fill if already has a number
  document.getElementById('approveNumber').value = currentNumber || '';

  // Auto-select action based on current status
  if (status === 'pending' || status === 'approved') switchTab('approve');
  else if (status === 'under_review') switchTab('approve');
  else if (status === 'rejected') switchTab('approve');
  else switchTab('approve');

  document.getElementById('reviewModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeReview() {
  document.getElementById('reviewModal').classList.remove('open');
  document.body.style.overflow = '';
}

function switchTab(tab) {
  ['approve','review','reject'].forEach(t => {
    document.getElementById('panel'  + t.charAt(0).toUpperCase() + t.slice(1)).style.display = t === tab ? '' : 'none';
    const btn = document.getElementById('tab' + t.charAt(0).toUpperCase() + t.slice(1));
    const isActive = t === tab;
    btn.style.borderBottomColor = isActive ? (t==='approve' ? 'var(--green)' : t==='reject' ? 'var(--danger)' : 'var(--navy)') : 'transparent';
    btn.style.color  = isActive ? (t==='approve' ? 'var(--green)' : t==='reject' ? 'var(--danger)' : 'var(--navy)') : 'var(--text-muted)';
    btn.style.fontWeight = isActive ? '700' : '600';
  });
}

// Close on backdrop click
document.getElementById('reviewModal').addEventListener('click', e => {
  if (e.target === document.getElementById('reviewModal')) closeReview();
});
</script>
