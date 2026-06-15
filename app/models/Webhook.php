<?php
class Webhook {
    public static function create(array $data): string {
        $id = DB::insert(
            "INSERT INTO webhooks (id, user_id, label, url, events, secret, is_active)
             VALUES (UUID(), ?, ?, ?, ?, ?, 1)",
            [
                $data['user_id'],
                $data['label'] ?? 'My Endpoint',
                $data['url'],
                json_encode($data['events'] ?? ['payment.completed', 'payment.failed']),
                $data['secret'] ?? bin2hex(random_bytes(16)),
            ]
        );
        $row = DB::fetch("SELECT id FROM webhooks WHERE user_id = ? ORDER BY created_at DESC LIMIT 1", [$data['user_id']]);
        return $row['id'] ?? $id;
    }

    public static function getForUser(string $userId): array {
        return DB::fetchAll(
            "SELECT w.*,
             (SELECT COUNT(*) FROM webhook_deliveries d WHERE d.webhook_id = w.id) as total_deliveries,
             (SELECT COUNT(*) FROM webhook_deliveries d WHERE d.webhook_id = w.id AND d.status='success') as successful_deliveries
             FROM webhooks w WHERE w.user_id = ? ORDER BY w.created_at DESC",
            [$userId]
        );
    }

    public static function getActiveForUser(string $userId): array {
        return DB::fetchAll(
            "SELECT * FROM webhooks WHERE user_id = ? AND is_active = 1",
            [$userId]
        );
    }

    public static function find(string $id): ?array {
        return DB::fetch("SELECT * FROM webhooks WHERE id = ? LIMIT 1", [$id]);
    }

    public static function delete(string $id, string $userId): void {
        DB::query("DELETE FROM webhooks WHERE id = ? AND user_id = ?", [$id, $userId]);
    }

    public static function toggle(string $id, string $userId): void {
        DB::query(
            "UPDATE webhooks SET is_active = 1 - is_active WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }

    public static function regenerateSecret(string $id, string $userId): string {
        $secret = bin2hex(random_bytes(16));
        DB::query("UPDATE webhooks SET secret = ? WHERE id = ? AND user_id = ?", [$secret, $id, $userId]);
        return $secret;
    }

    public static function getDeliveries(string $webhookId, int $limit = 30): array {
        return DB::fetchAll(
            "SELECT * FROM webhook_deliveries WHERE webhook_id = ? ORDER BY created_at DESC LIMIT ?",
            [$webhookId, $limit]
        );
    }

    public static function getRecentDeliveriesForUser(string $userId, int $limit = 10): array {
        return DB::fetchAll(
            "SELECT d.*, w.label as webhook_label, w.url as webhook_url
             FROM webhook_deliveries d
             JOIN webhooks w ON d.webhook_id = w.id
             WHERE w.user_id = ?
             ORDER BY d.created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
}
