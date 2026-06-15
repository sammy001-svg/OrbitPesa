<?php
$pageTitle = 'My Profile';
$backUrl   = APP_URL . '/wallet/home';
$wu        = $walletUser;
$initial   = strtoupper(substr($wu['full_name'], 0, 1));
$txnCount  = WalletTransaction::countForUser($wu['id']);
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

<!-- Links -->
<div class="profile-section-title">Links</div>
<div style="margin:0 14px;background:white;border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05)">
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
</script>
