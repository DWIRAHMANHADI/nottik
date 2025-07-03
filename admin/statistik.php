<?php
// admin/statistik.php - Statistik & Grafik Lanjutan Admin
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/Auth.php';
session_start();
$auth = new Auth();
if (!$auth->isAdminLoggedIn()) {
    header('Location: ../login.php');
    exit;
}
$db = Database::getInstance();
// Statistik dasar: total user, user aktif, user suspend
$totalUser = $db->fetchOne('SELECT COUNT(*) as total FROM users')['total'] ?? 0;
$activeUser = $db->fetchOne("SELECT COUNT(*) as total FROM users WHERE status = 'active'")['total'] ?? 0;
$suspendUser = $db->fetchOne("SELECT COUNT(*) as total FROM users WHERE status = 'suspended'")['total'] ?? 0;
// Statistik notifikasi WhatsApp (berhasil/gagal)
$totalNotif = $db->fetchOne('SELECT COUNT(*) as total FROM notif_log')['total'] ?? 0;
$successNotif = $db->fetchOne("SELECT COUNT(*) as total FROM notif_log WHERE status = 'success'")['total'] ?? 0;
$failedNotif = $db->fetchOne("SELECT COUNT(*) as total FROM notif_log WHERE status = 'failed'")['total'] ?? 0;
// Statistik harian (user login/logout per hari 7 hari terakhir)
$loginChart = $db->fetchAll("SELECT DATE(created_at) as tanggal, COUNT(*) as total FROM notif_log WHERE type = 'login' GROUP BY tanggal ORDER BY tanggal DESC LIMIT 7");
$logoutChart = $db->fetchAll("SELECT DATE(created_at) as tanggal, COUNT(*) as total FROM notif_log WHERE type = 'logout' GROUP BY tanggal ORDER BY tanggal DESC LIMIT 7");
// Top 5 user login terbanyak
$topLoginUsers = $db->fetchAll("SELECT u.name, u.phone, COUNT(l.id) as total_login FROM notif_log l JOIN users u ON l.user_id = u.id WHERE l.type = 'login' GROUP BY l.user_id ORDER BY total_login DESC LIMIT 5");
// --- Grafik Login/Logout PPPoE per Jam (Hari Ini, Semua User) ---
$today = date('Y-m-d');
$hourLabels = [];
$loginHourData = [];
$logoutHourData = [];
for ($h = 0; $h < 24; $h++) {
    $hour = str_pad($h, 2, '0', STR_PAD_LEFT);
    $hourLabels[] = $hour . ':00';
    $loginHourData[] = (int)$db->fetchColumn(
        "SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ? AND HOUR(event_time) = ?",
        [$today, $h]
    );
    $logoutHourData[] = (int)$db->fetchColumn(
        "SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ? AND HOUR(event_time) = ?",
        [$today, $h]
    );
}
// --- Grafik Login/Logout PPPoE per Hari (30 Hari Terakhir, Semua User) ---
$monthLabels = [];
$loginMonthData = [];
$logoutMonthData = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $monthLabels[] = date('d M', strtotime($date));
    $loginMonthData[] = (int)$db->fetchColumn(
        "SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ?",
        [$date]
    );
    $logoutMonthData[] = (int)$db->fetchColumn(
        "SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ?",
        [$date]
    );
}
// --- Filter periode untuk Top 5 user PPPoE login terbanyak ---
$periode = $_GET['periode_pppoe'] ?? 'all';
$wherePeriode = '';
if ($periode === 'today') {
    $today = date('Y-m-d');
    $wherePeriode = "AND l.event_date = '$today'";
} elseif ($periode === '7days') {
    $start = date('Y-m-d', strtotime('-6 days'));
    $end = date('Y-m-d');
    $wherePeriode = "AND l.event_date BETWEEN '$start' AND '$end'";
}
$topPppoeLoginUsers = $db->fetchAll(
    "SELECT u.name, u.phone, l.username, COUNT(l.id) as total_login
     FROM pppoe_logs l
     JOIN users u ON l.user_id = u.id
     WHERE l.event_type = 'login' $wherePeriode
     GROUP BY l.username
     ORDER BY total_login DESC
     LIMIT 5"
);
// --- Statistik User Koneksi Bermasalah (login >= 5 dalam 1 hari, 7 hari terakhir) ---
$problemUsers = $db->fetchAll("
    SELECT u.id as user_id, u.name, u.phone, l.username, l.event_date, COUNT(l.id) as total_login
    FROM pppoe_logs l
    JOIN users u ON l.user_id = u.id
    WHERE l.event_type = 'login' AND l.event_date >= ?
    GROUP BY l.user_id, l.username, l.event_date
    HAVING total_login >= 5
    ORDER BY l.event_date DESC, total_login DESC
", [date('Y-m-d', strtotime('-6 days'))]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik & Grafik - Admin Nottik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
<nav class="bg-indigo-600 text-white shadow-md">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center">
            <span class="font-bold text-xl">Nottik Admin</span>
        </div>
        <div class="flex items-center space-x-4">
            <a href="index.php" class="hover:underline"><i class="fas fa-tachometer-alt mr-1"></i> Dashboard</a>
            <a href="statistik.php" class="hover:underline font-bold underline"><i class="fas fa-chart-bar mr-1"></i> Statistik</a>
            <a href="help.php" class="hover:underline"><i class="fas fa-question-circle mr-1"></i> Bantuan</a>
            <a href="../logout.php" class="hover:underline"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
        </div>
    </div>
</nav>
<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6 text-gray-800"><i class="fas fa-chart-bar text-indigo-500 mr-2"></i>Statistik & Grafik Lanjutan</h2>

    <div class="bg-white rounded-lg shadow p-6 mb-10">
        <h3 class="text-lg font-bold mb-4">Grafik Login/Logout PPPoE per Jam (Hari Ini)</h3>
        <canvas id="pppoeHourChart" height="120"></canvas>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-10">
        <h3 class="text-lg font-bold mb-4">Grafik Login/Logout PPPoE 30 Hari Terakhir</h3>
        <canvas id="pppoeMonthChart" height="120"></canvas>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-10">
        <h3 class="text-lg font-bold mb-4">Grafik Login 7 Hari Terakhir</h3>
        <canvas id="loginChart" height="120"></canvas>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-8 mb-10">
        <h3 class="text-xl font-bold mb-6 flex items-center"><i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>User Koneksi Bermasalah (7 Hari Terakhir)</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm bg-white border rounded">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-2 px-3 border-b text-left">Nama</th>
                        <th class="py-2 px-3 border-b text-left">Username PPPoE</th>
                        <th class="py-2 px-3 border-b text-left">Tanggal</th>
                        <th class="py-2 px-3 border-b text-left">Jumlah Login</th>
                        <th class="py-2 px-3 border-b text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($problemUsers as $row): ?>
                    <tr>
                        <td class="py-2 px-3 border-b font-semibold text-gray-800"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td class="py-2 px-3 border-b text-gray-700 font-mono"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td class="py-2 px-3 border-b text-gray-500"><?php echo date('d M Y', strtotime($row['event_date'])); ?></td>
                        <td class="py-2 px-3 border-b text-red-700 font-bold"><?php echo $row['total_login']; ?></td>
                        <td class="py-2 px-3 border-b">
                            <button type="button" class="detail-problem-btn px-3 py-1 rounded bg-red-100 text-red-700 font-semibold hover:bg-red-200 focus:outline-none" 
                                data-userid="<?php echo $row['user_id']; ?>" 
                                data-username="<?php echo htmlspecialchars($row['username']); ?>" 
                                data-date="<?php echo $row['event_date']; ?>">
                                <i class="fas fa-search mr-1"></i>Detail
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($problemUsers)): ?>
                    <tr><td colspan="5" class="py-4 px-3 text-center text-gray-500">Tidak ada user yang terdeteksi koneksi bermasalah dalam 7 hari terakhir.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Detail Login User Bermasalah -->
    <div id="modalProblemDetail" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 relative animate-fadeInUp border-2 border-indigo-200">
        <button id="closeModalProblem" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-3xl font-extrabold focus:outline-none transition-transform hover:scale-125">&times;</button>
        <div class="flex items-center mb-4">
            <div class="bg-red-100 text-red-600 rounded-full p-3 mr-3"><i class="fas fa-user-clock fa-lg"></i></div>
            <div>
                <h3 class="text-xl font-extrabold text-gray-800 mb-1">Detail Login PPPoE</h3>
                <div class="text-xs text-gray-500">Username: <span id="modalProblemUsername" class="font-semibold text-indigo-600"></span></div>
                <div class="text-xs text-gray-500">Tanggal: <span id="modalProblemDate" class="font-semibold"></span></div>
            </div>
        </div>
        <div id="modalProblemLoading" class="flex flex-col items-center justify-center my-8">
            <svg class="animate-spin h-8 w-8 text-indigo-400 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
            <span class="text-gray-500">Memuat data login...</span>
        </div>
        <div id="modalProblemContent" class="hidden">
            <table class="min-w-full text-xs border rounded-xl overflow-hidden mb-2 shadow">
                <thead class="bg-indigo-50">
                    <tr>
                        <th class="py-2 px-4 text-left text-indigo-700 font-semibold">Jam Login</th>
                    </tr>
                </thead>
                <tbody id="modalProblemTableBody"></tbody>
            </table>
        </div>
        <div id="modalProblemError" class="text-center text-red-500 hidden mt-4">
            <i class="fas fa-exclamation-triangle mr-1"></i>Gagal memuat data. Silakan coba lagi.
        </div>
    </div>
