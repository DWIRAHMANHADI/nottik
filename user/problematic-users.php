<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/Auth.php';

// Cek login user
$auth = new Auth();
$userData = $auth->isLoggedIn();
if (!$userData) {
    header('Location: ../login.php');
    exit;
}

$userId = $userData['user_id'];
$username = isset($userData['username']) ? $userData['username'] : (isset($userData['phone']) ? $userData['phone'] : 'User');

// Ambil 7 hari terakhir
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
}

// Cek hari bermasalah untuk user ini (login >= 5)
$problemDays = [];
foreach ($dates as $d) {
    $loginCount = (int)Database::getInstance()->fetchColumn(
        "SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ? AND user_id = ?",
        [$d, $userId]
    );
    if ($loginCount >= 5) {
        $problemDays[] = $d;
    }
}

?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hari Bermasalah (Login Berlebihan) - (NotMiK) Notification Mikrotik</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-green-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <span class="font-bold text-xl">(NotMiK) Notification Mikrotik</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="hover:underline">Dashboard</a>
                <a href="statistik.php" class="hover:underline">Statistik</a>
                <a href="problematic-users.php" class="hover:underline font-bold border-b-2 border-white">Hari Bermasalah</a>
                <a href="../logout.php" class="hover:underline"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Hari Bermasalah User: <?php echo htmlspecialchars($username); ?></h2>
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <?php if (empty($problemDays)): ?>
                <div class="text-green-700 font-semibold">Tidak ada hari bermasalah (login ≥ 5) dalam 7 hari terakhir.</div>
            <?php else: ?>
                <div class="mb-4 p-3 rounded bg-red-100 border border-red-300 text-red-800 flex items-start gap-2">
                    <i class="fas fa-exclamation-triangle mt-0.5"></i>
                    <div>
                        <div class="font-bold mb-1">Peringatan: Ditemukan hari dengan login berlebihan</div>
                        <div class="text-xs">Tanggal:
                            <span class="font-semibold">
                                <?php echo implode(', ', array_map(function($tgl) { return date('d M Y', strtotime($tgl)); }, $problemDays)); ?>
                            </span>
                        </div>
                        <div class="text-xs mt-1">Login ≥ 5 pada hari tersebut. Silakan cek aktivitas Anda lebih lanjut.</div>
                    </div>
                </div>
                <?php foreach ($problemDays as $d): ?>
                    <?php
                    $logins = Database::getInstance()->fetchAll(
                        "SELECT event_time FROM pppoe_logs WHERE event_type = 'login' AND event_date = ? AND user_id = ? ORDER BY event_time",
                        [$d, $userId]
                    );
                    ?>
                    <div class="mb-2 mt-2">
                        <div class="font-semibold text-red-700 mb-1 text-xs">Detail Login pada <?php echo date('d M Y', strtotime($d)); ?>:</div>
                        <table class="min-w-[220px] text-xs border rounded overflow-hidden mb-2"><thead class="bg-gray-50"><tr>
                            <th class="py-1 px-2 text-left">Jam</th>
                            <th class="py-1 px-2 text-left">Username</th>
                        </tr></thead><tbody>
                        <?php foreach ($logins as $l): ?>
                            <tr>
                                <td class="py-1 px-2"><?php echo date('H:i', strtotime($l['event_time'])); ?></td>
                                <td class="py-1 px-2"><?php echo htmlspecialchars($username); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody></table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <a href="index.php" class="text-green-700 hover:underline"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
