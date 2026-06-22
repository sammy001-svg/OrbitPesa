<?php
$txns      = WalletTransaction::getForUser($wu['id'], 15);
$txnTotal  = WalletTransaction::countForUser($wu['id']);
$txnStats  = DB::fetch(
    "SELECT
       COUNT(*) as total,
       COALESCE(SUM(CASE WHEN type IN ('receive','deposit','cashback') THEN amount ELSE 0 END),0) as total_in,
       COALESCE(SUM(CASE WHEN type NOT IN ('receive','deposit','cashback') THEN amount ELSE 0 END),0) as total_out
     FROM wallet_transactions WHERE wallet_user_id=? AND status='completed'",
    [$wu['id']]
) ?? [];

// KYC documents
$kycDocs   = DB::fetchAll(
    "SELECT * FROM wallet_kyc_documents WHERE wallet_user_id=? ORDER BY created_at DESC",
    [$wu['id']]
);
$kycStatus = $wu['kyc_status'] ?? 'unverified';
$kycLabels = [
    'unverified' => ['chip' => '', 'label' => 'Unverified',   'color' => '#94a3b8', 'bg' => '#f1f5f9'],
    'pending'    => ['chip' => 'orange', 'label' => 'Pending Review','color' => '#d97706', 'bg' => '#fffbeb'],
    'approved'   => ['chip' => 'green',  'label' => 'KYC Approved',  'color' => '#158347', 'bg' => '#f0fdf4'],
    'rejected'   => ['chip' => '',       'label' => 'KYC Rejected',  'color' => '#dc2626', 'bg' => '#fef2f2'],
];
$ks = $kycLabels[$kycStatus] ?? $kycLabels['unverified'];
?>

<div class="section-hd">
  <div>
    <a href="<?= APP_URL ?>/admin/wallet-users" style="color:var(--text-muted);font-size:.84rem;text-decoration:none">
      <i class="fas fa-arrow-left"></i> Back to Wallet Users
    </a>
    <h2 style="margin-top:6px"><?= sanitize($wu['full_name']) ?></h2>
    <p><?= sanitize($wu['email']) ?> · <?= sanitize($wu['wallet_id']) ?></p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <?php if ($wu['status'] === 'active'): ?>
    <form method="POST" action="<?= APP_URL ?>/admin/wallet-users/suspend" style="display:inline">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= sanitize($wu['id']) ?>">
      <button type="submit" class="btn btn-danger btn-sm" data-confirm="Suspend this wallet user?">
        <i class="fas fa-ban"></i> Suspend
      </button>
    </form>
    <?php else: ?>
    <form method="POST" action="<?= APP_URL ?>/admin/wallet-users/activate" style="display:inline">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= sanitize($wu['id']) ?>">
      <button type="submit" class="btn btn-primary btn-sm" data-confirm="Activate this wallet user?">
        <i class="fas fa-check-circle"></i> Activate
      </button>
    </form>
    <?php endif; ?>
    <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('adjustModal').style.display='flex'">
      <i class="fas fa-sliders-h"></i> Adjust Balance
    </button>
  </div>
</div>