</div>

        <div class="flex flex-wrap items-center justify-between mb-6 gap-2">
            <h3 class="text-xl font-bold flex items-center"><i class="fas fa-crown text-yellow-400 mr-2"></i>Top 5 User PPPoE Login Terbanyak</h3>
            <form method="get" class="">
                <select name="periode_pppoe" onchange="this.form.submit()" class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-indigo-400 bg-white">
                    <option value="today" <?php if($periode==='today') echo 'selected';?>>Hari Ini</option>
                    <option value="7days" <?php if($periode==='7days') echo 'selected';?>>7 Hari Terakhir</option>
                    <option value="all" <?php if($periode==='all') echo 'selected';?>>Semua Waktu</option>
                </select>
            </form>
        </div>
        <div class="divide-y divide-gray-200">
            <?php 
            $maxLogin = 0;
            foreach ($topPppoeLoginUsers as $u) {
                if ($u['total_login'] > $maxLogin) $maxLogin = $u['total_login'];
            }
            $rank = 1;
            foreach ($topPppoeLoginUsers as $user): 
                $initial = strtoupper(mb_substr($user['name'], 0, 1));
                $progress = $maxLogin > 0 ? round(($user['total_login']/$maxLogin)*100) : 0;
                $highlight = $rank === 1 ? 'bg-gradient-to-r from-yellow-100 to-white border-yellow-400' : 'bg-gray-50';
            ?>
            <div class="flex items-center py-4 px-2 <?php echo $highlight; ?> rounded-lg mb-2">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-2xl font-bold text-indigo-600 mr-4 border-2 <?php echo $rank === 1 ? 'border-yellow-400' : 'border-indigo-200'; ?>">
                    <?php echo $initial; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center">
                        <span class="font-semibold text-gray-800 text-lg mr-2"><?php echo htmlspecialchars($user['name']); ?></span>
                        <?php if ($rank === 1): ?><span class="ml-1 px-2 py-0.5 text-xs font-bold bg-yellow-400 text-white rounded">#1</span><?php endif; ?>
                    </div>
                    <div class="text-sm text-gray-500 mb-1 flex items-center">
                        <i class="fas fa-user-tag mr-1"></i> <?php echo htmlspecialchars($user['username']); ?>
                        <span class="mx-2">|</span>
                        <i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($user['phone']); ?>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                        <div class="bg-indigo-500 h-2.5 rounded-full transition-all duration-700" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                </div>
                <div class="ml-4 text-right min-w-[60px]">
                    <span class="text-xl font-bold text-indigo-700"><?php echo $user['total_login']; ?></span>
                    <div class="text-xs text-gray-400">Login</div>
                </div>
            </div>
            <?php $rank++; endforeach; ?>
            <?php if (empty($topPppoeLoginUsers)): ?>
            <div class="py-6 text-center text-gray-500">Belum ada data login PPPoE.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
