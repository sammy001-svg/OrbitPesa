<?php
$account   = MpesaAccount::findByUserId($_SESSION['user_id']);
$hasActive = $account && $account['status'] === 'approved';

// Recent C2B transactions
$c2bTxns = [];
if ($hasActive) {
    $c2bTxns = DB::fetchAll(
        "SELECT * FROM transactions WHERE user_id = ? AND channel = 'mpesa_c2b' ORDER BY created_at DESC LIMIT 20",
        [$_SESSION['user_id']]
    );
}

$statusLabels = [
    'pending'      => ['color' => '#f59e0b', 'bg' => 'rgba(245,158,11,.12)', 'icon' => 'fa-clock', 'text' => 'Pending Review'],
    'under_review' => ['color' => '#3b82f6', 'bg' => 'rgba(59,130,246,.12)', 'icon' => 'fa-search', 'text' => 'Under Review'],
    'approved'     => ['color' => '#158347', 'bg' => 'rgba(21,131,71,.12)',  'icon' => 'fa-check-circle', 'text' => 'Approved'],
    'rejected'     => ['color' => '#ef4444', 'bg' => 'rgba(239,68,68,.12)', 'icon' => 'fa-times-circle', 'text' => 'Rejected'],
];
$sl = $statusLabels[$account['status'] ?? ''] ?? null;
?>

