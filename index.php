<?php
define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/app/config/config.php';
require_once BASE_PATH . '/app/config/database.php';
require_once BASE_PATH . '/app/core/functions.php';
require_once BASE_PATH . '/app/middleware/auth.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Transaction.php';
require_once BASE_PATH . '/app/models/PaymentLink.php';
require_once BASE_PATH . '/app/models/Wallet.php';
require_once BASE_PATH . '/app/models/ApiKey.php';
require_once BASE_PATH . '/app/models/Admin.php';
require_once BASE_PATH . '/app/models/Webhook.php';
require_once BASE_PATH . '/app/models/Notification.php';
require_once BASE_PATH . '/app/models/MpesaAccount.php';
require_once BASE_PATH . '/app/models/WalletUser.php';
require_once BASE_PATH . '/app/models/WalletTransaction.php';
require_once BASE_PATH . '/app/models/WalletPocket.php';
require_once BASE_PATH . '/app/models/WalletNotification.php';
require_once BASE_PATH . '/app/models/WalletReferral.php';
require_once BASE_PATH . '/app/core/WebhookDispatcher.php';
require_once BASE_PATH . '/app/core/Mailer.php';
require_once BASE_PATH . '/app/middleware/admin_auth.php';

session_name(SESSION_NAME);
session_start();

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = preg_replace('#^/OrbitPesa#', '', $uri);
$uri    = trim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

function renderView(string $template, array $data = [], string $layout = 'app'): void {
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

$routes = [
    ''                                  => ['handler' => 'landing',    'auth' => false,  'layout' => null],
    'login'                             => ['handler' => 'login',      'auth' => false,  'layout' => null],
    'register'                          => ['handler' => 'register',   'auth' => false,  'layout' => null],
    'logout'                            => ['handler' => 'logout',     'auth' => true,   'layout' => null],
    'dashboard'                         => ['handler' => 'dashboard',  'auth' => true,   'layout' => 'app'],
    'dashboard/transactions'            => ['handler' => 'transactions','auth' => true,   'layout' => 'app'],
    'dashboard/payment-links'           => ['handler' => 'paylinks',   'auth' => true,   'layout' => 'app'],
    'dashboard/wallet'                  => ['handler' => 'wallet',     'auth' => true,   'layout' => 'app'],
    'dashboard/api-keys'                => ['handler' => 'apikeys',    'auth' => true,   'layout' => 'app'],
    'dashboard/settings'                => ['handler' => 'settings',   'auth' => true,   'layout' => 'app'],
    'dashboard/mpesa'                   => ['handler' => 'mpesa',      'auth' => true,   'layout' => 'app'],
    'dashboard/card'                    => ['handler' => 'card',       'auth' => true,   'layout' => 'app'],
    'dashboard/wallet-pay'              => ['handler' => 'walletpay',  'auth' => true,   'layout' => 'app'],
    'dashboard/analytics'               => ['handler' => 'analytics',  'auth' => true,   'layout' => 'app'],
    'dashboard/webhooks'                => ['handler' => 'webhooks',   'auth' => true,   'layout' => 'app'],
    'dashboard/kyc'                     => ['handler' => 'kyc',        'auth' => true,   'layout' => 'app'],
    'dashboard/notifications'           => ['handler' => 'notifications','auth' => true,  'layout' => 'app'],
    'dashboard/mpesa-account'           => ['handler' => 'mpesa_account','auth' => true,  'layout' => 'app'],
    'apply-mpesa'                       => ['handler' => 'apply_mpesa',  'auth' => false, 'layout' => null],
    'contact'                           => ['handler' => 'contact',      'auth' => false, 'layout' => null],
    'developers'                        => ['handler' => 'devhome',      'auth' => false, 'layout' => null],
    'developers/docs'                   => ['handler' => 'devdocs',      'auth' => false, 'layout' => null],
    'developers/api-reference'          => ['handler' => 'devdocs',      'auth' => false, 'layout' => null],

    // Admin routes
    'admin/login'                       => ['handler' => 'admin_login',      'auth' => false, 'layout' => null, 'admin' => false],
    'admin/logout'                      => ['handler' => 'admin_logout',     'auth' => false, 'layout' => null, 'admin' => false],
    'admin'                             => ['handler' => 'admin_dash',       'auth' => false, 'layout' => null, 'admin' => true],
    'admin/dashboard'                   => ['handler' => 'admin_dash',       'auth' => false, 'layout' => null, 'admin' => true],
    'admin/merchants'                   => ['handler' => 'admin_merchants',  'auth' => false, 'layout' => null, 'admin' => true],
    'admin/transactions'                => ['handler' => 'admin_txns',       'auth' => false, 'layout' => null, 'admin' => true],
    'admin/withdrawals'                 => ['handler' => 'admin_wds',        'auth' => false, 'layout' => null, 'admin' => true],
    'admin/fees'                        => ['handler' => 'admin_fees',       'auth' => false, 'layout' => null, 'admin' => true],
    'admin/settings'                    => ['handler' => 'admin_settings',   'auth' => false, 'layout' => null, 'admin' => true],
    'admin/kyc'                         => ['handler' => 'admin_kyc',        'auth' => false, 'layout' => null, 'admin' => true],
    'admin/disputes'                    => ['handler' => 'admin_disputes',   'auth' => false, 'layout' => null, 'admin' => true],
    'admin/logs'                        => ['handler' => 'admin_logs',       'auth' => false, 'layout' => null, 'admin' => true],
    'admin/weekly-summary'              => ['handler' => 'admin_weekly',     'auth' => false, 'layout' => null, 'admin' => true],
    'admin/mpesa-accounts'              => ['handler' => 'admin_mpesa',      'auth' => false, 'layout' => null, 'admin' => true],
    'admin/wallet-users'                => ['handler' => 'admin_wallet_users','auth' => false, 'layout' => null, 'admin' => true],
    'admin/wallet-transactions'         => ['handler' => 'admin_wallet_txns', 'auth' => false, 'layout' => null, 'admin' => true],

    // Consumer wallet routes
    'wallet'              => ['handler' => 'wallet_landing',  'auth' => false, 'layout' => null],
    'wallet/register'     => ['handler' => 'wallet_register', 'auth' => false, 'layout' => null],
    'wallet/login'        => ['handler' => 'wallet_login',    'auth' => false, 'layout' => null],
    'wallet/home'         => ['handler' => 'wallet_home',     'auth' => false, 'layout' => null],
    'wallet/send'         => ['handler' => 'wallet_send',     'auth' => false, 'layout' => null],
    'wallet/receive'      => ['handler' => 'wallet_receive',  'auth' => false, 'layout' => null],
    'wallet/airtime'      => ['handler' => 'wallet_airtime',  'auth' => false, 'layout' => null],
    'wallet/paybill'      => ['handler' => 'wallet_paybill',  'auth' => false, 'layout' => null],
    'wallet/transfer'     => ['handler' => 'wallet_transfer', 'auth' => false, 'layout' => null],
    'wallet/transactions' => ['handler' => 'wallet_txns',     'auth' => false, 'layout' => null],
    'wallet/profile'      => ['handler' => 'wallet_profile',    'auth' => false, 'layout' => null],
    'wallet/pay-merchant' => ['handler' => 'wallet_pay_merchant','auth' => false, 'layout' => null],
    'wallet/pockets'      => ['handler' => 'wallet_pockets',     'auth' => false, 'layout' => null],
    'wallet/scan'          => ['handler' => 'wallet_scan',          'auth' => false, 'layout' => null],
    'wallet/notifications' => ['handler' => 'wallet_notifications', 'auth' => false, 'layout' => null],
];

// Wallet: user lookup (JSON)
if ($uri === 'wallet/find-user' && $method === 'GET') {
    header('Content-Type: application/json');
    if (empty($_SESSION['wallet_uid'])) { echo json_encode(['found' => false]); exit; }
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 3) { echo json_encode(['found' => false]); exit; }
    $wu = WalletUser::findByIdentifier($q);
    if (!$wu || $wu['id'] === $_SESSION['wallet_uid'] || $wu['status'] !== 'active') {
        echo json_encode(['found' => false]);
        exit;
    }
    echo json_encode(['found' => true, 'id' => $wu['id'], 'name' => $wu['full_name'], 'wallet_id' => $wu['wallet_id']]);
    exit;
}

// Dynamic wallet route — pocket detail: wallet/pockets/{id}
if (preg_match('#^wallet/pockets/([^/]+)$#', $uri, $m) && $method === 'GET') {
    if (empty($_SESSION['wallet_uid'])) { redirect('wallet/login'); }
    $pocket = WalletPocket::find($m[1]);
    if (!$pocket || $pocket['wallet_user_id'] !== $_SESSION['wallet_uid']) {
        http_response_code(404);
        echo '<div style="text-align:center;padding:80px;font-family:sans-serif"><h2>Pocket not found</h2></div>';
        exit;
    }
    $walletUser = WalletUser::find($_SESSION['wallet_uid']);
    if (!$walletUser || $walletUser['status'] === 'suspended') {
        unset($_SESSION['wallet_uid'], $_SESSION['wallet_user']);
        redirect('wallet/login');
    }
    $data = ['walletUser' => $walletUser, 'pocket' => $pocket, 'pageTitle' => htmlspecialchars($pocket['name'])];
    extract($data);
    $content = function() use ($data, $walletUser) {
        extract($data);
        require BASE_PATH . '/views/wallet/pocket-detail.php';
    };
    require BASE_PATH . '/views/layouts/wallet.php';
    exit;
}

// Wallet: find merchant by email/phone/business name (JSON)
if ($uri === 'wallet/find-merchant' && $method === 'GET') {
    header('Content-Type: application/json');
    if (empty($_SESSION['wallet_uid'])) { echo json_encode(['found' => false]); exit; }
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 3) { echo json_encode(['found' => false]); exit; }
    $like = '%' . $q . '%';
    $merchant = DB::fetch(
        "SELECT id, business_name, email FROM users WHERE status='active' AND (email LIKE ? OR phone LIKE ? OR business_name LIKE ?) LIMIT 1",
        [$like, $like, $like]
    );
    if (!$merchant) { echo json_encode(['found' => false]); exit; }
    echo json_encode(['found' => true, 'id' => $merchant['id'], 'name' => $merchant['business_name'], 'email' => $merchant['email']]);
    exit;
}

// Wallet notifications: unread count (JSON for badge polling)
if ($uri === 'wallet/notifications/count' && $method === 'GET') {
    header('Content-Type: application/json');
    $count = !empty($_SESSION['wallet_uid']) ? WalletNotification::unreadCount($_SESSION['wallet_uid']) : 0;
    echo json_encode(['count' => $count]);
    exit;
}

// Notifications: unread count (JSON for badge polling)
if ($uri === 'dashboard/notifications/count' && $method === 'GET') {
    header('Content-Type: application/json');
    $count = is_logged_in() ? Notification::unreadCount($_SESSION['user_id']) : 0;
    echo json_encode(['count' => $count]);
    exit;
}

// Notifications: go to URL and mark read
if ($uri === 'dashboard/notifications/goto' && $method === 'GET' && is_logged_in()) {
    $nid   = $_GET['id'] ?? '';
    $notif = DB::fetch("SELECT * FROM notifications WHERE id = ? AND user_id = ?", [$nid, $_SESSION['user_id']]);
    if ($notif) {
        Notification::markRead($nid, $_SESSION['user_id']);
        $dest = $notif['url'] ? APP_URL . $notif['url'] : APP_URL . '/dashboard/notifications';
        header('Location: ' . $dest);
        exit;
    }
    redirect('dashboard/notifications');
}

