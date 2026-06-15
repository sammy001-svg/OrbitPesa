<?php
define('BASE_PATH', dirname(dirname(dirname(__FILE__))));
define('API_REQUEST', true);

require_once BASE_PATH . '/app/config/config.php';
require_once BASE_PATH . '/app/config/database.php';
require_once BASE_PATH . '/app/core/functions.php';
require_once BASE_PATH . '/app/middleware/auth.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Transaction.php';
require_once BASE_PATH . '/app/models/PaymentLink.php';
require_once BASE_PATH . '/app/models/Wallet.php';
require_once BASE_PATH . '/app/models/ApiKey.php';
require_once BASE_PATH . '/app/models/Webhook.php';
require_once BASE_PATH . '/app/models/Notification.php';
require_once BASE_PATH . '/app/core/WebhookDispatcher.php';
require_once BASE_PATH . '/app/core/Mailer.php';
require_once BASE_PATH . '/app/models/WalletUser.php';
require_once BASE_PATH . '/app/models/WalletNotification.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$uri    = $_SERVER['REQUEST_URI'];
$uri    = parse_url($uri, PHP_URL_PATH);
$uri    = preg_replace('#^.*/api/v1#', '', $uri);
$uri    = trim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

$routes = [
    'GET'  => [
        'checkout/status/{ref}' => 'checkout#status',
        'payments/status/{ref}' => 'payments#status',
        'payments/status'       => 'payments#status',
        'transactions'          => 'transactions#index',
        'transactions/{ref}'    => 'transactions#show',
        'payment-links'         => 'payment_links#index',
        'wallet/lookup'         => 'wallet#lookup',
        'ping'                  => 'system#ping',
    ],
    'POST' => [
        'checkout/{slug}/pay'     => 'checkout#pay',
        'payments/mpesa/stk'      => 'payments#mpesa_stk',
        'payments/mpesa/callback' => 'payments#mpesa_callback',
        'payments/card/charge'    => 'payments#card_charge',
        'payments/wallet/pay'     => 'payments#wallet_pay',
        'payment-links'           => 'payment_links#create',
    ],
];

function matchRoute(array $routes, string $method, string $uri): ?array {
    $definitions = $routes[$method] ?? [];
    foreach ($definitions as $pattern => $action) {
        $regex = preg_replace('#\{([^}]+)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        if (preg_match($regex, $uri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return ['action' => $action, 'params' => $params];
        }
    }
    return null;
}

$match = matchRoute($routes, $method, $uri);

if (!$match) {
    api_error('Endpoint not found. See documentation at ' . APP_URL . '/developers/docs', 404);
}

[$controller, $actionName] = explode('#', $match['action']);
$params = $match['params'];

$publicEndpoints = ['payments#mpesa_callback', 'checkout#pay', 'checkout#status', 'system#ping'];
$merchantData = null;

if (!in_array($match['action'], $publicEndpoints)) {
    $merchantData = AuthMiddleware::handleApiKey();
}

switch ($controller) {
    case 'payments':
        require_once __DIR__ . '/payments.php';
        $c = new PaymentsController($merchantData, $body, $params);
        break;
    case 'transactions':
        require_once __DIR__ . '/transactions.php';
        $c = new TransactionsController($merchantData, $body, $params);
        break;
    case 'payment_links':
        require_once __DIR__ . '/payment_links.php';
        $c = new PaymentLinksController($merchantData, $body, $params);
        break;
    case 'checkout':
        require_once __DIR__ . '/checkout.php';
        $c = new CheckoutController($body, $params);
        break;
    case 'wallet':
        require_once __DIR__ . '/wallet.php';
        $c = new WalletController($merchantData, $body, $params);
        break;
    case 'system':
        api_success(['version' => APP_VERSION, 'timestamp' => date('c')], 'OrbitPesa API is operational');
        exit;
    default:
        api_error('Controller not found', 404);
}

$c->$actionName();
