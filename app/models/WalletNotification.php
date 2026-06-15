<?php

class WalletNotification {

    public static function create(
        string $userId,
        string $type,
        string $title,
        string $body,
        string $url = ''
    ): void {
        DB::insert(
            "INSERT INTO wallet_notifications (id, wallet_user_id, type, title, body, url)
             VALUES (UUID(), ?, ?, ?, ?, ?)",
            [$userId, $type, $title, $body, $url]
        );
    }

    public static function getForUser(string $userId, int $limit = 30, int $offset = 0): array {
        return DB::fetchAll(
            "SELECT * FROM wallet_notifications WHERE wallet_user_id = ?
             ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
    }

    public static function unreadCount(string $userId): int {
        $r = DB::fetch(
            "SELECT COUNT(*) as c FROM wallet_notifications WHERE wallet_user_id = ? AND is_read = 0",
            [$userId]
        );
        return (int)($r['c'] ?? 0);
    }

    public static function countAll(string $userId): int {
        $r = DB::fetch(
            "SELECT COUNT(*) as c FROM wallet_notifications WHERE wallet_user_id = ?",
            [$userId]
        );
        return (int)($r['c'] ?? 0);
    }

    public static function markRead(string $id, string $userId): void {
        DB::query(
            "UPDATE wallet_notifications SET is_read = 1 WHERE id = ? AND wallet_user_id = ?",
            [$id, $userId]
        );
    }

    public static function markAllRead(string $userId): void {
        DB::query(
            "UPDATE wallet_notifications SET is_read = 1 WHERE wallet_user_id = ?",
            [$userId]
        );
    }

    public static function typeIcon(string $type): string {
        return match ($type) {
            'payment'  => 'fa-arrow-down-left',
            'transfer' => 'fa-money-bill-wave',
            'purchase' => 'fa-shopping-bag',
            'pocket'   => 'fa-piggy-bank',
            'security' => 'fa-shield-alt',
            default    => 'fa-bell',
        };
    }

    public static function typeColor(string $type): string {
        return match ($type) {
            'payment'  => '#158347',
            'transfer' => '#0D1B3E',
            'purchase' => '#f59e0b',
            'pocket'   => '#7c3aed',
            'security' => '#dc2626',
            default    => '#64748b',
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
