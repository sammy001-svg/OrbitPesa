<?php

class WalletTransaction {

    public static function create(array $data): string {
        $uuid = DB::fetch("SELECT UUID() as id")['id'];
        DB::insert(
            "INSERT INTO wallet_transactions
             (id, reference, wallet_user_id, type, amount, fee, balance_before, balance_after,
              counterparty, counterparty_name, description, status, metadata)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $uuid,
                $data['reference'],
                $data['wallet_user_id'],
                $data['type'],
                $data['amount'],
                $data['fee'] ?? 0.00,
                $data['balance_before'] ?? 0.00,
                $data['balance_after']  ?? 0.00,
                $data['counterparty']      ?? null,
                $data['counterparty_name'] ?? null,
                $data['description']       ?? null,
                $data['status']            ?? 'completed',
                isset($data['metadata']) ? json_encode($data['metadata']) : null,
            ]
        );
        return $uuid;
    }

    public static function getForUser(string $userId, int $limit = 20, int $offset = 0): array {
        return DB::fetchAll(
            "SELECT * FROM wallet_transactions WHERE wallet_user_id=? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
    }

    public static function countForUser(string $userId): int {
        $r = DB::fetch("SELECT COUNT(*) as c FROM wallet_transactions WHERE wallet_user_id=?", [$userId]);
        return (int)($r['c'] ?? 0);
    }

    public static function isCredit(string $type): bool {
        return in_array($type, ['receive', 'deposit', 'cashback']);
    }

    public static function typeLabel(string $type): string {
        return match($type) {
            'send'          => 'Sent',
            'receive'       => 'Received',
            'airtime'       => 'Airtime',
            'data'          => 'Data Bundle',
            'paybill'       => 'Paybill',
            'bank_transfer' => 'Bank Transfer',
            'mpesa_out'     => 'M-Pesa',
            'deposit'       => 'Top Up',
            'cashback'      => 'Cashback',
            default         => ucfirst($type),
        };
    }

    public static function typeIcon(string $type): string {
        return match($type) {
            'send'          => 'fa-paper-plane',
            'receive'       => 'fa-arrow-down',
            'airtime'       => 'fa-mobile-alt',
            'data'          => 'fa-wifi',
            'paybill'       => 'fa-file-invoice',
            'bank_transfer' => 'fa-university',
            'mpesa_out'     => 'fa-money-bill-wave',
            'deposit'       => 'fa-plus-circle',
            'cashback'      => 'fa-gift',
            default         => 'fa-exchange-alt',
        };
    }

    public static function typeColor(string $type): string {
        return match($type) {
            'send'          => '#f97316',
            'receive'       => '#158347',
            'airtime'       => '#3b82f6',
            'data'          => '#2563eb',
            'paybill'       => '#f59e0b',
            'bank_transfer' => '#0D1B3E',
            'mpesa_out'     => '#158347',
            'deposit'       => '#7c3aed',
            'cashback'      => '#ec4899',
            default         => '#64748b',
        };
    }

    public static function systemStats(): array {
        return DB::fetch(
            "SELECT
               COUNT(*) as total,
               COALESCE(SUM(amount),0) as total_volume,
               SUM(CASE WHEN type='send' OR type='mpesa_out' OR type='bank_transfer' THEN 1 ELSE 0 END) as outgoing,
               SUM(CASE WHEN type='receive' OR type='deposit' THEN 1 ELSE 0 END) as incoming
             FROM wallet_transactions WHERE status='completed'"
        ) ?? [];
    }
}
