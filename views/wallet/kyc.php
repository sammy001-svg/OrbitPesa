<?php
/**
 * OrbitPesa Wallet — KYC / Identity Verification
 * Mobile-first page rendered by renderWallet('kyc')
 */
$pageTitle = 'Verify Identity';
$backUrl   = APP_URL . '/wallet/profile';
$wu        = $walletUser;

// Fetch existing submitted documents
$docs = DB::fetchAll(
    "SELECT * FROM wallet_kyc_documents WHERE wallet_user_id = ? ORDER BY created_at DESC",
    [$wu['id']]
);

// Index by doc_type for easy status look-up
$docStatus = [];
foreach ($docs as $d) {
    if (!isset($docStatus[$d['doc_type']])) {
        $docStatus[$d['doc_type']] = $d;
    }
}

$kycStatus = $wu['kyc_status'] ?? 'unverified';

$statusLabels = [
    'unverified' => ['label' => 'Not Verified',   'color' => '#94a3b8', 'bg' => '#f1f5f9', 'icon' => 'fa-circle-question'],
    'pending'    => ['label' => 'Under Review',   'color' => '#d97706', 'bg' => '#fffbeb', 'icon' => 'fa-clock'],
    'approved'   => ['label' => 'Verified ✓',     'color' => '#158347', 'bg' => '#f0fdf4', 'icon' => 'fa-circle-check'],
    'rejected'   => ['label' => 'Rejected',        'color' => '#dc2626', 'bg' => '#fef2f2', 'icon' => 'fa-circle-xmark'],
];
$s = $statusLabels[$kycStatus] ?? $statusLabels['unverified'];

$docTypes = [
    'national_id_front' => ['label' => 'National ID — Front',  'icon' => 'fa-id-card',    'desc' => 'Photo of the front side of your national ID'],
    'national_id_back'  => ['label' => 'National ID — Back',   'icon' => 'fa-id-card',    'desc' => 'Photo of the back side of your national ID'],
    'passport'          => ['label' => 'Passport',             'icon' => 'fa-passport',   'desc' => 'Photo of the personal data page of your passport'],
    'selfie'            => ['label' => 'Selfie with ID',       'icon' => 'fa-camera',     'desc' => 'Hold your ID next to your face (clear lighting)'],
];

$docStatusStyles = [
    'pending'  => ['color' => '#d97706', 'bg' => '#fffbeb', 'icon' => 'fa-clock',        'text' => 'Under Review'],
    'approved' => ['color' => '#158347', 'bg' => '#f0fdf4', 'icon' => 'fa-circle-check', 'text' => 'Approved'],
    'rejected' => ['color' => '#dc2626', 'bg' => '#fef2f2', 'icon' => 'fa-circle-xmark', 'text' => 'Rejected'],
];
?>

<!-- Overall KYC status banner -->
<div style="margin:14px 14px 0;border-radius:16px;padding:18px;display:flex;align-items:center;gap:14px;
            background:<?= $s['bg'] ?>;border:1.5px solid <?= $s['color'] ?>22">
  <div style="width:46px;height:46px;border-radius:50%;background:<?= $s['color'] ?>1a;
              display:flex;align-items:center;justify-content:center;flex-shrink:0">
    <i class="fas <?= $s['icon'] ?>" style="color:<?= $s['color'] ?>;font-size:1.3rem"></i>
  </div>
  <div>
    <div style="font-weight:800;font-size:.92rem;color:#0D1B3E">Identity Verification</div>
    <div style="font-size:.78rem;color:<?= $s['color'] ?>;font-weight:600;margin-top:2px"><?= $s['label'] ?></div>
    <div style="font-size:.72rem;color:#64748b;margin-top:3px">
      <?php if ($kycStatus === 'unverified'): ?>
        Upload your ID documents to unlock higher transaction limits.
      <?php elseif ($kycStatus === 'pending'): ?>
        Your documents are being reviewed. This typically takes 1–2 business days.
      <?php elseif ($kycStatus === 'approved'): ?>
        Your identity is verified. Thank you!
      <?php else: ?>
        Some documents were rejected. Please re-upload corrected versions.
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($kycStatus !== 'approved'): ?>
<!-- Upload instructions -->
<div style="margin:14px 14px 0;background:#eff6ff;border-radius:14px;padding:14px 16px;font-size:.75rem;color:#1e40af;line-height:1.6">
  <i class="fas fa-circle-info" style="margin-right:4px"></i>
  <strong>What we accept:</strong> JPG, PNG, or PDF · Max 5 MB per file · Documents must be clear and unobstructed
</div>

