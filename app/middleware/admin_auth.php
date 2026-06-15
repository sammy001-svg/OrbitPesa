<?php
class AdminAuthMiddleware {
    public static function handle(): void {
        if (empty($_SESSION['admin_id'])) {
            redirect('admin/login');
        }
    }

    public static function requireRole(string $role): void {
        self::handle();
        $roles = ['support' => 1, 'admin' => 2, 'super_admin' => 3];
        $current = $roles[$_SESSION['admin']['role'] ?? 'support'] ?? 0;
        $required = $roles[$role] ?? 99;
        if ($current < $required) {
            http_response_code(403);
            echo '<div style="text-align:center;padding:60px;font-family:sans-serif"><h2 style="color:#dc2626">Access Denied</h2><p>You do not have permission to access this page.</p></div>';
            exit;
        }
    }

    public static function isLoggedIn(): bool {
        return !empty($_SESSION['admin_id']);
    }

    public static function admin(): ?array {
        return $_SESSION['admin'] ?? null;
    }
}
