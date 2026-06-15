<?php $user = auth_user(); ?>

<div class="page-header">
  <h2>Account Settings</h2>
  <p>Manage your business profile, security, and notification preferences.</p>
</div>

<div class="tabs" style="margin-bottom:28px">
  <a href="#profile"  class="tab active" data-tab-group="settings" data-tab-target="profile">Profile</a>
  <a href="#security" class="tab" data-tab-group="settings" data-tab-target="security">Security</a>
  <a href="#webhooks" class="tab" data-tab-group="settings" data-tab-target="webhooks">Webhooks</a>
  <a href="#business" class="tab" data-tab-group="settings" data-tab-target="business">Business</a>
</div>

<!-- Profile Tab -->
<div data-tab-content="settings" data-tab-id="profile">
  <div class="grid-2-1" style="gap:20px">
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-user" style="color:var(--green);margin-right:6px"></i> Business Profile</h4></div>
      <form method="POST" action="<?= APP_URL ?>/dashboard/settings/profile">
        <?= csrf_field() ?>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Business / Full Name</label>
            <input type="text" class="form-control" name="business_name"
                   value="<?= sanitize($user['business_name'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" value="<?= sanitize($user['email'] ?? '') ?>" disabled>
            <div class="form-hint">Contact support to change your email.</div>
          </div>
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="tel" class="form-control" name="phone" value="<?= sanitize($user['phone'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Account Type</label>
            <input type="text" class="form-control" value="<?= ucfirst($user['account_type'] ?? 'Business') ?>" disabled>
          </div>
        </div>
        <div class="card-footer" style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Changes
          </button>
        </div>
      </form>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
      <!-- Account Status -->
      <div class="card">
        <div class="card-header"><h4><i class="fas fa-shield-alt" style="color:var(--green);margin-right:6px"></i> Account Status</h4></div>
        <div class="card-body">
          <div style="display:flex;flex-direction:column;gap:12px">
            <div style="display:flex;align-items:center;justify-content:space-between">
              <span style="font-size:.875rem">Account Status</span>
              <span class="badge badge-success"><?= ucfirst($user['status'] ?? 'Active') ?></span>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between">
              <span style="font-size:.875rem">KYC Verification</span>
              <?php
              $kyc = $user['kyc_status'] ?? 'unverified';
              $badge = ['unverified'=>'badge-secondary','pending'=>'badge-warning','verified'=>'badge-success'][$kyc] ?? 'badge-secondary';
              ?>
              <span class="badge <?= $badge ?>"><?= ucfirst($kyc) ?></span>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between">
              <span style="font-size:.875rem">Account Type</span>
              <span class="badge badge-navy"><?= ucfirst($user['account_type'] ?? 'Business') ?></span>
            </div>
          </div>
          <?php if ($kyc !== 'verified'): ?>
          <div class="separator"></div>
          <div class="alert alert-warning" style="margin:0">
            <i class="fas fa-exclamation-triangle"></i>
            Complete KYC to unlock live payments and higher limits.
          </div>
          <a href="#" class="btn btn-primary btn-block" style="margin-top:10px">
            <i class="fas fa-id-card"></i> Complete KYC
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="card" style="border-color:#fecaca">
        <div class="card-header" style="background:#fff5f5"><h4 style="color:var(--danger)"><i class="fas fa-exclamation-triangle" style="margin-right:6px"></i> Danger Zone</h4></div>
        <div class="card-body">
          <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:14px">
            Permanently delete your account and all associated data.
          </p>
          <button class="btn btn-danger btn-sm" data-confirm="Are you sure? This will permanently delete your account and all data. This cannot be undone.">
            <i class="fas fa-trash-alt"></i> Delete Account
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Security Tab -->
<div data-tab-content="settings" data-tab-id="security" style="display:none">
  <div class="grid-2" style="gap:20px">
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-lock" style="color:var(--green);margin-right:6px"></i> Change Password</h4></div>
      <form method="POST" action="<?= APP_URL ?>/dashboard/settings/password">
        <?= csrf_field() ?>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Current Password</label>
            <input type="password" class="form-control" name="current_password" required>
          </div>
          <div class="form-group">
            <label class="form-label">New Password</label>
            <input type="password" class="form-control" name="new_password" required>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" name="new_password_confirm" required>
          </div>
        </div>
        <div class="card-footer" style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-primary"><i class="fas fa-lock"></i> Update Password</button>
        </div>
      </form>
    </div>

    <div class="card">
      <div class="card-header"><h4><i class="fas fa-shield-alt" style="color:var(--green);margin-right:6px"></i> Two-Factor Authentication</h4></div>
      <div class="card-body">
        <div class="alert alert-info"><i class="fas fa-info-circle"></i> 2FA adds an extra layer of security to your account.</div>
        <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:16px">
          Enable two-factor authentication via SMS or an authenticator app to secure your account.
        </p>
        <button class="btn btn-outline btn-sm"><i class="fas fa-mobile-alt"></i> Enable 2FA via SMS</button>
      </div>
    </div>
  </div>
