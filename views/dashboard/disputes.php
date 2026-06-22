<?php
/**
 * OrbitPesa Merchant Dashboard — Disputes
 * Route: GET /dashboard/disputes
 */

// Fetch merchant's disputes
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 15;
$offset  = ($page - 1) * $limit;

$disputes = DB::fetchAll(
    "SELECT d.*, t.amount as txn_amount, t.channel as txn_channel
     FROM disputes d
     LEFT JOIN transactions t ON t.reference = d.transaction_ref
     WHERE d.user_id = ?
     ORDER BY d.created_at DESC
     LIMIT ? OFFSET ?",
    [$_SESSION['user_id'], $limit, $offset]
);
$totalDisputes = (int)(DB::fetch(
    "SELECT COUNT(*) as c FROM disputes WHERE user_id = ?",
    [$_SESSION['user_id']]
)['c'] ?? 0);
$pages = (int)ceil($totalDisputes / $limit);

// Fetch merchant's completed transactions for dispute filing
$completedTxns = DB::fetchAll(
    "SELECT reference, amount, channel, description, created_at
     FROM transactions
     WHERE user_id = ? AND status = 'completed'
     ORDER BY created_at DESC
     LIMIT 200",
    [$_SESSION['user_id']]
);

$openCount  = (int)(DB::fetch(
    "SELECT COUNT(*) as c FROM disputes WHERE user_id = ? AND status = 'open'",
    [$_SESSION['user_id']]
)['c'] ?? 0);
$resolvedCount = (int)(DB::fetch(
    "SELECT COUNT(*) as c FROM disputes WHERE user_id = ? AND status IN ('resolved','closed')",
    [$_SESSION['user_id']]
)['c'] ?? 0);

$statusBadge = function(string $status): string {
    $map = [
        'open'         => ['color' => 'orange', 'label' => 'Open'],
        'under_review' => ['color' => 'navy',   'label' => 'Under Review'],
        'resolved'     => ['color' => 'green',  'label' => 'Resolved'],
        'closed'       => ['color' => 'gray',   'label' => 'Closed'],
    ];
    $s = $map[$status] ?? ['color' => 'gray', 'label' => ucfirst($status)];
    return '<span class="chip ' . $s['color'] . '">' . $s['label'] . '</span>';
};

$categories = [
    'unauthorised_charge' => 'Unauthorised Charge',
    'double_charge'       => 'Double Charge',
    'payment_not_received'=> 'Payment Not Received',
    'wrong_amount'        => 'Incorrect Amount Charged',
    'refund_not_received' => 'Refund Not Received',
    'fraud'               => 'Fraud / Suspicious Activity',
    'other'               => 'Other',
];
?>

<div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px">
  <div>
    <h2>Disputes & Chargebacks</h2>
    <p>File and track disputes for transactions on your account.</p>
  </div>
  <button class="btn btn-primary btn-sm" onclick="openModal('newDisputeModal')" id="btn-new-dispute">
    <i class="fas fa-plus"></i> File a Dispute
  </button>
</div>

<!-- Summary stats -->
<div class="grid-4 mb-6" style="grid-template-columns:repeat(3,1fr)">
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fas fa-folder-open"></i></div>
    <div><div class="stat-value" style="font-size:1.3rem"><?= $openCount ?></div><div class="stat-label">Open</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon navy"><i class="fas fa-magnifying-glass"></i></div>
    <div>
      <div class="stat-value" style="font-size:1.3rem">
        <?= (int)(DB::fetch("SELECT COUNT(*) as c FROM disputes WHERE user_id=? AND status='under_review'", [$_SESSION['user_id']])['c'] ?? 0) ?>
      </div>
      <div class="stat-label">Under Review</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-circle-check"></i></div>
    <div><div class="stat-value" style="font-size:1.3rem"><?= $resolvedCount ?></div><div class="stat-label">Resolved</div></div>
  </div>
</div>