<!-- Document upload cards -->
<div style="margin-top:10px">
  <?php foreach ($docTypes as $type => $info):
    $existing = $docStatus[$type] ?? null;
    $dss = $existing ? ($docStatusStyles[$existing['status']] ?? null) : null;
  ?>
  <div style="margin:10px 14px;background:white;border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05)">

    <!-- Card header -->
    <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid #f1f5f9">
      <div style="width:38px;height:38px;border-radius:12px;background:#f0f4ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fas <?= $info['icon'] ?>" style="color:#3b5bdb;font-size:1rem"></i>
      </div>
      <div style="flex:1;min-width:0">
        <div style="font-weight:700;font-size:.85rem;color:#0D1B3E"><?= htmlspecialchars($info['label']) ?></div>
        <div style="font-size:.72rem;color:#64748b;margin-top:2px"><?= htmlspecialchars($info['desc']) ?></div>
      </div>
      <?php if ($dss): ?>
      <div style="display:flex;align-items:center;gap:4px;background:<?= $dss['bg'] ?>;color:<?= $dss['color'] ?>;
                  border-radius:8px;padding:4px 9px;font-size:.68rem;font-weight:700;flex-shrink:0;white-space:nowrap">
        <i class="fas <?= $dss['icon'] ?>"></i> <?= $dss['text'] ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Upload form -->
    <?php if (!$existing || $existing['status'] === 'rejected'): ?>
    <form method="POST" action="<?= APP_URL ?>/wallet/kyc/upload" enctype="multipart/form-data" style="padding:14px 16px">
      <?= csrf_field() ?>
      <input type="hidden" name="doc_type" value="<?= htmlspecialchars($type) ?>">
      <?php if ($existing && $existing['status'] === 'rejected' && $existing['admin_notes']): ?>
      <div style="margin-bottom:10px;padding:8px 12px;background:#fef2f2;border-radius:10px;font-size:.75rem;color:#dc2626">
        <i class="fas fa-circle-exclamation"></i> Rejection reason: <?= htmlspecialchars($existing['admin_notes']) ?>
      </div>
      <?php endif; ?>
      <label style="display:block;cursor:pointer">
        <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required
               style="display:none" id="file-<?= htmlspecialchars($type) ?>"
               onchange="showFilename(this, 'fn-<?= htmlspecialchars($type) ?>')">
        <div id="fn-<?= htmlspecialchars($type) ?>"
             style="border:2px dashed #cbd5e1;border-radius:12px;padding:16px;text-align:center;
                    cursor:pointer;color:#94a3b8;font-size:.8rem;transition:border-color .2s"
             onclick="document.getElementById('file-<?= htmlspecialchars($type) ?>').click()">
          <i class="fas fa-cloud-arrow-up" style="font-size:1.4rem;display:block;margin-bottom:6px"></i>
          <span class="fn-text">Tap to choose file</span>
          <div style="font-size:.7rem;margin-top:3px">JPG · PNG · PDF · max 5 MB</div>
        </div>
      </label>
      <button type="submit"
              style="width:100%;margin-top:10px;background:#158347;color:#fff;border:none;border-radius:12px;
                     padding:11px;font-weight:700;font-size:.84rem;cursor:pointer;font-family:inherit;
                     display:flex;align-items:center;justify-content:center;gap:6px">
        <i class="fas fa-upload"></i>
        <?= ($existing && $existing['status'] === 'rejected') ? 'Re-upload Document' : 'Upload Document' ?>
      </button>
    </form>
    <?php elseif ($existing['status'] === 'pending'): ?>
    <div style="padding:12px 16px;font-size:.78rem;color:#64748b;display:flex;align-items:center;gap:8px">
      <i class="fas fa-hourglass-half" style="color:#d97706"></i>
      Submitted <?= date('d M Y', strtotime($existing['created_at'])) ?> — awaiting review
    </div>
    <?php elseif ($existing['status'] === 'approved'): ?>
    <div style="padding:12px 16px;font-size:.78rem;color:#158347;display:flex;align-items:center;gap:8px">
      <i class="fas fa-circle-check"></i>
      Approved on <?= date('d M Y', strtotime($existing['reviewed_at'] ?? $existing['created_at'])) ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<?php else: ?>
<!-- Verified state -->
<div style="margin:20px 14px;background:white;border-radius:20px;padding:30px;text-align:center;box-shadow:0 2px 12px rgba(0,0,0,.05)">
  <div style="width:64px;height:64px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
    <i class="fas fa-shield-halved" style="color:#158347;font-size:1.8rem"></i>
  </div>
  <div style="font-weight:800;font-size:1.05rem;color:#0D1B3E;margin-bottom:8px">Identity Verified</div>
  <div style="font-size:.82rem;color:#64748b;line-height:1.6">
    Your identity has been successfully verified. You now have full access to all OrbitPesa Wallet features.
  </div>
</div>
<?php endif; ?>

<div style="height:20px"></div>

<script>
function showFilename(input, targetId) {
  const box = document.getElementById(targetId);
  if (input.files && input.files[0]) {
    const name = input.files[0].name;
    const size = (input.files[0].size / 1024 / 1024).toFixed(2);
    box.style.borderColor = '#158347';
    box.style.background  = '#f0fdf4';
    box.querySelector('.fn-text').textContent = name + ' (' + size + ' MB)';
    box.querySelector('.fn-text').style.color = '#158347';
    box.querySelector('.fn-text').style.fontWeight = '700';
  }
}
</script>
