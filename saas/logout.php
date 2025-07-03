<?php
// logout.php - Proses logout untuk sistem SaaS
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/Auth.php';

// Inisialisasi Auth
$auth = new Auth();

// Logout pengguna
$auth->logout();

// Logout admin (jika ada)
$auth->adminLogout();

// Redirect ke halaman login
header("Location: login.php");
exit;
?>
