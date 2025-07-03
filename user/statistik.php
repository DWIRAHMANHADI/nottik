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

// Sensor statistik jika status pending/suspended
if ($userData['status'] === 'pending' || $userData['status'] === 'suspended') {
    ?><!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Statistik Login/Logout - (NotMiK) Notification Mikrotik</title>
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
                    <a href="statistik.php" class="hover:underline font-bold border-b-2 border-white">Statistik</a>
                    <a href="../logout.php" class="hover:underline"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
                </div>
            </div>
        </nav>
        <div class="container mx-auto px-4 py-20 flex flex-col items-center justify-center min-h-[60vh]">
            <div class="bg-white border-l-4 border-yellow-400 shadow rounded-lg p-10 max-w-xl w-full text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-yellow-400 mb-4"></i>
                <h2 class="text-2xl font-bold mb-2">Akses Statistik Belum Tersedia</h2>
                <?php if ($userData['status'] === 'pending'): ?>
                    <p class="mb-2">Akun Anda masih <span class="font-bold uppercase">PENDING</span>. Statistik akan muncul setelah akun diaktivasi oleh admin.</p>
                <?php else: ?>
                    <p class="mb-2">Akun Anda sedang <span class="font-bold uppercase">SUSPEND</span>. Silakan hubungi admin untuk aktivasi ulang.</p>
                <?php endif; ?>
                <a href="index.php" class="mt-6 inline-block px-6 py-2 rounded bg-green-600 text-white font-semibold shadow hover:bg-green-700 transition">Kembali ke Dashboard</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$userId = $userData['user_id'];
$today = date('Y-m-d');
$thisMonth = date('Y-m');

// Grafik Login/Logout Hari Ini (per jam, user ini)
$hourLabels = [];
$loginHourData = [];
$logoutHourData = [];
for ($h = 0; $h < 24; $h++) {
    $hour = str_pad($h, 2, '0', STR_PAD_LEFT);
    $hourLabels[] = $hour . ':00';
    $loginHourData[] = (int)Database::getInstance()->fetchColumn(
        "SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ? AND HOUR(event_time) = ? AND user_id = ?",
        [$today, $h, $userId]
    );
    $logoutHourData[] = (int)Database::getInstance()->fetchColumn(
        "SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ? AND HOUR(event_time) = ? AND user_id = ?",
        [$today, $h, $userId]
    );
}
// Persentase Hari Ini vs Kemarin (user ini)
$yesterday = date('Y-m-d', strtotime('-1 day'));
$loginTodayTotal = array_sum($loginHourData);
$logoutTodayTotal = array_sum($logoutHourData);
$loginYesterday = (int)Database::getInstance()->fetchColumn(
    "SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ? AND user_id = ?",
    [$yesterday, $userId]
);
$logoutYesterday = (int)Database::getInstance()->fetchColumn(
    "SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ? AND user_id = ?",
    [$yesterday, $userId]
);
$loginTodayPct = $loginYesterday ? (($loginTodayTotal - $loginYesterday) / $loginYesterday) * 100 : ($loginTodayTotal ? 100 : 0);
$logoutTodayPct = $logoutYesterday ? (($logoutTodayTotal - $logoutYesterday) / $logoutYesterday) * 100 : ($logoutTodayTotal ? 100 : 0);

// Grafik Login/Logout 1 Bulan Terakhir (per hari, user ini)
$monthLabels = [];
$loginMonthData = [];
$logoutMonthData = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $monthLabels[] = date('d M', strtotime($date));
    $loginMonthData[] = (int)Database::getInstance()->fetchColumn(
        "SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ? AND user_id = ?",
        [$date, $userId]
    );
    $logoutMonthData[] = (int)Database::getInstance()->fetchColumn(
        "SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ? AND user_id = ?",
        [$date, $userId]
    );
}

