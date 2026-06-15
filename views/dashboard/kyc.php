<?php
$allDocs = DB::fetchAll(
    "SELECT * FROM kyc_documents WHERE user_id = ? ORDER BY created_at DESC",
    [$_SESSION['user_id']]
);

$latest = [];
foreach ($allDocs as $doc) {
    if (!isset($latest[$doc['doc_type']])) {
        $latest[$doc['doc_type']] = $doc;
    }
}

$u          = auth_user();
$kycStatus  = $u['kyc_status'] ?? 'unverified';
$acctType   = $u['account_type'] ?? 'business';

$docTypes = [
    'national_id'    => ['label' => 'National ID',               'icon' => 'fa-id-card',       'required' => true,                     'desc' => 'Front and back of your Kenya National ID (JPG, PNG or PDF, max 5 MB).'],
    'passport'       => ['label' => 'Passport',                   'icon' => 'fa-passport',       'required' => false,                    'desc' => 'Bio-data page of your passport — accepted instead of National ID.'],
    'business_reg'   => ['label' => 'Business Registration',      'icon' => 'fa-building',       'required' => $acctType === 'business', 'desc' => 'Certificate of Incorporation or Business Name Certificate.'],
    'bank_statement' => ['label' => 'Bank Statement',             'icon' => 'fa-university',     'required' => false,                    'desc' => 'Last 3 months\' bank statement (unlocks higher transaction limits).'],
    'utility_bill'   => ['label' => 'Utility Bill / Proof of Address', 'icon' => 'fa-file-invoice', 'required' => false,                'desc' => 'Recent utility bill showing your business or residential address.'],
];

$pendingCount  = 0;
$rejectedCount = 0;
foreach ($latest as $doc) {
    if ($doc['status'] === 'pending')  $pendingCount++;
    if ($doc['status'] === 'rejected') $rejectedCount++;
}

$statusConfig = [
    'unverified' => ['color' => '#64748b', 'bg' => '#f1f5f9', 'border' => '#cbd5e1', 'icon' => 'fa-clock',        'label' => 'Not Verified'],
    'pending'    => ['color' => '#92400e', 'bg' => '#fffbeb', 'border' => '#fcd34d', 'icon' => 'fa-hourglass-half','label' => 'Under Review'],
    'verified'   => ['color' => '#065f46', 'bg' => '#ecfdf5', 'border' => '#6ee7b7', 'icon' => 'fa-check-circle', 'label' => 'Verified'],
    'rejected'   => ['color' => '#991b1b', 'bg' => '#fef2f2', 'border' => '#fca5a5', 'icon' => 'fa-times-circle', 'label' => 'Action Required'],
];
$sc = $statusConfig[$kycStatus] ?? $statusConfig['unverified'];
?>

<!-- KYC Status Banner -->
<div style="background:<?= $sc['bg'] ?>;border:1.5px solid <?= $sc['border'] ?>;border-radius:10px;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;gap:12px;flex-wrap:wrap">
  <div style="display:flex;align-items:center;gap:14px">
    <div style="width:46px;height:46px;border-radius:50%;background:<?= $sc['border'] ?>;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:<?= $sc['color'] ?>">
      <i class="fas <?= $sc['icon'] ?>"></i>
    </div>
    <div>
      <div style="font-weight:800;font-size:1rem;color:<?= $sc['color'] ?>">KYC Status: <?= $sc['label'] ?></div>
      <div style="font-size:.82rem;color:<?= $sc['color'] ?>;opacity:.75;margin-top:2px">
        <?php if ($kycStatus === 'unverified'): ?>
          Upload your documents below to start the verification process.
        <?php elseif ($kycStatus === 'pending'): ?>
          <?= $pendingCount ?> document<?= $pendingCount !== 1 ? 's' : '' ?> submitted and awaiting admin review.
        <?php elseif ($kycStatus === 'verified'): ?>
          Your identity has been verified. You have full access to all payment features.
        <?php elseif ($kycStatus === 'rejected'): ?>
          One or more documents were rejected. Review the notes below and re-upload corrected documents.
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php if ($kycStatus === 'verified'): ?>
    <span style="background:#158347;color:#fff;padding:6px 16px;border-radius:20px;font-size:.8rem;font-weight:700;display:flex;align-items:center;gap:6px">
      <i class="fas fa-shield-alt"></i> Fully Verified
    </span>
  <?php endif; ?>