</div>

<!-- Webhooks Tab -->
<div data-tab-content="settings" data-tab-id="webhooks" style="display:none">
  <?php $webhooks = DB::fetchAll("SELECT * FROM webhooks WHERE user_id = ?", [$_SESSION['user_id']]); ?>
  <div style="display:flex;justify-content:flex-end;margin-bottom:16px">
    <button class="btn btn-primary" data-modal="addWebhookModal">
      <i class="fas fa-plus"></i> Add Webhook
    </button>
  </div>
  <div class="card">
    <div class="card-header"><h4><i class="fas fa-bell" style="color:var(--green);margin-right:6px"></i> Webhook Endpoints</h4></div>
    <div class="p-0">
      <?php if (empty($webhooks)): ?>
        <div class="empty-state"><i class="fas fa-bell-slash"></i><h4>No webhooks configured</h4><p>Add a webhook to receive real-time payment notifications.</p></div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="orb-table">
            <thead><tr><th>URL</th><th>Events</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
              <?php foreach ($webhooks as $wh): ?>
              <tr>
                <td><code style="font-size:.8rem"><?= sanitize($wh['url']) ?></code></td>
                <td><?php foreach (json_decode($wh['events'],true) as $e): ?><span class="chip" style="margin:2px"><?= sanitize($e) ?></span><?php endforeach; ?></td>
                <td><span class="badge <?= $wh['is_active'] ? 'badge-success' : 'badge-secondary' ?>"><?= $wh['is_active'] ? 'Active' : 'Disabled' ?></span></td>
                <td>
                  <form method="POST" action="<?= APP_URL ?>/dashboard/settings/webhooks/delete">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= sanitize($wh['id']) ?>">
                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)" data-confirm="Delete this webhook?">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Add Webhook Modal -->
  <div class="modal-backdrop" id="addWebhookModal">
    <div class="modal">
      <div class="modal-header"><h4><i class="fas fa-bell" style="color:var(--green);margin-right:8px"></i> Add Webhook</h4><button class="modal-close"><i class="fas fa-times"></i></button></div>
      <form method="POST" action="<?= APP_URL ?>/dashboard/settings/webhooks/add">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Webhook URL</label>
            <input type="url" class="form-control" name="url" placeholder="https://yourapp.com/webhook" required>
          </div>
          <div class="form-group">
            <label class="form-label">Events to Subscribe</label>
            <?php foreach (['payment.completed','payment.failed','payment.pending','withdrawal.completed','refund.initiated'] as $event): ?>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
              <input type="checkbox" name="events[]" value="<?= $event ?>" id="ev_<?= str_replace('.','_',$event) ?>" style="accent-color:var(--green)">
              <label for="ev_<?= str_replace('.','_',$event) ?>" style="font-size:.875rem;cursor:pointer"><code><?= $event ?></code></label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost modal-close">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Webhook</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Business Tab -->
<div data-tab-content="settings" data-tab-id="business" style="display:none">
  <div class="card">
    <div class="card-header"><h4><i class="fas fa-building" style="color:var(--green);margin-right:6px"></i> Business Information</h4></div>
    <div class="card-body">
      <div class="grid-2" style="gap:16px">
        <div class="form-group">
          <label class="form-label">Business Registration Number</label>
          <input type="text" class="form-control" placeholder="e.g. CPR/2020/123456">
        </div>
        <div class="form-group">
          <label class="form-label">Industry / Business Type</label>
          <select class="form-control form-select">
            <option>E-Commerce</option><option>Services</option><option>Healthcare</option>
            <option>Education</option><option>Real Estate</option><option>Other</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Physical Address</label>
          <input type="text" class="form-control" placeholder="Nairobi, Kenya">
        </div>
        <div class="form-group">
          <label class="form-label">Website (optional)</label>
          <input type="url" class="form-control" placeholder="https://yourbusiness.co.ke">
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;margin-top:8px">
        <button class="btn btn-primary"><i class="fas fa-save"></i> Save Business Info</button>
      </div>
    </div>
  </div>
</div>
