<?php
class User {
    public static function create(array $data): string {
        return DB::insert(
            "INSERT INTO users (id, business_name, email, phone, password, account_type, status, created_at)
             VALUES (UUID(), ?, ?, ?, ?, ?, 'active', NOW())",
            [
                $data['business_name'],
                $data['email'],
                $data['phone'],
                password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                $data['account_type'] ?? 'business',
            ]
        );
    }

    public static function findByEmail(string $email): ?array {
        return DB::fetch("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);
    }

    public static function findById(string $id): ?array {
        return DB::fetch("SELECT * FROM users WHERE id = ? LIMIT 1", [$id]);
    }

    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public static function updateProfile(string $id, array $data): void {
        DB::query(
            "UPDATE users SET business_name = ?, phone = ?, updated_at = NOW() WHERE id = ?",
            [$data['business_name'], $data['phone'], $id]
        );
    }

    public static function getStats(string $userId): array {
        $total = DB::fetch(
            "SELECT COALESCE(SUM(amount), 0) as total, COUNT(*) as count
             FROM transactions WHERE user_id = ? AND status = 'completed'",
            [$userId]
        );
        $today = DB::fetch(
            "SELECT COALESCE(SUM(amount), 0) as total
             FROM transactions WHERE user_id = ? AND status = 'completed' AND DATE(created_at) = CURDATE()",
            [$userId]
        );
        $pending = DB::fetch(
            "SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND status = 'pending'",
            [$userId]
        );
        $wallet = DB::fetch("SELECT balance FROM wallets WHERE user_id = ?", [$userId]);

        return [
            'total_received'   => $total['total'] ?? 0,
            'total_count'      => $total['count'] ?? 0,
            'today_received'   => $today['total'] ?? 0,
            'pending_count'    => $pending['count'] ?? 0,
            'wallet_balance'   => $wallet['balance'] ?? 0,
        ];
    }
}
