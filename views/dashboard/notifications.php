<?php
$filter  = $_GET['filter'] ?? 'all';
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 20;
$offset  = ($page - 1) * $limit;

$unreadOnly = $filter === 'unread';
$total   = Notification::countAll($_SESSION['user_id'], $unreadOnly);
$pages   = (int)ceil($total / $limit);
$notifs  = $unreadOnly
    ? DB::fetchAll(
        "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT ? OFFSET ?",
        [$_SESSION['user_id'], $limit, $offset]
    )
    : Notification::getForUser($_SESSION['user_id'], $limit, $offset);

$unreadCount = Notification::unreadCount($_SESSION['user_id']);
?>

<div class="section-hd">
  <div>
    <h2>Notifications</h2>
    <p>Stay up to date with payments, KYC status, withdrawals, and more.</p>
  </div>
  <?php if ($unreadCount > 0): ?>
  <form method="POST" action="<?= APP_URL ?>/dashboard/notifications/read-all">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-ghost btn-sm">
      <i class="fas fa-check-double"></i> Mark all read
    </button>
  </form>
  <?php endif; ?>
</div>

<!-- Filter tabs -->
<div class="tabs mb-4">
  <a href="?filter=all"    class="tab <?= $filter === 'all'    ? 'active' : '' ?>">All</a>
  <a href="?filter=unread" class="tab <?= $filter === 'unread' ? 'active' : '' ?>">
    Unread <?php if ($unreadCount > 0): ?><span class="badge badge-warning"><?= $unreadCount ?></span><?php endif; ?>
  </a>
</div>

<div class="card">
  <?php if (empty($notifs)): ?>
    <div class="empty-state">
      <i class="fas fa-bell-slash" style="color:var(--text-muted)"></i>
      <h4><?= $filter === 'unread' ? 'All caught up!' : 'No notifications yet' ?></h4>
      <p><?= $filter === 'unread' ? 'You have no unread notifications.' : 'Notifications will appear here when you receive payments, process withdrawals, and more.' ?></p>
    </div>
  <?php else: ?>
  <div class="notif-page-list">
    <?php foreach ($notifs as $n): ?>
    <div class="notif-page-item <?= $n['is_read'] ? '' : 'notif-page-unread' ?>">
      <div class="notif-icon notif-type-<?= htmlspecialchars($n['type']) ?>">
        <i class="fas <?= Notification::typeIcon($n['type']) ?>"></i>
      </div>
      <div class="notif-page-content">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
          <div>
            <div class="notif-page-title">
              <?= sanitize($n['title']) ?>
              <?php if (!$n['is_read']): ?>
                <span class="badge badge-warning" style="font-size:.65rem;padding:2px 7px;vertical-align:middle">New</span>
              <?php endif; ?>
            </div>
            <div class="notif-page-body"><?= sanitize($n['body']) ?></div>
          </div>
          <div class="notif-page-meta">
            <div class="notif-page-time"><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></div>
            <div class="notif-page-ago"><?= Notification::timeAgo($n['created_at']) ?></div>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-top:10px">
          <?php if ($n['url']): ?>
            <a href="<?= APP_URL ?>/dashboard/notifications/goto?id=<?= urlencode($n['id']) ?>" class="btn btn-ghost btn-sm">
              <i class="fas fa-external-link-alt"></i> View Details
            </a>
          <?php endif; ?>
          <?php if (!$n['is_read']): ?>
            <form method="POST" action="<?= APP_URL ?>/dashboard/notifications/read" style="margin:0">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= sanitize($n['id']) ?>">
              <button type="submit" class="btn btn-ghost btn-sm">
                <i class="fas fa-check"></i> Mark read
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if ($pages > 1): ?>
  <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between">
    <span style="font-size:.82rem;color:var(--text-muted)">
      Showing <?= $offset + 1 ?>–<?= min($offset + $limit, $total) ?> of <?= number_format($total) ?>
    </span>
    <div class="pagination">
      <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
        <a href="?filter=<?= $filter ?>&page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<style>
.notif-page-list { display: flex; flex-direction: column; }
.notif-page-item {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 18px 24px;
  border-bottom: 1px solid var(--border);
  transition: background .12s;
}
.notif-page-item:last-child { border-bottom: none; }
.notif-page-item:hover { background: var(--bg-light); }
.notif-page-unread { background: #f0f9ff; }
.notif-page-unread:hover { background: #e0f2fe; }
.notif-page-content { flex: 1; min-width: 0; }
.notif-page-title { font-weight: 700; font-size: .9rem; color: var(--navy); margin-bottom: 4px; }
.notif-page-body  { font-size: .84rem; color: var(--text-muted); line-height: 1.5; }
.notif-page-meta  { text-align: right; flex-shrink: 0; }
.notif-page-time  { font-size: .78rem; color: var(--text-muted); white-space: nowrap; }
.notif-page-ago   { font-size: .72rem; color: #94a3b8; margin-top: 2px; }
</style>
