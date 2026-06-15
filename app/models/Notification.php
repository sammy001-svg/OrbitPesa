<?php
class Notification {

    public static function create(
        string $userId,
        string $type,
        string $title,
        string $body,
        string $url = ''
    ): void {
        DB::insert(
            "INSERT INTO notifications (id, user_id, type, title, body, url) VALUES (UUID(), ?, ?, ?, ?, ?)",
            [$userId, $type, $title, $body, $url]
        );
    }

    public static function getForUser(string $userId, int $limit = 20, int $offset = 0): array {
        return DB::fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
    }

    public static function unreadCount(string $userId): int {
        $r = DB::fetch("SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0", [$userId]);
        return (int)($r['c'] ?? 0);
    }

    public static function countAll(string $userId, bool $unreadOnly = false): int {
        $where = $unreadOnly ? 'AND is_read = 0' : '';
        $r = DB::fetch("SELECT COUNT(*) as c FROM notifications WHERE user_id = ? $where", [$userId]);
        return (int)($r['c'] ?? 0);
    }

    public static function markRead(string $id, string $userId): void {
        DB::query("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$id, $userId]);
    }

    public static function markAllRead(string $userId): void {
        DB::query("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$userId]);
    }

    public static function typeIcon(string $type): string {
        return match ($type) {
            'payment'    => 'fa-credit-card',
            'withdrawal' => 'fa-money-bill-wave',
            'kyc'        => 'fa-id-card',
            'webhook'    => 'fa-satellite-dish',
            default      => 'fa-bell',
        };
    }

    public static function timeAgo(string $datetime): string {
        $diff = time() - strtotime($datetime);
        if ($diff < 60)     return 'Just now';
        if ($diff < 3600)   return floor($diff / 60) . 'm ago';
        if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('d M', strtotime($datetime));
    }
}
