<?php
define('APP_NAME', 'OrbitPesa');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/OrbitPesa');
define('APP_ENV', 'development'); // 'production' in live

define('SESSION_NAME', 'orbitpesa_session');
define('SESSION_LIFETIME', 7200); // 2 hours

define('MPESA_CONSUMER_KEY', '');
define('MPESA_CONSUMER_SECRET', '');
define('MPESA_SHORTCODE', '');
define('MPESA_PASSKEY', '');
define('MPESA_CALLBACK_URL', APP_URL . '/api/v1/mpesa/callback');
define('MPESA_ENV', 'sandbox'); // 'production'

define('STRIPE_PUBLIC_KEY', '');
define('STRIPE_SECRET_KEY', '');

define('API_RATE_LIMIT', 100); // requests per minute
define('WEBHOOK_SECRET', 'orbitpesa_wh_secret_change_in_production');

define('MAIL_FROM', 'noreply@orbitpesa.com');
define('MAIL_FROM_NAME', 'OrbitPesa');
define('MAIL_ENABLED', true);
define('ADMIN_EMAIL', 'admin@orbitpesa.com');

date_default_timezone_set('Africa/Nairobi');
error_reporting(APP_ENV === 'development' ? E_ALL : 0);
ini_set('display_errors', APP_ENV === 'development' ? 1 : 0);
