<?php
class ApiKey {
    public static function generate(string $userId, string $label, string $env = 'test'): array {
        $prefix = $env === 'live' ? 'op_live_' : 'op_test_';
        $key = $prefix . bin2hex(random_bytes(24));

        DB::insert(
            "INSERT INTO api_keys (id, user_id, label, key_value, environment, is_active, created_at)
             VALUES (UUID(), ?, ?, ?, ?, 1, NOW())",
            [$userId, $label, $key, $env]
        );

        return ['key' => $key, 'label' => $label, 'environment' => $env];
    }

    public static function getForUser(string $userId): array {
        return DB::fetchAll(
            "SELECT id, label, CONCAT(LEFT(key_value, 12), '...', RIGHT(key_value, 4)) as masked_key,
                    environment, is_active, last_used_at, created_at
             FROM api_keys WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );
    }

    public static function revoke(string $id, string $userId): void {
        DB::query("UPDATE api_keys SET is_active = 0 WHERE id = ? AND user_id = ?", [$id, $userId]);
    }

    public static function delete(string $id, string $userId): void {
        DB::query("DELETE FROM api_keys WHERE id = ? AND user_id = ?", [$id, $userId]);
    }
}
