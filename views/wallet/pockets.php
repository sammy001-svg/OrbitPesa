<?php
$pageTitle = 'Savings Pockets';
$backUrl   = APP_URL . '/wallet/home';

$pockets      = WalletPocket::findForUser($walletUser['id']);
$totalPockets = WalletPocket::totalBalance($walletUser['id']);

$emojis = ['💰','🏠','✈️','🎓','🚗','📱','💊','🎁','🛒','⚽'];
?>

<div style="padding-top:6px">

  <?php if ($flash = flash('wallet_success')): ?>
  <div class="walert walert-success" style="margin:0 0 12px">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flash) ?>
  </div>
  <?php endif; ?>
  <?php if ($flash = flash('wallet_error')): ?>
  <div class="walert walert-error" style="margin:0 0 12px">
    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($flash) ?>
  </div>
  <?php endif; ?>

  <!-- Total savings strip -->
  <div style="background:#0D1B3E;border-radius:18px;margin:0 0 16px;padding:18px 20px;display:flex;align-items:center;justify-content:space-between">
    <div>
      <div style="color:rgba(255,255,255,.6);font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Total in Pockets</div>
      <div style="color:#fff;font-size:1.6rem;font-weight:700;margin-top:2px">
        KES <?= number_format($totalPockets, 2) ?>
      </div>
    </div>
    <div style="font-size:2rem">🐖</div>
  </div>

  <!-- Pocket list -->
  <?php if (empty($pockets)): ?>
  <div class="wempty" style="margin-bottom:16px">
    <i class="fas fa-piggy-bank"></i>
    <p>No pockets yet</p>
    <p style="font-size:.75rem;margin-top:4px">Create one below to start saving for a goal</p>
  </div>
  <?php else: ?>
  <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px">
    <?php foreach ($pockets as $p):
      $pct = WalletPocket::progressPercent($p);
    ?>
    <a href="<?= APP_URL ?>/wallet/pockets/<?= urlencode($p['id']) ?>"
       style="display:block;background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 10px rgba(0,0,0,.06);text-decoration:none;color:inherit">
      <div style="display:flex;align-items:center;gap:12px">
        <div style="width:46px;height:46px;border-radius:14px;background:#f4f4f8;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0">
          <?= htmlspecialchars($p['emoji']) ?>
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-weight:600;font-size:.95rem;color:#0D1B3E;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            <?= htmlspecialchars($p['name']) ?>
          </div>
          <?php if ($p['target_amount']): ?>
          <div style="font-size:.72rem;color:#64748b;margin-top:1px">
            KES <?= number_format((float)$p['balance'], 2) ?> of <?= number_format((float)$p['target_amount'], 0) ?> target
          </div>
          <?php else: ?>
          <div style="font-size:.72rem;color:#64748b;margin-top:1px">No target set</div>
          <?php endif; ?>
        </div>
        <div style="text-align:right;flex-shrink:0">
          <div style="font-weight:700;font-size:1rem;color:#158347">
            KES <?= number_format((float)$p['balance'], 2) ?>
          </div>
          <?php if ($p['target_amount']): ?>
          <div style="font-size:.7rem;color:#64748b"><?= $pct ?>%</div>
          <?php endif; ?>
        </div>
      </div>
      <?php if ($p['target_amount'] && $pct > 0): ?>
      <div style="margin-top:10px;height:5px;background:#f0f0f4;border-radius:99px;overflow:hidden">
        <div style="height:100%;width:<?= $pct ?>%;background:<?= $pct >= 100 ? '#158347' : '#7c3aed' ?>;border-radius:99px;transition:width .4s"></div>
      </div>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Create new pocket -->
  <?php if (count($pockets) < 10): ?>
  <div class="wform-card" id="createSection">
    <div style="font-weight:600;font-size:.9rem;color:#0D1B3E;margin-bottom:14px">
      <i class="fas fa-plus-circle" style="color:#7c3aed"></i> New Pocket
    </div>
    <form method="POST" action="<?= APP_URL ?>/wallet/pockets/create">
      <?= csrf_field() ?>

      <!-- Emoji picker -->
      <div class="wform-group">
        <label class="wform-label">Choose an icon</label>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:4px" id="emojiPicker">
          <?php foreach ($emojis as $e): ?>
          <button type="button" class="emoji-pick <?= $e === '💰' ? 'selected' : '' ?>"
                  onclick="selectEmoji('<?= $e ?>', this)"
                  style="width:38px;height:38px;border-radius:10px;border:2px solid <?= $e === '💰' ? '#7c3aed' : '#e2e8f0' ?>;background:<?= $e === '💰' ? '#f4f0ff' : '#fff' ?>;font-size:1.2rem;cursor:pointer">
            <?= $e ?>
          </button>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="emoji" id="emojiInput" value="💰">
      </div>

      <div class="wform-group">
        <label class="wform-label">Pocket name</label>
        <input type="text" name="name" class="wform-control" placeholder="e.g. Rent, Holiday, New Phone" maxlength="60" required>
      </div>

      <div class="wform-group">
        <label class="wform-label">Savings target <span style="font-weight:400">(optional)</span></label>
        <input type="number" name="target_amount" class="wform-control" placeholder="0" min="0" step="1">
        <div class="wform-hint">Set a goal amount to track your progress</div>
      </div>

      <button type="submit" class="wbtn wbtn-primary">
        <i class="fas fa-piggy-bank"></i> Create Pocket
      </button>
    </form>
  </div>
  <?php else: ?>
  <div style="text-align:center;padding:12px;font-size:.8rem;color:#64748b">
    You've reached the 10-pocket limit.
  </div>
  <?php endif; ?>

</div>

<script>
function selectEmoji(e, btn) {
  document.querySelectorAll('.emoji-pick').forEach(b => {
    b.style.borderColor = '#e2e8f0';
    b.style.background  = '#fff';
  });
  btn.style.borderColor = '#7c3aed';
  btn.style.background  = '#f4f0ff';
  document.getElementById('emojiInput').value = e;
}
</script>
