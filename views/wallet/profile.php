<?php
$pageTitle    = 'My Profile';
$backUrl      = APP_URL . '/wallet/home';
$wu           = $walletUser;
$initial      = strtoupper(substr($wu['full_name'], 0, 1));
$txnCount     = WalletTransaction::countForUser($wu['id']);
$refStats     = WalletReferral::statsForUser($wu['id']);
$referralCode = $wu['referral_code'] ?? '';
$referralLink = APP_URL . '/wallet/register?ref=' . urlencode($referralCode);
?>

<!-- Profile hero -->
<div class="profile-hero" style="border-radius:0 0 0 0;margin:0">
  <div class="profile-avatar"><?= $initial ?></div>
  <div class="profile-name"><?= htmlspecialchars($wu['full_name']) ?></div>
  <div class="profile-email"><?= htmlspecialchars($wu['email']) ?></div>
  <div class="profile-wid"><?= htmlspecialchars($wu['wallet_id']) ?></div>
  <div style="display:flex;gap:24px;justify-content:center;margin-top:16px">
    <div style="text-align:center">
      <div style="font-size:1.2rem;font-weight:800;color:#0D1B3E">KES <?= number_format((float)$wu['balance'], 2) ?></div>
      <div style="font-size:.7rem;color:#64748b;font-weight:600">Balance</div>
    </div>
    <div style="width:1px;background:#e2e8f0"></div>
    <div style="text-align:center">
      <div style="font-size:1.2rem;font-weight:800;color:#0D1B3E"><?= $txnCount ?></div>
      <div style="font-size:.7rem;color:#64748b;font-weight:600">Transactions</div>
    </div>
    <div style="width:1px;background:#e2e8f0"></div>
    <div style="text-align:center">
      <div style="font-size:1.2rem;font-weight:800;color:#158347">Active</div>
      <div style="font-size:.7rem;color:#64748b;font-weight:600">Status</div>
    </div>
  </div>
</div>

<!-- Demo top-up -->
<div style="margin:14px 14px 0;background:white;border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05)">
  <form method="POST" action="<?= APP_URL ?>/wallet/deposit" style="margin:0">
    <?= csrf_field() ?>
    <input type="hidden" name="amount" value="1000">
    <button type="submit" class="profile-menu-item" style="border:none;width:100%;text-align:left;cursor:pointer;font-family:inherit;background:white">
      <div class="profile-menu-icon green"><i class="fas fa-plus-circle"></i></div>
      <div class="profile-menu-label">Add Demo Funds (KES 1,000)</div>
      <i class="fas fa-chevron-right profile-menu-chevron"></i>
    </button>
  </form>
</div>

<!-- Account section -->
<div class="profile-section-title" style="margin-top:6px">Account</div>
<div style="margin:0 14px;background:white;border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05)">
  <button class="profile-menu-item" style="border:none;width:100%;text-align:left;cursor:pointer;font-family:inherit"
          onclick="openModal('editModal')">
    <div class="profile-menu-icon navy"><i class="fas fa-user-edit"></i></div>
    <div class="profile-menu-label">Edit Profile</div>
    <i class="fas fa-chevron-right profile-menu-chevron"></i>
  </button>
  <button class="profile-menu-item" style="border:none;width:100%;text-align:left;cursor:pointer;font-family:inherit"
          onclick="openModal('pinModal')">
    <div class="profile-menu-icon navy"><i class="fas fa-lock"></i></div>
    <div class="profile-menu-label">Change PIN</div>
    <i class="fas fa-chevron-right profile-menu-chevron"></i>
  </button>
  <button class="profile-menu-item" style="border:none;width:100%;text-align:left;cursor:pointer;font-family:inherit"
          onclick="openModal('passwordModal')">
    <div class="profile-menu-icon navy"><i class="fas fa-key"></i></div>
    <div class="profile-menu-label">Change Password</div>
    <i class="fas fa-chevron-right profile-menu-chevron"></i>
  </button>
  <a href="<?= APP_URL ?>/wallet/kyc" class="profile-menu-item">
    <div class="profile-menu-icon" style="background:#f0fdf4"><i class="fas fa-shield-halved" style="color:#158347"></i></div>
    <div class="profile-menu-label">
      Verify Identity
      <?php $kycSt = $wu['kyc_status'] ?? 'unverified'; ?>
      <?php if ($kycSt === 'approved'): ?>
        <span style="font-size:.68rem;background:#dcfce7;color:#158347;border-radius:6px;padding:1px 7px;margin-left:6px;font-weight:700">Verified</span>
      <?php elseif ($kycSt === 'pending'): ?>
        <span style="font-size:.68rem;background:#fffbeb;color:#d97706;border-radius:6px;padding:1px 7px;margin-left:6px;font-weight:700">Pending</span>
      <?php else: ?>
        <span style="font-size:.68rem;background:#f1f5f9;color:#94a3b8;border-radius:6px;padding:1px 7px;margin-left:6px;font-weight:700">Unverified</span>
      <?php endif; ?>
    </div>
    <i class="fas fa-chevron-right profile-menu-chevron"></i>
  </a>
  <a href="<?= APP_URL ?>/wallet/transactions" class="profile-menu-item">
    <div class="profile-menu-icon navy"><i class="fas fa-history"></i></div>
    <div class="profile-menu-label">Transaction History</div>
    <i class="fas fa-chevron-right profile-menu-chevron"></i>
  </a>
