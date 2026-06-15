<?php
$pageTitle = 'Notifications';
$backUrl   = APP_URL . '/wallet/home';

$perPage  = 20;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $perPage;
$total    = WalletNotification::countAll($walletUser['id']);
$notifs   = WalletNotification::getForUser($walletUser['id'], $perPage, $offset);
$unread   = WalletNotification::unreadCount($walletUser['id']);
$pages    = (int)ceil($total / $perPage);
?>

<div style="padding-top:6px">

  <!-- Header row -->
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
    <div style="font-size:.82rem;color:#64748b"><?= $total ?> notification<?= $total !== 1 ? 's' : '' ?></div>
    <?php if ($unread > 0): ?>
    <form method="POST" action="<?= APP_URL ?>/wallet/notifications/read-all" style="margin:0">
      <?= csrf_field() ?>
      <button type="submit" style="background:none;border:none;color:#158347;font-size:.78rem;font-weight:600;cursor:pointer;font-family:inherit;padding:0">
        <i class="fas fa-check-double"></i> Mark all read
      </button>
    </form>
    <?php endif; ?>
  </div>

  <?php if (empty($notifs)): ?>
  <div class="wempty" style="margin-top:40px">
    <i class="fas fa-bell"></i>
    <p>No notifications yet</p>
    <p style="font-size:.75rem;margin-top:4px">You'll see payment alerts and updates here</p>
  </div>

  <?php else: ?>
  <div style="display:flex;flex-direction:column;gap:0;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06)">
    <?php foreach ($notifs as $i => $n):
      $icon  = WalletNotification::typeIcon($n['type']);
      $color = WalletNotification::typeColor($n['type']);
      $isLast = $i === count($notifs) - 1;
    ?>
    <div style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;<?= !$n['is_read'] ? 'background:#f8fbff;' : '' ?><?= !$isLast ? 'border-bottom:1px solid #f1f5f9;' : '' ?>position:relative">
      <?php if (!$n['is_read']): ?>
      <div style="position:absolute;top:18px;left:7px;width:6px;height:6px;border-radius:50%;background:#3b82f6"></div>
      <?php endif; ?>

      <div style="width:40px;height:40px;border-radius:12px;background:<?= $color ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-left:<?= !$n['is_read'] ? '8px' : '0' ?>">
        <i class="fas <?= $icon ?>" style="color:#fff;font-size:.85rem"></i>
      </div>

      <div style="flex:1;min-width:0">
        <div style="font-weight:<?= $n['is_read'] ? '500' : '700' ?>;font-size:.85rem;color:#0D1B3E;line-height:1.3">
          <?= htmlspecialchars($n['title']) ?>
        </div>
        <div style="font-size:.78rem;color:#64748b;margin-top:3px;line-height:1.45">
          <?= htmlspecialchars($n['body']) ?>
        </div>
        <div style="font-size:.7rem;color:#94a3b8;margin-top:5px">
          <?= WalletNotification::timeAgo($n['created_at']) ?>
        </div>
      </div>

      <?php if (!$n['is_read']): ?>
      <form method="POST" action="<?= APP_URL ?>/wallet/notifications/read" style="margin:0;flex-shrink:0">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($n['id']) ?>">
        <button type="submit" title="Mark read"
                style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px;font-size:.75rem">
          <i class="fas fa-times"></i>
        </button>
      </form>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <?php if ($pages > 1): ?>
  <div style="display:flex;justify-content:center;gap:8px;margin-top:16px">
    <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>" class="wbtn" style="padding:8px 18px;font-size:.8rem;background:#f1f5f9;color:#475569;text-decoration:none">
      <i class="fas fa-chevron-left"></i> Prev
    </a>
    <?php endif; ?>
    <span style="display:flex;align-items:center;font-size:.8rem;color:#64748b">
      <?= $page ?> / <?= $pages ?>
    </span>
    <?php if ($page < $pages): ?>
    <a href="?page=<?= $page + 1 ?>" class="wbtn" style="padding:8px 18px;font-size:.8rem;background:#f1f5f9;color:#475569;text-decoration:none">
      Next <i class="fas fa-chevron-right"></i>
    </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>

</div>
