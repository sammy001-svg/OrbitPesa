<?php
class Wallet {
    public static function getOrCreate(string $userId): array {
        $wallet = DB::fetch("SELECT * FROM wallets WHERE user_id = ?", [$userId]);
        if (!$wallet) {
            DB::insert(
                "INSERT INTO wallets (id, user_id, balance, currency, created_at) VALUES (UUID(), ?, 0.00, 'KES', NOW())",
                [$userId]
            );
            $wallet = DB::fetch("SELECT * FROM wallets WHERE user_id = ?", [$userId]);
        }
        return $wallet;
    }

    public static function credit(string $userId, float $amount, string $description): void {
        DB::query(
            "UPDATE wallets SET balance = balance + ?, updated_at = NOW() WHERE user_id = ?",
            [$amount, $userId]
        );
        self::addLedger($userId, 'credit', $amount, $description);
    }

    public static function debit(string $userId, float $amount, string $description): bool {
        $wallet = self::getOrCreate($userId);
        if ($wallet['balance'] < $amount) return false;

        DB::query(
            "UPDATE wallets SET balance = balance - ?, updated_at = NOW() WHERE user_id = ?",
            [$amount, $userId]
        );
        self::addLedger($userId, 'debit', $amount, $description);
        return true;
    }

    private static function addLedger(string $userId, string $type, float $amount, string $description): void {
        $wallet = DB::fetch("SELECT * FROM wallets WHERE user_id = ?", [$userId]);
        DB::insert(
            "INSERT INTO wallet_ledger (id, user_id, type, amount, balance_after, description, created_at)
             VALUES (UUID(), ?, ?, ?, ?, ?, NOW())",
            [$userId, $type, $amount, $wallet['balance'], $description]
        );
    }

    public static function getLedger(string $userId, int $limit = 20): array {
        return DB::fetchAll(
            "SELECT * FROM wallet_ledger WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
}
