<?php
$wallet  = DB::fetch("SELECT * FROM wallets WHERE user_id = ?", [$merchant['id']]);
$txnSum  = DB::fetch("SELECT COALESCE(SUM(amount),0) as vol, COUNT(*) as cnt FROM transactions WHERE user_id = ? AND status='completed'", [$merchant['id']]);
$apiKeys = DB::fetchAll("SELECT * FROM api_keys WHERE user_id = ?", [$merchant['id']]);
$payLinks= DB::fetchAll("SELECT * FROM payment_links WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$merchant['id']]);
$recentTxns = Transaction::getForUser($merchant['id'], 10);
$kycDocs = DB::fetchAll("SELECT * FROM kyc_documents WHERE user_id = ? ORDER BY created_at DESC", [$merchant['id']]);
?>

<div class="section-hd">
  <div>
    <a href="<?= APP_URL ?>/admin/merchants" style="color:var(--text-muted);font-size:.85rem;text-decoration:none">
      <i class="fas fa-arrow-left"></i> Back to Merchants
    </a>
    <h2 style="margin-top:6px"><?= sanitize($merchant['business_name']) ?></h2>
    <p><?= sanitize($merchant['email']) ?> · <?= sanitize($merchant['phone']) ?></p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <?php if ($merchant['status'] === 'active'): ?>
    <form method="POST" action="<?= APP_URL ?>/admin/merchants/suspend" style="display:inline">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= sanitize($merchant['id']) ?>">
      <button type="submit" class="btn btn-danger btn-sm" data-confirm="Suspend this merchant?">
        <i class="fas fa-ban"></i> Suspend
      </button>
    </form>
    <?php else: ?>
    <form method="POST" action="<?= APP_URL ?>/admin/merchants/activate" style="display:inline">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= sanitize($merchant['id']) ?>">
      <button type="submit" class="btn btn-primary btn-sm">
        <i class="fas fa-check-circle"></i> Activate
      </button>
    </form>
    <?php endif; ?>
    <?php if ($merchant['kyc_status'] !== 'verified'): ?>
    <form method="POST" action="<?= APP_URL ?>/admin/merchants/kyc-approve" style="display:inline">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= sanitize($merchant['id']) ?>">
      <button type="submit" class="btn btn-outline btn-sm">
        <i class="fas fa-id-card"></i> Approve KYC
      </button>
    </form>
    <?php endif; ?>
  </div>
</div>

<!-- Status Badges -->
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap">
  <span class="status-<?= $merchant['status'] ?>" style="font-size:.82rem"><?= ucfirst($merchant['status']) ?></span>
  <span class="kyc-badge kyc-<?= $merchant['kyc_status'] ?>" style="font-size:.82rem">KYC: <?= ucfirst($merchant['kyc_status']) ?></span>
  <span class="badge badge-navy"><?= ucfirst($merchant['account_type']) ?></span>
  <span style="font-size:.8rem;color:var(--text-muted)">Joined <?= date('d M Y', strtotime($merchant['created_at'])) ?></span>
</div>

<!-- Stat Cards -->
<div class="admin-stats" style="margin-bottom:24px">
  <div class="admin-stat green">
    <div class="admin-stat-icon green"><i class="fas fa-coins"></i></div>
    <div>
      <div class="admin-stat-val"><?= format_amount($txnSum['vol']) ?></div>
      <div class="admin-stat-lbl">Total Volume</div>
      <div class="admin-stat-sub"><?= number_format($txnSum['cnt']) ?> transactions</div>
    </div>
  </div>
  <div class="admin-stat navy">
    <div class="admin-stat-icon navy"><i class="fas fa-wallet"></i></div>
    <div>
      <div class="admin-stat-val"><?= format_amount($wallet['balance'] ?? 0) ?></div>
      <div class="admin-stat-lbl">Wallet Balance</div>
    </div>
  </div>
  <div class="admin-stat green">
    <div class="admin-stat-icon green"><i class="fas fa-key"></i></div>
    <div>
      <div class="admin-stat-val"><?= count($apiKeys) ?></div>
      <div class="admin-stat-lbl">API Keys</div>
    </div>
  </div>
  <div class="admin-stat navy">
    <div class="admin-stat-icon navy"><i class="fas fa-link"></i></div>
    <div>
      <div class="admin-stat-val"><?= count($payLinks) ?>+</div>
      <div class="admin-stat-lbl">Payment Links</div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
  <!-- Transactions -->
  <div class="card">
    <div class="card-header">
      <h4><i class="fas fa-exchange-alt" style="color:var(--green);margin-right:6px"></i> Recent Transactions</h4>
      <a href="<?= APP_URL ?>/admin/transactions?merchant=<?= urlencode($merchant['id']) ?>" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <div class="p-0">
      <div class="table-wrap">
        <table class="orb-table">
          <thead><tr><th>Reference</th><th>Channel</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($recentTxns as $t): ?>
            <tr>
              <td><code style="font-size:.78rem"><?= sanitize($t['reference']) ?></code></td>
              <td><span class="chip <?= in_array($t['channel'],['mpesa','wallet'])?'green':'navy' ?>"><?= ucfirst(str_replace('_',' ',$t['channel'])) ?></span></td>
              <td style="font-weight:700"><?= format_amount($t['amount'],$t['currency']) ?></td>
              <td><?= transaction_status_badge($t['status']) ?></td>
              <td style="font-size:.78rem;color:var(--text-muted)"><?= date('d M H:i',strtotime($t['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Profile + KYC -->
  <div style="display:flex;flex-direction:column;gap:16px">
    <!-- Profile Info -->
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-user" style="color:var(--green);margin-right:6px"></i> Merchant Info</h4></div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:10px;font-size:.875rem">
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-muted)">Business Name</span>
          <span style="font-weight:600"><?= sanitize($merchant['business_name']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-muted)">Email</span>
          <span><?= sanitize($merchant['email']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-muted)">Phone</span>
          <span><?= sanitize($merchant['phone']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-muted)">Account Type</span>
          <span><?= ucfirst($merchant['account_type']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--text-muted)">KYC Status</span>
          <span class="kyc-badge kyc-<?= $merchant['kyc_status'] ?>"><?= ucfirst($merchant['kyc_status']) ?></span>
        </div>
      </div>
    </div>

    <!-- KYC Documents -->
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-id-card" style="color:var(--green);margin-right:6px"></i> KYC Documents</h4></div>
      <div class="card-body">
        <?php if (empty($kycDocs)): ?>
          <p style="font-size:.85rem;color:var(--text-muted)">No documents submitted.</p>
        <?php else: ?>
          <?php foreach ($kycDocs as $doc): ?>
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;padding:10px;background:var(--bg);border-radius:var(--radius);border:1px solid var(--border)">
            <div>
              <div style="font-weight:600;font-size:.84rem"><?= ucwords(str_replace('_',' ',$doc['doc_type'])) ?></div>
              <div style="font-size:.75rem;color:var(--text-muted)"><?= date('d M Y',strtotime($doc['created_at'])) ?></div>
            </div>
            <span class="badge badge-<?= $doc['status']==='approved'?'success':($doc['status']==='rejected'?'danger':'warning') ?>">
              <?= ucfirst($doc['status']) ?>
            </span>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($merchant['kyc_status'] !== 'verified'): ?>
        <div class="separator"></div>
        <form method="POST" action="<?= APP_URL ?>/admin/merchants/kyc-approve">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= sanitize($merchant['id']) ?>">
          <div class="form-group">
            <label class="form-label">Review Notes (optional)</label>
            <textarea class="form-control" name="notes" rows="2" placeholder="Notes for the merchant..."></textarea>
          </div>
          <div style="display:flex;gap:8px">
            <button type="submit" name="action" value="approve" class="btn btn-primary btn-sm">
              <i class="fas fa-check"></i> Approve KYC
            </button>
            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">
              <i class="fas fa-times"></i> Reject
            </button>
          </div>
        </form>
        <?php endif; ?>
      </div>
    </div>

    <!-- Wallet Actions -->
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-wallet" style="color:var(--green);margin-right:6px"></i> Wallet Actions</h4></div>
      <div class="card-body">
        <p style="font-size:.83rem;color:var(--text-muted);margin-bottom:12px">
          Current balance: <strong><?= format_amount($wallet['balance'] ?? 0) ?></strong>
        </p>
        <form method="POST" action="<?= APP_URL ?>/admin/merchants/wallet-credit">
          <?= csrf_field() ?>
          <input type="hidden" name="merchant_id" value="<?= sanitize($merchant['id']) ?>">
          <div class="form-group">
            <label class="form-label" style="font-size:.8rem">Manual Credit (KES)</label>
            <input type="number" class="form-control" name="amount" placeholder="0.00" min="1" step="0.01">
          </div>
          <div class="form-group">
            <label class="form-label" style="font-size:.8rem">Reason</label>
            <input type="text" class="form-control" name="reason" placeholder="e.g. Refund, correction...">
          </div>
          <button type="submit" class="btn btn-outline btn-sm btn-block" data-confirm="Credit this merchant's wallet?">
            <i class="fas fa-plus-circle"></i> Credit Wallet
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
