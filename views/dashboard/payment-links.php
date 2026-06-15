<?php $links = PaymentLink::getForUser($_SESSION['user_id']); ?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
  <div>
    <h2>Payment Links</h2>
    <p>Create shareable links to collect payments — no code required.</p>
  </div>
  <button class="btn btn-primary" data-modal="createLinkModal">
    <i class="fas fa-plus"></i> New Payment Link
  </button>
</div>

<?php if (empty($links)): ?>
<div class="card">
  <div class="empty-state" style="padding:64px 24px">
    <i class="fas fa-link" style="font-size:3rem;color:var(--text-light);margin-bottom:16px;display:block"></i>
    <h4>No payment links yet</h4>
    <p style="margin-bottom:20px">Create your first payment link to start collecting payments via WhatsApp, email or social media.</p>
    <button class="btn btn-primary" data-modal="createLinkModal">
      <i class="fas fa-plus"></i> Create Payment Link
    </button>
  </div>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:20px">
  <?php foreach ($links as $link): ?>
  <div class="card">
    <div class="card-header" style="padding:16px 20px">
      <div style="flex:1;min-width:0">
        <div style="font-weight:700;color:var(--navy);margin-bottom:2px"><?= sanitize($link['title']) ?></div>
        <div style="font-size:.78rem;color:var(--text-muted)"><?= $link['uses'] ?> payments collected</div>
      </div>
      <span class="badge <?= $link['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
        <?= ucfirst($link['status']) ?>
      </span>
    </div>
    <div class="card-body" style="padding:16px 20px">
      <?php if ($link['description']): ?>
        <p style="font-size:.84rem;color:var(--text-muted);margin-bottom:12px"><?= sanitize(substr($link['description'], 0, 80)) ?></p>
      <?php endif; ?>

      <div style="display:flex;justify-content:space-between;margin-bottom:14px">
        <div>
          <div style="font-size:.75rem;color:var(--text-muted)">Amount</div>
          <div style="font-weight:700;color:var(--navy)">
            <?= $link['is_fixed_amount'] ? format_amount($link['amount'], $link['currency']) : 'Customer enters amount' ?>
          </div>
        </div>
        <?php if ($link['expires_at']): ?>
        <div>
          <div style="font-size:.75rem;color:var(--text-muted)">Expires</div>
          <div style="font-size:.85rem;font-weight:600"><?= date('d M Y', strtotime($link['expires_at'])) ?></div>
        </div>
        <?php endif; ?>
        <?php if ($link['max_uses']): ?>
        <div>
          <div style="font-size:.75rem;color:var(--text-muted)">Limit</div>
          <div style="font-size:.85rem;font-weight:600"><?= $link['uses'] ?>/<?= $link['max_uses'] ?> uses</div>
        </div>
        <?php endif; ?>
      </div>

      <?php $url = APP_URL . '/pay/' . $link['slug']; ?>
      <div class="copy-field" style="margin-bottom:12px">
        <input type="text" value="<?= $url ?>" readonly>
        <button onclick="copyToClipboard('<?= $url ?>', this)">Copy</button>
      </div>

      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="<?= $url ?>" target="_blank" class="btn btn-outline btn-sm">
          <i class="fas fa-external-link-alt"></i> Open
        </a>
        <?php if ($link['status'] === 'active'): ?>
        <form method="POST" action="<?= APP_URL ?>/dashboard/payment-links/deactivate" style="display:inline">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= sanitize($link['id']) ?>">
          <button type="submit" class="btn btn-ghost btn-sm" data-confirm="Deactivate this payment link?">
            <i class="fas fa-pause"></i> Deactivate
          </button>
        </form>
        <?php endif; ?>
        <form method="POST" action="<?= APP_URL ?>/dashboard/payment-links/delete" style="display:inline">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= sanitize($link['id']) ?>">
          <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)" data-confirm="Delete this payment link? This cannot be undone.">
            <i class="fas fa-trash"></i>
          </button>
        </form>
      </div>
    </div>
    <div class="card-footer" style="font-size:.75rem;color:var(--text-muted)">
      Created <?= time_ago($link['created_at']) ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Create Payment Link Modal -->
<div class="modal-backdrop" id="createLinkModal">
  <div class="modal" style="max-width:520px">
    <div class="modal-header">
      <h4><i class="fas fa-link" style="color:var(--green);margin-right:8px"></i> New Payment Link</h4>
      <button class="modal-close"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/dashboard/payment-links/create">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Link Title *</label>
          <input type="text" class="form-control" name="title" placeholder="e.g. Monthly Rent, Product Payment" required>
        </div>
        <div class="form-group">
          <label class="form-label">Description (optional)</label>
          <textarea class="form-control" name="description" rows="2" placeholder="Brief description shown to payers" style="resize:vertical"></textarea>
        </div>

        <div style="display:flex;gap:10px;margin-bottom:16px">
          <label class="toggle-wrap">
            <div class="toggle on" id="fixedToggle"></div>
            <input type="hidden" name="is_fixed_amount" id="fixedInput" value="1">
            <span style="font-size:.875rem;font-weight:500">Fixed Amount</span>
          </label>
        </div>

        <div class="form-group" id="amountField">
          <label class="form-label">Amount (KES) *</label>
          <div class="input-group">
            <span class="input-addon">KES</span>
            <input type="number" class="form-control" name="amount" placeholder="0.00" min="1" step="0.01">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div class="form-group">
            <label class="form-label">Max Uses (optional)</label>
            <input type="number" class="form-control" name="max_uses" placeholder="Unlimited" min="1">
          </div>
          <div class="form-group">
            <label class="form-label">Expiry Date (optional)</label>
            <input type="date" class="form-control" name="expires_at" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost modal-close">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-link"></i> Create Link</button>
      </div>
    </form>
  </div>
</div>

<script>
const fixedToggle = document.getElementById('fixedToggle');
const fixedInput  = document.getElementById('fixedInput');
const amountField = document.getElementById('amountField');
if (fixedToggle) {
  fixedToggle.addEventListener('click', () => {
    const isOn = fixedToggle.classList.contains('on');
    fixedInput.value = isOn ? '0' : '1';
    amountField.style.display = isOn ? 'none' : '';
  });
}
</script>
