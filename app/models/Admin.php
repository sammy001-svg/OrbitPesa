<?php
class Admin {
    public static function findByEmail(string $email): ?array {
        return DB::fetch("SELECT * FROM admins WHERE email = ? AND is_active = 1 LIMIT 1", [$email]);
    }

    public static function findById(string $id): ?array {
        return DB::fetch("SELECT * FROM admins WHERE id = ? LIMIT 1", [$id]);
    }

    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public static function updateLastLogin(string $id): void {
        DB::query("UPDATE admins SET last_login = NOW() WHERE id = ?", [$id]);
    }

    public static function getSystemStats(): array {
        $merchants    = DB::fetch("SELECT COUNT(*) as cnt FROM users");
        $active       = DB::fetch("SELECT COUNT(*) as cnt FROM users WHERE status = 'active'");
        $suspended    = DB::fetch("SELECT COUNT(*) as cnt FROM users WHERE status = 'suspended'");
        $kycPending   = DB::fetch("SELECT COUNT(*) as cnt FROM users WHERE kyc_status = 'pending'");
        $kycVerified  = DB::fetch("SELECT COUNT(*) as cnt FROM users WHERE kyc_status = 'verified'");

        $volume       = DB::fetch("SELECT COALESCE(SUM(amount),0) as total, COUNT(*) as cnt FROM transactions WHERE status = 'completed'");
        $todayVol     = DB::fetch("SELECT COALESCE(SUM(amount),0) as total FROM transactions WHERE status = 'completed' AND DATE(created_at) = CURDATE()");
        $monthVol     = DB::fetch("SELECT COALESCE(SUM(amount),0) as total FROM transactions WHERE status = 'completed' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())");
        $pendingTxns  = DB::fetch("SELECT COUNT(*) as cnt FROM transactions WHERE status = 'pending'");
        $failedTxns   = DB::fetch("SELECT COUNT(*) as cnt FROM transactions WHERE status = 'failed' AND DATE(created_at) = CURDATE()");

        $totalFees    = DB::fetch("SELECT COALESCE(SUM(fee),0) as total FROM transactions WHERE status = 'completed'");
        $pendingWd    = DB::fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(amount),0) as vol FROM withdrawals WHERE status = 'pending'");

        return [
            'merchants_total'    => $merchants['cnt']   ?? 0,
            'merchants_active'   => $active['cnt']      ?? 0,
            'merchants_suspended'=> $suspended['cnt']   ?? 0,
            'kyc_pending'        => $kycPending['cnt']  ?? 0,
            'kyc_verified'       => $kycVerified['cnt'] ?? 0,
            'volume_all_time'    => $volume['total']    ?? 0,
            'txn_count'          => $volume['cnt']      ?? 0,
            'volume_today'       => $todayVol['total']  ?? 0,
            'volume_month'       => $monthVol['total']  ?? 0,
            'pending_txns'       => $pendingTxns['cnt'] ?? 0,
            'failed_today'       => $failedTxns['cnt']  ?? 0,
            'total_fees'         => $totalFees['total'] ?? 0,
            'pending_withdrawals'=> $pendingWd['cnt']   ?? 0,
            'pending_wd_vol'     => $pendingWd['vol']   ?? 0,
        ];
    }

    public static function getRecentMerchants(int $limit = 10): array {
        return DB::fetchAll(
            "SELECT u.*, w.balance FROM users u LEFT JOIN wallets w ON w.user_id = u.id ORDER BY u.created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public static function getRecentTransactions(int $limit = 10): array {
        return DB::fetchAll(
            "SELECT t.*, u.business_name FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public static function log(string $adminId, string $action, string $targetType = '', string $targetId = '', string $notes = '', ?string $ip = null): void {
        DB::insert(
            "INSERT INTO admin_logs (id, admin_id, action, description, target_type, target_id, ip_address)
             VALUES (UUID(), ?, ?, ?, ?, ?, ?)",
            [$adminId, $action, $notes ?: null, $targetType ?: null, $targetId ?: null, $ip ?? $_SERVER['REMOTE_ADDR'] ?? null]
        );
    }

    public static function getVolumeByDay(int $days = 14): array {
        return DB::fetchAll(
            "SELECT DATE(created_at) as date, COALESCE(SUM(amount),0) as volume, COUNT(*) as count
             FROM transactions WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(created_at) ORDER BY date ASC",
            [$days]
        );
    }

    public static function getVolumeByChannel(): array {
        return DB::fetchAll(
            "SELECT channel, COALESCE(SUM(amount),0) as volume, COUNT(*) as count
             FROM transactions WHERE status = 'completed'
             GROUP BY channel ORDER BY volume DESC"
        );
    }
}
