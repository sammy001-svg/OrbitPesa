<?php
$webhooks   = Webhook::getForUser($_SESSION['user_id']);
$deliveries = Webhook::getRecentDeliveriesForUser($_SESSION['user_id'], 20);

// Selected webhook for delivery log
$selectedId = $_GET['webhook'] ?? null;
$selectedWh = null;
$selectedDeliveries = [];
if ($selectedId) {
    foreach ($webhooks as $wh) {
        if ($wh['id'] === $selectedId) { $selectedWh = $wh; break; }
    }
    if ($selectedWh) $selectedDeliveries = Webhook::getDeliveries($selectedId);
}

$allEvents = [
    'payment.completed' => 'Payment Completed',
    'payment.failed'    => 'Payment Failed',
    'payment.pending'   => 'Payment Initiated (M-Pesa STK sent)',
    'withdrawal.created'=> 'Withdrawal Requested',
    'withdrawal.done'   => 'Withdrawal Processed',
];
?>

<div class="page-header">
  <div>
    <h2>Webhooks</h2>
    <p>Receive real-time HTTP notifications when events happen on your account.</p>
  </div>
  <button class="btn btn-primary" data-modal="addWebhookModal">
    <i class="fas fa-plus"></i> Add Endpoint
  </button>
</div>

<?php if ($s = flash('success')): ?>
  <div class="alert alert-success" style="margin-bottom:16px"><i class="fas fa-check-circle"></i> <?= sanitize($s) ?></div>
<?php endif; ?>
<?php if ($e = flash('error')): ?>
  <div class="alert alert-danger" style="margin-bottom:16px"><i class="fas fa-exclamation-circle"></i> <?= sanitize($e) ?></div>
<?php endif; ?>

<!-- Intro banner -->
<div class="card" style="margin-bottom:20px;border-left:4px solid var(--green)">
  <div class="card-body" style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap">
    <div style="flex:1;min-width:220px">
      <h4 style="color:var(--navy);margin-bottom:6px"><i class="fas fa-shield-alt" style="color:var(--green);margin-right:8px"></i>HMAC Verification</h4>
      <p style="font-size:.85rem;color:var(--text-muted);line-height:1.6">
        Every request includes an <code>X-OrbitPesa-Signature</code> header signed with your endpoint's secret using HMAC-SHA256.
        Verify it in your server to ensure authenticity.
      </p>
    </div>
    <div style="flex:1;min-width:220px">
      <h4 style="color:var(--navy);margin-bottom:6px"><i class="fas fa-redo" style="color:var(--green);margin-right:8px"></i>Automatic Retries</h4>
      <p style="font-size:.85rem;color:var(--text-muted);line-height:1.6">
        Failed deliveries are retried up to 3 times with exponential backoff. Your endpoint must return a 2xx status code to confirm receipt.
      </p>
    </div>
    <div>
      <a href="<?= APP_URL ?>/developers/docs#webhooks" class="btn btn-ghost btn-sm" style="white-space:nowrap">
        <i class="fas fa-book"></i> Docs
      </a>
    </div>
  </div>
</div>

