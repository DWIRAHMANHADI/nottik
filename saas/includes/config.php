<?php
// includes/config.php - Konfigurasi utama untuk sistem SaaS

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'wa_notification_saas');
define('DB_USER', 'wa_notification_saas'); // Sesuaikan dengan user database Anda
define('DB_PASS', 'Saputra@110509'); // Sesuaikan dengan password database Anda

// WhatsApp API configuration (default, akan diambil dari database)
define('DEFAULT_WHATSAPP_API_URL', 'https://send.simpan.id');
define('DEFAULT_WHATSAPP_API_USER', 'admin');
define('DEFAULT_WHATSAPP_API_PASS', 'Saputra@110509');

// Application settings
define('APP_NAME', 'WhatsApp Notification Panel SaaS');
define('APP_URL', 'https://logs.simpan.id/saas/');
define('APP_VERSION', '1.0.0');

// Session settings
define('SESSION_NAME', 'wa_notification_saas');
define('SESSION_LIFETIME', 86400); // 24 jam dalam detik

// OTP settings
define('OTP_LENGTH', 6);
define('OTP_EXPIRY', 300); // 5 menit dalam detik

// Security settings
define('HASH_COST', 10); // Cost untuk bcrypt

// Path settings
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('USER_PATH', ROOT_PATH . '/user');
define('AUTH_PATH', ROOT_PATH . '/auth');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Autoload function untuk class
spl_autoload_register(function ($class_name) {
    $class_file = INCLUDES_PATH . '/' . $class_name . '.php';
    if (file_exists($class_file)) {
        require_once $class_file;
    }
});