<?php if ($hasActive): ?>
<!-- Approved: show number + simulate -->
<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start">

  <!-- Left: account card -->
  <div>
    <div style="background:linear-gradient(135deg,#0D1B3E 60%,#1a2d5a);border-radius:16px;padding:32px;color:#fff;margin-bottom:24px;position:relative;overflow:hidden">
      <div style="position:absolute;top:-30px;right:-30px;width:160px;height:160px;border-radius:50%;background:rgba(21,131,71,.15)"></div>
      <div style="position:absolute;bottom:-20px;left:-20px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.04)"></div>

      <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px">
        <div style="width:44px;height:44px;border-radius:12px;background:#158347;display:flex;align-items:center;justify-content:center;font-size:1.2rem">
          <i class="fas fa-<?= $account['application_type'] === 'till' ? 'cash-register' : 'receipt' ?>"></i>
        </div>
        <div>
          <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.08em">
            <?= $account['application_type'] === 'till' ? 'Buy Goods (Till)' : 'Pay Bill (Paybill)' ?>
          </div>
          <div style="font-size:1rem;font-weight:700">M-Pesa Business Account</div>
        </div>
        <div style="margin-left:auto">
          <span style="background:rgba(21,131,71,.3);color:#7dce9d;font-size:.7rem;font-weight:700;padding:4px 12px;border-radius:10px">ACTIVE</span>
        </div>
      </div>

      <div style="margin-bottom:8px;font-size:.75rem;color:rgba(255,255,255,.55);font-weight:600;letter-spacing:.06em">
        <?= $account['application_type'] === 'till' ? 'TILL NUMBER' : 'PAYBILL NUMBER' ?>
      </div>
      <div style="font-size:2.8rem;font-weight:900;letter-spacing:.05em;color:#fff;margin-bottom:8px;font-family:'JetBrains Mono',monospace">
        <?= sanitize($account['account_number']) ?>
      </div>
      <div style="font-size:.9rem;color:rgba(255,255,255,.7)"><?= sanitize($account['business_name']) ?></div>

      <div style="margin-top:24px;padding-top:20px;border-top:1px solid rgba(255,255,255,.1);display:flex;gap:24px;font-size:.78rem;color:rgba(255,255,255,.55)">
        <div><strong style="color:rgba(255,255,255,.9);display:block;font-size:.82rem">Approved</strong><?= date('d M Y', strtotime($account['reviewed_at'] ?? $account['created_at'])) ?></div>
        <div><strong style="color:rgba(255,255,255,.9);display:block;font-size:.82rem">Business Type</strong><?= ucwords(str_replace('_', ' ', $account['business_type'])) ?></div>
      </div>
    </div>

    <!-- Share info box -->
    <div class="card" style="margin-bottom:24px">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-share-alt"></i> Share with Customers</h3></div>
      <div class="card-body">
        <p style="font-size:.88rem;color:var(--text-muted);margin:0 0 16px">
          Tell your customers to use these steps to pay via M-Pesa:
        </p>
        <?php if ($account['application_type'] === 'till'): ?>
        <ol style="font-size:.88rem;color:var(--text);line-height:2;margin:0 0 16px;padding-left:18px">
          <li>Dial <strong>*150*01#</strong> on M-Pesa</li>
          <li>Select <strong>Buy Goods &amp; Services</strong></li>
          <li>Enter Till Number: <strong style="color:#158347;font-family:monospace"><?= sanitize($account['account_number']) ?></strong></li>
          <li>Enter amount and confirm</li>
        </ol>
        <?php else: ?>
        <ol style="font-size:.88rem;color:var(--text);line-height:2;margin:0 0 16px;padding-left:18px">
          <li>Go to <strong>M-Pesa → Pay Bill</strong></li>
          <li>Business Number: <strong style="color:#158347;font-family:monospace"><?= sanitize($account['account_number']) ?></strong></li>
          <li>Account Number: <strong>Your order/reference</strong></li>
          <li>Enter amount and confirm</li>
        </ol>
        <?php endif; ?>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:12px 14px;display:flex;align-items:center;justify-content:space-between">
          <span style="font-size:.9rem;color:var(--text-muted)">
            <?= $account['application_type'] === 'till' ? 'Till' : 'Paybill' ?> Number:
            <strong style="color:var(--text);font-family:monospace;font-size:1rem"><?= sanitize($account['account_number']) ?></strong>
          </span>
          <button onclick="copyToClipboard('<?= sanitize($account['account_number']) ?>', this)" style="background:none;border:none;color:#158347;font-size:.8rem;font-weight:700;cursor:pointer">
            <i class="fas fa-copy"></i> Copy
          </button>
        </div>
      </div>
    </div>

    <!-- Recent transactions -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list"></i> Recent C2B Payments</h3>
        <?php if (!empty($c2bTxns)): ?>
          <a href="<?= APP_URL ?>/dashboard/transactions?channel=mpesa_c2b" style="font-size:.8rem;color:#158347;text-decoration:none">View all →</a>
        <?php endif; ?>
      </div>
      <div class="card-body" style="padding:0">
        <?php if (empty($c2bTxns)): ?>
          <div style="text-align:center;padding:40px 20px;color:var(--text-muted)">
            <i class="fas fa-mobile-alt" style="font-size:2rem;margin-bottom:12px;display:block;color:#dde2ec"></i>
            <div style="font-size:.9rem">No payments received yet</div>
            <div style="font-size:.78rem;margin-top:4px">Share your <?= $account['application_type'] === 'till' ? 'till' : 'paybill' ?> number to start collecting</div>
          </div>
        <?php else: ?>
          <table class="data-table">
            <thead><tr>
              <th>Reference</th>
              <th>Phone</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date</th>
            </tr></thead>
            <tbody>
            <?php foreach ($c2bTxns as $t): ?>
              <tr>
                <td style="font-family:monospace;font-size:.82rem"><?= sanitize($t['reference']) ?></td>
                <td><?= sanitize($t['phone'] ?? '—') ?></td>
                <td style="font-weight:700;color:#158347"><?= format_amount((float)$t['amount']) ?></td>
                <td><span class="badge badge-<?= $t['status'] === 'completed' ? 'success' : 'warning' ?>"><?= ucfirst($t['status']) ?></span></td>
                <td style="color:var(--text-muted);font-size:.82rem"><?= date('d M y H:i', strtotime($t['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Right: simulate C2B -->
  <div>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-flask"></i> Simulate C2B Payment</h3>
      </div>
      <div class="card-body">
        <p style="font-size:.82rem;color:var(--text-muted);margin:0 0 16px">
          Simulate a customer paying to your <?= $account['application_type'] ?> number. Funds will be credited to your wallet immediately (sandbox mode).
        </p>
        <form method="POST" action="<?= APP_URL ?>/dashboard/mpesa-account/simulate-c2b">
          <?= csrf_field() ?>
          <div style="margin-bottom:12px">
            <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Customer Phone</label>
            <input type="tel" name="phone" placeholder="07XXXXXXXX" required
              style="width:100%;box-sizing:border-box;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:.9rem;font-family:inherit;color:var(--text)">
          </div>
          <div style="margin-bottom:12px">
            <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Amount (KES)</label>
            <input type="number" name="amount" placeholder="1000" min="1" max="150000" required
              style="width:100%;box-sizing:border-box;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:.9rem;font-family:inherit;color:var(--text)">
          </div>
          <div style="margin-bottom:16px">
            <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Sender Name (optional)</label>
            <input type="text" name="sender_name" placeholder="JOHN DOE"
              style="width:100%;box-sizing:border-box;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:.9rem;font-family:inherit;color:var(--text)">
          </div>
          <?php if ($account['application_type'] === 'paybill'): ?>
          <div style="margin-bottom:16px">
            <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Account Reference</label>
            <input type="text" name="account_ref" placeholder="Order number or reference"
              style="width:100%;box-sizing:border-box;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:.9rem;font-family:inherit;color:var(--text)">
          </div>
          <?php endif; ?>
          <button type="submit" style="width:100%;padding:10px;background:#158347;color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer;font-family:inherit">
            <i class="fas fa-play"></i> Simulate Payment
          </button>
        </form>
      </div>
    </div>

    <!-- Stats -->
    <?php
    $c2bStats = DB::fetch(
        "SELECT COUNT(*) as txn_count,
                COALESCE(SUM(CASE WHEN status='completed' THEN amount ELSE 0 END),0) as total_received
           FROM transactions WHERE user_id=? AND channel='mpesa_c2b'",
        [$_SESSION['user_id']]
    );
    ?>
    <div class="card" style="margin-top:20px">
      <div class="card-header"><h3 class="card-title">All Time</h3></div>
      <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div style="text-align:center;padding:16px;background:var(--surface);border-radius:10px">
          <div style="font-size:1.4rem;font-weight:800;color:#158347"><?= format_amount((float)$c2bStats['total_received']) ?></div>
          <div style="font-size:.72rem;color:var(--text-muted);font-weight:600;margin-top:3px">Total Received</div>
        </div>
        <div style="text-align:center;padding:16px;background:var(--surface);border-radius:10px">
          <div style="font-size:1.4rem;font-weight:800;color:#0D1B3E"><?= number_format((int)$c2bStats['txn_count']) ?></div>
          <div style="font-size:.72rem;color:var(--text-muted);font-weight:600;margin-top:3px">Transactions</div>
        </div>
      </div>
    </div>
  </div>

</div>

<?php elseif ($account && $account['status'] !== 'rejected'): ?>
<!-- Pending / Under Review -->
<div style="max-width:560px;margin:40px auto;text-align:center">
  <div style="width:72px;height:72px;border-radius:50%;margin:0 auto 20px;display:flex;align-items:center;justify-content:center;background:<?= $sl['bg'] ?>;font-size:1.8rem;color:<?= $sl['color'] ?>">
    <i class="fas <?= $sl['icon'] ?>"></i>
  </div>
  <h2 style="font-size:1.5rem;font-weight:800;color:#0D1B3E;margin:0 0 8px"><?= $sl['text'] ?></h2>
  <p style="color:var(--text-muted);margin:0 0 24px">
    <?php if ($account['status'] === 'pending'): ?>
      Your application is in the queue. We'll review it within 1–2 business days and notify you by email.
    <?php else: ?>
      Your application is currently being reviewed by our compliance team. This usually takes less than 24 hours.
    <?php endif; ?>
  </p>

  <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:20px;text-align:left;margin-bottom:24px">
    <div style="font-size:.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px">Application Details</div>
    <div style="display:grid;gap:8px;font-size:.88rem">
      <div style="display:flex;justify-content:space-between"><span style="color:var(--text-muted)">Business Name</span><strong><?= sanitize($account['business_name']) ?></strong></div>
      <div style="display:flex;justify-content:space-between"><span style="color:var(--text-muted)">Account Type</span><strong><?= ucfirst($account['application_type']) ?></strong></div>
      <div style="display:flex;justify-content:space-between"><span style="color:var(--text-muted)">Applied</span><strong><?= date('d M Y', strtotime($account['created_at'])) ?></strong></div>
      <div style="display:flex;justify-content:space-between"><span style="color:var(--text-muted)">Status</span>
        <span style="color:<?= $sl['color'] ?>;font-weight:700"><i class="fas <?= $sl['icon'] ?>"></i> <?= $sl['text'] ?></span>
      </div>
    </div>
  </div>
  <a href="<?= APP_URL ?>/dashboard" class="btn btn-outline">← Back to Dashboard</a>
</div>

<?php elseif ($account && $account['status'] === 'rejected'): ?>
<!-- Rejected: show reason + reapply option -->
<div style="max-width:560px;margin:40px auto">
  <div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:12px;padding:24px;margin-bottom:24px">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
      <i class="fas fa-times-circle" style="color:#ef4444;font-size:1.4rem"></i>
      <div>
        <div style="font-weight:800;color:#9f1239;font-size:1rem">Application Not Approved</div>
        <div style="font-size:.8rem;color:#b91c1c">Submitted <?= date('d M Y', strtotime($account['created_at'])) ?></div>
      </div>
    </div>
    <?php if ($account['admin_notes']): ?>
      <div style="background:rgba(255,255,255,.7);border-radius:8px;padding:12px 14px;font-size:.87rem;color:#7f1d1d">
        <strong>Reason:</strong> <?= sanitize($account['admin_notes']) ?>
      </div>
    <?php endif; ?>
  </div>
  <p style="color:var(--text-muted);font-size:.88rem;margin:0 0 20px">
    Please address the issue above and re-submit your application. Our team will review the new submission promptly.
  </p>
  <button data-modal="reapplyModal" class="btn btn-primary" style="width:100%">
    <i class="fas fa-redo"></i> Re-apply
  </button>
</div>

<!-- Re-apply modal -->
<div class="modal-backdrop" id="reapplyModal">
  <div class="modal" style="max-width:540px">
    <div class="modal-header">
      <h3 class="modal-title">Re-apply for M-Pesa Account</h3>
      <button class="modal-close"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/dashboard/mpesa-account/apply">
      <?= csrf_field() ?>
      <div class="modal-body" style="display:grid;gap:14px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <label style="cursor:pointer">
            <input type="radio" name="application_type" value="till" <?= $account['application_type'] === 'till' ? 'checked' : '' ?>> Till (Buy Goods)
          </label>
          <label style="cursor:pointer">
            <input type="radio" name="application_type" value="paybill" <?= $account['application_type'] === 'paybill' ? 'checked' : '' ?>> Paybill
          </label>
        </div>
        <div>
          <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Business Name *</label>
          <input type="text" name="business_name" value="<?= sanitize($account['business_name']) ?>" required class="form-control">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Business Type *</label>
            <select name="business_type" required class="form-control">
              <option value="">Select</option>
              <?php foreach (['retail'=>'Retail / Shop','restaurant'=>'Restaurant / Food','services'=>'Services','transport'=>'Transport','education'=>'Education','healthcare'=>'Healthcare','ngo'=>'NGO / Church','ecommerce'=>'E-Commerce','other'=>'Other'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= $account['business_type'] === $v ? 'selected' : '' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Monthly Volume</label>
            <select name="monthly_volume" class="form-control">
              <?php foreach (['under_50k'=>'Under KES 50K','50k_200k'=>'KES 50K–200K','200k_1m'=>'KES 200K–1M','over_1m'=>'Over KES 1M'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= $account['monthly_volume'] === $v ? 'selected' : '' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div>
          <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Contact Phone *</label>
          <input type="tel" name="contact_phone" value="<?= sanitize($account['contact_phone']) ?>" required class="form-control">
        </div>
        <div>
          <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Business Reg No.</label>
          <input type="text" name="business_reg_no" value="<?= sanitize($account['business_reg_no'] ?? '') ?>" class="form-control">
        </div>
        <div>
          <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Description</label>
          <textarea name="description" class="form-control" rows="3"><?= sanitize($account['description'] ?? '') ?></textarea>
        </div>
        <input type="hidden" name="contact_name" value="<?= sanitize(auth_user()['business_name'] ?? '') ?>">
        <input type="hidden" name="contact_email" value="<?= sanitize(auth_user()['email'] ?? '') ?>">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline modal-close">Cancel</button>
        <button type="submit" class="btn btn-primary">Submit Re-application</button>
      </div>
    </form>
  </div>
</div>

<?php else: ?>
<!-- No application — show form -->
<div style="display:grid;grid-template-columns:1fr 400px;gap:24px;align-items:start">

  <div>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:20px">
      <h3 style="font-size:1rem;font-weight:800;color:var(--heading);margin:0 0 16px">What you'll get</h3>
      <div style="display:grid;gap:12px">
        <?php foreach ([
          ['fa-mobile-alt','Dedicated number','A unique till or paybill number your customers can use in M-Pesa'],
          ['fa-bolt','Instant settlement','Every payment lands in your wallet within seconds'],
          ['fa-chart-bar','Transaction history','Full history of all payments in your dashboard'],
          ['fa-satellite-dish','Webhook support','Get notified of every payment to your server in real time'],
        ] as [$icon,$title,$desc]): ?>
        <div style="display:flex;gap:12px;align-items:flex-start">
          <div style="width:36px;height:36px;border-radius:10px;background:rgba(21,131,71,.1);color:#158347;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas <?= $icon ?>"></i>
          </div>
          <div>
            <div style="font-weight:700;font-size:.88rem;color:var(--heading)"><?= $title ?></div>
            <div style="font-size:.8rem;color:var(--text-muted);margin-top:2px"><?= $desc ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div style="background:rgba(21,131,71,.06);border:1px solid rgba(21,131,71,.2);border-radius:10px;padding:16px 20px">
      <div style="font-size:.78rem;font-weight:700;color:#158347;margin-bottom:6px">Transaction Fee</div>
      <div style="font-size:1.1rem;font-weight:900;color:#0D1B3E">0.5% per transaction</div>
      <div style="font-size:.75rem;color:var(--text-muted);margin-top:3px">Maximum KES 300 per transaction</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-paper-plane"></i> Apply Now</h3></div>
    <div class="card-body">
      <form method="POST" action="<?= APP_URL ?>/dashboard/mpesa-account/apply">
        <?= csrf_field() ?>
        <div style="margin-bottom:14px">
          <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:6px">Account Type *</label>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <label style="cursor:pointer;display:flex;flex-direction:column;align-items:center;padding:12px 8px;border:2px solid var(--border);border-radius:10px;text-align:center;gap:4px;font-size:.8rem;font-weight:700;color:var(--text-muted);transition:all .2s" id="till-label">
              <input type="radio" name="application_type" value="till" checked style="position:absolute;opacity:0">
              <i class="fas fa-cash-register" style="font-size:1.2rem"></i> Till / Buy Goods
            </label>
            <label style="cursor:pointer;display:flex;flex-direction:column;align-items:center;padding:12px 8px;border:2px solid var(--border);border-radius:10px;text-align:center;gap:4px;font-size:.8rem;font-weight:700;color:var(--text-muted);transition:all .2s" id="paybill-label">
              <input type="radio" name="application_type" value="paybill" style="position:absolute;opacity:0">
              <i class="fas fa-receipt" style="font-size:1.2rem"></i> Paybill
            </label>
          </div>
        </div>
        <div style="margin-bottom:12px">
          <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Business Name *</label>
          <input type="text" name="business_name" value="<?= sanitize(auth_user()['business_name'] ?? '') ?>" required class="form-control">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
          <div>
            <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Business Type *</label>
            <select name="business_type" required class="form-control">
              <option value="">Select</option>
              <?php foreach (['retail'=>'Retail','restaurant'=>'Restaurant','services'=>'Services','transport'=>'Transport','education'=>'Education','healthcare'=>'Healthcare','ngo'=>'NGO/Church','ecommerce'=>'E-Commerce','other'=>'Other'] as $v=>$l): ?>
                <option value="<?= $v ?>"><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Monthly Volume</label>
            <select name="monthly_volume" class="form-control">
              <?php foreach (['under_50k'=>'Under 50K','50k_200k'=>'50K–200K','200k_1m'=>'200K–1M','over_1m'=>'Over 1M'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= $v === '200k_1m' ? 'selected' : '' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div style="margin-bottom:12px">
          <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Contact Phone *</label>
          <input type="tel" name="contact_phone" value="<?= sanitize(auth_user()['phone'] ?? '') ?>" placeholder="07XXXXXXXX" required class="form-control">
        </div>
        <div style="margin-bottom:12px">
          <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Business Reg No. (optional)</label>
          <input type="text" name="business_reg_no" placeholder="BN/2023/001234" class="form-control">
        </div>
        <div style="margin-bottom:16px">
          <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:5px">Description (optional)</label>
          <textarea name="description" class="form-control" rows="3" placeholder="What does your business sell?"></textarea>
        </div>
        <input type="hidden" name="contact_name" value="<?= sanitize(auth_user()['business_name'] ?? '') ?>">
        <input type="hidden" name="contact_email" value="<?= sanitize(auth_user()['email'] ?? '') ?>">
        <button type="submit" class="btn btn-primary" style="width:100%">
          <i class="fas fa-paper-plane"></i> Submit Application
        </button>
        <div style="font-size:.72rem;color:var(--text-muted);text-align:center;margin-top:8px">
          Review takes 1–2 business days. You'll be notified by email.
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('input[name="application_type"]').forEach(r => {
  r.addEventListener('change', () => {
    document.getElementById('till-label').style.borderColor    = r.value === 'till'     ? '#158347' : '';
    document.getElementById('paybill-label').style.borderColor = r.value === 'paybill' ? '#158347' : '';
  });
});
document.getElementById('till-label').style.borderColor = '#158347';
</script>

<?php endif; ?>
