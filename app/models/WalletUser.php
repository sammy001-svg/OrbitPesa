<?php

class WalletUser {

    private static function generateWalletId(): string {
        do {
            $id     = 'OP' . str_pad((string)mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
            $exists = DB::fetch("SELECT id FROM wallet_users WHERE wallet_id=?", [$id]);
        } while ($exists);
        return $id;
    }

    public static function create(array $data): string {
        $uuid     = DB::fetch("SELECT UUID() as id")['id'];
        $walletId = self::generateWalletId();
        DB::insert(
            "INSERT INTO wallet_users (id, wallet_id, full_name, email, phone, national_id, password, pin_hash)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $uuid,
                $walletId,
                $data['full_name'],
                $data['email'],
                $data['phone'],
                $data['national_id'] ?? null,
                password_hash($data['password'], PASSWORD_BCRYPT),
                password_hash($data['pin'], PASSWORD_BCRYPT),
            ]
        );
        return $uuid;
    }

    public static function find(string $id): ?array {
        return DB::fetch("SELECT * FROM wallet_users WHERE id=?", [$id]) ?: null;
    }

    public static function findByEmail(string $email): ?array {
        return DB::fetch("SELECT * FROM wallet_users WHERE email=?", [$email]) ?: null;
    }

    public static function findByPhone(string $phone): ?array {
        return DB::fetch("SELECT * FROM wallet_users WHERE phone=?", [$phone]) ?: null;
    }

    public static function findByWalletId(string $walletId): ?array {
        return DB::fetch("SELECT * FROM wallet_users WHERE wallet_id=?", [$walletId]) ?: null;
    }

    public static function findByIdentifier(string $q): ?array {
        return DB::fetch(
            "SELECT * FROM wallet_users WHERE email=? OR phone=? OR wallet_id=? LIMIT 1",
            [$q, $q, $q]
        ) ?: null;
    }

    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public static function verifyPin(string $pin, string $hash): bool {
        return password_verify($pin, $hash);
    }

    public static function credit(string $id, float $amount): void {
        DB::query("UPDATE wallet_users SET balance = balance + ?, updated_at=NOW() WHERE id=?", [$amount, $id]);
    }

    public static function debit(string $id, float $amount): bool {
        $user = self::find($id);
        if (!$user || (float)$user['balance'] < $amount) return false;
        DB::query("UPDATE wallet_users SET balance = balance - ?, updated_at=NOW() WHERE id=?", [$amount, $id]);
        return true;
    }

    public static function updateProfile(string $id, string $name, string $email, string $phone): void {
        DB::query(
            "UPDATE wallet_users SET full_name=?, email=?, phone=?, updated_at=NOW() WHERE id=?",
            [$name, $email, $phone, $id]
        );
    }

    public static function updatePin(string $id, string $pin): void {
        DB::query(
            "UPDATE wallet_users SET pin_hash=?, updated_at=NOW() WHERE id=?",
            [password_hash($pin, PASSWORD_BCRYPT), $id]
        );
    }

    public static function getAll(array $filters = [], int $limit = 50, int $offset = 0): array {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[]  = 'status=?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where[]  = '(full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR wallet_id LIKE ?)';
            $s        = '%' . $filters['search'] . '%';
            $params   = array_merge($params, [$s, $s, $s, $s]);
        }
        return DB::fetchAll(
            "SELECT * FROM wallet_users WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [...$params, $limit, $offset]
        );
    }

    public static function countAll(array $filters = []): int {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[]  = 'status=?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where[]  = '(full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR wallet_id LIKE ?)';
            $s        = '%' . $filters['search'] . '%';
            $params   = array_merge($params, [$s, $s, $s, $s]);
        }
        $r = DB::fetch("SELECT COUNT(*) as c FROM wallet_users WHERE " . implode(' AND ', $where), $params);
        return (int)($r['c'] ?? 0);
    }

    public static function stats(): array {
        return DB::fetch(
            "SELECT
               COUNT(*) as total,
               COALESCE(SUM(balance),0) as total_balance,
               SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active,
               SUM(CASE WHEN created_at >= DATE_SUB(NOW(),INTERVAL 7 DAY) THEN 1 ELSE 0 END) as new_this_week
             FROM wallet_users"
        ) ?? [];
    }
}