<!-- Profile + Stats Grid -->
<div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;margin-bottom:24px">

  <!-- Profile Card -->
  <div class="card" style="padding:24px">
    <div style="text-align:center;margin-bottom:20px">
      <div style="width:72px;height:72px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.6rem;color:#fff;margin:0 auto 12px">
        <?= strtoupper(substr($wu['full_name'], 0, 1)) ?>
      </div>
      <div style="font-weight:700;font-size:1.1rem"><?= sanitize($wu['full_name']) ?></div>
      <div style="font-size:.8rem;color:var(--text-muted);margin-top:3px"><?= sanitize($wu['wallet_id']) ?></div>
      <div style="margin-top:8px">
        <?php if ($wu['status'] === 'active'): ?>
          <span class="chip green"><i class="fas fa-circle" style="font-size:.5rem"></i> Active</span>
        <?php else: ?>
          <span class="chip" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca"><i class="fas fa-ban"></i> Suspended</span>
        <?php endif; ?>
      </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:12px;font-size:.84rem">
      <div style="display:flex;align-items:flex-start;gap:10px">
        <i class="fas fa-envelope" style="color:var(--text-muted);width:16px;margin-top:2px"></i>
        <div>
          <div style="color:var(--text-muted);font-size:.73rem;text-transform:uppercase;letter-spacing:.04em">Email</div>
          <div style="font-weight:600"><?= sanitize($wu['email']) ?></div>
        </div>
      </div>
      <div style="display:flex;align-items:flex-start;gap:10px">
        <i class="fas fa-phone" style="color:var(--text-muted);width:16px;margin-top:2px"></i>
        <div>
          <div style="color:var(--text-muted);font-size:.73rem;text-transform:uppercase;letter-spacing:.04em">Phone</div>
          <div style="font-weight:600"><?= sanitize($wu['phone']) ?></div>
        </div>
      </div>
      <?php if ($wu['national_id']): ?>
      <div style="display:flex;align-items:flex-start;gap:10px">
        <i class="fas fa-id-card" style="color:var(--text-muted);width:16px;margin-top:2px"></i>
        <div>
          <div style="color:var(--text-muted);font-size:.73rem;text-transform:uppercase;letter-spacing:.04em">National ID</div>
          <div style="font-weight:600"><?= sanitize($wu['national_id']) ?></div>
        </div>
      </div>
      <?php endif; ?>
      <div style="display:flex;align-items:flex-start;gap:10px">
        <i class="fas fa-calendar" style="color:var(--text-muted);width:16px;margin-top:2px"></i>
        <div>
          <div style="color:var(--text-muted);font-size:.73rem;text-transform:uppercase;letter-spacing:.04em">Joined</div>
          <div style="font-weight:600"><?= date('d M Y', strtotime($wu['created_at'])) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats -->
  <div style="display:flex;flex-direction:column;gap:14px">
    <!-- Balance -->
    <div class="card" style="padding:20px 24px;background:var(--navy);color:#fff">
      <div style="font-size:.8rem;opacity:.7;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Current Balance</div>
      <div style="font-size:2.2rem;font-weight:800;letter-spacing:-.02em"><?= format_amount($wu['balance']) ?></div>
      <div style="font-size:.78rem;opacity:.6;margin-top:4px">OrbitPesa Wallet</div>
    </div>
    <!-- Tx Stats -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
      <div class="card" style="padding:16px 18px;text-align:center;border-top:3px solid var(--navy)">
        <div style="font-size:1.4rem;font-weight:800;color:var(--navy)"><?= number_format($txnTotal) ?></div>
        <div style="font-size:.73rem;color:var(--text-muted);margin-top:2px">Total Transactions</div>
      </div>
      <div class="card" style="padding:16px 18px;text-align:center;border-top:3px solid var(--green)">
        <div style="font-size:1.4rem;font-weight:800;color:var(--green)"><?= format_amount($txnStats['total_in'] ?? 0) ?></div>
        <div style="font-size:.73rem;color:var(--text-muted);margin-top:2px">Total Money In</div>
      </div>
      <div class="card" style="padding:16px 18px;text-align:center;border-top:3px solid #dc2626">
        <div style="font-size:1.4rem;font-weight:800;color:#dc2626"><?= format_amount($txnStats['total_out'] ?? 0) ?></div>
        <div style="font-size:.73rem;color:var(--text-muted);margin-top:2px">Total Money Out</div>
      </div>
    </div>
  </div>
</div>

