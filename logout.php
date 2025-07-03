<?php
// logout.php - Proses logout untuk sistem SaaS
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/Auth.php';

// Inisialisasi Auth
$auth = new Auth();

// Ambil user_id sebelum logout
$userId = $_SESSION['user_id'] ?? null;

// Logout pengguna
$auth->logout();

// Logging logout user setelah logout
if ($userId) {
    $db = Database::getInstance();
    $db->insert('notif_log', [
        'user_id' => $userId,
        'type' => 'logout',
        'status' => 'success',
        'message' => 'Logout user'
    ]);
}
// Logout pengguna
$auth->logout();

// Logout admin (jika ada)
$auth->adminLogout();

// Redirect ke halaman login
header("Location: login.php");
exit;
?>
