<?php

class WalletReferral {

    const REFERRER_BONUS = 50.00;
    const REFERRED_BONUS = 25.00;

    public static function generateCode(): string {
        do {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 7));
        } while (DB::fetch("SELECT id FROM wallet_users WHERE referral_code = ?", [$code]));
        return $code;
    }

    public static function assignCode(string $userId): string {
        $code = self::generateCode();
        DB::query("UPDATE wallet_users SET referral_code = ? WHERE id = ?", [$code, $userId]);
        return $code;
    }

    public static function findByCode(string $code): ?array {
        return DB::fetch("SELECT * FROM wallet_users WHERE referral_code = ?", [strtoupper(trim($code))]) ?: null;
    }

    public static function create(string $referrerId, string $referredId): void {
        DB::insert(
            "INSERT IGNORE INTO wallet_referrals (id, referrer_id, referred_id, referrer_bonus, referred_bonus)
             VALUES (UUID(), ?, ?, ?, ?)",
            [$referrerId, $referredId, self::REFERRER_BONUS, self::REFERRED_BONUS]
        );
    }

    public static function getPending(string $referredId): ?array {
        return DB::fetch(
            "SELECT * FROM wallet_referrals WHERE referred_id = ? AND status = 'pending'",
            [$referredId]
        ) ?: null;
    }

    /**
     * Called after each transaction. If this is the referred user's first transaction,
     * credit cashback to both parties and mark the referral completed.
     */
    public static function checkAndComplete(string $referredId): void {
        $referral = self::getPending($referredId);
        if (!$referral) return;

        // Count completed transactions (excluding welcome deposit and pocket moves)
        $txnCount = DB::fetch(
            "SELECT COUNT(*) as c FROM wallet_transactions
             WHERE wallet_user_id = ? AND type NOT IN ('deposit','pocket_in','pocket_out') AND status = 'completed'",
            [$referredId]
        );
        if ((int)($txnCount['c'] ?? 0) !== 1) return;

        // First real transaction — pay out cashback
        $referrerId    = $referral['referrer_id'];
        $referrerBonus = (float)$referral['referrer_bonus'];
        $referredBonus = (float)$referral['referred_bonus'];

        $ref = 'CB' . strtoupper(bin2hex(random_bytes(5)));

        // Credit referrer
        $referrer    = DB::fetch("SELECT * FROM wallet_users WHERE id = ?", [$referrerId]);
        if ($referrer) {
            $balBefore = (float)$referrer['balance'];
            WalletUser::credit($referrerId, $referrerBonus);
            WalletTransaction::create([
                'reference'         => $ref . 'A',
                'wallet_user_id'    => $referrerId,
                'type'              => 'cashback',
                'amount'            => $referrerBonus,
                'fee'               => 0.00,
                'balance_before'    => $balBefore,
                'balance_after'     => $balBefore + $referrerBonus,
                'counterparty'      => $referredId,
                'counterparty_name' => 'Referral Reward',
                'description'       => 'Referral cashback — your friend made their first transaction',
                'status'            => 'completed',
            ]);
            WalletNotification::create($referrerId, 'payment',
                'Referral bonus — KES ' . number_format($referrerBonus, 2) . ' credited!',
                'Your referred friend completed their first transaction. Enjoy your KES ' . number_format($referrerBonus, 2) . ' reward!',
                '/wallet/transactions'
            );
        }

        // Credit referred user
        $referred  = DB::fetch("SELECT * FROM wallet_users WHERE id = ?", [$referredId]);
        if ($referred) {
            $balBefore = (float)$referred['balance'];
            WalletUser::credit($referredId, $referredBonus);
            WalletTransaction::create([
                'reference'         => $ref . 'B',
                'wallet_user_id'    => $referredId,
                'type'              => 'cashback',
                'amount'            => $referredBonus,
                'fee'               => 0.00,
                'balance_before'    => $balBefore,
                'balance_after'     => $balBefore + $referredBonus,
                'counterparty'      => $referrerId,
                'counterparty_name' => 'Referral Reward',
                'description'       => 'Welcome cashback — thanks for joining via a referral!',
                'status'            => 'completed',
            ]);
            WalletNotification::create($referredId, 'payment',
                'Welcome cashback — KES ' . number_format($referredBonus, 2) . ' credited!',
                'Congratulations on your first transaction! You earned KES ' . number_format($referredBonus, 2) . ' referral cashback.',
                '/wallet/transactions'
            );
        }

        // Mark referral completed
        DB::query(
            "UPDATE wallet_referrals SET status = 'completed', completed_at = NOW() WHERE id = ?",
            [$referral['id']]
        );
    }

    public static function statsForUser(string $userId): array {
        $total = DB::fetch(
            "SELECT COUNT(*) as total,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status='completed' THEN referrer_bonus ELSE 0 END) as earned
             FROM wallet_referrals WHERE referrer_id = ?",
            [$userId]
        );
        return [
            'total'     => (int)($total['total']    ?? 0),
            'completed' => (int)($total['completed'] ?? 0),
            'earned'    => (float)($total['earned']   ?? 0),
        ];
    }
}