?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Login/Logout - (Nottik) Notification Mikrotik</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-green-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <span class="font-bold text-xl">(Nottik) Notification Mikrotik</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="hover:underline">Dashboard</a>
                <a href="../logout.php" class="hover:underline"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-6">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Statistik Login & Logout</h2>

        <!-- Card 7 Hari Terakhir -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="font-semibold mb-4">Rekap 7 Hari Terakhir</h3>
            <?php
            // Cari hari dengan status "Ada kendala"
            $warningDates = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $login7 = (int)Database::getInstance()->fetchColumn(
                    "SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ? AND user_id = ?",
                    [$date, $userId]
                );
                if ($login7 >= 5) {
                    $warningDates[] = date('d M Y', strtotime($date));
                }
            }
            if (!empty($warningDates)):
            ?>
            <div class="mb-4 p-3 rounded bg-red-100 border border-red-300 text-red-800 flex items-start gap-2">
                <i class="fas fa-exclamation-triangle mt-0.5"></i>
                <div>
                    <div class="font-bold mb-1">Peringatan: Ada hari dengan status <span class='text-red-600'>"Ada kendala"</span></div>
                    <div class="text-xs">Tanggal:
                        <span class="font-semibold">
                        <?php echo implode(', ', $warningDates); ?>
                        </span>
                    </div>
                    <div class="text-xs mt-1">Login ≥ 5 pada hari tersebut. Silakan cek aktivitas user lebih lanjut.</div>
                    <a href="problematic-users-list.php" class="inline-block mt-2 ml-2 px-3 py-1 rounded bg-orange-600 text-white text-xs font-semibold hover:bg-orange-700 transition">
                        Lihat Semua User Bermasalah
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left">Tanggal</th>
                            <th class="py-2 px-4 text-left">Login</th>
                            <th class="py-2 px-4 text-left">Logout</th>
                            <th class="py-2 px-4 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 6; $i >= 0; $i--): ?>
                            <?php
                                $date = date('Y-m-d', strtotime("-$i days"));
                                $login7 = (int)Database::getInstance()->fetchColumn(
                                    "SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ? AND user_id = ?",
                                    [$date, $userId]
                                );
                                $logout7 = (int)Database::getInstance()->fetchColumn(
                                    "SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ? AND user_id = ?",
                                    [$date, $userId]
                                );
                            ?>
                            <tr>
                                <td class="py-2 px-4 font-semibold"><?php echo date('d M Y', strtotime($date)); ?></td>
                                <td class="py-2 px-4 text-green-700 font-bold"><?php echo $login7; ?></td>
                                <td class="py-2 px-4 text-red-700 font-bold"><?php echo $logout7; ?></td>
                                <td class="py-2 px-4">
                                    <?php
                                        if ($login7 <= 3) {
                                            $status = 'Aman'; $statusClass = 'bg-green-100 text-green-700';
                                        } elseif ($login7 == 4) {
                                            $status = 'Perlu dicek'; $statusClass = 'bg-yellow-100 text-yellow-700';
                                        } else {
                                            $status = 'Ada kendala'; $statusClass = 'bg-red-100 text-red-700';
                                        }
                                    ?>
                                    <span class="inline-block px-2 py-0.5 rounded font-semibold <?php echo $statusClass; ?>"><?php echo $status; ?></span>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold mb-2">Login/Logout Hari Ini (per Jam)</h3>
<canvas id="hourChart"></canvas>
<!-- Top 3 User Hari Ini -->
<?php
$top10Today = Database::getInstance()->fetchAll(
    "SELECT username, COUNT(*) as total FROM pppoe_logs WHERE event_type = 'login' AND event_date = ? GROUP BY username ORDER BY total DESC LIMIT 10",
    [$today]
);
?>
<div class="mt-4">
    <div class="font-semibold text-gray-700 mb-1 flex items-center"><i class="fas fa-trophy text-yellow-400 mr-2"></i>Top 10 User Login Hari Ini</div>
    <?php if (empty($top10Today)): ?>
        <div class="text-gray-400 text-sm">Belum ada login hari ini.</div>
    <?php else: ?>
    <div class="w-full">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <?php foreach ($top10Today as $i => $row): $rank = $i+1; ?>
        <div class="flex flex-col items-center bg-gray-50 rounded-lg px-3 py-2 shadow-sm border w-full <?php echo $rank==1?'border-yellow-400':($rank==2?'border-gray-400':($rank==3?'border-orange-400':'border-gray-200')); ?>">
            <div class="mb-1">
                <?php if ($rank == 1): ?><span class="text-2xl">🥇</span><?php elseif ($rank == 2): ?><span class="text-2xl">🥈</span><?php elseif ($rank == 3): ?><span class="text-2xl">🥉</span><?php else: ?><span class="text-xs font-bold text-gray-400">#<?php echo $rank; ?></span><?php endif; ?>
            </div>
            <div class="font-bold <?php echo $rank==1?'text-yellow-600':($rank==2?'text-gray-600':($rank==3?'text-orange-700':'text-gray-700')); ?> text-center break-words"><?php echo htmlspecialchars($row['username']); ?></div>
            <div class="text-xs bg-green-100 text-green-700 rounded px-2 py-0.5 font-bold mt-1"><?php echo $row['total']; ?> login</div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
    <?php endif; ?>