// CSV export: dashboard/analytics/export
if ($uri === 'dashboard/analytics/export' && $method === 'GET') {
    require_auth();
    $filters = [
        'date_from' => $_GET['date_from'] ?? date('Y-m-d', strtotime('-29 days')),
        'date_to'   => $_GET['date_to']   ?? date('Y-m-d'),
        'channel'   => $_GET['channel']   ?? '',
        'status'    => $_GET['status']    ?? '',
    ];
    $rows = Transaction::exportCsv($_SESSION['user_id'], array_filter($filters));
    $filename = 'orbitpesa-transactions-' . date('Ymd') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Reference','Channel','Phone','Card Last4','Description','Amount','Fee','Net Amount','Currency','Status','Created At','Updated At']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['reference'],$r['channel'],$r['phone'],$r['card_last4'],$r['description'],$r['amount'],$r['fee'],$r['net_amount'],$r['currency'],$r['status'],$r['created_at'],$r['updated_at']]);
    }
    fclose($out);
    exit;
}

// Dynamic admin route — merchant detail: admin/merchants/{id}
if (preg_match('#^admin/merchants/([^/]+)$#', $uri, $m) && $method === 'GET') {
    AdminAuthMiddleware::handle();
    $merchantId = $m[1];
    $merchant = DB::fetch("SELECT * FROM users WHERE id = ?", [$merchantId]);
    if (!$merchant) { http_response_code(404); echo '<div style="text-align:center;padding:80px;font-family:sans-serif"><h2>Merchant not found</h2></div>'; exit; }
    $adminUser   = AdminAuthMiddleware::admin();
    $pageTitle   = sanitize($merchant['business_name']);
    $activeAdmin = 'merchants';
    $content = function() use ($merchant) {
        require BASE_PATH . '/views/admin/merchants/show.php';
    };
    require BASE_PATH . '/views/admin/layouts/admin.php';
    exit;
}

// Dynamic admin route — wallet user detail: admin/wallet-users/{id}
if (preg_match('#^admin/wallet-users/([^/]+)$#', $uri, $m) && $method === 'GET') {
    AdminAuthMiddleware::handle();
    $wu = WalletUser::find($m[1]);
    if (!$wu) { http_response_code(404); echo '<div style="text-align:center;padding:80px;font-family:sans-serif"><h2>Wallet user not found</h2></div>'; exit; }
    $adminUser   = AdminAuthMiddleware::admin();
    $pageTitle   = sanitize($wu['full_name']);
    $breadcrumb  = 'Wallet Users';
    $activeAdmin = 'wallet-users';
    $content = function() use ($wu) {
        require BASE_PATH . '/views/admin/wallet-users/show.php';
    };
    require BASE_PATH . '/views/admin/layouts/admin.php';
    exit;
}

// Dynamic routes — payment links (pay/{slug})
if (preg_match('#^pay/([a-z0-9]+)$#', $uri, $m)) {
    $slug = $m[1];
    $link = PaymentLink::findBySlug($slug);
    if (!$link || $link['status'] !== 'active') {
        http_response_code(404);
        echo '<div style="text-align:center;padding:80px;font-family:sans-serif"><h2>Payment link not found or expired</h2><a href="' . APP_URL . '/">Back to home</a></div>';
        exit;
    }
    if ($link['expires_at'] && strtotime($link['expires_at']) < time()) {
        echo '<div style="text-align:center;padding:80px;font-family:sans-serif"><h2>This payment link has expired</h2></div>';
        exit;
    }
    require BASE_PATH . '/views/pay/payment-link.php';
    exit;
}

$route = $routes[$uri] ?? null;