</div>

<!-- Info -->
<div class="profile-section-title">Information</div>
<div style="margin:0 14px;background:white;border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05);padding:16px">
  <?php
  $rows = [
    'Phone'      => $wu['phone'],
    'National ID'=> $wu['national_id'] ?: '—',
    'Joined'     => date('d M Y', strtotime($wu['created_at'])),
    'Wallet ID'  => $wu['wallet_id'],
  ];
  foreach ($rows as $label => $val):
  ?>
  <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:.82rem">
    <span style="color:#64748b;font-weight:600"><?= $label ?></span>
    <span style="color:#0f172a;font-weight:700;font-family:<?= $label === 'Wallet ID' ? 'monospace' : 'inherit' ?>"><?= htmlspecialchars((string)$val) ?></span>
  </div>
  <?php endforeach; ?>
</div>

<!-- Referral & Cashback section -->
<div class="profile-section-title">Referral & Cashback</div>
<div style="margin:0 14px;background:white;border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05);padding:18px">

  <?php if ($referralCode): ?>
  <!-- Code display -->
  <div style="background:#f4f0ff;border-radius:14px;padding:16px;text-align:center;margin-bottom:14px">
    <div style="font-size:.7rem;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Your Referral Code</div>
    <div style="font-size:1.8rem;font-weight:800;color:#0D1B3E;letter-spacing:4px"><?= htmlspecialchars($referralCode) ?></div>
    <button onclick="copyRef('<?= addslashes($referralCode) ?>', this)"
            style="margin-top:10px;background:#7c3aed;color:#fff;border:none;border-radius:10px;padding:7px 20px;font-size:.78rem;font-weight:600;cursor:pointer;font-family:inherit">
      <i class="fas fa-copy"></i> Copy Code
    </button>
  </div>

  <!-- Stats -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px">
    <div style="text-align:center;background:#f8fafc;border-radius:12px;padding:12px 8px">
      <div style="font-size:1.3rem;font-weight:800;color:#0D1B3E"><?= $refStats['total'] ?></div>
      <div style="font-size:.68rem;color:#64748b;font-weight:600;margin-top:2px">Invited</div>
    </div>
    <div style="text-align:center;background:#f0fdf4;border-radius:12px;padding:12px 8px">
      <div style="font-size:1.3rem;font-weight:800;color:#158347"><?= $refStats['completed'] ?></div>
      <div style="font-size:.68rem;color:#64748b;font-weight:600;margin-top:2px">Transacted</div>
    </div>
    <div style="text-align:center;background:#fef9c3;border-radius:12px;padding:12px 8px">
      <div style="font-size:1.1rem;font-weight:800;color:#854d0e">KES <?= number_format($refStats['earned'], 0) ?></div>
      <div style="font-size:.68rem;color:#64748b;font-weight:600;margin-top:2px">Earned</div>
    </div>
  </div>

  <!-- Share row -->
  <div style="font-size:.75rem;font-weight:600;color:#64748b;margin-bottom:8px">Share your link</div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <a href="https://wa.me/?text=<?= urlencode('Join me on OrbitPesa Wallet and earn KES 25 cashback on your first transaction! Use my referral code ' . $referralCode . ' when you sign up: ' . $referralLink) ?>"
       target="_blank"
       style="display:flex;align-items:center;gap:6px;background:#25d366;color:#fff;border-radius:10px;padding:8px 14px;font-size:.78rem;font-weight:600;text-decoration:none">
      <i class="fab fa-whatsapp"></i> WhatsApp
    </a>
    <a href="sms:?body=<?= urlencode('Join OrbitPesa Wallet using my referral code ' . $referralCode . ': ' . $referralLink) ?>"
       style="display:flex;align-items:center;gap:6px;background:#3b82f6;color:#fff;border-radius:10px;padding:8px 14px;font-size:.78rem;font-weight:600;text-decoration:none">
      <i class="fas fa-sms"></i> SMS
    </a>
    <button onclick="copyRef('<?= addslashes($referralLink) ?>', this)"
            style="display:flex;align-items:center;gap:6px;background:#f1f5f9;color:#475569;border:none;border-radius:10px;padding:8px 14px;font-size:.78rem;font-weight:600;cursor:pointer;font-family:inherit">
      <i class="fas fa-link"></i> Copy Link
    </button>
  </div>

  <div style="margin-top:12px;padding:10px 12px;background:#f8fafc;border-radius:10px;font-size:.75rem;color:#64748b;line-height:1.5">
    <i class="fas fa-gift" style="color:#7c3aed"></i>
    You earn <strong>KES 50</strong> and your friend earns <strong>KES 25</strong> when they complete their first transaction.
  </div>

  <?php else: ?>
  <!-- No code yet — generate button -->
  <div style="text-align:center;padding:10px 0">
    <div style="font-size:1.6rem;margin-bottom:8px">🎁</div>
    <div style="font-weight:600;color:#0D1B3E;font-size:.9rem;margin-bottom:6px">Get your referral code</div>
    <div style="color:#64748b;font-size:.8rem;margin-bottom:14px">Invite friends and earn KES 50 for each one who transacts</div>
    <form method="POST" action="<?= APP_URL ?>/wallet/referral/generate" style="margin:0">
      <?= csrf_field() ?>
      <button type="submit" class="wbtn wbtn-primary" style="background:#7c3aed">
        <i class="fas fa-gift"></i> Generate My Code
      </button>
    </form>
  </div>
  <?php endif; ?>

