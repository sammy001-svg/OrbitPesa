<?php
class PaymentLink {
    public static function create(array $data): string {
        $slug = strtolower(bin2hex(random_bytes(6)));
        return DB::insert(
            "INSERT INTO payment_links
             (id, user_id, slug, title, description, amount, currency, is_fixed_amount, max_uses, expires_at, status, created_at)
             VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())",
            [
                $data['user_id'],
                $slug,
                $data['title'],
                $data['description'] ?? '',
                $data['amount'] ?? null,
                $data['currency'] ?? 'KES',
                $data['is_fixed_amount'] ? 1 : 0,
                $data['max_uses'] ?? null,
                $data['expires_at'] ?? null,
            ]
        );
    }

    public static function findBySlug(string $slug): ?array {
        return DB::fetch("SELECT pl.*, u.business_name FROM payment_links pl JOIN users u ON pl.user_id = u.id WHERE pl.slug = ? LIMIT 1", [$slug]);
    }

    public static function getForUser(string $userId): array {
        return DB::fetchAll(
            "SELECT pl.*, (SELECT COUNT(*) FROM transactions t WHERE JSON_UNQUOTE(JSON_EXTRACT(t.metadata, '$.payment_link_id')) = pl.id) as uses
             FROM payment_links pl WHERE pl.user_id = ? ORDER BY pl.created_at DESC",
            [$userId]
        );
    }

    public static function deactivate(string $id, string $userId): void {
        DB::query("UPDATE payment_links SET status = 'inactive' WHERE id = ? AND user_id = ?", [$id, $userId]);
    }

    public static function delete(string $id, string $userId): void {
        DB::query("DELETE FROM payment_links WHERE id = ? AND user_id = ?", [$id, $userId]);
    }
}