// =============================================
// POST HANDLERS (form submissions)
// =============================================
if ($method === 'POST') {
    if (!csrf_verify()) {
        flash('error', 'Invalid request. Please try again.');
        redirect($_SERVER['HTTP_REFERER'] ?? '');
    }

    switch ($uri) {
        case 'login':
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!$email || !$password) {
                flash('error', 'Email and password are required.');
                redirect('login');
            }

            $user = User::findByEmail($email);
            if (!$user || !User::verifyPassword($password, $user['password'])) {
                flash('error', 'Invalid email or password.');
                redirect('login');
            }
            if ($user['status'] === 'suspended') {
                flash('error', 'Your account has been suspended. Contact support.');
                redirect('login');
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user']    = [
                'id'            => $user['id'],
                'business_name' => $user['business_name'],
                'email'         => $user['email'],
                'phone'         => $user['phone'],
                'account_type'  => $user['account_type'],
                'kyc_status'    => $user['kyc_status'],
                'status'        => $user['status'],
                'env'           => 'test',
            ];

            if (!empty($_POST['remember'])) {
                setcookie(SESSION_NAME, session_id(), time() + 30 * 86400, '/', '', false, true);
            }

            redirect('dashboard');

        case 'register':
            $businessName  = trim($_POST['business_name'] ?? '');
            $email         = trim($_POST['email'] ?? '');
            $phone         = trim($_POST['phone'] ?? '');
            $password      = $_POST['password'] ?? '';
            $passwordConf  = $_POST['password_confirm'] ?? '';
            $accountType   = $_POST['account_type'] ?? 'business';

            $errors = [];
            if (!$businessName) $errors[] = 'Business name is required.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email address is required.';
            if (!preg_match('/^(07|01)\d{8}$/', $phone)) $errors[] = 'Valid Kenyan phone number is required.';
            if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
            if ($password !== $passwordConf) $errors[] = 'Passwords do not match.';
            if (User::findByEmail($email)) $errors[] = 'An account with this email already exists.';

            if ($errors) {
                flash('error', implode(' ', $errors));
                redirect('register');
            }

            User::create([
                'business_name' => $businessName,
                'email'         => $email,
                'phone'         => $phone,
                'password'      => $password,
                'account_type'  => $accountType,
            ]);

            $user = User::findByEmail($email);
            Wallet::getOrCreate($user['id']);
            ApiKey::generate($user['id'], 'Default Test Key', 'test');
            Mailer::welcome($user);

            flash('success', 'Account created! Please log in.');
            redirect('login');

        case 'dashboard/notifications/read':
            require_auth();
            Notification::markRead($_POST['id'] ?? '', $_SESSION['user_id']);
            redirect('dashboard/notifications' . (isset($_GET['filter']) ? '?filter=' . urlencode($_GET['filter']) : ''));

        case 'dashboard/notifications/read-all':
            require_auth();
            Notification::markAllRead($_SESSION['user_id']);
            redirect($_SERVER['HTTP_REFERER'] ?? 'dashboard/notifications');

        case 'dashboard/kyc/upload':
            require_auth();
            $docType = $_POST['doc_type'] ?? '';
            $allowed = ['national_id','passport','business_reg','bank_statement','utility_bill'];
            if (!in_array($docType, $allowed)) {
                flash('error', 'Invalid document type.');
                redirect('dashboard/kyc');
            }

            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE   => 'File exceeds server size limit.',
                    UPLOAD_ERR_FORM_SIZE  => 'File exceeds form size limit.',
                    UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
                    UPLOAD_ERR_NO_FILE    => 'No file was selected.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Server temporary folder is missing.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                ];
                $errCode = $_FILES['document']['error'] ?? UPLOAD_ERR_NO_FILE;
                flash('error', $uploadErrors[$errCode] ?? 'File upload failed.');
                redirect('dashboard/kyc');
            }

            $file     = $_FILES['document'];
            $maxBytes = 5 * 1024 * 1024;
            if ($file['size'] > $maxBytes) {
                flash('error', 'File size must not exceed 5 MB.');
                redirect('dashboard/kyc');
            }

            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
            $mimeToExt    = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
            if (!in_array($mime, $allowedMimes)) {
                flash('error', 'Only JPG, PNG and PDF files are accepted.');
                redirect('dashboard/kyc');
            }

            $ext      = $mimeToExt[$mime];
            $userId   = $_SESSION['user_id'];
            $dir      = BASE_PATH . '/storage/kyc/' . $userId;
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            $filename = $docType . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest     = $dir . '/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                flash('error', 'Failed to save file. Please try again.');
                redirect('dashboard/kyc');
            }

            $filePath = 'storage/kyc/' . $userId . '/' . $filename;
            DB::insert(
                "INSERT INTO kyc_documents (id, user_id, doc_type, file_path, status) VALUES (UUID(), ?, ?, ?, 'pending')",
                [$userId, $docType, $filePath]
            );
            DB::query(
                "UPDATE users SET kyc_status = 'pending' WHERE id = ? AND kyc_status = 'unverified'",
                [$userId]
            );
            DB::query(
                "UPDATE users SET kyc_status = 'pending' WHERE id = ? AND kyc_status = 'rejected'",
                [$userId]
            );

            if (in_array($_SESSION['user']['kyc_status'] ?? '', ['unverified', 'rejected'])) {
                $_SESSION['user']['kyc_status'] = 'pending';
            }
            Mailer::kycSubmitted(auth_user(), $docType);

            $docLabel = ucwords(str_replace('_', ' ', $docType));
            flash('success', $docLabel . ' submitted successfully. Our team will review it within 1–2 business days.');
            redirect('dashboard/kyc');

        case 'contact':
            $name    = trim($_POST['name'] ?? '');
            $email   = trim($_POST['email'] ?? '');
            $phone   = trim($_POST['phone'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$subject || !$message) {
                flash('contact_error', 'Please fill in all required fields correctly.');
                redirect('/#contact');
            }

            $adminEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : MAIL_FROM;
            $html = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto;padding:24px">'
                  . '<h2 style="color:#0D1B3E;margin-bottom:16px">New Enquiry — OrbitPesa</h2>'
                  . '<table style="width:100%;border-collapse:collapse;font-size:.9rem">'
                  . '<tr><td style="padding:8px 12px;background:#f4f7fb;font-weight:700;width:120px">Name</td><td style="padding:8px 12px;border:1px solid #dde2ec">' . htmlspecialchars($name) . '</td></tr>'
                  . '<tr><td style="padding:8px 12px;background:#f4f7fb;font-weight:700">Email</td><td style="padding:8px 12px;border:1px solid #dde2ec">' . htmlspecialchars($email) . '</td></tr>'
                  . '<tr><td style="padding:8px 12px;background:#f4f7fb;font-weight:700">Phone</td><td style="padding:8px 12px;border:1px solid #dde2ec">' . htmlspecialchars($phone ?: 'N/A') . '</td></tr>'
                  . '<tr><td style="padding:8px 12px;background:#f4f7fb;font-weight:700">Subject</td><td style="padding:8px 12px;border:1px solid #dde2ec">' . htmlspecialchars($subject) . '</td></tr>'
                  . '</table>'
                  . '<p style="margin-top:16px;font-weight:700;color:#0D1B3E">Message:</p>'
                  . '<div style="background:#f4f7fb;border-left:4px solid #158347;padding:14px 16px;font-size:.9rem;line-height:1.6">' . nl2br(htmlspecialchars($message)) . '</div>'
                  . '</div>';

            Mailer::send($adminEmail, 'OrbitPesa Enquiry: ' . $subject, $html);
            flash('contact_success', 'Thank you for reaching out! We\'ll get back to you within 1 business day.');
            redirect('/#contact');

        case 'dashboard/payment-links/create':
            require_auth();
            $title    = trim($_POST['title'] ?? '');
            $amount   = $_POST['amount'] ?? null;
            $isFixed  = (int)($_POST['is_fixed_amount'] ?? 1);
            $desc     = trim($_POST['description'] ?? '');
            $maxUses  = $_POST['max_uses'] ?? null;
            $expires  = $_POST['expires_at'] ?? null;

            if (!$title) { flash('error', 'Title is required.'); redirect('dashboard/payment-links'); }

            PaymentLink::create([
                'user_id'         => $_SESSION['user_id'],
                'title'           => $title,
                'description'     => $desc,
                'amount'          => $isFixed && $amount ? (float)$amount : null,
                'is_fixed_amount' => (bool)$isFixed,
                'max_uses'        => $maxUses ?: null,
                'expires_at'      => $expires ?: null,
            ]);

            flash('success', 'Payment link created successfully!');
            redirect('dashboard/payment-links');

        case 'dashboard/payment-links/deactivate':
            require_auth();
            PaymentLink::deactivate($_POST['id'] ?? '', $_SESSION['user_id']);
            flash('success', 'Payment link deactivated.');
            redirect('dashboard/payment-links');

        case 'dashboard/payment-links/delete':
            require_auth();
            PaymentLink::delete($_POST['id'] ?? '', $_SESSION['user_id']);
            flash('success', 'Payment link deleted.');
            redirect('dashboard/payment-links');

        case 'dashboard/webhooks/create':
            require_auth();
            $url    = trim($_POST['url'] ?? '');
            $label  = trim($_POST['label'] ?? 'My Endpoint');
            $events = $_POST['events'] ?? [];
            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
                flash('error', 'A valid endpoint URL is required.');
                redirect('dashboard/webhooks');
            }
            if (empty($events)) {
                flash('error', 'Select at least one event to subscribe to.');
                redirect('dashboard/webhooks');
            }
            $allowedEvents = ['payment.completed','payment.failed','payment.pending','withdrawal.created','withdrawal.done'];
            $events = array_values(array_intersect($events, $allowedEvents));
            Webhook::create([
                'user_id' => $_SESSION['user_id'],
                'label'   => $label,
                'url'     => $url,
                'events'  => $events,
                'secret'  => bin2hex(random_bytes(16)),
            ]);
            flash('success', 'Webhook endpoint added. Click the history icon to view the signing secret.');
            redirect('dashboard/webhooks');

        case 'dashboard/webhooks/toggle':
            require_auth();
            Webhook::toggle($_POST['id'] ?? '', $_SESSION['user_id']);
            redirect('dashboard/webhooks');

        case 'dashboard/webhooks/delete':
            require_auth();
            Webhook::delete($_POST['id'] ?? '', $_SESSION['user_id']);
            flash('success', 'Webhook endpoint deleted.');
            redirect('dashboard/webhooks');

        case 'dashboard/webhooks/regen-secret':
            require_auth();
            $wh = Webhook::find($_POST['id'] ?? '');
            if ($wh && $wh['user_id'] === $_SESSION['user_id']) {
                Webhook::regenerateSecret($wh['id'], $_SESSION['user_id']);
                flash('success', 'Signing secret regenerated.');
            }
            redirect('dashboard/webhooks?webhook=' . ($_POST['id'] ?? ''));

        case 'dashboard/api-keys/create':
            require_auth();
            $label = trim($_POST['label'] ?? '');
            $env   = $_POST['environment'] ?? 'test';
            if (!$label) { flash('error', 'Label is required.'); redirect('dashboard/api-keys'); }
            $key = ApiKey::generate($_SESSION['user_id'], $label, $env);
            flash('success', 'API Key generated: ' . $key['key'] . ' — Copy it now, it won\'t be shown again!');
            redirect('dashboard/api-keys');

        case 'dashboard/api-keys/revoke':
            require_auth();
            ApiKey::revoke($_POST['id'] ?? '', $_SESSION['user_id']);
            flash('success', 'API key revoked.');
            redirect('dashboard/api-keys');

        case 'dashboard/api-keys/delete':
            require_auth();
            ApiKey::delete($_POST['id'] ?? '', $_SESSION['user_id']);
            flash('success', 'API key deleted.');
            redirect('dashboard/api-keys');

        case 'dashboard/settings/profile':
            require_auth();
            User::updateProfile($_SESSION['user_id'], [
                'business_name' => trim($_POST['business_name'] ?? ''),
                'phone'         => trim($_POST['phone'] ?? ''),
            ]);
            $_SESSION['user']['business_name'] = trim($_POST['business_name'] ?? '');
            flash('success', 'Profile updated successfully.');
            redirect('dashboard/settings');

        case 'dashboard/wallet/withdraw':
            require_auth();
            $amount  = (float)($_POST['amount'] ?? 0);
            $channel = $_POST['channel'] ?? 'mpesa';
            $dest    = trim($_POST['destination'] ?? '');

            if ($amount < 100) { flash('error', 'Minimum withdrawal is KES 100.'); redirect('dashboard/wallet'); }
            if (!$dest) { flash('error', 'Destination is required.'); redirect('dashboard/wallet'); }

            $ok = Wallet::debit($_SESSION['user_id'], $amount, "Withdrawal to $channel: $dest");
            if (!$ok) { flash('error', 'Insufficient wallet balance.'); redirect('dashboard/wallet'); }

            $wdRef = generate_reference('WD');
            DB::insert(
                "INSERT INTO withdrawals (id,user_id,amount,channel,destination,status,reference) VALUES (UUID(),?,?,?,?,'pending',?)",
                [$_SESSION['user_id'], $amount, $channel, $dest, $wdRef]
            );
            $newWd = DB::fetch("SELECT * FROM withdrawals WHERE reference = ?", [$wdRef]);
            if ($newWd) {
                Mailer::withdrawalInitiated(auth_user(), $newWd);
                Notification::create(
                    $_SESSION['user_id'], 'withdrawal',
                    'Withdrawal Submitted',
                    format_amount($amount) . ' withdrawal to ' . $dest . ' is being processed.',
                    '/dashboard/wallet'
                );
            }

            flash('success', 'Withdrawal initiated. Funds will arrive shortly.');
            redirect('dashboard/wallet');

        case 'dashboard/settings/webhooks/add':
            require_auth();
            $url    = trim($_POST['url'] ?? '');
            $events = $_POST['events'] ?? [];
            if (!filter_var($url, FILTER_VALIDATE_URL) || empty($events)) {
                flash('error', 'Valid URL and at least one event required.');
                redirect('dashboard/settings#webhooks');
            }
            DB::insert(
                "INSERT INTO webhooks (id,user_id,url,events,secret,is_active) VALUES (UUID(),?,?,?,?,1)",
                [$_SESSION['user_id'], $url, json_encode($events), bin2hex(random_bytes(16))]
            );
            flash('success', 'Webhook added.');
            redirect('dashboard/settings');

        case 'dashboard/settings/webhooks/delete':
            require_auth();
            DB::query("DELETE FROM webhooks WHERE id = ? AND user_id = ?", [$_POST['id'] ?? '', $_SESSION['user_id']]);
            flash('success', 'Webhook deleted.');
            redirect('dashboard/settings');

        // ---- Admin POST handlers ----

        case 'admin/login':
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            if (!$email || !$password) {
                flash('error', 'Email and password are required.');
                redirect('admin/login');
            }
            $admin = Admin::findByEmail($email);
            if (!$admin || !Admin::verifyPassword($password, $admin['password'])) {
                flash('error', 'Invalid administrator credentials.');
                redirect('admin/login');
            }
            if (!$admin['is_active']) {
                flash('error', 'This admin account has been deactivated.');
                redirect('admin/login');
            }
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin']    = ['id'=>$admin['id'],'name'=>$admin['name'],'email'=>$admin['email'],'role'=>$admin['role']];
            Admin::updateLastLogin($admin['id']);
            Admin::log($admin['id'], 'login', '', '', '', $_SERVER['REMOTE_ADDR'] ?? null);
            redirect('admin/dashboard');

        case 'admin/logout':
            unset($_SESSION['admin_id'], $_SESSION['admin']);
            redirect('admin/login');

        case 'admin/merchants/suspend':
            AdminAuthMiddleware::handle();
            $id = $_POST['id'] ?? '';
            DB::query("UPDATE users SET status='suspended' WHERE id=?", [$id]);
            Admin::log($_SESSION['admin_id'], 'suspend_merchant', 'user', $id);
            flash('success', 'Merchant suspended.');
            redirect('admin/merchants');

        case 'admin/merchants/activate':
            AdminAuthMiddleware::handle();
            $id = $_POST['id'] ?? '';
            DB::query("UPDATE users SET status='active' WHERE id=?", [$id]);
            Admin::log($_SESSION['admin_id'], 'activate_merchant', 'user', $id);
            flash('success', 'Merchant activated.');
            redirect('admin/merchants');

        case 'admin/merchants/kyc-approve':
            AdminAuthMiddleware::handle();
            $id     = $_POST['id'] ?? '';
            $action = $_POST['action'] ?? 'approve';
            $notes  = trim($_POST['notes'] ?? '');
            $status = $action === 'approve' ? 'verified' : 'rejected';
            DB::query("UPDATE users SET kyc_status=? WHERE id=?", [$status, $id]);
            DB::query("UPDATE kyc_documents SET status=?,review_notes=?,reviewed_by=? WHERE user_id=? AND status='pending'",
                [$action === 'approve' ? 'approved' : 'rejected', $notes, $_SESSION['admin_id'], $id]);
            $kycMerchant = DB::fetch("SELECT * FROM users WHERE id=?", [$id]);
            if ($kycMerchant) {
                if ($action === 'approve') {
                    Mailer::kycApproved($kycMerchant);
                    Notification::create($id, 'kyc', 'KYC Verified!', 'Your identity has been verified. You now have full access to live payments.', '/dashboard/kyc');
                } else {
                    Mailer::kycRejected($kycMerchant, $notes ?: 'Document could not be verified.');
                    Notification::create($id, 'kyc', 'KYC Document Rejected', 'One or more documents could not be approved. Please re-upload corrected documents.', '/dashboard/kyc');
                }
            }
            Admin::log($_SESSION['admin_id'], "kyc_{$action}", 'user', $id, $notes);
            flash('success', 'KYC status updated.');
            redirect('admin/merchants/' . $id);

        case 'admin/merchants/wallet-credit':
            AdminAuthMiddleware::handle();
            $merchantId = $_POST['merchant_id'] ?? '';
            $amount     = (float)($_POST['amount'] ?? 0);
            $reason     = trim($_POST['reason'] ?? 'Admin credit');
            if ($amount < 1) { flash('error', 'Amount must be at least 1.'); redirect('admin/merchants/' . $merchantId); }
            Wallet::credit($merchantId, $amount, $reason);
            Admin::log($_SESSION['admin_id'], 'wallet_credit', 'user', $merchantId, "KES $amount — $reason");
            flash('success', 'Wallet credited successfully.');
            redirect('admin/merchants/' . $merchantId);

        case 'admin/transactions/mark-complete':
            AdminAuthMiddleware::handle();
            $ref = $_POST['reference'] ?? '';
            DB::query("UPDATE transactions SET status='completed' WHERE reference=?", [$ref]);
            Admin::log($_SESSION['admin_id'], 'mark_transaction_complete', 'transaction', $ref);
            flash('success', 'Transaction marked as completed.');
            redirect('admin/transactions');

        case 'admin/transactions/mark-failed':
            AdminAuthMiddleware::handle();
            $ref = $_POST['reference'] ?? '';
            DB::query("UPDATE transactions SET status='failed' WHERE reference=?", [$ref]);
            Admin::log($_SESSION['admin_id'], 'mark_transaction_failed', 'transaction', $ref);
            flash('success', 'Transaction marked as failed.');
            redirect('admin/transactions');

        case 'admin/withdrawals/approve':
            AdminAuthMiddleware::handle();
            $id = $_POST['id'] ?? '';
            $wd = DB::fetch("SELECT * FROM withdrawals WHERE id=?", [$id]);
            DB::query("UPDATE withdrawals SET status='completed',updated_at=NOW() WHERE id=?", [$id]);
            if ($wd) {
                $merchant = DB::fetch("SELECT * FROM users WHERE id=?", [$wd['user_id']]);
                if ($merchant) Mailer::withdrawalProcessed($merchant, $wd, true);
                Notification::create(
                    $wd['user_id'], 'withdrawal',
                    'Withdrawal Approved',
                    format_amount((float)$wd['amount']) . ' has been sent to ' . ($wd['destination'] ?? 'your account') . '.',
                    '/dashboard/wallet'
                );
            }
            Admin::log($_SESSION['admin_id'], 'approve_withdrawal', 'withdrawal', $id);
            flash('success', 'Withdrawal approved and processed.');
            redirect('admin/withdrawals');

        case 'admin/withdrawals/reject':
            AdminAuthMiddleware::handle();
            $id = $_POST['id'] ?? '';
            $wd = DB::fetch("SELECT * FROM withdrawals WHERE id=?", [$id]);
            if ($wd) {
                DB::query("UPDATE withdrawals SET status='failed',updated_at=NOW() WHERE id=?", [$id]);
                Wallet::credit($wd['user_id'], $wd['amount'], 'Withdrawal rejected — funds returned');
                $merchant = DB::fetch("SELECT * FROM users WHERE id=?", [$wd['user_id']]);
                if ($merchant) Mailer::withdrawalProcessed($merchant, $wd, false);
                Notification::create(
                    $wd['user_id'], 'withdrawal',
                    'Withdrawal Rejected',
                    format_amount((float)$wd['amount']) . ' could not be processed. Funds have been returned to your wallet.',
                    '/dashboard/wallet'
                );
            }
            Admin::log($_SESSION['admin_id'], 'reject_withdrawal', 'withdrawal', $id);
            flash('success', 'Withdrawal rejected. Funds returned to merchant wallet.');
            redirect('admin/withdrawals');

        case 'admin/fees/update':
            AdminAuthMiddleware::handle();
            $fees = $_POST['fees'] ?? [];
            foreach ($fees as $channel => $f) {
                $channel = preg_replace('/[^a-z_]/', '', $channel);
                DB::query(
                    "UPDATE fee_config SET fee_type=?,flat_fee=?,percentage=?,min_fee=?,max_fee=?,is_active=? WHERE channel=?",
                    [
                        $f['fee_type'] ?? 'combined',
                        (float)($f['flat_fee'] ?? 0),
                        (float)($f['percentage'] ?? 0) / 100,
                        (float)($f['min_fee'] ?? 0),
                        isset($f['max_fee']) && $f['max_fee'] !== '' ? (float)$f['max_fee'] : null,
                        (int)($f['is_active'] ?? 1),
                        $channel,
                    ]
                );
            }
            Admin::log($_SESSION['admin_id'], 'update_fees');
            flash('success', 'Fee configuration saved successfully.');
            redirect('admin/fees');

        case 'admin/settings/update':
            AdminAuthMiddleware::handle();
            $settings = $_POST['settings'] ?? [];
            foreach ($settings as $key => $value) {
                $key = preg_replace('/[^a-z_]/', '', $key);
                DB::query("INSERT INTO system_settings (key,value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=?", [$key, $value, $value]);
            }
            Admin::log($_SESSION['admin_id'], 'update_settings');
            flash('success', 'System settings saved.');
            redirect('admin/settings');

        case 'admin/kyc/review':
            AdminAuthMiddleware::handle();
            $docId  = $_POST['doc_id'] ?? '';
            $userId = $_POST['user_id'] ?? '';
            $action = $_POST['action'] ?? 'approve';
            $notes  = trim($_POST['notes'] ?? '');
            $docStatus = $action === 'approve' ? 'approved' : 'rejected';
            DB::query("UPDATE kyc_documents SET status=?,review_notes=?,reviewed_by=? WHERE id=?",
                [$docStatus, $notes, $_SESSION['admin_id'], $docId]);
            $kycDocUser = DB::fetch("SELECT * FROM users WHERE id=?", [$userId]);
            if ($action === 'approve') {
                $pending = DB::fetch("SELECT COUNT(*) as c FROM kyc_documents WHERE user_id=? AND status='pending'",$userId);
                if (($pending['c'] ?? 1) === 0) {
                    DB::query("UPDATE users SET kyc_status='verified' WHERE id=?", [$userId]);
                    if ($kycDocUser) {
                        Mailer::kycApproved($kycDocUser);
                        Notification::create($userId, 'kyc', 'KYC Verified!', 'Your identity has been verified. You now have full access to live payments.', '/dashboard/kyc');
                    }
                }
            } else {
                if ($kycDocUser) {
                    Mailer::kycRejected($kycDocUser, $notes ?: 'Document could not be verified.');
                    Notification::create($userId, 'kyc', 'KYC Document Rejected', $notes ?: 'A document could not be approved. Please re-upload a corrected version.', '/dashboard/kyc');
                }
            }
            Admin::log($_SESSION['admin_id'], "kyc_doc_{$action}", 'kyc_document', $docId, $notes);
            flash('success', 'Document ' . ($action === 'approve' ? 'approved' : 'rejected') . ' successfully.');
            redirect('admin/kyc');

        case 'apply-mpesa': {
            $appType   = in_array($_POST['application_type'] ?? '', ['till','paybill']) ? $_POST['application_type'] : 'till';
            $bizName   = trim($_POST['business_name']  ?? '');
            $bizType   = trim($_POST['business_type']  ?? '');
            $volume    = trim($_POST['monthly_volume'] ?? 'under_50k');
            $cName     = trim($_POST['contact_name']   ?? '');
            $cEmail    = trim($_POST['contact_email']  ?? '');
            $cPhone    = trim($_POST['contact_phone']  ?? '');
            $regNo     = trim($_POST['business_reg_no'] ?? '');
            $desc      = trim($_POST['description']    ?? '');

            if (!$bizName || !$bizType || !$cName || !filter_var($cEmail, FILTER_VALIDATE_EMAIL) || !$cPhone) {
                flash('mpesa_error', 'Please fill in all required fields correctly.');
                redirect('apply-mpesa');
            }

            $userId = is_logged_in() ? $_SESSION['user_id'] : null;
            if ($userId && MpesaAccount::findByUserId($userId)) {
                flash('mpesa_error', 'You already have an active application. Visit your dashboard to check its status.');
                redirect('apply-mpesa');
            }

            MpesaAccount::create([
                'user_id'          => $userId,
                'application_type' => $appType,
                'business_name'    => $bizName,
                'business_reg_no'  => $regNo ?: null,
                'contact_name'     => $cName,
                'contact_email'    => $cEmail,
                'contact_phone'    => $cPhone,
                'business_type'    => $bizType,
                'monthly_volume'   => $volume,
                'description'      => $desc ?: null,
            ]);

            if ($userId) {
                Notification::create($userId, 'kyc', 'M-Pesa Account Application Submitted', 'Your ' . $appType . ' application is under review. We\'ll notify you within 1-2 business days.', '/dashboard/mpesa-account');
            }
            $adminEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : MAIL_FROM;
            $typeLabel  = $appType === 'till' ? 'Till (Buy Goods)' : 'Paybill';
            Mailer::send($adminEmail, 'New M-Pesa ' . $typeLabel . ' Application — ' . $bizName,
                '<div style="font-family:Arial,sans-serif;max-width:520px;margin:0 auto;padding:24px">
                 <h2 style="color:#0D1B3E">New M-Pesa Account Application</h2>
                 <table style="width:100%;border-collapse:collapse;font-size:.9rem">
                 <tr><td style="padding:8px 12px;background:#f4f7fb;font-weight:700">Type</td><td style="padding:8px 12px;border:1px solid #dde2ec">' . $typeLabel . '</td></tr>
                 <tr><td style="padding:8px 12px;background:#f4f7fb;font-weight:700">Business</td><td style="padding:8px 12px;border:1px solid #dde2ec">' . htmlspecialchars($bizName) . '</td></tr>
                 <tr><td style="padding:8px 12px;background:#f4f7fb;font-weight:700">Contact</td><td style="padding:8px 12px;border:1px solid #dde2ec">' . htmlspecialchars($cName) . ' — ' . htmlspecialchars($cEmail) . ' / ' . htmlspecialchars($cPhone) . '</td></tr>
                 </table>
                 <p><a href="' . APP_URL . '/admin/mpesa-accounts" style="color:#158347">Review in Admin Panel →</a></p></div>'
            );

            flash('mpesa_success', 'Application submitted! We\'ll review it within 1–2 business days and notify you at ' . $cEmail . '.');
            redirect('apply-mpesa');
        }

        case 'dashboard/mpesa-account/apply':
            require_auth();
            $userId    = $_SESSION['user_id'];
            $appType   = in_array($_POST['application_type'] ?? '', ['till','paybill']) ? $_POST['application_type'] : 'till';
            $bizName   = trim($_POST['business_name']  ?? '');
            $bizType   = trim($_POST['business_type']  ?? '');
            $volume    = trim($_POST['monthly_volume'] ?? 'under_50k');
            $cPhone    = trim($_POST['contact_phone']  ?? '');
            $regNo     = trim($_POST['business_reg_no'] ?? '');
            $desc      = trim($_POST['description']    ?? '');
            $u         = auth_user();

            if (!$bizName || !$bizType || !$cPhone) {
                flash('error', 'Please fill in all required fields.');
                redirect('dashboard/mpesa-account');
            }

            $existing = MpesaAccount::findByUserId($userId);
            if ($existing && !in_array($existing['status'], ['rejected'])) {
                flash('error', 'You already have a pending or active application.');
                redirect('dashboard/mpesa-account');
            }

            MpesaAccount::create([
                'user_id'          => $userId,
                'application_type' => $appType,
                'business_name'    => $bizName,
                'business_reg_no'  => $regNo ?: null,
                'contact_name'     => $u['business_name'] ?? $bizName,
                'contact_email'    => $u['email'],
                'contact_phone'    => $cPhone,
                'business_type'    => $bizType,
                'monthly_volume'   => $volume,
                'description'      => $desc ?: null,
            ]);

            Notification::create($userId, 'kyc', 'M-Pesa Account Application Submitted',
                'Your ' . $appType . ' application is under review. We\'ll notify you within 1–2 business days.',
                '/dashboard/mpesa-account');
            $adminEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : MAIL_FROM;
            Mailer::send($adminEmail, 'New M-Pesa Account Application — ' . $bizName,
                '<div style="font-family:Arial,sans-serif;padding:24px"><h2>New M-Pesa Application</h2>'
                . '<p>Type: <strong>' . ucfirst($appType) . '</strong></p>'
                . '<p>Business: <strong>' . htmlspecialchars($bizName) . '</strong></p>'
                . '<p>Merchant: ' . htmlspecialchars($u['email']) . '</p>'
                . '<p><a href="' . APP_URL . '/admin/mpesa-accounts" style="color:#158347">Review →</a></p></div>');

            flash('success', 'Application submitted! We\'ll notify you at ' . ($u['email'] ?? '') . ' within 1–2 business days.');
            redirect('dashboard/mpesa-account');

        case 'dashboard/mpesa-account/simulate-c2b':
            require_auth();
            $userId  = $_SESSION['user_id'];
            $account = MpesaAccount::findByUserId($userId);
            if (!$account || $account['status'] !== 'approved') {
                flash('error', 'You do not have an approved M-Pesa account.');
                redirect('dashboard/mpesa-account');
            }
            $phone  = trim($_POST['phone'] ?? '');
            $amount = (float)($_POST['amount'] ?? 0);
            $sender = trim($_POST['sender_name'] ?? 'CUSTOMER');
            $accRef = trim($_POST['account_ref'] ?? '');

            if (!$phone || !preg_match('/^(07|01|254)\d+$/', $phone)) {
                flash('error', 'Valid phone number is required.');
                redirect('dashboard/mpesa-account');
            }
            if ($amount < 1) {
                flash('error', 'Amount must be at least KES 1.');
                redirect('dashboard/mpesa-account');
            }
            if (strlen($phone) === 10 && str_starts_with($phone, '0')) $phone = '254' . substr($phone, 1);

            $ref   = generate_reference('C2B');
            $meta  = ['till_number' => $account['account_number'], 'sender_name' => $sender, 'account_ref' => $accRef, 'simulated' => true];
            $txnId = Transaction::create([
                'user_id'     => $userId,
                'amount'      => $amount,
                'currency'    => 'KES',
                'channel'     => 'mpesa_c2b',
                'phone'       => $phone,
                'description' => ($account['application_type'] === 'till' ? 'Till' : 'Paybill') . ' payment from ' . $sender,
                'metadata'    => $meta,
            ]);
            $txn = DB::fetch("SELECT * FROM transactions WHERE id = ?", [$txnId]);
            Transaction::updateStatus($txn['reference'], 'completed');
            Wallet::credit($userId, $amount, 'C2B payment from ' . $phone . ' via ' . ($account['application_type'] === 'till' ? 'Till' : 'Paybill') . ' ' . $account['account_number']);
            Notification::create($userId, 'payment', 'C2B Payment Received',
                format_amount($amount) . ' received from ' . $phone . ' via ' . ucfirst($account['application_type']) . ' ' . $account['account_number'] . '.',
                '/dashboard/mpesa-account');
            $completedTxn = Transaction::findByRef($txn['reference']);
            if ($completedTxn) {
                WebhookDispatcher::dispatch($userId, 'payment.completed', WebhookDispatcher::buildPayload($completedTxn), $txn['reference']);
            }

            flash('success', 'Simulated: ' . format_amount($amount) . ' from ' . $phone . ' credited to your wallet.');
            redirect('dashboard/mpesa-account');

        case 'admin/mpesa-accounts/approve':
            AdminAuthMiddleware::handle();
            $id     = $_POST['id'] ?? '';
            $number = trim($_POST['account_number'] ?? '');
            if (!$id || !$number) {
                flash('error', 'Account number is required.');
                redirect('admin/mpesa-accounts');
            }
            $acct = MpesaAccount::find($id);
            if (!$acct) { flash('error', 'Application not found.'); redirect('admin/mpesa-accounts'); }
            MpesaAccount::approve($id, $number, $_SESSION['admin_id']);
            if ($acct['user_id']) {
                $merchant = DB::fetch("SELECT * FROM users WHERE id=?", [$acct['user_id']]);
                if ($merchant) {
                    $freshAcct = MpesaAccount::find($id);
                    Mailer::mpesaApproved($merchant, $freshAcct);
                    Notification::create($acct['user_id'], 'payment', 'M-Pesa Account Approved!',
                        'Your ' . ucfirst($acct['application_type']) . ' number ' . $number . ' is now active. Customers can start paying you.',
                        '/dashboard/mpesa-account');
                }
            }
            Admin::log($_SESSION['admin_id'], 'mpesa_account_approve', 'mpesa_account', $id, 'Number: ' . $number);
            flash('success', 'Application approved and number ' . $number . ' assigned.');
            redirect('admin/mpesa-accounts');

        case 'admin/mpesa-accounts/reject':
            AdminAuthMiddleware::handle();
            $id    = $_POST['id'] ?? '';
            $notes = trim($_POST['admin_notes'] ?? '');
            if (!$id || !$notes) {
                flash('error', 'Rejection reason is required.');
                redirect('admin/mpesa-accounts');
            }
            $acct = MpesaAccount::find($id);
            if (!$acct) { flash('error', 'Application not found.'); redirect('admin/mpesa-accounts'); }
            MpesaAccount::reject($id, $notes, $_SESSION['admin_id']);
            if ($acct['user_id']) {
                $merchant = DB::fetch("SELECT * FROM users WHERE id=?", [$acct['user_id']]);
                if ($merchant) {
                    Mailer::mpesaRejected($merchant, $acct, $notes);
                    Notification::create($acct['user_id'], 'kyc', 'M-Pesa Account Application Rejected',
                        'Your application could not be approved. Please visit your dashboard for details.',
                        '/dashboard/mpesa-account');
                }
            }
            Admin::log($_SESSION['admin_id'], 'mpesa_account_reject', 'mpesa_account', $id, $notes);
            flash('success', 'Application rejected.');
            redirect('admin/mpesa-accounts');

        case 'admin/mpesa-accounts/under-review':
            AdminAuthMiddleware::handle();
            $id   = $_POST['id'] ?? '';
            $acct = MpesaAccount::find($id);
            if (!$acct) { flash('error', 'Application not found.'); redirect('admin/mpesa-accounts'); }
            MpesaAccount::setUnderReview($id, $_SESSION['admin_id']);
            if ($acct['user_id']) {
                Notification::create($acct['user_id'], 'kyc', 'M-Pesa Application Under Review',
                    'Your ' . ucfirst($acct['application_type']) . ' application is now being actively reviewed by our team.',
                    '/dashboard/mpesa-account');
            }
            Admin::log($_SESSION['admin_id'], 'mpesa_account_under_review', 'mpesa_account', $id);
            flash('success', 'Application marked as under review.');
            redirect('admin/mpesa-accounts');

        case 'admin/weekly-summary':
            AdminAuthMiddleware::handle();
            $merchants = DB::fetchAll("SELECT * FROM users WHERE status='active'");
            $sent = 0;
            foreach ($merchants as $m) {
                $stats = DB::fetch(
                    "SELECT
                        COUNT(*) as txn_count,
                        COALESCE(SUM(CASE WHEN status='completed' THEN amount ELSE 0 END), 0) as total_received,
                        ROUND(SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) * 100.0 / GREATEST(COUNT(*),1), 1) as success_rate,
                        (SELECT COALESCE(balance,0) FROM wallets WHERE user_id=?) as wallet_balance,
                        (SELECT channel FROM transactions WHERE user_id=? AND created_at >= DATE_SUB(NOW(),INTERVAL 7 DAY) GROUP BY channel ORDER BY COUNT(*) DESC LIMIT 1) as top_channel
                     FROM transactions WHERE user_id=? AND created_at >= DATE_SUB(NOW(),INTERVAL 7 DAY)",
                    [$m['id'], $m['id'], $m['id']]
                );
                if ($stats) {
                    Mailer::weeklySummary($m, $stats);
                    $sent++;
                }
            }
            Admin::log($_SESSION['admin_id'], 'send_weekly_summary', '', '', "Sent to $sent merchants");
            flash('success', "Weekly summary sent to $sent active merchants.");
            redirect('admin/settings');

        // ─── Admin Wallet POST handlers ───────────────────────────────────

        case 'admin/wallet-users/suspend':
            AdminAuthMiddleware::handle();
            $id = $_POST['id'] ?? '';
            DB::query("UPDATE wallet_users SET status='suspended', updated_at=NOW() WHERE id=?", [$id]);
            Admin::log($_SESSION['admin_id'], 'suspend_wallet_user', 'wallet_user', $id);
            flash('success', 'Wallet user suspended.');
            redirect('admin/wallet-users/' . $id);

        case 'admin/wallet-users/activate':
            AdminAuthMiddleware::handle();
            $id = $_POST['id'] ?? '';
            DB::query("UPDATE wallet_users SET status='active', updated_at=NOW() WHERE id=?", [$id]);
            Admin::log($_SESSION['admin_id'], 'activate_wallet_user', 'wallet_user', $id);
            flash('success', 'Wallet user activated.');
            redirect('admin/wallet-users/' . $id);

        case 'admin/wallet-users/adjust-balance':
            AdminAuthMiddleware::handle();
            $id       = $_POST['id'] ?? '';
            $adjType  = in_array($_POST['type'] ?? '', ['credit', 'debit']) ? $_POST['type'] : 'credit';
            $adjAmt   = round((float)($_POST['amount'] ?? 0), 2);
            $adjNote  = trim($_POST['reason'] ?? 'Admin adjustment');
            $wuAdj    = WalletUser::find($id);
            if (!$wuAdj) { flash('error', 'User not found.'); redirect('admin/wallet-users'); }
            if ($adjAmt < 1) { flash('error', 'Amount must be at least KES 1.'); redirect('admin/wallet-users/' . $id); }
            $balBefore = (float)$wuAdj['balance'];
            if ($adjType === 'debit') {
                if ($balBefore < $adjAmt) { flash('error', 'Insufficient balance.'); redirect('admin/wallet-users/' . $id); }
                WalletUser::debit($id, $adjAmt);
                $balAfter = $balBefore - $adjAmt;
            } else {
                WalletUser::credit($id, $adjAmt);
                $balAfter = $balBefore + $adjAmt;
            }
            WalletTransaction::create([
                'reference'         => 'ADM' . strtoupper(bin2hex(random_bytes(5))),
                'wallet_user_id'    => $id,
                'type'              => $adjType === 'credit' ? 'deposit' : 'withdrawal',
                'amount'            => $adjAmt,
                'fee'               => 0.00,
                'balance_before'    => $balBefore,
                'balance_after'     => $balAfter,
                'counterparty'      => 'Admin',
                'counterparty_name' => 'OrbitPesa Admin',
                'description'       => 'Admin ' . $adjType . ': ' . $adjNote,
                'status'            => 'completed',
            ]);
            Admin::log($_SESSION['admin_id'], 'wallet_balance_adjust', 'wallet_user', $id, "KES $adjAmt $adjType — $adjNote");
            flash('success', ucfirst($adjType) . 'ed KES ' . number_format($adjAmt, 2) . ' successfully.');
            redirect('admin/wallet-users/' . $id);

        // ─── Consumer Wallet POST handlers ────────────────────────────────

        case 'wallet/register': {
            $name    = trim($_POST['full_name'] ?? '');
            $email   = trim($_POST['email'] ?? '');
            $phone   = trim(preg_replace('/\s+/', '', $_POST['phone'] ?? ''));
            $natId   = trim($_POST['national_id'] ?? '');
            $pass    = $_POST['password'] ?? '';
            $passC   = $_POST['password_confirm'] ?? '';
            $pin     = $_POST['pin'] ?? '';
            $pinC    = $_POST['pin_confirm'] ?? '';

            // Normalise phone: 07xx → 2547xx
            if (preg_match('/^(07|01)\d{8}$/', $phone)) {
                $phone = '254' . substr($phone, 1);
            }

            $errs = [];
            if (strlen($name) < 3) $errs[] = 'Full name must be at least 3 characters.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = 'Valid email address is required.';
            if (!preg_match('/^2547\d{8}$|^2541\d{8}$/', $phone)) $errs[] = 'Valid Kenyan phone number is required (e.g. 0712345678).';
            if (strlen($pass) < 8) $errs[] = 'Password must be at least 8 characters.';
            if ($pass !== $passC) $errs[] = 'Passwords do not match.';
            if (!preg_match('/^\d{4}$/', $pin)) $errs[] = 'PIN must be exactly 4 digits.';
            if ($pin !== $pinC) $errs[] = 'PINs do not match.';
            if (WalletUser::findByEmail($email)) $errs[] = 'An account with this email already exists.';
            if (WalletUser::findByPhone($phone)) $errs[] = 'An account with this phone number already exists.';

            if ($errs) { flash('wallet_error', implode(' ', $errs)); redirect('wallet/register'); }

            $refCode = strtoupper(trim($_POST['referral_code'] ?? ''));
            $refBy   = null;
            if ($refCode) {
                $refUser = WalletReferral::findByCode($refCode);
                if ($refUser && $refUser['id'] !== null) $refBy = $refUser['id'];
            }

            $newId = WalletUser::create([
                'full_name'   => $name,
                'email'       => $email,
                'phone'       => $phone,
                'national_id' => $natId ?: null,
                'password'    => $pass,
                'pin'         => $pin,
            ]);

            // Assign referral code to new user
            WalletReferral::assignCode($newId);

            // Store referred_by and create referral record
            if ($refBy) {
                DB::query("UPDATE wallet_users SET referred_by = ? WHERE id = ?", [$refBy, $newId]);
                WalletReferral::create($refBy, $newId);
            }

            // Welcome deposit (sandbox)
            WalletUser::credit($newId, 1000.00);
            $newUser = WalletUser::find($newId);
            WalletTransaction::create([
                'reference'        => 'WEL' . strtoupper(bin2hex(random_bytes(5))),
                'wallet_user_id'   => $newId,
                'type'             => 'deposit',
                'amount'           => 1000.00,
                'fee'              => 0.00,
                'balance_before'   => 0.00,
                'balance_after'    => 1000.00,
                'counterparty'     => 'OrbitPesa',
                'counterparty_name'=> 'OrbitPesa Welcome Bonus',
                'description'      => 'Welcome deposit — sandbox demo wallet',
                'status'           => 'completed',
            ]);

            $_SESSION['wallet_uid']  = $newId;
            $_SESSION['wallet_user'] = ['id' => $newId, 'wallet_id' => $newUser['wallet_id'], 'full_name' => $newUser['full_name'], 'email' => $newUser['email']];
            redirect('wallet/home');
        }

        case 'wallet/login': {
            $ident = trim($_POST['identifier'] ?? '');
            $pass  = $_POST['password'] ?? '';
            if (!$ident || !$pass) { flash('wallet_error', 'Enter your email/phone/wallet ID and password.'); redirect('wallet/login'); }
            $wu = WalletUser::findByIdentifier($ident);
            if (!$wu || !WalletUser::verifyPassword($pass, $wu['password'])) {
                flash('wallet_error', 'Invalid credentials. Please try again.');
                redirect('wallet/login');
            }
            if ($wu['status'] === 'suspended') { flash('wallet_error', 'Account suspended. Contact support.'); redirect('wallet/login'); }
            $_SESSION['wallet_uid']  = $wu['id'];
            $_SESSION['wallet_user'] = ['id' => $wu['id'], 'wallet_id' => $wu['wallet_id'], 'full_name' => $wu['full_name'], 'email' => $wu['email']];
            redirect('wallet/home');
        }

        case 'wallet/send': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            $senderId    = $_SESSION['wallet_uid'];
            $recipientId = trim($_POST['recipient_id'] ?? '');
            $amount      = round((float)($_POST['amount'] ?? 0), 2);
            $pin         = $_POST['pin'] ?? '';
            $note        = trim($_POST['note'] ?? '');

            if (!$recipientId || $amount < 10) { flash('wallet_error', 'Select a recipient and enter at least KES 10.'); redirect('wallet/send'); }
            if ($recipientId === $senderId) { flash('wallet_error', 'You cannot send money to yourself.'); redirect('wallet/send'); }

            $sender    = WalletUser::find($senderId);
            $recipient = WalletUser::find($recipientId);

            if (!$recipient || $recipient['status'] !== 'active') { flash('wallet_error', 'Recipient not found or inactive.'); redirect('wallet/send'); }
            if (!WalletUser::verifyPin($pin, $sender['pin_hash'])) { flash('wallet_error', 'Incorrect PIN.'); redirect('wallet/send'); }
            if ((float)$sender['balance'] < $amount) { flash('wallet_error', 'Insufficient balance.'); redirect('wallet/send'); }

            $ref        = 'WS' . strtoupper(bin2hex(random_bytes(6)));
            $sndBefore  = (float)$sender['balance'];
            $rcpBefore  = (float)$recipient['balance'];

            WalletUser::debit($senderId, $amount);
            WalletTransaction::create([
                'reference'        => $ref . 'S',
                'wallet_user_id'   => $senderId,
                'type'             => 'send',
                'amount'           => $amount,
                'fee'              => 0.00,
                'balance_before'   => $sndBefore,
                'balance_after'    => $sndBefore - $amount,
                'counterparty'     => $recipient['wallet_id'],
                'counterparty_name'=> $recipient['full_name'],
                'description'      => $note ?: 'Transfer to ' . $recipient['full_name'],
                'status'           => 'completed',
            ]);

            WalletUser::credit($recipientId, $amount);
            WalletTransaction::create([
                'reference'        => $ref . 'R',
                'wallet_user_id'   => $recipientId,
                'type'             => 'receive',
                'amount'           => $amount,
                'fee'              => 0.00,
                'balance_before'   => $rcpBefore,
                'balance_after'    => $rcpBefore + $amount,
                'counterparty'     => $sender['wallet_id'],
                'counterparty_name'=> $sender['full_name'],
                'description'      => $note ?: 'From ' . $sender['full_name'],
                'status'           => 'completed',
            ]);

            WalletNotification::create($recipientId, 'payment',
                'You received KES ' . number_format($amount, 2),
                $sender['full_name'] . ' sent you KES ' . number_format($amount, 2) . ($note ? ' — ' . $note : '') . '.',
                '/wallet/transactions'
            );
            WalletReferral::checkAndComplete($senderId);
            flash('wallet_success', 'KES ' . number_format($amount, 2) . ' sent to ' . $recipient['full_name'] . ' successfully.');
            redirect('wallet/send');
        }

        case 'wallet/airtime': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            $userId  = $_SESSION['wallet_uid'];
            $phone   = trim($_POST['phone'] ?? '');
            $amount  = round((float)($_POST['amount'] ?? 0), 2);
            $network = in_array($_POST['network'] ?? '', ['safaricom','airtel','telkom','faiba']) ? $_POST['network'] : 'safaricom';
            $type    = ($_POST['type'] ?? 'airtime') === 'data' ? 'data' : 'airtime';
            $pin     = $_POST['pin'] ?? '';

            if (!$phone || $amount < 5 || $amount > 10000) { flash('wallet_error', 'Enter a valid phone and amount (KES 5–10,000).'); redirect('wallet/airtime'); }
            $wu = WalletUser::find($userId);
            if (!WalletUser::verifyPin($pin, $wu['pin_hash'])) { flash('wallet_error', 'Incorrect PIN.'); redirect('wallet/airtime'); }
            if ((float)$wu['balance'] < $amount) { flash('wallet_error', 'Insufficient balance.'); redirect('wallet/airtime'); }

            $balBefore = (float)$wu['balance'];
            WalletUser::debit($userId, $amount);
            WalletTransaction::create([
                'reference'        => strtoupper($type === 'data' ? 'DAT' : 'AIR') . strtoupper(bin2hex(random_bytes(5))),
                'wallet_user_id'   => $userId,
                'type'             => $type,
                'amount'           => $amount,
                'fee'              => 0.00,
                'balance_before'   => $balBefore,
                'balance_after'    => $balBefore - $amount,
                'counterparty'     => $phone,
                'counterparty_name'=> ucfirst($network),
                'description'      => 'KES ' . number_format($amount) . ' ' . ($type === 'data' ? 'data' : 'airtime') . ' for ' . $phone . ' (' . ucfirst($network) . ')',
                'status'           => 'completed',
            ]);
            WalletNotification::create($userId, 'purchase',
                ($type === 'data' ? 'Data bundle' : 'Airtime') . ' purchased',
                'KES ' . number_format($amount) . ' ' . ($type === 'data' ? 'data bundle' : 'airtime') . ' sent to ' . $phone . ' (' . ucfirst($network) . ').',
                '/wallet/transactions'
            );
            WalletReferral::checkAndComplete($userId);
            flash('wallet_success', 'KES ' . number_format($amount) . ' ' . ($type === 'data' ? 'data bundle' : 'airtime') . ' sent to ' . $phone . '.');
            redirect('wallet/airtime');
        }

        case 'wallet/paybill': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            $userId  = $_SESSION['wallet_uid'];
            $paybill = trim($_POST['paybill_number'] ?? '');
            $account = trim($_POST['account_number'] ?? '');
            $amount  = round((float)($_POST['amount'] ?? 0), 2);
            $pin     = $_POST['pin'] ?? '';

            if (!$paybill || !$account || $amount < 1) { flash('wallet_error', 'Please fill in all payment details.'); redirect('wallet/paybill'); }
            $wu = WalletUser::find($userId);
            if (!WalletUser::verifyPin($pin, $wu['pin_hash'])) { flash('wallet_error', 'Incorrect PIN.'); redirect('wallet/paybill'); }
            if ((float)$wu['balance'] < $amount) { flash('wallet_error', 'Insufficient balance.'); redirect('wallet/paybill'); }

            $balBefore = (float)$wu['balance'];
            WalletUser::debit($userId, $amount);
            WalletTransaction::create([
                'reference'        => 'PB' . strtoupper(bin2hex(random_bytes(6))),
                'wallet_user_id'   => $userId,
                'type'             => 'paybill',
                'amount'           => $amount,
                'fee'              => 0.00,
                'balance_before'   => $balBefore,
                'balance_after'    => $balBefore - $amount,
                'counterparty'     => $paybill,
                'counterparty_name'=> 'Paybill ' . $paybill,
                'description'      => 'Paybill ' . $paybill . ' Acc: ' . $account,
                'status'           => 'completed',
            ]);
            WalletNotification::create($userId, 'purchase',
                'Paybill payment sent',
                'KES ' . number_format($amount, 2) . ' paid to Paybill ' . $paybill . ', account ' . $account . '.',
                '/wallet/transactions'
            );
            WalletReferral::checkAndComplete($userId);
            flash('wallet_success', 'KES ' . number_format($amount, 2) . ' paid to Paybill ' . $paybill . ' (Acc: ' . $account . ').');
            redirect('wallet/paybill');
        }

        case 'wallet/transfer': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            $userId  = $_SESSION['wallet_uid'];
            $txType  = in_array($_POST['transfer_type'] ?? '', ['mpesa','bank']) ? $_POST['transfer_type'] : 'mpesa';
            $dest    = trim($_POST['destination'] ?? '');
            $amount  = round((float)($_POST['amount'] ?? 0), 2);
            $pin     = $_POST['pin'] ?? '';
            $fee     = $txType === 'bank' ? 50.00 : 25.00;
            $total   = $amount + $fee;

            if (!$dest || $amount < 100) { flash('wallet_error', 'Enter a valid destination and amount (min KES 100).'); redirect('wallet/transfer'); }
            $wu = WalletUser::find($userId);
            if (!WalletUser::verifyPin($pin, $wu['pin_hash'])) { flash('wallet_error', 'Incorrect PIN.'); redirect('wallet/transfer'); }
            if ((float)$wu['balance'] < $total) { flash('wallet_error', 'Insufficient balance (amount + fee = KES ' . number_format($total, 2) . ').'); redirect('wallet/transfer'); }

            $balBefore = (float)$wu['balance'];
            WalletUser::debit($userId, $total);
            $txnType = $txType === 'bank' ? 'bank_transfer' : 'mpesa_out';
            $label   = $txType === 'bank' ? 'Bank Transfer' : 'M-Pesa';
            WalletTransaction::create([
                'reference'        => strtoupper($txType === 'bank' ? 'BNK' : 'MPS') . strtoupper(bin2hex(random_bytes(5))),
                'wallet_user_id'   => $userId,
                'type'             => $txnType,
                'amount'           => $amount,
                'fee'              => $fee,
                'balance_before'   => $balBefore,
                'balance_after'    => $balBefore - $total,
                'counterparty'     => $dest,
                'counterparty_name'=> $label . ' — ' . $dest,
                'description'      => $label . ' to ' . $dest,
                'status'           => 'completed',
            ]);
            WalletNotification::create($userId, 'transfer',
                $label . ' transfer confirmed',
                'KES ' . number_format($amount, 2) . ' sent to ' . $dest . '. Fee: KES ' . number_format($fee, 2) . '.',
                '/wallet/transactions'
            );
            WalletReferral::checkAndComplete($userId);
            flash('wallet_success', 'KES ' . number_format($amount, 2) . ' sent via ' . $label . ' to ' . $dest . ' (fee: KES ' . number_format($fee, 2) . ').');
            redirect('wallet/transfer');
        }

        case 'wallet/deposit': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            $userId = $_SESSION['wallet_uid'];
            $amount = min(100000, max(1, round((float)($_POST['amount'] ?? 1000), 2)));
            $wu     = WalletUser::find($userId);
            $before = (float)$wu['balance'];
            WalletUser::credit($userId, $amount);
            WalletTransaction::create([
                'reference'        => 'DEP' . strtoupper(bin2hex(random_bytes(5))),
                'wallet_user_id'   => $userId,
                'type'             => 'deposit',
                'amount'           => $amount,
                'fee'              => 0.00,
                'balance_before'   => $before,
                'balance_after'    => $before + $amount,
                'counterparty'     => 'OrbitPesa Demo',
                'counterparty_name'=> 'OrbitPesa Demo',
                'description'      => 'Test deposit (sandbox)',
                'status'           => 'completed',
            ]);
            flash('wallet_success', 'KES ' . number_format($amount, 2) . ' added to your wallet (demo).');
            redirect('wallet/home');
        }

        case 'wallet/profile/update': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            $userId = $_SESSION['wallet_uid'];
            $name   = trim($_POST['full_name'] ?? '');
            $email  = trim($_POST['email'] ?? '');
            $phone  = trim($_POST['phone'] ?? '');
            $errs   = [];
            if (strlen($name) < 3) $errs[] = 'Full name must be at least 3 characters.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = 'Valid email is required.';
            $existing = WalletUser::findByEmail($email);
            if ($existing && $existing['id'] !== $userId) $errs[] = 'Email already in use by another account.';
            if ($errs) { flash('wallet_error', implode(' ', $errs)); redirect('wallet/profile'); }
            WalletUser::updateProfile($userId, $name, $email, $phone);
            $_SESSION['wallet_user']['full_name'] = $name;
            $_SESSION['wallet_user']['email']     = $email;
            flash('wallet_success', 'Profile updated successfully.');
            redirect('wallet/profile');
        }

        case 'wallet/profile/change-pin': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            $userId = $_SESSION['wallet_uid'];
            $curPin = $_POST['current_pin'] ?? '';
            $newPin = $_POST['new_pin'] ?? '';
            $conPin = $_POST['confirm_pin'] ?? '';
            $wu     = WalletUser::find($userId);
            if (!WalletUser::verifyPin($curPin, $wu['pin_hash'])) { flash('wallet_error', 'Current PIN is incorrect.'); redirect('wallet/profile'); }
            if (!preg_match('/^\d{4}$/', $newPin)) { flash('wallet_error', 'New PIN must be exactly 4 digits.'); redirect('wallet/profile'); }
            if ($newPin !== $conPin) { flash('wallet_error', 'New PINs do not match.'); redirect('wallet/profile'); }
            WalletUser::updatePin($userId, $newPin);
            WalletNotification::create($userId, 'security',
                'PIN changed',
                'Your wallet PIN was changed successfully. If you did not do this, contact support immediately.',
                '/wallet/profile'
            );
            flash('wallet_success', 'PIN changed successfully.');
            redirect('wallet/profile');
        }

        case 'wallet/referral/generate': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            $userId = $_SESSION['wallet_uid'];
            $wu     = WalletUser::find($userId);
            if (empty($wu['referral_code'])) {
                WalletReferral::assignCode($userId);
            }
            redirect('wallet/profile');
        }

        case 'wallet/notifications/read': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            WalletNotification::markRead($_POST['id'] ?? '', $_SESSION['wallet_uid']);
            redirect('wallet/notifications');
        }

        case 'wallet/notifications/read-all': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            WalletNotification::markAllRead($_SESSION['wallet_uid']);
            redirect('wallet/notifications');
        }

        case 'wallet/pockets/create': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            $userId = $_SESSION['wallet_uid'];
            $name   = trim($_POST['name'] ?? '');
            $emoji  = trim($_POST['emoji'] ?? '💰');
            $target = (float)($_POST['target_amount'] ?? 0);

            if (strlen($name) < 2) { flash('wallet_error', 'Pocket name must be at least 2 characters.'); redirect('wallet/pockets'); }

            $existing = WalletPocket::findForUser($userId);
            if (count($existing) >= 10) { flash('wallet_error', 'You can have up to 10 pockets.'); redirect('wallet/pockets'); }

            WalletPocket::create([
                'wallet_user_id' => $userId,
                'name'           => $name,
                'emoji'          => mb_substr($emoji, 0, 5),
                'target_amount'  => $target > 0 ? $target : null,
            ]);
            flash('wallet_success', '"' . $name . '" pocket created!');
            redirect('wallet/pockets');
        }

        case 'wallet/pockets/deposit': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            $userId   = $_SESSION['wallet_uid'];
            $pocketId = trim($_POST['pocket_id'] ?? '');
            $amount   = round((float)($_POST['amount'] ?? 0), 2);
            $pin      = $_POST['pin'] ?? '';

            $pocket = WalletPocket::find($pocketId);
            if (!$pocket || $pocket['wallet_user_id'] !== $userId) { flash('wallet_error', 'Pocket not found.'); redirect('wallet/pockets'); }
            if ($amount < 1) { flash('wallet_error', 'Enter at least KES 1.'); redirect('wallet/pockets/' . $pocketId); }

            $wu = WalletUser::find($userId);
            if (!WalletUser::verifyPin($pin, $wu['pin_hash'])) { flash('wallet_error', 'Incorrect PIN.'); redirect('wallet/pockets/' . $pocketId); }
            if ((float)$wu['balance'] < $amount) { flash('wallet_error', 'Insufficient wallet balance.'); redirect('wallet/pockets/' . $pocketId); }

            $balBefore = (float)$wu['balance'];
            WalletUser::debit($userId, $amount);
            WalletPocket::deposit($pocketId, $amount);

            WalletTransaction::create([
                'reference'         => 'PKD' . strtoupper(bin2hex(random_bytes(5))),
                'wallet_user_id'    => $userId,
                'type'              => 'pocket_in',
                'amount'            => $amount,
                'fee'               => 0.00,
                'balance_before'    => $balBefore,
                'balance_after'     => $balBefore - $amount,
                'counterparty'      => $pocketId,
                'counterparty_name' => $pocket['name'] . ' pocket',
                'description'       => 'Saved to "' . $pocket['name'] . '" pocket',
                'status'            => 'completed',
            ]);
            flash('wallet_success', 'KES ' . number_format($amount, 2) . ' saved to "' . $pocket['name'] . '".');
            redirect('wallet/pockets/' . $pocketId);
        }

        case 'wallet/pockets/withdraw': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            $userId   = $_SESSION['wallet_uid'];
            $pocketId = trim($_POST['pocket_id'] ?? '');
            $amount   = round((float)($_POST['amount'] ?? 0), 2);
            $pin      = $_POST['pin'] ?? '';

            $pocket = WalletPocket::find($pocketId);
            if (!$pocket || $pocket['wallet_user_id'] !== $userId) { flash('wallet_error', 'Pocket not found.'); redirect('wallet/pockets'); }
            if ($amount < 1) { flash('wallet_error', 'Enter at least KES 1.'); redirect('wallet/pockets/' . $pocketId); }
            if ((float)$pocket['balance'] < $amount) { flash('wallet_error', 'Insufficient pocket balance.'); redirect('wallet/pockets/' . $pocketId); }

            $wu = WalletUser::find($userId);
            if (!WalletUser::verifyPin($pin, $wu['pin_hash'])) { flash('wallet_error', 'Incorrect PIN.'); redirect('wallet/pockets/' . $pocketId); }

            $balBefore = (float)$wu['balance'];
            WalletPocket::withdraw($pocketId, $amount);
            WalletUser::credit($userId, $amount);

            WalletTransaction::create([
                'reference'         => 'PKW' . strtoupper(bin2hex(random_bytes(5))),
                'wallet_user_id'    => $userId,
                'type'              => 'pocket_out',
                'amount'            => $amount,
                'fee'               => 0.00,
                'balance_before'    => $balBefore,
                'balance_after'     => $balBefore + $amount,
                'counterparty'      => $pocketId,
                'counterparty_name' => $pocket['name'] . ' pocket',
                'description'       => 'Withdrawn from "' . $pocket['name'] . '" pocket',
                'status'            => 'completed',
            ]);
            flash('wallet_success', 'KES ' . number_format($amount, 2) . ' returned from "' . $pocket['name'] . '" to your wallet.');
            redirect('wallet/pockets/' . $pocketId);
        }

        case 'wallet/pockets/delete': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            $userId   = $_SESSION['wallet_uid'];
            $pocketId = trim($_POST['pocket_id'] ?? '');

            $pocket = WalletPocket::find($pocketId);
            if (!$pocket || $pocket['wallet_user_id'] !== $userId) { flash('wallet_error', 'Pocket not found.'); redirect('wallet/pockets'); }

            $remaining = (float)$pocket['balance'];
            if ($remaining > 0) {
                $wu        = WalletUser::find($userId);
                $balBefore = (float)$wu['balance'];
                WalletUser::credit($userId, $remaining);
                WalletTransaction::create([
                    'reference'         => 'PKC' . strtoupper(bin2hex(random_bytes(5))),
                    'wallet_user_id'    => $userId,
                    'type'              => 'pocket_out',
                    'amount'            => $remaining,
                    'fee'               => 0.00,
                    'balance_before'    => $balBefore,
                    'balance_after'     => $balBefore + $remaining,
                    'counterparty'      => $pocketId,
                    'counterparty_name' => $pocket['name'] . ' pocket',
                    'description'       => '"' . $pocket['name'] . '" pocket closed — funds returned',
                    'status'            => 'completed',
                ]);
            }
            WalletPocket::delete($pocketId);
            flash('wallet_success', '"' . $pocket['name'] . '" pocket deleted' . ($remaining > 0 ? '. KES ' . number_format($remaining, 2) . ' returned to your wallet.' : '.'));
            redirect('wallet/pockets');
        }

        case 'wallet/pay-merchant': {
            if (empty($_SESSION['wallet_uid'])) redirect('wallet/login');
            $userId     = $_SESSION['wallet_uid'];
            $merchantId = trim($_POST['merchant_id'] ?? '');
            $amount     = round((float)($_POST['amount'] ?? 0), 2);
            $pin        = $_POST['pin'] ?? '';
            $note       = trim($_POST['note'] ?? '');

            if (!$merchantId) { flash('wallet_error', 'Please search for and select a business.'); redirect('wallet/pay-merchant'); }
            if ($amount < 1)  { flash('wallet_error', 'Enter a valid amount (min KES 1).'); redirect('wallet/pay-merchant'); }

            $merchant = DB::fetch("SELECT * FROM users WHERE id=? AND status='active'", [$merchantId]);
            if (!$merchant) { flash('wallet_error', 'Business not found or inactive.'); redirect('wallet/pay-merchant'); }

            $wu = WalletUser::find($userId);
            if (!WalletUser::verifyPin($pin, $wu['pin_hash'])) { flash('wallet_error', 'Incorrect PIN.'); redirect('wallet/pay-merchant'); }
            if ((float)$wu['balance'] < $amount) { flash('wallet_error', 'Insufficient balance.'); redirect('wallet/pay-merchant'); }

            $ref       = 'WP' . strtoupper(bin2hex(random_bytes(6)));
            $balBefore = (float)$wu['balance'];
            $balAfter  = $balBefore - $amount;
            $bizName   = $merchant['business_name'];
            $desc      = $note ?: 'Payment to ' . $bizName;

            WalletUser::debit($userId, $amount);

            WalletTransaction::create([
                'reference'         => $ref,
                'wallet_user_id'    => $userId,
                'type'              => 'payment',
                'amount'            => $amount,
                'fee'               => 0.00,
                'balance_before'    => $balBefore,
                'balance_after'     => $balAfter,
                'counterparty'      => $merchantId,
                'counterparty_name' => $bizName,
                'description'       => $desc,
                'status'            => 'completed',
            ]);

            Wallet::credit($merchantId, $amount, 'OrbitPesa Wallet — ' . $ref);

            $txnId = Transaction::create([
                'user_id'     => $merchantId,
                'amount'      => $amount,
                'currency'    => 'KES',
                'channel'     => 'wallet',
                'description' => $desc,
                'metadata'    => [
                    'source'          => 'wallet_app',
                    'payer_wallet_id' => $wu['wallet_id'],
                    'payer_name'      => $wu['full_name'],
                ],
            ]);
            $txn = DB::fetch("SELECT * FROM transactions WHERE id = ?", [$txnId]);
            Transaction::updateStatus($txn['reference'], 'completed');

            WebhookDispatcher::dispatch($merchantId, 'payment.completed',
                WebhookDispatcher::buildPayload(Transaction::findByRef($txn['reference'])),
                $txn['reference']
            );
            Notification::create($merchantId, 'payment', 'Wallet Payment Received',
                format_amount($amount) . ' received from ' . $wu['full_name'] . ' via OrbitPesa Wallet.',
                '/dashboard/transactions'
            );

            WalletNotification::create($userId, 'purchase',
                'Payment to ' . $bizName,
                'KES ' . number_format($amount, 2) . ' paid to ' . $bizName . '. Ref: ' . $ref . '.',
                '/wallet/transactions'
            );
            WalletReferral::checkAndComplete($userId);
            flash('wallet_success', 'KES ' . number_format($amount, 2) . ' paid to ' . $bizName . '. Ref: ' . $ref);
            redirect('wallet/pay-merchant');
        }

        case 'admin/disputes/update':
            AdminAuthMiddleware::handle();
            $id     = $_POST['id'] ?? '';
            $status = $_POST['status'] ?? 'under_review';
            $notes  = trim($_POST['resolution_notes'] ?? '');
            $allowed = ['under_review','resolved','closed'];
            if (!in_array($status, $allowed)) { flash('error', 'Invalid status.'); redirect('admin/disputes'); }
            DB::query("UPDATE disputes SET status=?,resolution=?,updated_at=NOW() WHERE id=?", [$status, $notes, $id]);
            Admin::log($_SESSION['admin_id'], "dispute_{$status}", 'dispute', $id);
            flash('success', 'Dispute updated to ' . str_replace('_',' ',$status) . '.');
            redirect('admin/disputes');
    }
}

