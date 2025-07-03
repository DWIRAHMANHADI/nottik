<?php
// config.php

// Pastikan konstanta hanya didefinisikan sekali
if (!defined('SECRET_TOKEN')) {
    define('SECRET_TOKEN', 'rahasia123');
}

if (!defined('WHATSAPP_API_URL')) {
    define('WHATSAPP_API_URL', 'https://send.simpan.id');
}

// URL endpoint untuk pengiriman pesan default
if (!defined('WHATSAPP_SEND_MESSAGE_URL')) {
    define('WHATSAPP_SEND_MESSAGE_URL', rtrim(WHATSAPP_API_URL, '/') . '/send/message');
}
// jika whatsapp AUTHENTICATION
if (!defined('WHATSAPP_API_USER')) {
    define('WHATSAPP_API_USER', 'admin');
}

if (!defined('WHATSAPP_API_PASS')) {
    define('WHATSAPP_API_PASS', 'Saputra@110509');
}
// penutup AUTHENTICATION
if (!defined('WHATSAPP_GROUP_ID')) {
    define('WHATSAPP_GROUP_ID', '120363408186330281@g.us');
}

if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', 'mikrotik_logs');
}

if (!defined('DB_USER')) {
    define('DB_USER', 'mikrotik_logs');
}

if (!defined('DB_PASS')) {
    define('DB_PASS', 'Saputra@110509'); // Ganti sesuai password MySQL kamu
}
// Admin credentials untuk login panel
if (!defined('ADMIN_USER')) {
    define('ADMIN_USER', 'admin'); // Ganti sesuai kebutuhan
}
if (!defined('ADMIN_PASS')) {
    define('ADMIN_PASS', 'password123'); // Ganti sesuai kebutuhan
}
?>