</div>

<!-- Links -->
<div class="profile-section-title">Links</div>
<div style="margin:0 14px;background:white;border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05)">
  <a href="<?= APP_URL ?>/wallet/statement" class="profile-menu-item">
    <div class="profile-menu-icon navy"><i class="fas fa-file-lines"></i></div>
    <div class="profile-menu-label">Download Statement</div>
    <i class="fas fa-chevron-right profile-menu-chevron"></i>
  </a>
  <a href="<?= APP_URL ?>/" class="profile-menu-item" target="_blank">
    <div class="profile-menu-icon navy"><i class="fas fa-store"></i></div>
    <div class="profile-menu-label">OrbitPesa Merchant</div>
    <i class="fas fa-external-link-alt profile-menu-chevron"></i>
  </a>
  <a href="<?= APP_URL ?>/wallet/logout" class="profile-menu-item" onclick="return confirm('Log out of your wallet?')">
    <div class="profile-menu-icon red"><i class="fas fa-sign-out-alt"></i></div>
    <div class="profile-menu-label" style="color:#dc2626">Logout</div>
    <i class="fas fa-chevron-right profile-menu-chevron"></i>
  </a>
</div>
<div style="height:20px"></div>

<!-- Edit Profile Modal -->
<div class="wmodal-overlay" id="editModal">
  <div class="wmodal">
    <div class="wmodal-hd">
      <span class="wmodal-title">Edit Profile</span>
      <button class="wmodal-close" onclick="closeModal('editModal')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/wallet/profile/update">
      <?= csrf_field() ?>
      <div class="wform-group">
        <label class="wform-label">Full Name</label>
        <input type="text" name="full_name" class="wform-control" value="<?= htmlspecialchars($wu['full_name']) ?>" required>
      </div>
      <div class="wform-group">
        <label class="wform-label">Email</label>
        <input type="email" name="email" class="wform-control" value="<?= htmlspecialchars($wu['email']) ?>" required>
      </div>
      <div class="wform-group">
        <label class="wform-label">Phone</label>
        <input type="tel" name="phone" class="wform-control" value="<?= htmlspecialchars($wu['phone']) ?>">
      </div>
      <button type="submit" class="wbtn wbtn-primary" style="margin-top:8px">Save Changes</button>
    </form>
  </div>
