<?php

class WalletPocket {

    public static function create(array $data): string {
        $id = DB::fetch("SELECT UUID() as id")['id'];
        DB::insert(
            "INSERT INTO wallet_pockets (id, wallet_user_id, name, emoji, target_amount, balance)
             VALUES (?, ?, ?, ?, ?, 0.00)",
            [
                $id,
                $data['wallet_user_id'],
                $data['name'],
                (isset($data['emoji']) && $data['emoji'] !== '') ? $data['emoji'] : '*',
                isset($data['target_amount']) && $data['target_amount'] > 0 ? (float)$data['target_amount'] : null,
            ]
        );
        return $id;
    }

    public static function find(string $id): ?array {
        return DB::fetch("SELECT * FROM wallet_pockets WHERE id = ?", [$id]) ?: null;
    }

    public static function findForUser(string $userId): array {
        return DB::fetchAll(
            "SELECT * FROM wallet_pockets WHERE wallet_user_id = ? ORDER BY created_at ASC",
            [$userId]
        );
    }

    public static function totalBalance(string $userId): float {
        $r = DB::fetch("SELECT COALESCE(SUM(balance),0) as t FROM wallet_pockets WHERE wallet_user_id=?", [$userId]);
        return (float)($r['t'] ?? 0);
    }

    public static function deposit(string $id, float $amount): void {
        DB::query(
            "UPDATE wallet_pockets SET balance = balance + ?, updated_at = NOW() WHERE id = ?",
            [$amount, $id]
        );
    }

    public static function withdraw(string $id, float $amount): void {
        DB::query(
            "UPDATE wallet_pockets SET balance = GREATEST(0, balance - ?), updated_at = NOW() WHERE id = ?",
            [$amount, $id]
        );
    }

    public static function delete(string $id): void {
        DB::query("DELETE FROM wallet_pockets WHERE id = ?", [$id]);
    }

    public static function progressPercent(array $pocket): int {
        if (!$pocket['target_amount'] || (float)$pocket['target_amount'] <= 0) return 0;
        return (int)min(100, round((float)$pocket['balance'] / (float)$pocket['target_amount'] * 100));
    }
}
