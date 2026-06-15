<?php $keys = ApiKey::getForUser($_SESSION['user_id']); ?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
  <div>
    <h2>API Keys</h2>
    <p>Manage your API keys for integrating OrbitPesa into your applications.</p>
  </div>
  <button class="btn btn-primary" data-modal="createKeyModal">
    <i class="fas fa-plus"></i> Generate API Key
  </button>
</div>

<div class="grid-2 mb-6" style="gap:16px">
  <div class="card" style="border-left:4px solid var(--warning)">
    <div class="card-body" style="display:flex;align-items:center;gap:14px">
      <div style="width:42px;height:42px;background:#fef9c3;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#854d0e">
        <i class="fas fa-flask"></i>
      </div>
      <div>
        <div style="font-weight:700;color:var(--navy)">Test Environment</div>
        <div style="font-size:.82rem;color:var(--text-muted)">Use test keys for development. No real money moves.</div>
      </div>
    </div>
  </div>
  <div class="card" style="border-left:4px solid var(--green)">
    <div class="card-body" style="display:flex;align-items:center;gap:14px">
      <div style="width:42px;height:42px;background:var(--green-light);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--green)">
        <i class="fas fa-check-circle"></i>
      </div>
      <div>
        <div style="font-weight:700;color:var(--navy)">Live Environment</div>
        <div style="font-size:.82rem;color:var(--text-muted)">Live keys process real payments. Keep them secret.</div>
      </div>
    </div>
  </div>
</div>

<div class="alert alert-warning mb-6">
  <i class="fas fa-exclamation-triangle"></i>
  <div>
    <strong>Keep your API keys secret.</strong> Never commit them to version control or expose them in client-side code.
    Use environment variables or a secrets manager.
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h4><i class="fas fa-key" style="color:var(--green);margin-right:6px"></i> Your API Keys (<?= count($keys) ?>)</h4>
  </div>
  <div class="p-0">
    <?php if (empty($keys)): ?>
      <div class="empty-state">
        <i class="fas fa-key"></i>
        <h4>No API keys yet</h4>
        <p>Generate your first API key to start integrating.</p>
        <button class="btn btn-primary" data-modal="createKeyModal" style="margin-top:12px">
          <i class="fas fa-plus"></i> Generate Key
        </button>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="orb-table">
          <thead>
            <tr><th>Label</th><th>Key</th><th>Environment</th><th>Status</th><th>Last Used</th><th>Created</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($keys as $key): ?>
            <tr>
              <td style="font-weight:600"><?= sanitize($key['label']) ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:8px">
                  <code style="font-size:.79rem"><?= sanitize($key['masked_key']) ?></code>
                  <button class="copy-btn" style="position:static;font-size:.72rem;padding:3px 8px"
                          data-copy="<?= sanitize($key['masked_key']) ?>">Copy</button>
                </div>
              </td>
              <td>
                <span class="badge <?= $key['environment'] === 'live' ? 'badge-success' : 'badge-warning' ?>">
                  <?= strtoupper($key['environment']) ?>
                </span>
              </td>
              <td>
                <span class="badge <?= $key['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
                  <?= $key['is_active'] ? 'Active' : 'Revoked' ?>
                </span>
              </td>
              <td style="font-size:.82rem;color:var(--text-muted)">
                <?= $key['last_used_at'] ? time_ago($key['last_used_at']) : 'Never' ?>
              </td>
              <td style="font-size:.82rem;color:var(--text-muted)"><?= date('d M Y', strtotime($key['created_at'])) ?></td>
              <td>
                <div style="display:flex;gap:6px">
                  <?php if ($key['is_active']): ?>
                  <form method="POST" action="<?= APP_URL ?>/dashboard/api-keys/revoke" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= sanitize($key['id']) ?>">
                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--warning)"
                            data-confirm="Revoke this API key? Apps using it will stop working immediately.">
                      <i class="fas fa-ban"></i> Revoke
                    </button>
                  </form>
                  <?php endif; ?>
                  <form method="POST" action="<?= APP_URL ?>/dashboard/api-keys/delete" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= sanitize($key['id']) ?>">
                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)"
                            data-confirm="Delete this API key permanently?">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Base URL & Authentication Reference -->
<div class="card mt-6">
  <div class="card-header"><h4><i class="fas fa-terminal" style="color:var(--green);margin-right:6px"></i> Quick Reference</h4></div>
  <div class="card-body">
    <div class="grid-2" style="gap:20px">
      <div>
        <div style="font-weight:600;font-size:.85rem;color:var(--navy);margin-bottom:8px">Base URL</div>
        <div class="copy-field">
          <input type="text" value="<?= APP_URL ?>/api/v1" readonly>
          <button onclick="copyToClipboard('<?= APP_URL ?>/api/v1', this)">Copy</button>
        </div>
      </div>
      <div>
        <div style="font-weight:600;font-size:.85rem;color:var(--navy);margin-bottom:8px">Authentication Header</div>
        <div class="copy-field">
          <input type="text" value="X-API-Key: op_test_your_key_here" readonly>
          <button onclick="copyToClipboard('X-API-Key: op_test_your_key_here', this)">Copy</button>
        </div>
      </div>
    </div>
    <div style="margin-top:16px">
      <div style="font-weight:600;font-size:.85rem;color:var(--navy);margin-bottom:8px">Example cURL Request</div>
      <div class="code-block">
        <button class="copy-btn">Copy</button>
        <pre>curl -X POST <?= APP_URL ?>/api/v1/payments/mpesa/stk \
  -H <span class="str">"Content-Type: application/json"</span> \
  -H <span class="str">"X-API-Key: op_test_your_key_here"</span> \
  -d '<span class="str">{"phone":"0712345678","amount":500,"description":"Test payment"}'</span></pre>
      </div>
    </div>
    <div style="margin-top:12px">
      <a href="<?= APP_URL ?>/developers/docs" class="btn btn-outline btn-sm">
        <i class="fas fa-book"></i> Full API Documentation
      </a>
    </div>
  </div>
</div>

<!-- Generate Key Modal -->
<div class="modal-backdrop" id="createKeyModal">
  <div class="modal">
    <div class="modal-header">
      <h4><i class="fas fa-key" style="color:var(--green);margin-right:8px"></i> Generate API Key</h4>
      <button class="modal-close"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/dashboard/api-keys/create">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Key Label *</label>
          <input type="text" class="form-control" name="label" placeholder="e.g. Production Server, Dev Machine" required>
          <div class="form-hint">A descriptive name so you can identify the key later.</div>
        </div>
        <div class="form-group">
          <label class="form-label">Environment *</label>
          <select class="form-control form-select" name="environment">
            <option value="test">Test (sandbox — no real money)</option>
            <option value="live">Live (real payments)</option>
          </select>
        </div>
        <div class="alert alert-info" style="margin-top:4px">
          <i class="fas fa-info-circle"></i>
          The full key will only be shown once after creation. Copy and store it securely.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost modal-close">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Generate Key</button>
      </div>
    </form>
  </div>
</div>
