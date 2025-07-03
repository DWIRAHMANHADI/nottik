<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
// dashboard.php
require_once 'config.php';
require_once 'database.php';

$today = date('Y-m-d');
$thisMonth = date('Y-m');

// Statistik harian
$stmtLogin = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ?");
$stmtLogin->execute([$today]);
$loginToday = $stmtLogin->fetchColumn();

$stmtLogout = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ?");
$stmtLogout->execute([$today]);
$logoutToday = $stmtLogout->fetchColumn();

$stmtUser = $pdo->prepare("SELECT COUNT(DISTINCT username) FROM pppoe_logs WHERE event_date = ?");
$stmtUser->execute([$today]);
$userToday = $stmtUser->fetchColumn();

// Statistik bulanan
$stmtLoginMonth = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date LIKE ?");
$stmtLoginMonth->execute(["$thisMonth%"]);
$loginMonth = $stmtLoginMonth->fetchColumn();

$stmtLogoutMonth = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date LIKE ?");
$stmtLogoutMonth->execute(["$thisMonth%"]);
$logoutMonth = $stmtLogoutMonth->fetchColumn();

// Data grafik 7 hari terakhir
$labels = [];
$loginData = [];
$logoutData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d M', strtotime($date));
    $stmtL = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ?");
    $stmtL->execute([$date]);
    $loginData[] = (int)$stmtL->fetchColumn();
    $stmtO = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ?");
    $stmtO->execute([$date]);
    $logoutData[] = (int)$stmtO->fetchColumn();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Statistik PPPoE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <nav class="flex flex-col md:flex-row md:justify-between md:items-center mb-8 gap-2 md:gap-0">
            <div class="font-bold text-xl text-indigo-700">Dashboard Statistik PPPoE</div>
            <div class="flex gap-2">
                <a href="index.php" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 transition">Data Detail</a>
                <a href="statistik.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">Statistik Lengkap</a>
            </div>
        </nav>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                <span class="text-5xl font-bold text-green-600"><?php echo $loginToday; ?></span>
                <span class="mt-2 text-lg text-gray-700">Login Hari Ini</span>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                <span class="text-5xl font-bold text-red-600"><?php echo $logoutToday; ?></span>
                <span class="mt-2 text-lg text-gray-700">Logout Hari Ini</span>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                <span class="text-5xl font-bold text-blue-600"><?php echo $userToday; ?></span>
                <span class="mt-2 text-lg text-gray-700">User Unik Hari Ini</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                <span class="text-4xl font-bold text-green-500"><?php echo $loginMonth; ?></span>
                <span class="mt-2 text-md text-gray-700">Total Login Bulan Ini</span>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                <span class="text-4xl font-bold text-red-500"><?php echo $logoutMonth; ?></span>
                <span class="mt-2 text-md text-gray-700">Total Logout Bulan Ini</span>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-center">Grafik Login & Logout 7 Hari Terakhir</h2>
            <canvas id="statChart" height="100"></canvas>
        </div>
        <div class="mt-10 text-center">
            <a href="index.php" class="inline-block bg-indigo-600 text-white px-6 py-2 rounded shadow hover:bg-indigo-700 transition">Lihat Data Detail</a>
        </div>
    </div>
    <script>
        const ctx = document.getElementById('statChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [
                    {
                        label: 'Login',
                        data: <?php echo json_encode($loginData); ?>,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: 'Logout',
                        data: <?php echo json_encode($logoutData); ?>,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        tension: 0.4,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: false }
                }
            }
        });
    </script>
</body>
</html>
