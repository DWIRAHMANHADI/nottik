<?php
// Endpoint AJAX: admin/problem-login-detail.php
if (isset($_GET['user_id'], $_GET['date'], $_GET['username'])) {
    require_once '../includes/config.php';
    require_once '../includes/database.php';
    $userId = (int)$_GET['user_id'];
    $date = $_GET['date'];
    $db = Database::getInstance();
    $username = $_GET['username'];
    $logins = $db->fetchAll(
        "SELECT event_time FROM pppoe_logs WHERE event_type = 'login' AND event_date = ? AND user_id = ? AND username = ? ORDER BY event_time",
        [$date, $userId, $username]
    );
    $result = array_map(function($l) { return date('H:i', strtotime($l['event_time'])); }, $logins);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
// Jika tidak ada parameter, tampilkan halaman HTML detail yang cantik
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Login User Bermasalah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg p-8 max-w-md w-full mt-10">
        <h2 class="text-2xl font-bold mb-4 text-red-600 flex items-center"><i class="fas fa-user-clock mr-2"></i>Detail Login User Bermasalah</h2>
        <div class="mb-4 text-sm text-gray-600">Halaman ini hanya dapat diakses melalui tombol detail di statistik admin.</div>
        <a href="statistik.php" class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition"><i class="fas fa-arrow-left mr-1"></i>Kembali ke Statistik</a>
    </div>
</body>
</html>
