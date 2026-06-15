<?php
$settings = [];
foreach (DB::fetchAll("SELECT * FROM system_settings") as $s) {
    $settings[$s['key']] = $s;
}
$get = fn($key) => $settings[$key]['value'] ?? '';
?>

<div class="section-hd">
  <div>
    <h2>System Settings</h2>
    <p>Configure global platform behaviour, limits, and integrations.</p>
  </div>
</div>

<form method="POST" action="<?= APP_URL ?>/admin/settings/update">
  <?= csrf_field() ?>

  <div style="display:flex;flex-direction:column;gap:20px">

    <!-- Platform Settings -->
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-cog" style="color:var(--green);margin-right:6px"></i> Platform Settings</h4></div>
      <div class="card-body">
        <div class="grid-2" style="gap:16px">
          <div>
            <label class="form-label" style="display:flex;align-items:center;justify-content:space-between">
              Maintenance Mode
              <label class="toggle-wrap">
                <div class="toggle <?= $get('maintenance_mode')?'on':'' ?>" id="maintToggle"></div>
                <input type="hidden" name="settings[maintenance_mode]" id="maintInput" value="<?= $get('maintenance_mode') ?>">
              </label>
            </label>
            <div class="form-hint">When enabled, all merchant-facing pages show a maintenance message.</div>
          </div>
          <div>
            <label class="form-label" style="display:flex;align-items:center;justify-content:space-between">
              New Registrations
              <label class="toggle-wrap">
                <div class="toggle <?= $get('new_registrations')?'on':'' ?>"></div>
                <input type="hidden" name="settings[new_registrations]" value="<?= $get('new_registrations') ?>">
              </label>
            </label>
            <div class="form-hint">Allow new merchants to register on the platform.</div>
          </div>
          <div>
            <label class="form-label" style="display:flex;align-items:center;justify-content:space-between">
              Require KYC for Live Keys
              <label class="toggle-wrap">
                <div class="toggle <?= $get('kyc_required_for_live')?'on':'' ?>"></div>
                <input type="hidden" name="settings[kyc_required_for_live]" value="<?= $get('kyc_required_for_live') ?>">
              </label>
            </label>
            <div class="form-hint">Merchants must pass KYC before activating live API keys.</div>
          </div>
          <div>
            <label class="form-label" style="display:flex;align-items:center;justify-content:space-between">
              M-Pesa Sandbox Mode
              <label class="toggle-wrap">
                <div class="toggle <?= $get('mpesa_sandbox')?'on':'' ?>"></div>
                <input type="hidden" name="settings[mpesa_sandbox]" value="<?= $get('mpesa_sandbox') ?>">
              </label>
            </label>
            <div class="form-hint">Use Safaricom sandbox API (no real M-Pesa transactions).</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Financial Settings -->
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-money-bill-wave" style="color:var(--green);margin-right:6px"></i> Financial Limits</h4></div>
      <div class="card-body">
        <div class="grid-2" style="gap:16px">
          <div class="form-group">
            <label class="form-label">Max Daily Withdrawal per Merchant (KES)</label>
            <div class="input-group">
              <span class="input-addon">KES</span>
              <input type="number" class="form-control" name="settings[max_daily_withdrawal]"
                     value="<?= $get('max_daily_withdrawal') ?>" min="0" step="1">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Minimum Withdrawal Amount (KES)</label>
            <div class="input-group">
              <span class="input-addon">KES</span>
              <input type="number" class="form-control" name="settings[min_withdrawal_amount]"
                     value="<?= $get('min_withdrawal_amount') ?>" min="0" step="1">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Bank Settlement Days</label>
            <input type="number" class="form-control" name="settings[settlement_days]"
                   value="<?= $get('settlement_days') ?>" min="0" max="7" step="1">
            <div class="form-hint">0 = instant settlement, 1 = next day, etc.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact & Company -->
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-building" style="color:var(--green);margin-right:6px"></i> Company Info</h4></div>
      <div class="card-body">
        <div class="grid-2" style="gap:16px">
          <div class="form-group">
            <label class="form-label">Company Name</label>
            <input type="text" class="form-control" name="settings[company_name]"
                   value="<?= sanitize($get('company_name')) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Support Email</label>
            <input type="email" class="form-control" name="settings[support_email]"
                   value="<?= sanitize($get('support_email')) ?>">
          </div>
        </div>
      </div>
    </div>

    <!-- Save -->
    <div style="display:flex;justify-content:flex-end;gap:10px">
      <a href="<?= APP_URL ?>/admin/settings" class="btn btn-ghost">Discard</a>
      <button type="submit" class="btn btn-primary btn-lg">
        <i class="fas fa-save"></i> Save Settings
      </button>
    </div>

  </div>
</form>

<!-- Email Actions -->
<div class="card" style="margin-top:24px">
  <div class="card-header"><h4><i class="fas fa-envelope" style="color:var(--green);margin-right:6px"></i> Email Actions</h4></div>
  <div class="card-body">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
      <div>
        <div style="font-weight:600;font-size:.9rem;color:var(--navy)">Weekly Summary Emails</div>
        <div style="font-size:.82rem;color:var(--text-muted);margin-top:3px">
          Send a 7-day performance summary to all active merchants. Includes total received, transaction count, success rate, and wallet balance.
        </div>
      </div>
      <form method="POST" action="<?= APP_URL ?>/admin/weekly-summary" style="flex-shrink:0">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-primary" onclick="return confirm('Send weekly summary to ALL active merchants?')">
          <i class="fas fa-paper-plane"></i> Send Now
        </button>
      </form>
    </div>
  </div>
</div>