</div>

<!-- Info box for live mode requirement -->
<?php if ($kycStatus !== 'verified'): ?>
<div class="alert alert-warning" style="margin-bottom:24px">
  <i class="fas fa-exclamation-triangle"></i>
  KYC verification is required before you can switch to <strong>Live Mode</strong> and process real payments.
  <?php if ($kycStatus === 'pending'): ?>
    Your documents are currently being reviewed — this typically takes 1–2 business days.
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Documents Grid -->
<div class="section-hd" style="margin-bottom:16px">
  <div>
    <h3 style="font-size:1rem;font-weight:700;color:var(--navy)">Your Documents</h3>
    <p style="font-size:.83rem;color:var(--text-muted)">Upload clear, legible scans or photos. Accepted formats: JPG, PNG, PDF. Max size: 5 MB per file.</p>
  </div>
</div>

<div class="kyc-doc-grid">
<?php foreach ($docTypes as $type => $cfg): ?>
  <?php $doc = $latest[$type] ?? null; ?>
  <div class="kyc-doc-card <?= $doc ? 'kyc-has-doc' : '' ?> <?= ($doc && $doc['status'] === 'rejected') ? 'kyc-rejected' : '' ?>">

    <!-- Card Header -->
    <div class="kyc-card-hd">
      <div class="kyc-card-icon">
        <i class="fas <?= $cfg['icon'] ?>"></i>
      </div>
      <div style="flex:1">
        <div class="kyc-card-title">
          <?= $cfg['label'] ?>
          <?php if ($cfg['required']): ?>
            <span class="kyc-badge-required">Required</span>
          <?php else: ?>
            <span class="kyc-badge-optional">Optional</span>
          <?php endif; ?>
        </div>
        <div class="kyc-card-desc"><?= $cfg['desc'] ?></div>
      </div>
    </div>

    <!-- Current document status -->
    <?php if ($doc): ?>
    <div class="kyc-doc-status kyc-doc-status-<?= $doc['status'] ?>">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
        <div style="display:flex;align-items:center;gap:8px">
          <i class="fas <?= $doc['status'] === 'approved' ? 'fa-check-circle' : ($doc['status'] === 'rejected' ? 'fa-times-circle' : 'fa-hourglass-half') ?>"></i>
          <span style="font-weight:600;font-size:.82rem">
            <?= $doc['status'] === 'approved' ? 'Approved' : ($doc['status'] === 'rejected' ? 'Rejected' : 'Pending Review') ?>
          </span>
        </div>
        <span style="font-size:.75rem;opacity:.7"><?= date('d M Y', strtotime($doc['created_at'])) ?></span>
      </div>
      <?php if ($doc['status'] === 'rejected' && $doc['review_notes']): ?>
        <div class="kyc-reject-note">
          <i class="fas fa-comment-alt" style="margin-right:4px"></i>
          Admin note: <em><?= sanitize($doc['review_notes']) ?></em>
        </div>
      <?php endif; ?>
      <?php if ($doc['file_path']): ?>
        <a href="<?= APP_URL ?>/<?= sanitize($doc['file_path']) ?>" target="_blank" rel="noopener" class="kyc-view-link">
          <i class="fas fa-external-link-alt"></i> View Uploaded File
        </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Upload form — show if no doc, or if rejected -->
    <?php if (!$doc || $doc['status'] === 'rejected'): ?>
    <form class="kyc-upload-form" method="POST" action="<?= APP_URL ?>/dashboard/kyc/upload" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="doc_type" value="<?= $type ?>">

      <label class="kyc-dropzone" for="file_<?= $type ?>">
        <input type="file" id="file_<?= $type ?>" name="document" accept=".jpg,.jpeg,.png,.pdf" required
               onchange="kycPreview(this, '<?= $type ?>')">
        <div class="kyc-dropzone-inner" id="dz_<?= $type ?>">
          <i class="fas fa-cloud-upload-alt"></i>
          <span><?= $doc ? 'Upload replacement document' : 'Click or drag file here' ?></span>
          <small>JPG, PNG or PDF — max 5 MB</small>
        </div>
      </label>

      <button type="submit" class="kyc-submit-btn" id="submit_<?= $type ?>" disabled>
        <i class="fas fa-upload"></i> <?= $doc ? 'Re-upload Document' : 'Submit Document' ?>
      </button>
    </form>
    <?php elseif ($doc['status'] === 'approved'): ?>
    <div style="padding:10px 0 0;text-align:center;font-size:.8rem;color:var(--text-muted)">
      Document approved — no action needed.
    </div>
    <?php else: ?>
    <div style="padding:10px 0 0;text-align:center;font-size:.8rem;color:var(--text-muted)">
      Awaiting admin review — no action needed.
    </div>
    <?php endif; ?>

  </div>
