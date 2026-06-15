<?php
function redirect(string $path): void {
    header('Location: ' . APP_URL . '/' . ltrim($path, '/'));
    exit;
}

function view(string $template, array $data = [], string $layout = 'app'): void {
    extract($data);
    $content = function() use ($template, $data) {
        extract($data);
        require BASE_PATH . '/views/' . $template . '.php';
    };
    if ($layout) {
        require BASE_PATH . '/views/layouts/' . $layout . '.php';
    } else {
        $content();
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function csrf_verify(): bool {
    $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function format_amount(float $amount, string $currency = 'KES'): string {
    return $currency . ' ' . number_format($amount, 2);
}

function generate_reference(string $prefix = 'OP'): string {
    return strtoupper($prefix) . '-' . strtoupper(bin2hex(random_bytes(5))) . '-' . date('Ymd');
}

function time_ago(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}

function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function api_error(string $message, int $status = 400, array $extra = []): void {
    json_response(array_merge(['success' => false, 'message' => $message], $extra), $status);
}

function api_success(array $data = [], string $message = 'Success'): void {
    json_response(array_merge(['success' => true, 'message' => $message], $data));
}

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function auth_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_auth(): void {
    if (!is_logged_in()) {
        redirect('login');
    }
}

function require_guest(): void {
    if (is_logged_in()) {
        redirect('dashboard');
    }
}

function flash(string $key, ?string $message = null): ?string {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function mask_phone(string $phone): string {
    return substr($phone, 0, 4) . '****' . substr($phone, -3);
}

function mask_card(string $card): string {
    return '**** **** **** ' . substr($card, -4);
}

function transaction_status_badge(string $status): string {
    $classes = [
        'completed'  => 'badge-success',
        'pending'    => 'badge-warning',
        'failed'     => 'badge-danger',
        'reversed'   => 'badge-secondary',
        'processing' => 'badge-info',
    ];
    $class = $classes[$status] ?? 'badge-secondary';
    return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
}