<!-- KYC Documents Panel -->
<div class="card mb-5">
  <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
    <h4>
      <i class="fas fa-shield-halved" style="color:var(--green);margin-right:6px"></i>
      KYC Verification
    </h4>
    <span style="background:<?= $ks['bg'] ?>;color:<?= $ks['color'] ?>;border-radius:8px;padding:4px 12px;font-size:.78rem;font-weight:700">
      <?= $ks['label'] ?>
    </span>
  </div>
  <?php if (empty($kycDocs)): ?>
  <div class="empty-state" style="padding:30px 0">
    <i class="fas fa-id-card" style="font-size:1.8rem;color:#cbd5e1;display:block;margin-bottom:8px"></i>
    <p>No documents submitted yet.</p>
  </div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="orb-table">
      <thead>
        <tr>
          <th>Document Type</th>
          <th>File</th>
          <th>Submitted</th>
          <th>Status</th>
          <th>Notes</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $docTypeLabels = [
            'national_id_front' => 'National ID (Front)',
            'national_id_back'  => 'National ID (Back)',
            'passport'          => 'Passport',
            'selfie'            => 'Selfie with ID',
        ];
        $docStatusStyles = [
            'pending'  => 'chip orange',
            'approved' => 'chip green',
            'rejected' => 'chip',
        ];
        foreach ($kycDocs as $doc):
          $ds = $docStatusStyles[$doc['status']] ?? 'chip';
        ?>
        <tr>
          <td style="font-weight:600;font-size:.84rem"><?= htmlspecialchars($docTypeLabels[$doc['doc_type']] ?? $doc['doc_type']) ?></td>
          <td>
            <a href="<?= APP_URL ?>/<?= htmlspecialchars($doc['file_path']) ?>" target="_blank"
               class="btn btn-ghost btn-sm" style="font-size:.76rem">
              <i class="fas fa-eye"></i> View
            </a>
          </td>
          <td style="font-size:.8rem;color:var(--text-muted)"><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
          <td>
            <span class="<?= $ds ?>" style="<?= $doc['status'] === 'rejected' ? 'background:#fef2f2;color:#dc2626;border:1px solid #fecaca' : '' ?>">
              <?= ucfirst($doc['status']) ?>
            </span>
          </td>
          <td style="font-size:.8rem;color:var(--text-muted);max-width:160px">
            <?= $doc['admin_notes'] ? htmlspecialchars(mb_substr($doc['admin_notes'], 0, 60)) : '—' ?>
          </td>
          <td>
            <?php if ($doc['status'] === 'pending'): ?>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              <form method="POST" action="<?= APP_URL ?>/admin/wallet-users/kyc-approve" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="wallet_user_id" value="<?= sanitize($wu['id']) ?>">
                <input type="hidden" name="doc_id" value="<?= sanitize($doc['id']) ?>">
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="btn btn-primary btn-sm"
                        data-confirm="Approve this document?">
                  <i class="fas fa-check"></i> Approve
                </button>
              </form>
              <button type="button" class="btn btn-danger btn-sm"
                      onclick="openRejectModal('<?= addslashes($doc['id']) ?>','<?= addslashes($wu['id']) ?>')">
                <i class="fas fa-times"></i> Reject
              </button>
            </div>
            <?php else: ?>
            <span style="color:#94a3b8;font-size:.8rem">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- Reject KYC Document Modal -->
<div id="rejectKycModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;padding:28px 32px;max-width:420px;width:90%;box-shadow:0 24px 60px rgba(0,0,0,.2)">
    <h3 style="margin:0 0 16px;color:var(--navy)">Reject KYC Document</h3>
    <form method="POST" action="<?= APP_URL ?>/admin/wallet-users/kyc-approve">
      <?= csrf_field() ?>
      <input type="hidden" name="wallet_user_id" id="rkWuId" value="">
      <input type="hidden" name="doc_id" id="rkDocId" value="">
      <input type="hidden" name="action" value="reject">
      <div style="margin-bottom:14px">
        <label style="font-size:.82rem;font-weight:600;display:block;margin-bottom:6px">Rejection Reason</label>
        <textarea name="notes" rows="3" required class="form-control"
                  placeholder="Explain why the document was rejected…"></textarea>
      </div>
      <div style="display:flex;gap:10px">
        <button type="button" class="btn btn-ghost" onclick="document.getElementById('rejectKycModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn btn-danger">Reject Document</button>
      </div>
    </form>
  </div>
</div>

