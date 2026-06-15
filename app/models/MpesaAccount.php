<?php
class MpesaAccount {

    public static function create(array $data): string {
        $uuid = DB::fetch("SELECT UUID() as id")['id'];
        DB::insert(
            "INSERT INTO mpesa_accounts (id, user_id, application_type, business_name, business_reg_no,
             contact_name, contact_email, contact_phone, business_type, monthly_volume, description)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $uuid,
                $data['user_id'] ?? null,
                $data['application_type'],
                $data['business_name'],
                $data['business_reg_no'] ?? null,
                $data['contact_name'],
                $data['contact_email'],
                $data['contact_phone'],
                $data['business_type'],
                $data['monthly_volume'],
                $data['description'] ?? null,
            ]
        );
        return $uuid;
    }

    public static function find(string $id): ?array {
        return DB::fetch("SELECT * FROM mpesa_accounts WHERE id = ?", [$id]);
    }

    public static function findByUserId(string $userId): ?array {
        return DB::fetch(
            "SELECT * FROM mpesa_accounts WHERE user_id = ? ORDER BY created_at DESC LIMIT 1",
            [$userId]
        );
    }

    public static function findByAccountNumber(string $number): ?array {
        return DB::fetch(
            "SELECT * FROM mpesa_accounts WHERE account_number = ? AND status = 'approved'",
            [$number]
        );
    }

    public static function getAll(array $filters = [], int $limit = 50, int $offset = 0): array {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[]  = 'ma.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[]  = 'ma.application_type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['search'])) {
            $s        = '%' . $filters['search'] . '%';
            $where[]  = '(ma.business_name LIKE ? OR ma.contact_email LIKE ? OR ma.account_number LIKE ?)';
            $params[] = $s; $params[] = $s; $params[] = $s;
        }
        $params[] = $limit;
        $params[] = $offset;
        return DB::fetchAll(
            "SELECT ma.*, u.business_name AS merchant_name
               FROM mpesa_accounts ma
               LEFT JOIN users u ON u.id = ma.user_id
              WHERE " . implode(' AND ', $where) . "
           ORDER BY ma.created_at DESC
              LIMIT ? OFFSET ?",
            $params
        );
    }

    public static function countAll(array $filters = []): int {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[]  = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[]  = 'application_type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['search'])) {
            $s        = '%' . $filters['search'] . '%';
            $where[]  = '(business_name LIKE ? OR contact_email LIKE ? OR account_number LIKE ?)';
            $params[] = $s; $params[] = $s; $params[] = $s;
        }
        $row = DB::fetch(
            "SELECT COUNT(*) AS c FROM mpesa_accounts WHERE " . implode(' AND ', $where),
            $params
        );
        return (int)($row['c'] ?? 0);
    }

    public static function countByStatus(): array {
        $rows = DB::fetchAll("SELECT status, COUNT(*) AS c FROM mpesa_accounts GROUP BY status");
        $out  = ['pending' => 0, 'under_review' => 0, 'approved' => 0, 'rejected' => 0];
        foreach ($rows as $r) {
            if (isset($out[$r['status']])) $out[$r['status']] = (int)$r['c'];
        }
        return $out;
    }

    public static function approve(string $id, string $accountNumber, string $adminId): void {
        DB::query(
            "UPDATE mpesa_accounts SET status='approved', account_number=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?",
            [$accountNumber, $adminId, $id]
        );
    }

    public static function reject(string $id, string $notes, string $adminId): void {
        DB::query(
            "UPDATE mpesa_accounts SET status='rejected', admin_notes=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?",
            [$notes, $adminId, $id]
        );
    }

    public static function setUnderReview(string $id, string $adminId): void {
        DB::query(
            "UPDATE mpesa_accounts SET status='under_review', reviewed_by=? WHERE id=?",
            [$adminId, $id]
        );
    }
}