<!-- Disputes table -->
<div class="card">
  <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
    <h4><i class="fas fa-scale-balanced" style="color:var(--green);margin-right:6px"></i>
      <?= number_format($totalDisputes) ?> Dispute<?= $totalDisputes !== 1 ? 's' : '' ?>
    </h4>
  </div>
  <?php if (empty($disputes)): ?>
  <div class="empty-state">
    <i class="fas fa-scale-balanced"></i>
    <h4>No disputes filed</h4>
    <p>If you believe a transaction was processed incorrectly, click <strong>File a Dispute</strong> above.</p>
  </div>
  <?php else: ?>
  <div class="table-wrap p-0">
    <table class="orb-table">
      <thead>
        <tr>
          <th>Transaction Ref</th>
          <th>Category</th>
          <th>Amount</th>
          <th>Reason</th>
          <th>Status</th>
          <th>Filed</th>
          <th>Resolution</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($disputes as $d): ?>
        <tr>
          <td><code style="font-size:.8rem"><?= sanitize($d['transaction_ref']) ?></code></td>
          <td>
            <span style="font-size:.8rem;color:var(--text-muted)">
              <?= htmlspecialchars($categories[$d['category'] ?? ''] ?? ucfirst(str_replace('_',' ', $d['category'] ?? ''))) ?>
            </span>
          </td>
          <td>
            <?php if ($d['txn_amount']): ?>
            <span style="font-weight:700;color:var(--navy)"><?= format_amount($d['txn_amount']) ?></span>
            <?php else: ?>—<?php endif; ?>
          </td>
          <td style="max-width:220px">
            <div class="truncate" style="font-size:.82rem" title="<?= htmlspecialchars($d['reason']) ?>">
              <?= htmlspecialchars(mb_substr($d['reason'], 0, 80)) ?>
            </div>
          </td>
          <td><?= $statusBadge($d['status']) ?></td>
          <td style="font-size:.8rem;color:var(--text-muted);white-space:nowrap">
            <?= date('d M Y', strtotime($d['created_at'])) ?>
          </td>
          <td style="max-width:200px">
            <?php if ($d['resolution']): ?>
            <div class="truncate" style="font-size:.8rem;color:var(--text-muted)" title="<?= htmlspecialchars($d['resolution']) ?>">
              <?= htmlspecialchars(mb_substr($d['resolution'], 0, 60)) ?>
            </div>
            <?php else: ?><span style="color:#94a3b8;font-size:.8rem">—</span><?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
  <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between">
    <span style="font-size:.82rem;color:var(--text-muted)">
      Showing <?= $offset + 1 ?>–<?= min($offset + $limit, $totalDisputes) ?> of <?= $totalDisputes ?>
    </span>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a>
      <?php endif; ?>
      <?php for ($i = max(1,$page-2); $i <= min($pages,$page+2); $i++): ?>
        <a href="?page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <?php if ($page < $pages): ?>
        <a href="?page=<?= $page + 1 ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<!-- File a Dispute Modal -->
<div class="modal-overlay" id="newDisputeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;overflow-y:auto;padding:30px 16px">
  <div style="background:#fff;border-radius:20px;max-width:520px;margin:0 auto;box-shadow:0 20px 60px rgba(0,0,0,.15)">
    <div style="padding:20px 24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between">
      <div>
        <h3 style="font-size:1.05rem;font-weight:800;color:#0D1B3E;margin:0">File a Dispute</h3>
        <p style="font-size:.78rem;color:#64748b;margin:3px 0 0">We'll investigate and respond within 3–5 business days.</p>
      </div>
      <button onclick="closeModal('newDisputeModal')" style="background:none;border:none;cursor:pointer;color:#64748b;font-size:1.1rem">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/dashboard/disputes/file" style="padding:24px">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label">Transaction Reference <span class="text-danger">*</span></label>
        <?php if (!empty($completedTxns)): ?>
        <select name="transaction_ref" class="form-control form-select" id="dispute-txn-select" required onchange="populateAmount(this)">
          <option value="">— Select a transaction —</option>
          <?php foreach ($completedTxns as $ct): ?>
          <option value="<?= htmlspecialchars($ct['reference']) ?>"
                  data-amount="<?= htmlspecialchars((string)$ct['amount']) ?>">
            <?= htmlspecialchars($ct['reference']) ?>
            &nbsp;·&nbsp; <?= format_amount($ct['amount']) ?>
            &nbsp;·&nbsp; <?= date('d M Y', strtotime($ct['created_at'])) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <?php else: ?>
        <input type="text" name="transaction_ref" class="form-control" placeholder="e.g. MP2025XXXX" required>
        <?php endif; ?>
        <div style="margin-top:4px;font-size:.75rem;color:#64748b">
          Or type a reference directly:
          <input type="text" name="transaction_ref_manual" id="dispute-ref-manual" class="form-control" style="margin-top:6px"
                 placeholder="Paste a reference if not in the list above">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Category <span class="text-danger">*</span></label>
        <select name="category" class="form-control form-select" required>
          <option value="">— Select category —</option>
          <?php foreach ($categories as $k => $v): ?>
          <option value="<?= $k ?>"><?= htmlspecialchars($v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Transaction Amount (KES)</label>
        <input type="number" name="transaction_amount" id="dispute-amount" class="form-control"
               placeholder="Auto-filled from selection" step="0.01" min="0">
      </div>

      <div class="form-group">
        <label class="form-label">Describe the Issue <span class="text-danger">*</span></label>
        <textarea name="reason" class="form-control" rows="4" required
                  placeholder="Please provide as much detail as possible — dates, amounts, what happened, and what you expect as a resolution."></textarea>
      </div>

      <div style="display:flex;gap:10px;margin-top:4px">
        <button type="button" onclick="closeModal('newDisputeModal')"
                class="btn btn-ghost" style="flex:1">Cancel</button>
        <button type="submit" class="btn btn-primary" style="flex:2;background:var(--navy)">
          <i class="fas fa-paper-plane"></i> Submit Dispute
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) {
  const el = document.getElementById(id);
  if (el) { el.style.display = 'block'; document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) { el.style.display = 'none'; document.body.style.overflow = ''; }
}
document.getElementById('newDisputeModal')?.addEventListener('click', function(e) {
  if (e.target === this) closeModal('newDisputeModal');
});

function populateAmount(sel) {
  const opt = sel.options[sel.selectedIndex];
  const amt = opt.dataset.amount;
  const amtField = document.getElementById('dispute-amount');
  if (amtField && amt) amtField.value = amt;
  // If a dropdown item is selected, clear the manual field
  const manual = document.getElementById('dispute-ref-manual');
  if (manual) manual.value = '';
}

// Auto-open if ?filed=1
<?php if (!empty($_GET['filed'])): ?>openModal('newDisputeModal');<?php endif; ?>
</script>
