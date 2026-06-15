<?php
class Transaction {
    public static function create(array $data): string {
        $ref = generate_reference('TXN');
        return DB::insert(
            "INSERT INTO transactions
             (id, user_id, reference, amount, currency, channel, phone, description, status, metadata, created_at)
             VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())",
            [
                $data['user_id'],
                $ref,
                $data['amount'],
                $data['currency'] ?? 'KES',
                $data['channel'],
                $data['phone'] ?? null,
                $data['description'] ?? '',
                json_encode($data['metadata'] ?? []),
            ]
        );
    }

    public static function updateStatus(string $reference, string $status, array $meta = []): void {
        DB::query(
            "UPDATE transactions SET status = ?, metadata = JSON_MERGE_PATCH(COALESCE(metadata,'{}'), ?), updated_at = NOW()
             WHERE reference = ?",
            [$status, json_encode($meta), $reference]
        );
    }

    public static function findByRef(string $reference): ?array {
        return DB::fetch("SELECT * FROM transactions WHERE reference = ? LIMIT 1", [$reference]);
    }

    public static function getForUser(string $userId, int $limit = 20, int $offset = 0, array $filters = []): array {
        $where = "WHERE user_id = ?";
        $params = [$userId];

        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['channel'])) {
            $where .= " AND channel = ?";
            $params[] = $filters['channel'];
        }
        if (!empty($filters['date_from'])) {
            $where .= " AND DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where .= " AND DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $params[] = $limit;
        $params[] = $offset;

        return DB::fetchAll(
            "SELECT * FROM transactions {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            $params
        );
    }

    public static function countForUser(string $userId, array $filters = []): int {
        $where = "WHERE user_id = ?";
        $params = [$userId];

        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $row = DB::fetch("SELECT COUNT(*) as cnt FROM transactions {$where}", $params);
        return (int)($row['cnt'] ?? 0);
    }

    public static function getAnalytics(string $userId, string $dateFrom, string $dateTo): array {
        $params = [$userId, $dateFrom, $dateTo];
        $range  = "WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?";

        $summary = DB::fetch(
            "SELECT
               COALESCE(SUM(CASE WHEN status='completed' THEN amount END), 0) as volume,
               COALESCE(SUM(CASE WHEN status='completed' THEN fee END), 0)    as fees,
               COALESCE(SUM(CASE WHEN status='completed' THEN net_amount END),0) as net,
               COUNT(*) as total,
               SUM(status='completed') as completed,
               SUM(status='failed')    as failed,
               SUM(status='pending')   as pending
             FROM transactions $range",
            $params
        );

        $byDay = DB::fetchAll(
            "SELECT DATE(created_at) as day,
               COALESCE(SUM(CASE WHEN status='completed' THEN amount END),0) as volume,
               COUNT(*) as count
             FROM transactions $range
             GROUP BY DATE(created_at) ORDER BY day ASC",
            $params
        );

        $byChannel = DB::fetchAll(
            "SELECT channel,
               COALESCE(SUM(CASE WHEN status='completed' THEN amount END),0) as volume,
               COUNT(*) as count,
               SUM(status='completed') as completed
             FROM transactions $range
             GROUP BY channel ORDER BY volume DESC",
            $params
        );

        $byStatus = DB::fetchAll(
            "SELECT status, COUNT(*) as count,
               COALESCE(SUM(amount),0) as volume
             FROM transactions $range
             GROUP BY status",
            $params
        );

        $topDays = DB::fetchAll(
            "SELECT DATE(created_at) as day,
               SUM(CASE WHEN status='completed' THEN amount END) as volume
             FROM transactions $range
             GROUP BY DATE(created_at) ORDER BY volume DESC LIMIT 5",
            $params
        );

        return compact('summary', 'byDay', 'byChannel', 'byStatus', 'topDays');
    }

    public static function exportCsv(string $userId, array $filters = []): array {
        $where  = "WHERE user_id = ?";
        $params = [$userId];

        if (!empty($filters['status']))    { $where .= " AND status = ?";              $params[] = $filters['status']; }
        if (!empty($filters['channel']))   { $where .= " AND channel = ?";             $params[] = $filters['channel']; }
        if (!empty($filters['date_from'])) { $where .= " AND DATE(created_at) >= ?";  $params[] = $filters['date_from']; }
        if (!empty($filters['date_to']))   { $where .= " AND DATE(created_at) <= ?";  $params[] = $filters['date_to']; }

        return DB::fetchAll(
            "SELECT reference, channel, phone, card_last4, description, amount, fee, net_amount,
                    currency, status, created_at, updated_at
             FROM transactions $where ORDER BY created_at DESC LIMIT 10000",
            $params
        );
    }
}
