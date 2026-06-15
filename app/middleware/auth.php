<?php
class AuthMiddleware {
    public static function handle(): void {
        if (!is_logged_in()) {
            if (self::isApiRequest()) {
                api_error('Unauthorized. Please provide a valid API key.', 401);
            }
            redirect('login');
        }
    }

    public static function handleApiKey(): ?array {
        $key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
        if (empty($key)) {
            api_error('API key is required.', 401);
        }

        $apiKey = DB::fetch(
            "SELECT ak.*, u.id as user_id, u.business_name, u.status as account_status
             FROM api_keys ak
             JOIN users u ON ak.user_id = u.id
             WHERE ak.key_value = ? AND ak.is_active = 1",
            [$key]
        );

        if (!$apiKey) {
            api_error('Invalid or inactive API key.', 401);
        }

        if ($apiKey['account_status'] !== 'active') {
            api_error('Your account has been suspended.', 403);
        }

        DB::query("UPDATE api_keys SET last_used_at = NOW() WHERE id = ?", [$apiKey['id']]);

        return $apiKey;
    }

    private static function isApiRequest(): bool {
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
    }
}
