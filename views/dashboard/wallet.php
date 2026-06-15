<?php
$wallet  = Wallet::getOrCreate($_SESSION['user_id']);
$ledger  = Wallet::getLedger($_SESSION['user_id'], 30);
?>

<div class="page-header">
  <h2>Wallet</h2>
  <p>Manage your OrbitPesa wallet — hold funds, withdraw, and transfer.</p>
</div>

<!-- Balance Card -->
<div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;margin-bottom:24px">
  <div class="card" style="background:var(--navy);color:#fff;border-color:var(--navy)">
    <div class="card-body" style="text-align:center;padding:36px 24px">
      <div style="font-size:.85rem;color:rgba(255,255,255,.55);margin-bottom:8px;text-transform:uppercase;letter-spacing:.07em">Available Balance</div>
      <div style="font-size:2.5rem;font-weight:900;color:#fff;margin-bottom:4px">
        <?= format_amount($wallet['balance']) ?>
      </div>
      <div style="font-size:.8rem;color:rgba(255,255,255,.4);margin-bottom:28px"><?= $wallet['currency'] ?> Wallet</div>
      <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
        <button class="btn btn-primary btn-sm" data-modal="withdrawModal">
          <i class="fas fa-arrow-up"></i> Withdraw
        </button>
        <button class="btn" style="background:rgba(255,255,255,.1);color:#fff;border-color:rgba(255,255,255,.2)" data-modal="fundModal">
          <i class="fas fa-arrow-down"></i> Add Funds
        </button>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h4><i class="fas fa-info-circle" style="color:var(--green);margin-right:6px"></i> Wallet Overview</h4></div>
    <div class="card-body">
      <div class="grid-3" style="gap:16px">
        <?php
        $credits = DB::fetch("SELECT COALESCE(SUM(amount),0) as t FROM wallet_ledger WHERE user_id = ? AND type='credit'", [$_SESSION['user_id']]);
        $debits  = DB::fetch("SELECT COALESCE(SUM(amount),0) as t FROM wallet_ledger WHERE user_id = ? AND type='debit'",  [$_SESSION['user_id']]);
        $today   = DB::fetch("SELECT COALESCE(SUM(amount),0) as t FROM wallet_ledger WHERE user_id = ? AND type='credit' AND DATE(created_at)=CURDATE()", [$_SESSION['user_id']]);
        ?>
        <div style="text-align:center;padding:16px;background:var(--green-light);border-radius:var(--radius);border:1px solid #b7dfc9">
          <div style="font-size:1.2rem;font-weight:800;color:var(--green)"><?= format_amount($credits['t']) ?></div>
          <div style="font-size:.78rem;color:var(--green-dark);margin-top:4px">Total Credits</div>
        </div>
        <div style="text-align:center;padding:16px;background:#fef2f2;border-radius:var(--radius);border:1px solid #fecaca">
          <div style="font-size:1.2rem;font-weight:800;color:var(--danger)"><?= format_amount($debits['t']) ?></div>
          <div style="font-size:.78rem;color:#991b1b;margin-top:4px">Total Debits</div>
        </div>
        <div style="text-align:center;padding:16px;background:var(--navy-lighter);border-radius:var(--radius);border:1px solid #b8c4df">
          <div style="font-size:1.2rem;font-weight:800;color:var(--navy)"><?= format_amount($today['t']) ?></div>
          <div style="font-size:.78rem;color:var(--navy);margin-top:4px">Credited Today</div>
        </div>
      </div>
      <div class="separator"></div>
      <div style="font-size:.85rem;color:var(--text-muted);line-height:1.7">
        <i class="fas fa-info-circle" style="color:var(--green)"></i>
        Funds in your wallet are settled from completed transactions. Withdrawals to M-Pesa are processed instantly.
        Bank withdrawals take 1–2 business days.
      </div>
    </div>
  </div>
</div>