<!-- Transactions -->
<div class="card">
  <div class="card-header">
    <h4><i class="fas fa-history" style="color:var(--green);margin-right:6px"></i> Recent Transactions</h4>
    <a href="<?= APP_URL ?>/admin/wallet-transactions?user_id=<?= urlencode($wu['id']) ?>" class="btn btn-ghost btn-sm">View All</a>
  </div>
  <?php if (empty($txns)): ?>
    <div class="empty-state" style="padding:40px 0">
      <i class="fas fa-inbox"></i>
      <h4>No transactions yet</h4>
    </div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="orb-table">
      <thead>
        <tr><th>Reference</th><th>Type</th><th>Amount</th><th>Balance After</th><th>Description</th><th>Time</th></tr>
      </thead>
      <tbody>
        <?php foreach ($txns as $t):
          $isCredit = WalletTransaction::isCredit($t['type']);
          $typeLabel = WalletTransaction::typeLabel($t['type']);
          $typeIcon  = WalletTransaction::typeIcon($t['type']);
          $typeColor = WalletTransaction::typeColor($t['type']);
        ?>
        <tr>
          <td><code style="font-size:.77rem"><?= sanitize($t['reference']) ?></code></td>
          <td>
            <span class="chip" style="background:<?= $typeColor ?>1a;color:<?= $typeColor ?>;border:1px solid <?= $typeColor ?>33">
              <i class="fas fa-<?= $typeIcon ?>"></i> <?= $typeLabel ?>
            </span>
          </td>
          <td style="font-weight:700;color:<?= $isCredit ? 'var(--green)' : '#dc2626' ?>">
            <?= $isCredit ? '+' : '-' ?><?= format_amount($t['amount']) ?>
          </td>
          <td style="font-weight:600;color:var(--navy)"><?= format_amount($t['balance_after']) ?></td>
          <td style="font-size:.8rem;color:var(--text-muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <?= sanitize($t['description'] ?? '') ?>
          </td>
          <td style="font-size:.78rem;color:var(--text-muted)"><?= time_ago($t['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- Adjust Balance Modal -->
<div id="adjustModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;padding:28px 32px;max-width:420px;width:90%;box-shadow:0 24px 60px rgba(0,0,0,.2)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h3 style="margin:0;color:var(--navy)">Adjust Balance</h3>
      <button onclick="document.getElementById('adjustModal').style.display='none'"
              style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--text-muted)">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div style="background:#f8fafc;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:.84rem">
      <div style="color:var(--text-muted)">Current Balance</div>
      <div style="font-size:1.3rem;font-weight:800;color:var(--navy)"><?= format_amount($wu['balance']) ?></div>
    </div>
    <form method="POST" action="<?= APP_URL ?>/admin/wallet-users/adjust-balance">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= sanitize($wu['id']) ?>">

      <div style="margin-bottom:14px">
        <label style="font-size:.82rem;font-weight:600;display:block;margin-bottom:6px">Operation</label>
        <div style="display:flex;gap:10px">
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;flex:1;padding:10px 14px;border:2px solid transparent;border-radius:8px;background:#f0fdf4" id="creditLabel">
            <input type="radio" name="type" value="credit" checked onchange="toggleAdjust(this)"> <span style="font-weight:600;color:var(--green)"><i class="fas fa-plus-circle"></i> Credit</span>
          </label>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;flex:1;padding:10px 14px;border:2px solid transparent;border-radius:8px;background:#fef2f2" id="debitLabel">
            <input type="radio" name="type" value="debit"  onchange="toggleAdjust(this)"> <span style="font-weight:600;color:#dc2626"><i class="fas fa-minus-circle"></i> Debit</span>
          </label>
        </div>
      </div>

      <div style="margin-bottom:14px">
        <label style="font-size:.82rem;font-weight:600;display:block;margin-bottom:6px">Amount (KES)</label>
        <input type="number" name="amount" min="1" step="0.01" placeholder="0.00" required
               class="form-control">
      </div>

      <div style="margin-bottom:20px">
        <label style="font-size:.82rem;font-weight:600;display:block;margin-bottom:6px">Reason / Note</label>
        <input type="text" name="reason" placeholder="e.g. Refund, Correction, Promotion…" required
               class="form-control">
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn-ghost" onclick="document.getElementById('adjustModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn btn-primary">Apply Adjustment</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleAdjust(radio) {
  document.getElementById('creditLabel').style.border = '2px solid transparent';
  document.getElementById('debitLabel').style.border  = '2px solid transparent';
  if (radio.value === 'credit') document.getElementById('creditLabel').style.border = '2px solid var(--green)';
  else document.getElementById('debitLabel').style.border = '2px solid #dc2626';
}
// Highlight default
document.getElementById('creditLabel').style.border = '2px solid var(--green)';

function openRejectModal(docId, wuId) {
  document.getElementById('rkDocId').value = docId;
  document.getElementById('rkWuId').value  = wuId;
  document.getElementById('rejectKycModal').style.display = 'flex';
}
document.getElementById('rejectKycModal')?.addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});
</script>