<?php endforeach; ?>
</div>

<!-- Submission History -->
<?php if (!empty($allDocs)): ?>
<div style="margin-top:36px">
  <h3 style="font-size:1rem;font-weight:700;color:var(--navy);margin-bottom:12px">Submission History</h3>
  <div class="card">
    <div class="table-wrap p-0">
      <table class="orb-table">
        <thead>
          <tr>
            <th>Document Type</th>
            <th>Status</th>
            <th>Admin Notes</th>
            <th>Submitted</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allDocs as $h): ?>
          <tr>
            <td style="font-weight:600;font-size:.84rem"><?= ucwords(str_replace('_', ' ', $h['doc_type'])) ?></td>
            <td>
              <span class="badge <?= $h['status'] === 'approved' ? 'badge-success' : ($h['status'] === 'rejected' ? 'badge-danger' : 'badge-warning') ?>">
                <?= ucfirst($h['status']) ?>
              </span>
            </td>
            <td style="font-size:.8rem;color:var(--text-muted);max-width:240px">
              <?= $h['review_notes'] ? sanitize($h['review_notes']) : '—' ?>
            </td>
            <td style="font-size:.78rem;color:var(--text-muted)"><?= date('d M Y H:i', strtotime($h['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<style>
.kyc-doc-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}
.kyc-doc-card {
  background: #fff;
  border: 1.5px solid var(--border);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  transition: border-color .15s;
}
.kyc-doc-card.kyc-rejected { border-color: #fca5a5; }
.kyc-doc-card.kyc-has-doc  { border-color: #cbd5e1; }

.kyc-card-hd { display: flex; align-items: flex-start; gap: 12px; }
.kyc-card-icon {
  width: 44px; height: 44px;
  background: var(--green-light, #e8f5ee);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.15rem; color: var(--green, #158347);
  flex-shrink: 0;
}
.kyc-card-title { font-weight: 700; font-size: .9rem; color: var(--navy); display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.kyc-card-desc  { font-size: .78rem; color: var(--text-muted); margin-top: 3px; line-height: 1.5; }
.kyc-badge-required { background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 20px; font-size: .68rem; font-weight: 700; }
.kyc-badge-optional { background: #f1f5f9; color: #64748b; padding: 2px 8px; border-radius: 20px; font-size: .68rem; font-weight: 600; }

.kyc-doc-status {
  border-radius: 8px;
  padding: 10px 14px;
  font-size: .82rem;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.kyc-doc-status-pending  { background: #fffbeb; color: #92400e; border: 1px solid #fcd34d; }
.kyc-doc-status-approved { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
.kyc-doc-status-rejected { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }

.kyc-reject-note {
  font-size: .78rem;
  background: rgba(153,27,27,.06);
  border-radius: 5px;
  padding: 6px 10px;
  margin-top: 4px;
  line-height: 1.5;
}
.kyc-view-link {
  font-size: .75rem;
  color: var(--green, #158347);
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-top: 2px;
}
.kyc-view-link:hover { text-decoration: underline; }

.kyc-upload-form { display: flex; flex-direction: column; gap: 10px; }
.kyc-dropzone {
  display: block;
  border: 2px dashed var(--border, #dde2ec);
  border-radius: 8px;
  cursor: pointer;
  transition: border-color .15s, background .15s;
  overflow: hidden;
}
.kyc-dropzone:hover, .kyc-dropzone.drag-over {
  border-color: var(--green, #158347);
  background: var(--green-light, #e8f5ee);
}
.kyc-dropzone input[type=file] {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}
.kyc-dropzone-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 18px;
  gap: 4px;
  text-align: center;
}
.kyc-dropzone-inner i    { font-size: 1.6rem; color: var(--text-muted, #64748b); margin-bottom: 4px; }
.kyc-dropzone-inner span { font-size: .83rem; font-weight: 600; color: var(--navy, #0D1B3E); }
.kyc-dropzone-inner small{ font-size: .73rem; color: var(--text-muted, #64748b); }

.kyc-submit-btn {
  width: 100%;
  padding: 10px 18px;
  background: var(--green, #158347);
  color: #fff;
  border: none;
  border-radius: 7px;
  font-size: .875rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  transition: background .15s, opacity .15s;
}
.kyc-submit-btn:disabled { opacity: .45; cursor: not-allowed; }
.kyc-submit-btn:not(:disabled):hover { background: #0f6435; }
</style>

<script>
function kycPreview(input, type) {
  const dz  = document.getElementById('dz_' + type);
  const btn = document.getElementById('submit_' + type);
  const file = input.files[0];
  if (!file) { btn.disabled = true; return; }

  const allowed = ['image/jpeg','image/png','application/pdf'];
  const maxSize = 5 * 1024 * 1024;

  if (!allowed.includes(file.type)) {
    alert('Only JPG, PNG and PDF files are accepted.');
    input.value = '';
    btn.disabled = true;
    return;
  }
  if (file.size > maxSize) {
    alert('File size must not exceed 5 MB.');
    input.value = '';
    btn.disabled = true;
    return;
  }

  const icon = dz.querySelector('i');
  const label = dz.querySelector('span');
  const hint  = dz.querySelector('small');
  const ext   = file.name.split('.').pop().toUpperCase();
  icon.className  = 'fas ' + (ext === 'PDF' ? 'fa-file-pdf' : 'fa-file-image');
  icon.style.color = '#158347';
  label.textContent = file.name.length > 32 ? file.name.substring(0,29) + '...' : file.name;
  hint.textContent  = (file.size / 1024 < 1024
    ? (file.size / 1024).toFixed(1) + ' KB'
    : (file.size / 1024 / 1024).toFixed(2) + ' MB');

  btn.disabled = false;
}

// Drag-and-drop on dropzones
document.querySelectorAll('.kyc-dropzone').forEach(zone => {
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    const input = zone.querySelector('input[type=file]');
    if (input && e.dataTransfer.files.length) {
      input.files = e.dataTransfer.files;
      input.dispatchEvent(new Event('change'));
    }
  });
});

// Show upload progress on submit
document.querySelectorAll('.kyc-upload-form').forEach(form => {
  form.addEventListener('submit', function() {
    const btn = this.querySelector('.kyc-submit-btn');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading…';
    }
  });
});
</script>