// Grafik PPPoE per jam (hari ini)
const pppoeHourData = {
    labels: <?php echo json_encode($hourLabels); ?>,
    datasets: [
        {
            label: 'Login',
            data: <?php echo json_encode($loginHourData); ?>,
            backgroundColor: 'rgba(99, 102, 241, 0.5)',
            borderColor: 'rgba(99, 102, 241, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        },
        {
            label: 'Logout',
            data: <?php echo json_encode($logoutHourData); ?>,
            backgroundColor: 'rgba(239, 68, 68, 0.5)',
            borderColor: 'rgba(239, 68, 68, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }
    ]
};
const ctxPppoeHour = document.getElementById('pppoeHourChart').getContext('2d');
new Chart(ctxPppoeHour, {type: 'line', data: pppoeHourData, options: {responsive: true}});

// Grafik PPPoE per hari (30 hari terakhir)
const pppoeMonthData = {
    labels: <?php echo json_encode($monthLabels); ?>,
    datasets: [
        {
            label: 'Login',
            data: <?php echo json_encode($loginMonthData); ?>,
            backgroundColor: 'rgba(99, 102, 241, 0.5)',
            borderColor: 'rgba(99, 102, 241, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        },
        {
            label: 'Logout',
            data: <?php echo json_encode($logoutMonthData); ?>,
            backgroundColor: 'rgba(239, 68, 68, 0.5)',
            borderColor: 'rgba(239, 68, 68, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }
    ]
};
const ctxPppoeMonth = document.getElementById('pppoeMonthChart').getContext('2d');
new Chart(ctxPppoeMonth, {type: 'line', data: pppoeMonthData, options: {responsive: true}});

const loginData = {
    labels: <?php echo json_encode(array_reverse(array_column($loginChart, 'tanggal'))); ?>,
    datasets: [{
        label: 'Login',
        data: <?php echo json_encode(array_reverse(array_column($loginChart, 'total'))); ?>,
        backgroundColor: 'rgba(99, 102, 241, 0.5)',
        borderColor: 'rgba(99, 102, 241, 1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
    }]
};
const ctxLogin = document.getElementById('loginChart').getContext('2d');
new Chart(ctxLogin, {type: 'line', data: loginData, options: {responsive: true}});
</script>
<script>
// Modal detail koneksi bermasalah (AJAX fetch)
document.querySelectorAll('.detail-problem-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const userId = this.getAttribute('data-userid');
        const username = this.getAttribute('data-username');
        const date = this.getAttribute('data-date');
        document.getElementById('modalProblemUsername').textContent = username;
        document.getElementById('modalProblemDate').textContent = new Date(date).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
        document.getElementById('modalProblemDetail').classList.remove('hidden');
        document.getElementById('modalProblemLoading').classList.remove('hidden');
        document.getElementById('modalProblemContent').classList.add('hidden');
        document.getElementById('modalProblemError').classList.add('hidden');
        fetch('problem-login-detail.php?user_id='+userId+'&date='+date+'&username='+encodeURIComponent(username))
            .then(res => res.json())
            .then(data => {
                if (!Array.isArray(data)) throw new Error('Format salah');
                let html = '';
                data.forEach(jam => {
                    html += `<tr><td class='py-1 px-2'>${jam}</td></tr>`;
                });
                document.getElementById('modalProblemTableBody').innerHTML = html;
                document.getElementById('modalProblemLoading').classList.add('hidden');
                document.getElementById('modalProblemContent').classList.remove('hidden');
            })
            .catch(() => {
                document.getElementById('modalProblemLoading').classList.add('hidden');
                document.getElementById('modalProblemError').classList.remove('hidden');
            });
    });
});
document.getElementById('closeModalProblem').onclick = function() {
    document.getElementById('modalProblemDetail').classList.add('hidden');
};
</script>
</body>
</html>