</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold mb-2">Login/Logout 30 Hari Terakhir (per Hari)</h3>
<canvas id="monthChart"></canvas>
<!-- Top 3 User 30 Hari Terakhir -->
<?php
$start30 = date('Y-m-d', strtotime('-29 days'));
$top10Month = Database::getInstance()->fetchAll(
    "SELECT username, COUNT(*) as total FROM pppoe_logs WHERE event_type = 'login' AND event_date BETWEEN ? AND ? GROUP BY username ORDER BY total DESC LIMIT 10",
    [$start30, $today]
);
?>
<div class="mt-4">
    <div class="font-semibold text-gray-700 mb-1 flex items-center"><i class="fas fa-crown text-yellow-500 mr-2"></i>Top 10 User Login 30 Hari</div>
    <?php if (empty($top10Month)): ?>
        <div class="text-gray-400 text-sm">Belum ada login 30 hari terakhir.</div>
    <?php else: ?>
    <div class="w-full">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <?php foreach ($top10Month as $i => $row): $rank = $i+1; ?>
        <div class="flex flex-col items-center bg-gray-50 rounded-lg px-3 py-2 shadow-sm border w-full <?php echo $rank==1?'border-yellow-400':($rank==2?'border-gray-400':($rank==3?'border-orange-400':'border-gray-200')); ?>">
            <div class="mb-1">
                <?php if ($rank == 1): ?><span class="text-2xl">🥇</span><?php elseif ($rank == 2): ?><span class="text-2xl">🥈</span><?php elseif ($rank == 3): ?><span class="text-2xl">🥉</span><?php else: ?><span class="text-xs font-bold text-gray-400">#<?php echo $rank; ?></span><?php endif; ?>
            </div>
            <div class="font-bold <?php echo $rank==1?'text-yellow-600':($rank==2?'text-gray-600':($rank==3?'text-orange-700':'text-gray-700')); ?> text-center break-words"><?php echo htmlspecialchars($row['username']); ?></div>
            <div class="text-xs bg-green-100 text-green-700 rounded px-2 py-0.5 font-bold mt-1"><?php echo $row['total']; ?> login</div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
    <?php endif; ?>
</div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="font-semibold mb-2">Rekap Hari Ini vs Kemarin</h3>
            <div class="flex flex-wrap gap-6">
                <div>
                    <span class="text-gray-500">Login Hari Ini</span><br>
                    <span class="text-2xl font-bold text-green-700"><?php echo $loginTodayTotal; ?></span>
                    <span class="ml-2 text-sm <?php echo $loginTodayPct>=0?'text-green-600':'text-red-600'; ?>">(<?php echo ($loginTodayPct>=0?'+':'').number_format($loginTodayPct,1); ?>%)</span>
                </div>
                <div>
                    <span class="text-gray-500">Logout Hari Ini</span><br>
                    <span class="text-2xl font-bold text-red-700"><?php echo $logoutTodayTotal; ?></span>
                    <span class="ml-2 text-sm <?php echo $logoutTodayPct>=0?'text-green-600':'text-red-600'; ?>">(<?php echo ($logoutTodayPct>=0?'+':'').number_format($logoutTodayPct,1); ?>%)</span>
                </div>
            </div>
        </div>
        <a href="index.php" class="text-green-700 hover:underline"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
        // Data grafik dari PHP
        const hourLabels = <?php echo json_encode($hourLabels); ?>;
        const loginHourData = <?php echo json_encode($loginHourData); ?>;
        const logoutHourData = <?php echo json_encode($logoutHourData); ?>;
        const monthLabels = <?php echo json_encode($monthLabels); ?>;
        const loginMonthData = <?php echo json_encode($loginMonthData); ?>;
        const logoutMonthData = <?php echo json_encode($logoutMonthData); ?>;
        // Grafik per jam
        new Chart(document.getElementById('hourChart'), {
            type: 'bar',
            data: {
                labels: hourLabels,
                datasets: [
                    { label: 'Login', data: loginHourData, backgroundColor: 'rgba(16,185,129,0.7)' },
                    { label: 'Logout', data: logoutHourData, backgroundColor: 'rgba(239,68,68,0.7)' }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'top' } } }
        });
        // Grafik per hari
        new Chart(document.getElementById('monthChart'), {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [
                    { label: 'Login', data: loginMonthData, borderColor: 'rgba(16,185,129,1)', backgroundColor: 'rgba(16,185,129,0.2)', fill: true },
                    { label: 'Logout', data: logoutMonthData, borderColor: 'rgba(239,68,68,1)', backgroundColor: 'rgba(239,68,68,0.2)', fill: true }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'top' } } }
        });
    </script>
</body>
</html>
