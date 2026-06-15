<?php
class WalletController {
    public function __construct(
        private ?array $merchant,
        private array  $body,
        private array  $params
    ) {}

    public function lookup(): void {
        $q = trim($_GET['q'] ?? '');
        if (!$q) api_error('Query parameter q is required', 400);

        $user = DB::fetch(
            "SELECT id, business_name, email, phone, status FROM users
             WHERE (email = ? OR phone = ?) AND status = 'active' LIMIT 1",
            [$q, $q]
        );

        if (!$user) api_error('No active OrbitPesa account found', 404);

        // Don't reveal full details — just enough for confirmation
        api_success([
            'id'            => $user['id'],
            'business_name' => $user['business_name'],
            'email'         => $user['email'],
            'phone'         => mask_phone($user['phone']),
        ]);
    }
}