</div>

</div>

<!-- Change Password Modal -->
<div class="wmodal-overlay" id="passwordModal">
  <div class="wmodal">
    <div class="wmodal-hd">
      <span class="wmodal-title">Change Password</span>
      <button class="wmodal-close" onclick="closeModal('passwordModal')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/wallet/profile/change-password">
      <?= csrf_field() ?>
      <div class="wform-group">
        <label class="wform-label">Current Password</label>
        <input type="password" name="current_password" class="wform-control"
               placeholder="Enter current password" required autocomplete="current-password">
      </div>
      <div class="wform-group">
        <label class="wform-label">New Password</label>
        <input type="password" name="new_password" class="wform-control"
               placeholder="Min 8 characters" required autocomplete="new-password">
      </div>
      <div class="wform-group">
        <label class="wform-label">Confirm New Password</label>
        <input type="password" name="confirm_password" class="wform-control"
               placeholder="Re-enter new password" required autocomplete="new-password">
      </div>
      <button type="submit" class="wbtn wbtn-primary" style="margin-top:8px">Change Password</button>
    </form>
  </div>
</div>

<!-- Change PIN Modal -->
<div class="wmodal-overlay" id="pinModal">
  <div class="wmodal">
    <div class="wmodal-hd">
      <span class="wmodal-title">Change PIN</span>
      <button class="wmodal-close" onclick="closeModal('pinModal')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/wallet/profile/change-pin">
      <?= csrf_field() ?>
      <div class="wform-group">
        <label class="wform-label">Current PIN</label>
        <input type="password" name="current_pin" class="wform-control pin-input"
               inputmode="numeric" maxlength="4" placeholder="····" required autocomplete="off">
      </div>
      <div class="wform-group">
        <label class="wform-label">New PIN</label>
        <input type="password" name="new_pin" class="wform-control pin-input"
               inputmode="numeric" maxlength="4" placeholder="····" required autocomplete="off">
      </div>
      <div class="wform-group">
        <label class="wform-label">Confirm New PIN</label>
        <input type="password" name="confirm_pin" class="wform-control pin-input"
               inputmode="numeric" maxlength="4" placeholder="····" required autocomplete="off">
      </div>
      <button type="submit" class="wbtn wbtn-primary" style="margin-top:8px">Change PIN</button>
    </form>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.wmodal-overlay').forEach(el => {
  el.addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
  });
});
// Auto-open modal if there was a profile or pin flash error
<?php if (flash('wallet_profile_modal') === 'edit'): ?>openModal('editModal');<?php endif; ?>
<?php if (flash('wallet_profile_modal') === 'pin'): ?>openModal('pinModal');<?php endif; ?>

function copyRef(text, btn) {
  const orig = btn.innerHTML;
  navigator.clipboard.writeText(text).then(() => {
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    setTimeout(() => { btn.innerHTML = orig; }, 2500);
  }).catch(() => {
    const ta = document.createElement('textarea');
    ta.value = text; document.body.appendChild(ta); ta.select();
    document.execCommand('copy'); document.body.removeChild(ta);
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    setTimeout(() => { btn.innerHTML = orig; }, 2500);
  });
}
</script>