<!-- Verification snippet -->
<div class="card" style="margin-bottom:24px">
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
    <h4><i class="fas fa-code" style="color:var(--green);margin-right:8px"></i>Verification Example (PHP)</h4>
  </div>
  <div class="card-body" style="padding:0">
    <pre style="background:#0D1B3E;color:#a5f3b0;padding:20px;margin:0;border-radius:0 0 var(--radius) var(--radius);font-size:.82rem;overflow-x:auto;line-height:1.7"><?php echo htmlspecialchars('<?php
$payload   = file_get_contents(\'php://input\');
$sigHeader = $_SERVER[\'HTTP_X_ORBITPESA_SIGNATURE\'] ?? \'\';
$secret    = \'YOUR_WEBHOOK_SECRET\';

$expected  = \'sha256=\' . hash_hmac(\'sha256\', $payload, $secret);

if (!hash_equals($expected, $sigHeader)) {
    http_response_code(401);
    exit(\'Invalid signature\');
}

$event = json_decode($payload, true);
// Handle $event[\'event\'] ...
http_response_code(200);'); ?></pre>
  </div>
</div>

<?php if (empty($webhooks)): ?>
  <div class="card">
    <div class="empty-state">
      <i class="fas fa-satellite-dish" style="color:var(--text-muted)"></i>
      <h4>No webhook endpoints yet</h4>
      <p>Add an endpoint URL to start receiving real-time payment notifications.</p>
      <button class="btn btn-primary" data-modal="addWebhookModal"><i class="fas fa-plus"></i> Add First Endpoint</button>
    </div>
  </div>

<?php else: ?>

<!-- Endpoints list -->
<div class="card" style="margin-bottom:24px">
  <div class="card-header"><h4>Registered Endpoints</h4></div>
  <div class="p-0">
    <div class="table-wrap">
      <table class="orb-table">
        <thead>
          <tr><th>Endpoint</th><th>Events</th><th>Success Rate</th><th>Status</th><th style="width:180px">Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($webhooks as $wh): ?>
          <?php
          $evts  = json_decode($wh['events'], true) ?? [];
          $rate  = $wh['total_deliveries'] > 0 ? round($wh['successful_deliveries'] / $wh['total_deliveries'] * 100) : null;
          ?>
          <tr>
            <td>
              <div style="font-weight:600;font-size:.875rem;color:var(--navy);word-break:break-all"><?= sanitize($wh['label']) ?></div>
              <div style="font-size:.76rem;color:var(--text-muted);font-family:monospace;margin-top:2px"><?= sanitize($wh['url']) ?></div>
            </td>
            <td>
              <div style="display:flex;flex-wrap:wrap;gap:4px">
                <?php foreach ($evts as $ev): ?>
                  <span class="badge badge-navy" style="font-size:.65rem;padding:2px 7px"><?= sanitize($ev) ?></span>
                <?php endforeach; ?>
              </div>
            </td>
            <td>
              <?php if ($rate !== null): ?>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="flex:1;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden">
                  <div style="width:<?= $rate ?>%;height:100%;background:<?= $rate >= 90 ? 'var(--green)' : ($rate >= 70 ? '#f59e0b' : '#dc2626') ?>"></div>
                </div>
                <span style="font-size:.8rem;font-weight:600"><?= $rate ?>%</span>
              </div>
              <div style="font-size:.72rem;color:var(--text-muted);margin-top:2px"><?= $wh['successful_deliveries'] ?>/<?= $wh['total_deliveries'] ?> successful</div>
              <?php else: ?>
                <span style="font-size:.8rem;color:var(--text-muted)">No deliveries yet</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge <?= $wh['is_active'] ? 'badge-success' : 'badge-warning' ?>">
                <?= $wh['is_active'] ? 'Active' : 'Disabled' ?>
              </span>
            </td>
            <td>
              <div style="display:flex;gap:6px;flex-wrap:wrap">
                <a href="?webhook=<?= $wh['id'] ?>" class="btn btn-ghost btn-sm" title="View deliveries">
                  <i class="fas fa-history"></i>
                </a>
                <form method="POST" action="<?= APP_URL ?>/dashboard/webhooks/toggle" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= $wh['id'] ?>">
                  <button type="submit" class="btn btn-ghost btn-sm" title="<?= $wh['is_active'] ? 'Disable' : 'Enable' ?>">
                    <i class="fas fa-<?= $wh['is_active'] ? 'pause' : 'play' ?>"></i>
                  </button>
                </form>
                <form method="POST" action="<?= APP_URL ?>/dashboard/webhooks/delete" style="display:inline"
                      onsubmit="return confirm('Delete this webhook endpoint?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= $wh['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Selected webhook delivery log -->
<?php if ($selectedWh): ?>
<div class="section-hd" style="margin-bottom:12px">
  <div>
    <h3 style="font-size:1rem;color:var(--navy)"><?= sanitize($selectedWh['label']) ?> — Delivery Log</h3>
    <p style="font-size:.8rem;color:var(--text-muted);font-family:monospace"><?= sanitize($selectedWh['url']) ?></p>
  </div>
  <a href="<?= APP_URL ?>/dashboard/webhooks" class="btn btn-ghost btn-sm">← Back</a>
</div>

<!-- Secret reveal -->
<div class="card" style="margin-bottom:16px;border-left:4px solid var(--navy)">
  <div class="card-body" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <div style="flex:1">
      <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:4px;font-weight:600">SIGNING SECRET</div>
      <code id="secretVal" style="font-size:.82rem;color:var(--navy)">••••••••••••••••••••••••••••••••</code>
      <button onclick="toggleSecret('<?= htmlspecialchars($selectedWh['secret'], ENT_QUOTES) ?>')" class="btn btn-ghost btn-sm" style="margin-left:8px" id="revealBtn">
        <i class="fas fa-eye"></i> Reveal
      </button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/dashboard/webhooks/regen-secret"
          onsubmit="return confirm('Generate a new secret? Your existing integration will stop working until updated.')">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= $selectedWh['id'] ?>">
      <button type="submit" class="btn btn-ghost btn-sm"><i class="fas fa-sync"></i> Regenerate</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header"><h4>Recent Deliveries</h4></div>
  <div class="p-0">
    <?php if (empty($selectedDeliveries)): ?>
      <div class="empty-state"><i class="fas fa-inbox"></i><h4>No deliveries yet</h4><p>Events will appear here once a payment occurs.</p></div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="orb-table">
        <thead>
          <tr><th>Event</th><th>Reference</th><th>Status</th><th>HTTP</th><th>Attempts</th><th>Time</th></tr>
        </thead>
        <tbody>
          <?php foreach ($selectedDeliveries as $d): ?>
          <tr>
            <td><code style="font-size:.78rem"><?= sanitize($d['event']) ?></code></td>
            <td style="font-size:.8rem;font-family:monospace"><?= $d['transaction_ref'] ? sanitize($d['transaction_ref']) : '—' ?></td>
            <td>
              <span class="badge <?= $d['status'] === 'success' ? 'badge-success' : ($d['status'] === 'failed' ? 'badge-danger' : 'badge-warning') ?>">
                <i class="fas fa-<?= $d['status'] === 'success' ? 'check' : ($d['status'] === 'failed' ? 'times' : 'clock') ?>"></i>
                <?= ucfirst($d['status']) ?>
              </span>
            </td>
            <td>
              <?php if ($d['response_status']): ?>
                <span style="font-weight:700;color:<?= $d['response_status'] >= 200 && $d['response_status'] < 300 ? 'var(--success)' : 'var(--danger)' ?>;font-size:.85rem">
                  <?= $d['response_status'] ?>
                </span>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td style="font-size:.85rem"><?= $d['attempts'] ?></td>
            <td style="font-size:.8rem;color:var(--text-muted)"><?= date('d M H:i', strtotime($d['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php else: ?>

<!-- Recent deliveries (all webhooks) -->
<?php if (!empty($deliveries)): ?>
<div class="card">
  <div class="card-header"><h4><i class="fas fa-history" style="color:var(--green);margin-right:8px"></i>Recent Deliveries (All Endpoints)</h4></div>
  <div class="p-0">
    <div class="table-wrap">
      <table class="orb-table">
        <thead>
          <tr><th>Endpoint</th><th>Event</th><th>Reference</th><th>Status</th><th>HTTP</th><th>Time</th></tr>
        </thead>
        <tbody>
          <?php foreach ($deliveries as $d): ?>
          <tr>
            <td>
              <div style="font-size:.82rem;font-weight:600;color:var(--navy)"><?= sanitize($d['webhook_label']) ?></div>
              <div style="font-size:.72rem;color:var(--text-muted);font-family:monospace"><?= sanitize(parse_url($d['webhook_url'], PHP_URL_HOST) ?? $d['webhook_url']) ?></div>
            </td>
            <td><code style="font-size:.76rem"><?= sanitize($d['event']) ?></code></td>
            <td style="font-size:.78rem;font-family:monospace"><?= $d['transaction_ref'] ? sanitize($d['transaction_ref']) : '—' ?></td>
            <td>
              <span class="badge <?= $d['status'] === 'success' ? 'badge-success' : ($d['status'] === 'failed' ? 'badge-danger' : 'badge-warning') ?>">
                <?= ucfirst($d['status']) ?>
              </span>
            </td>
            <td style="font-size:.85rem;font-weight:700;color:<?= ($d['response_status'] >= 200 && $d['response_status'] < 300) ? 'var(--success)' : 'var(--danger)' ?>">
              <?= $d['response_status'] ?? '—' ?>
            </td>
            <td style="font-size:.78rem;color:var(--text-muted)"><?= date('d M H:i', strtotime($d['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php endif; ?>

<!-- Add Webhook Modal -->
<div class="modal-backdrop" id="addWebhookModal">
  <div class="modal">
    <div class="modal-header">
      <h4><i class="fas fa-satellite-dish" style="color:var(--green);margin-right:8px"></i> Add Webhook Endpoint</h4>
      <button class="modal-close"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/dashboard/webhooks/create">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Endpoint Label</label>
          <input type="text" class="form-control" name="label" placeholder="e.g. Production Server" required>
        </div>
        <div class="form-group">
          <label class="form-label">Endpoint URL</label>
          <input type="url" class="form-control" name="url" placeholder="https://yoursite.com/webhook/orbitpesa" required>
          <div class="form-hint">Must be publicly accessible (HTTPS recommended in production)</div>
        </div>
        <div class="form-group">
          <label class="form-label">Subscribe to Events</label>
          <div style="display:flex;flex-direction:column;gap:8px;margin-top:6px">
            <?php foreach ($allEvents as $ev => $label): ?>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.875rem">
              <input type="checkbox" name="events[]" value="<?= $ev ?>"
                <?= in_array($ev, ['payment.completed','payment.failed']) ? 'checked' : '' ?>>
              <span style="font-weight:600"><?= sanitize($label) ?></span>
              <code style="font-size:.72rem;color:var(--text-muted)"><?= $ev ?></code>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="alert alert-info" style="font-size:.8rem">
          <i class="fas fa-info-circle"></i>
          A unique signing secret will be generated automatically. You can view it after adding the endpoint.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost modal-close">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Endpoint</button>
      </div>
    </form>
  </div>
</div>

<script>
let secretRevealed = false;
function toggleSecret(val) {
  secretRevealed = !secretRevealed;
  document.getElementById('secretVal').textContent = secretRevealed ? val : '••••••••••••••••••••••••••••••••';
  document.getElementById('revealBtn').innerHTML = secretRevealed
    ? '<i class="fas fa-eye-slash"></i> Hide'
    : '<i class="fas fa-eye"></i> Reveal';
}
</script>