// =============================================
// GET ROUTES
// =============================================
if ($uri === 'wallet/logout') {
    unset($_SESSION['wallet_uid'], $_SESSION['wallet_user']);
    redirect('wallet/login');
}

if ($uri === 'logout') {
    session_destroy();
    redirect('login');
}

if ($uri === 'admin/logout') {
    unset($_SESSION['admin_id'], $_SESSION['admin']);
    redirect('admin/login');
}

if (!$route) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 — OrbitPesa</title></head><body style="font-family:Inter,sans-serif;text-align:center;padding:80px">
    <h1 style="font-size:4rem;color:#0D1B3E">404</h1><p style="color:#64748b">Page not found.</p>
    <a href="' . APP_URL . '/" style="color:#158347">← Back to home</a></body></html>';
    exit;
}

// Admin routes — redirect logged-in admin away from login
if ($route['handler'] === 'admin_login' && AdminAuthMiddleware::isLoggedIn()) {
    redirect('admin/dashboard');
}

// Protect all admin routes except login
if (!empty($route['admin'])) {
    AdminAuthMiddleware::handle();
    $adminUser = AdminAuthMiddleware::admin();
}

if ($route['auth']) require_auth();
if (!$route['auth'] && in_array($uri, ['login', 'register']) && is_logged_in()) redirect('dashboard');