<!-- Ledger -->
<div class="card">
  <div class="card-header">
    <h4><i class="fas fa-history" style="color:var(--green);margin-right:6px"></i> Ledger History</h4>
  </div>
  <div class="p-0">
    <?php if (empty($ledger)): ?>
      <div class="empty-state"><i class="fas fa-receipt"></i><h4>No ledger entries yet</h4><p>Your wallet activity will appear here.</p></div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="orb-table">
          <thead>
            <tr><th>Type</th><th>Description</th><th>Amount</th><th>Balance After</th><th>Date</th></tr>
          </thead>
          <tbody>
            <?php foreach ($ledger as $entry): ?>
            <tr>
              <td>
                <span class="badge <?= $entry['type'] === 'credit' ? 'badge-success' : 'badge-danger' ?>">
                  <i class="fas fa-<?= $entry['type'] === 'credit' ? 'arrow-down' : 'arrow-up' ?>"></i>
                  <?= ucfirst($entry['type']) ?>
                </span>
              </td>
              <td style="font-size:.85rem"><?= sanitize($entry['description']) ?></td>
              <td style="font-weight:700;color:<?= $entry['type'] === 'credit' ? 'var(--success)' : 'var(--danger)' ?>">
                <?= ($entry['type'] === 'credit' ? '+' : '-') ?><?= format_amount($entry['amount']) ?>
              </td>
              <td style="font-size:.85rem"><?= format_amount($entry['balance_after']) ?></td>
              <td style="font-size:.8rem;color:var(--text-muted)"><?= date('d M Y H:i', strtotime($entry['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Withdraw Modal -->
<div class="modal-backdrop" id="withdrawModal">
  <div class="modal">
    <div class="modal-header">
      <h4><i class="fas fa-arrow-up" style="color:var(--green);margin-right:8px"></i> Withdraw Funds</h4>
      <button class="modal-close"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/dashboard/wallet/withdraw">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="alert alert-info" style="margin-bottom:16px">
          <i class="fas fa-info-circle"></i>
          Available balance: <strong><?= format_amount($wallet['balance']) ?></strong>
        </div>
        <div class="form-group">
          <label class="form-label">Withdrawal Method</label>
          <select class="form-control form-select" name="channel" id="withdrawChannel" onchange="updateWithdrawFields()">
            <option value="mpesa">M-Pesa (Instant)</option>
            <option value="bank">Bank Transfer (1-2 days)</option>
          </select>
        </div>
        <div class="form-group" id="mpesaField">
          <label class="form-label">M-Pesa Phone Number</label>
          <input type="tel" class="form-control" name="destination" placeholder="0712345678">
        </div>
        <div class="form-group" id="bankField" style="display:none">
          <label class="form-label">Account Number</label>
          <input type="text" class="form-control" name="bank_account" placeholder="Bank account number">
          <div class="form-hint">Ensure the account is registered and active.</div>
        </div>
        <div class="form-group">
          <label class="form-label">Amount (KES)</label>
          <div class="input-group">
            <span class="input-addon">KES</span>
            <input type="number" class="form-control" name="amount" placeholder="0.00"
                   min="100" max="<?= $wallet['balance'] ?>" step="0.01" required>
          </div>
          <div class="form-hint">Minimum withdrawal: KES 100. Withdrawal fee: KES 30</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost modal-close">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Withdraw</button>
      </div>
    </form>
  </div>
</div>

<!-- Fund Modal -->
<div class="modal-backdrop" id="fundModal">
  <div class="modal">
    <div class="modal-header">
      <h4><i class="fas fa-arrow-down" style="color:var(--green);margin-right:8px"></i> Add Funds to Wallet</h4>
      <button class="modal-close"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/dashboard/mpesa">
      <?= csrf_field() ?>
      <input type="hidden" name="destination" value="wallet">
      <div class="modal-body">
        <p style="font-size:.9rem;color:var(--text-muted);margin-bottom:16px">
          Send an M-Pesa STK Push to yourself to add funds to your OrbitPesa wallet.
        </p>
        <div class="form-group">
          <label class="form-label">M-Pesa Phone Number</label>
          <input type="tel" class="form-control" name="phone" placeholder="0712345678" value="<?= sanitize(auth_user()['phone'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Amount (KES)</label>
          <div class="input-group">
            <span class="input-addon">KES</span>
            <input type="number" class="form-control" name="amount" placeholder="0.00" min="1" step="0.01" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost modal-close">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-mobile-alt"></i> Send STK Push</button>
      </div>
    </form>
  </div>
</div>

<script>
function updateWithdrawFields() {
  const ch = document.getElementById('withdrawChannel').value;
  document.getElementById('mpesaField').style.display = ch === 'mpesa' ? '' : 'none';
  document.getElementById('bankField').style.display  = ch === 'bank'  ? '' : 'none';
}
</script>