function renderWallet(string $view, array $data = []): void {
    if (empty($_SESSION['wallet_uid'])) {
        flash('wallet_error', 'Please log in to continue.');
        redirect('wallet/login');
    }
    $walletUser = WalletUser::find($_SESSION['wallet_uid']);
    if (!$walletUser || $walletUser['status'] === 'suspended') {
        unset($_SESSION['wallet_uid'], $_SESSION['wallet_user']);
        flash('wallet_error', 'Session expired. Please log in again.');
        redirect('wallet/login');
    }
    $data['walletUser'] = $walletUser;
    extract($data);
    $content = function() use ($view, $data, $walletUser) {
        extract($data);
        require BASE_PATH . '/views/wallet/' . $view . '.php';
    };
    require BASE_PATH . '/views/layouts/wallet.php';
    exit;
}

function renderAdmin(string $view, array $data = []): void {
    global $adminUser;
    extract($data);
    $activeAdmin = $data['activeNav'] ?? '';
    $content = function() use ($view, $data) {
        extract($data);
        require BASE_PATH . '/views/admin/' . $view . '.php';
    };
    require BASE_PATH . '/views/admin/layouts/admin.php';
}

$layout = $route['layout'];
switch ($route['handler']) {
    case 'landing':
        require BASE_PATH . '/views/landing/index.php';
        break;
    case 'contact':
        redirect('/#contact');
    case 'login':
        require BASE_PATH . '/views/auth/login.php';
        break;
    case 'register':
        require BASE_PATH . '/views/auth/register.php';
        break;
    case 'dashboard':
        renderView('dashboard/index',        ['pageTitle' => 'Overview',       'activeNav' => 'dashboard'],   'app');
        break;
    case 'transactions':
        renderView('dashboard/transactions', ['pageTitle' => 'Transactions',   'activeNav' => 'transactions'], 'app');
        break;
    case 'paylinks':
        renderView('dashboard/payment-links',['pageTitle' => 'Payment Links',  'activeNav' => 'payment-links'],'app');
        break;
    case 'wallet':
        renderView('dashboard/wallet',       ['pageTitle' => 'Wallet',         'activeNav' => 'wallet'],      'app');
        break;
    case 'apikeys':
        renderView('dashboard/api-keys',     ['pageTitle' => 'API Keys',       'activeNav' => 'api-keys'],    'app');
        break;
    case 'settings':
        renderView('dashboard/settings',     ['pageTitle' => 'Settings',       'activeNav' => 'settings'],    'app');
        break;
    case 'mpesa':
        renderView('dashboard/mpesa',        ['pageTitle' => 'M-Pesa Push',    'activeNav' => 'mpesa'],       'app');
        break;
    case 'card':
        renderView('dashboard/card',         ['pageTitle' => 'Card Payment',   'activeNav' => 'card'],        'app');
        break;
    case 'walletpay':
        renderView('dashboard/wallet-pay',   ['pageTitle' => 'Wallet Pay',     'activeNav' => 'wallet-pay'],  'app');
        break;
    case 'analytics':
        renderView('dashboard/analytics',    ['pageTitle' => 'Analytics',      'activeNav' => 'analytics'],   'app');
        break;
    case 'webhooks':
        renderView('dashboard/webhooks',     ['pageTitle' => 'Webhooks',       'activeNav' => 'webhooks'],    'app');
        break;
    case 'kyc':
        renderView('dashboard/kyc',          ['pageTitle' => 'KYC Verification','activeNav' => 'kyc'],        'app');
        break;
    case 'notifications':
        renderView('dashboard/notifications',['pageTitle' => 'Notifications',   'activeNav' => 'notifications'],'app');
        break;
    case 'mpesa_account':
        renderView('dashboard/mpesa-account',['pageTitle' => 'Business Account','activeNav' => 'mpesa-account'],'app');
        break;
    case 'apply_mpesa':
        require BASE_PATH . '/views/landing/apply-mpesa.php';
        break;

    // Consumer wallet GET handlers
    case 'wallet_landing':
        require BASE_PATH . '/views/wallet/landing.php';
        break;
    case 'wallet_register':
        if (!empty($_SESSION['wallet_uid'])) redirect('wallet/home');
        require BASE_PATH . '/views/wallet/register.php';
        break;
    case 'wallet_login':
        if (!empty($_SESSION['wallet_uid'])) redirect('wallet/home');
        require BASE_PATH . '/views/wallet/login.php';
        break;
    case 'wallet_home':
        renderWallet('home', ['pageTitle' => 'Home', 'activeWalletNav' => 'home']);
        break;
    case 'wallet_send':
        renderWallet('send', ['pageTitle' => 'Send Money', 'activeWalletNav' => 'send']);
        break;
    case 'wallet_receive':
        renderWallet('receive', ['pageTitle' => 'Receive Money', 'activeWalletNav' => 'receive']);
        break;
    case 'wallet_airtime':
        renderWallet('airtime', ['pageTitle' => ($_GET['type'] ?? '') === 'data' ? 'Buy Data Bundle' : 'Buy Airtime', 'activeWalletNav' => '']);
        break;
    case 'wallet_paybill':
        renderWallet('paybill', ['pageTitle' => 'Pay Paybill', 'activeWalletNav' => '']);
        break;
    case 'wallet_transfer':
        renderWallet('transfer', ['pageTitle' => 'Transfer Money', 'activeWalletNav' => '']);
        break;
    case 'wallet_txns':
        renderWallet('transactions', ['pageTitle' => 'Transaction History', 'activeWalletNav' => 'history']);
        break;
    case 'wallet_profile':
        renderWallet('profile', ['pageTitle' => 'My Profile', 'activeWalletNav' => 'profile']);
        break;
    case 'wallet_pay_merchant':
        renderWallet('pay-merchant', ['pageTitle' => 'Pay Business', 'activeWalletNav' => '']);
        break;
    case 'wallet_pockets':
        renderWallet('pockets', ['pageTitle' => 'Savings Pockets', 'activeWalletNav' => 'pockets']);
        break;
    case 'wallet_scan':
        if (!empty($_GET['wid'])) {
            redirect('wallet/send?wid=' . urlencode($_GET['wid']));
        }
        renderWallet('scan', ['pageTitle' => 'Scan QR Code', 'activeWalletNav' => '']);
        break;
    case 'wallet_notifications':
        renderWallet('notifications', ['pageTitle' => 'Notifications', 'activeWalletNav' => 'notifications']);
        break;
    case 'devhome':
        require BASE_PATH . '/views/developers/index.php';
        break;
    case 'devdocs':
        require BASE_PATH . '/views/developers/docs.php';
        break;

    // Admin GET handlers
    case 'admin_login':
        require BASE_PATH . '/views/admin/auth/login.php';
        break;
    case 'admin_logout':
        unset($_SESSION['admin_id'], $_SESSION['admin']);
        redirect('admin/login');
        break;
    case 'admin_dash':
        renderAdmin('dashboard/index', ['pageTitle' => 'Dashboard', 'activeNav' => 'dashboard']);
        break;
    case 'admin_merchants':
        renderAdmin('merchants/index', ['pageTitle' => 'Merchants', 'activeNav' => 'merchants']);
        break;
    case 'admin_txns':
        renderAdmin('transactions/index', ['pageTitle' => 'Transactions', 'activeNav' => 'transactions']);
        break;
    case 'admin_wds':
        renderAdmin('withdrawals/index', ['pageTitle' => 'Withdrawals', 'activeNav' => 'withdrawals']);
        break;
    case 'admin_fees':
        renderAdmin('fees/index', ['pageTitle' => 'Fee Config', 'activeNav' => 'fees']);
        break;
    case 'admin_settings':
        renderAdmin('settings/index', ['pageTitle' => 'System Settings', 'activeNav' => 'settings']);
        break;
    case 'admin_kyc':
        renderAdmin('kyc/index', ['pageTitle' => 'KYC Queue', 'activeNav' => 'kyc']);
        break;
    case 'admin_disputes':
        renderAdmin('disputes/index', ['pageTitle' => 'Disputes', 'activeNav' => 'disputes']);
        break;
    case 'admin_logs':
        renderAdmin('logs/index', ['pageTitle' => 'Activity Logs', 'activeNav' => 'logs']);
        break;
    case 'admin_mpesa':
        renderAdmin('mpesa-accounts/index', ['pageTitle' => 'M-Pesa Accounts', 'activeNav' => 'mpesa-accounts']);
        break;
    case 'admin_weekly':
        redirect('admin/settings');
        break;
    case 'admin_wallet_users':
        renderAdmin('wallet-users/index', ['pageTitle' => 'Wallet Users', 'activeNav' => 'wallet-users']);
        break;
    case 'admin_wallet_txns':
        renderAdmin('wallet-transactions/index', ['pageTitle' => 'Wallet Transactions', 'activeNav' => 'wallet-transactions']);
        break;
}
