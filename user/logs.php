<?php
// user/logs.php - Daftar lengkap log aktivitas user dengan pagination
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/Auth.php';

session_start();
$auth = new Auth();
$userData = $auth->isLoggedIn();
if (!$userData) {
    header('Location: ../login.php');
    exit;
}

$db = Database::getInstance();
$perPage = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Hitung total log
$totalLogs = $db->fetchColumn("SELECT COUNT(*) FROM pppoe_logs WHERE user_id = ?", [$userData['user_id']]);
$totalPages = ceil($totalLogs / $perPage);

// Ambil log sesuai halaman
$logs = $db->fetchAll(
    "SELECT * FROM pppoe_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
    [$userData['user_id'], $perPage, $offset]
);

function format_tanggal_indo($tanggal) {
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tgl = date('j', strtotime($tanggal));
    $bln = $bulan[(int)date('n', strtotime($tanggal))];
    $thn = date('Y', strtotime($tanggal));
    return $tgl . ' ' . $bln . ' ' . $thn;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Log Aktivitas - (NotMiK) Notification Mikrotik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-green-600 text-white shadow-md mb-8">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <span class="font-bold text-xl">(NotMiK) Notification Mikrotik</span>
            <div>
                <a href="index.php" class="hover:underline mr-4"><i class="fas fa-home mr-1"></i>Dashboard</a>
                <a href="../logout.php" class="hover:underline"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-extrabold text-gray-800 mb-6 flex items-center"><i class="fas fa-list mr-2"></i>Semua Log Aktivitas</h2>
            <div class="overflow-x-auto">
                <?php if (empty($logs)): ?>
                    <div class="text-center text-gray-500 py-10">Belum ada aktivitas tercatat.</div>
                <?php else: ?>
                <table class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr>
                            <th class="py-2 px-3 text-left text-xs font-semibold text-gray-500">Tanggal</th>
                            <th class="py-2 px-3 text-left text-xs font-semibold text-gray-500">Waktu</th>
                            <th class="py-2 px-3 text-left text-xs font-semibold text-gray-500">Event</th>
                            <th class="py-2 px-3 text-left text-xs font-semibold text-gray-500">Username</th>
                            <th class="py-2 px-3 text-left text-xs font-semibold text-gray-500">IP / Alasan</th>
                        </tr>
                    </thead>
                    <tbody id="logsTbody" class="divide-y divide-gray-50">
                        <?php foreach ($logs as $log): ?>
                        <tr class="transition hover:bg-green-50/60 group">
                            <td class="py-2 px-3 align-top">
                                <span class="text-xs text-gray-400 font-mono block leading-tight">
                                    <i class="fas fa-calendar-alt mr-1"></i><?php echo format_tanggal_indo($log['event_date']); ?>
                                </span>
                            </td>
                            <td class="py-2 px-3 align-top">
                                <span class="text-xs text-gray-400 font-mono block leading-tight">
                                    <i class="fas fa-clock mr-1"></i><?php echo htmlspecialchars($log['event_time']); ?>
                                </span>
                            </td>
                            <td class="py-2 px-3 align-top">
                                <?php if ($log['event_type'] == 'login'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold gap-1">
                                        <i class="fas fa-sign-in-alt"></i> Login
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-red-100 text-red-600 text-xs font-bold gap-1">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-2 px-3 align-top">
                                <span class="font-semibold text-gray-800"><i class="fas fa-user mr-1 text-gray-400"></i><?php echo htmlspecialchars($log['username']); ?></span>
<button type="button" class="ml-2 text-xs px-2 py-1 rounded bg-green-100 text-green-700 font-semibold hover:bg-green-200 focus:outline-none detail-btn" data-username="<?php echo htmlspecialchars($log['username']); ?>">
    <i class="fas fa-chart-line mr-1"></i>Detail
</button>
                            </td>
                            <td class="py-2 px-3 align-top">
                                <?php if ($log['event_type'] == 'login'): ?>
                                    <span class="inline-flex items-center text-xs text-gray-600"><i class="fas fa-network-wired mr-1"></i><?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?></span>
                                <?php else: ?>
                                    <span class="inline-flex items-center text-xs text-gray-500"><i class="fas fa-times-circle mr-1"></i><?php echo htmlspecialchars($log['last_disconnect_reason'] ?? '-'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="flex justify-center mt-8 space-x-1">
                <?php if ($page > 1): ?>
                    <a href="?page=1" class="px-3 py-1 rounded bg-gray-200 hover:bg-green-200 text-gray-700 font-semibold">&laquo; First</a>
                    <a href="?page=<?php echo $page-1; ?>" class="px-3 py-1 rounded bg-gray-200 hover:bg-green-200 text-gray-700 font-semibold">&lsaquo; Prev</a>
                <?php endif; ?>
                <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="px-3 py-1 rounded <?php echo $i == $page ? 'bg-green-600 text-white' : 'bg-gray-200 hover:bg-green-200 text-gray-700'; ?> font-semibold"><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page+1; ?>" class="px-3 py-1 rounded bg-gray-200 hover:bg-green-200 text-gray-700 font-semibold">Next &rsaquo;</a>
                    <a href="?page=<?php echo $totalPages; ?>" class="px-3 py-1 rounded bg-gray-200 hover:bg-green-200 text-gray-700 font-semibold">Last &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="text-right">
            <a href="index.php" class="text-green-700 hover:underline font-semibold"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard</a>
        </div>
    </div>
</body>
<footer class="bg-white py-4 mt-6">
    <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
        &copy; <?php echo date('Y'); ?> (Nottik) Notification Mikrotik. All rights reserved.
    </div>
</footer>
<!-- Modal Detail Aktivitas User-->
<div id="modalDetail" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg max-w-lg w-full p-6 relative animate-fadeInUp">
        <button id="closeModal" class="absolute top-3 right-3 text-gray-400 hover:text-red-500 text-2xl font-bold focus:outline-none">&times;</button>
        <h3 class="text-xl font-bold mb-2 text-gray-800 flex items-center"><i class="fas fa-chart-bar mr-2"></i>Detail Aktivitas <span id="modalUsername" class="ml-2 text-green-700"></span></h3>
        <div id="modalChartWrap" class="my-4 min-h-[220px] flex items-center justify-center">
            <canvas id="modalChart" width="360" height="220"></canvas>
        </div>
        <div id="modalLoading" class="text-center text-gray-500 hidden">Memuat data...</div>
        <div id="modalError" class="text-center text-red-500 hidden">Gagal memuat data.</div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Modal Detail Aktivitas
    const modal = document.getElementById('modalDetail');
    const closeModalBtn = document.getElementById('closeModal');
    const modalUsername = document.getElementById('modalUsername');
    const modalChartWrap = document.getElementById('modalChartWrap');
    const modalLoading = document.getElementById('modalLoading');
    const modalError = document.getElementById('modalError');
    let modalChart = null;

    document.querySelectorAll('.detail-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const username = this.getAttribute('data-username');
            modalUsername.textContent = username;
            modal.classList.remove('hidden');
            modalLoading.classList.remove('hidden');
            modalError.classList.add('hidden');
            modalChartWrap.style.display = 'block';
            // Fetch data
            fetch('../user-activity-history.php?username=' + encodeURIComponent(username))
                .then(res => res.json())
                .then(data => {
                    modalLoading.classList.add('hidden');
                    if (!Array.isArray(data)) {
                        modalError.classList.remove('hidden');
                        modalChartWrap.style.display = 'none';
                        return;
                    }
                    // Prepare chart
                    const labels = data.map(d => {
                        const tgl = d.date.split('-');
                        return tgl[2] + ' ' + ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][parseInt(tgl[1])-1];
                    });
                    const login = data.map(d => d.login);
                    const logout = data.map(d => d.logout);
                    if (modalChart) modalChart.destroy();
                    const ctx = document.getElementById('modalChart').getContext('2d');
                    modalChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [
                                {label: 'Login', data: login, backgroundColor: '#22c55e'},
                                {label: 'Logout', data: logout, backgroundColor: '#ef4444'}
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {legend: {position: 'top'}},
                            scales: {y: {beginAtZero: true, ticks: {precision:0}}}
                        }
                    });
                })
                .catch(() => {
                    modalLoading.classList.add('hidden');
                    modalError.classList.remove('hidden');
                    modalChartWrap.style.display = 'none';
                });
        });
    });
    closeModalBtn.addEventListener('click', function() {
        modal.classList.add('hidden');
    });
    window.addEventListener('click', function(e) {
        if (e.target === modal) modal.classList.add('hidden');
    });
</script>
<script>
// Realtime polling log aktivitas (20 baris, 3 detik)
function renderLogRow(log) {
    let badge = log.event_type === 'login'
        ? `<span class=\"inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold gap-1\"><i class=\"fas fa-sign-in-alt\"></i> Login</span>`
        : `<span class=\"inline-flex items-center px-2 py-1 rounded-full bg-red-100 text-red-600 text-xs font-bold gap-1\"><i class=\"fas fa-sign-out-alt\"></i> Logout</span>`;
    let ipOrReason = log.event_type === 'login'
        ? `<span class=\"inline-flex items-center text-xs text-gray-600\"><i class=\"fas fa-network-wired mr-1\"></i>${log.ip_address}</span>`
        : `<span class=\"inline-flex items-center text-xs text-gray-500\"><i class=\"fas fa-times-circle mr-1\"></i>${log.last_disconnect_reason}</span>`;
    return `<tr class=\"transition hover:bg-green-50/60 group\">\n` +
        `<td class=\"py-2 px-3 align-top\"><span class=\"text-xs text-gray-400 font-mono block leading-tight\"><i class=\"fas fa-calendar-alt mr-1\"></i>${log.tanggal}</span></td>` +
        `<td class=\"py-2 px-3 align-top\"><span class=\"text-xs text-gray-400 font-mono block leading-tight\"><i class=\"fas fa-clock mr-1\"></i>${log.waktu}</span></td>` +
        `<td class=\"py-2 px-3 align-top\">${badge}</td>` +
        `<td class=\"py-2 px-3 align-top\"><span class=\"font-semibold text-gray-800\"><i class=\"fas fa-user mr-1 text-gray-400\"></i>${log.username}</span>\n<button type=\"button\" class=\"ml-2 text-xs px-2 py-1 rounded bg-green-100 text-green-700 font-semibold hover:bg-green-200 focus:outline-none detail-btn\" data-username=\"${log.username}\"><i class=\"fas fa-chart-line mr-1\"></i>Detail</button></td>` +
        `<td class=\"py-2 px-3 align-top\">${ipOrReason}</td>` +
        `</tr>`;
}
function rebindDetailButtons() {
    document.querySelectorAll('.detail-btn').forEach(btn => {
        btn.onclick = function() {
            const username = this.getAttribute('data-username');
            modalUsername.textContent = username;
            modal.classList.remove('hidden');
            modalLoading.classList.remove('hidden');
            modalError.classList.add('hidden');
            modalChartWrap.style.display = 'block';
            fetch('../user-activity-history.php?username=' + encodeURIComponent(username))
                .then(res => res.json())
                .then(data => {
                    modalLoading.classList.add('hidden');
                    if (!data || !data.history || !Array.isArray(data.history)) {
                        modalError.classList.remove('hidden');
                        modalChartWrap.style.display = 'none';
                        return;
                    }
                    // Tampilkan info profile di modal (optional, bisa custom tampilan)
                    if (data.profile) {
                        modalUsername.innerHTML = `${data.profile.username} <span class='ml-2 text-xs text-gray-500'>(Login: <b>${data.profile.total_login}</b> | Logout: <b>${data.profile.total_logout}</b>)</span>`;
                    }
                    // Prepare chart
                    const labels = data.history.map(d => {
                        const tgl = d.date.split('-');
                        return tgl[2] + ' ' + ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][parseInt(tgl[1])-1];
                    });
                    const login = data.history.map(d => d.login);
                    const logout = data.history.map(d => d.logout);
                    if (modalChart) modalChart.destroy();
                    const ctx = document.getElementById('modalChart').getContext('2d');
                    modalChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [
                                {label: 'Login', data: login, backgroundColor: '#22c55e'},
                                {label: 'Logout', data: logout, backgroundColor: '#ef4444'}
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {legend: {position: 'top'}},
                            scales: {y: {beginAtZero: true, ticks: {precision:0}}}
                        }
                    });
                    // Hapus tabel status sebelumnya jika ada
                    const prevStatusTable = document.getElementById('status-table-modal');
                    if (prevStatusTable) prevStatusTable.remove();
                    // Tampilkan status harian dalam bentuk tabel
                    let statusTable = `<div class='mt-4' id='status-table-modal'>
                        <div class='font-semibold mb-1 text-gray-800 flex items-center gap-2'>
                            <i class='fas fa-info-circle text-blue-400'></i>Status Login Harian (7 Hari):
                        </div>
                        <div class='mb-2 text-xs text-gray-500'>
                            <span class='inline-block px-2 py-1 rounded bg-green-100 text-green-700 font-semibold mr-2'>Aman (≤3)</span>
                            <span class='inline-block px-2 py-1 rounded bg-yellow-100 text-yellow-700 font-semibold mr-2'>Perlu dicek (=4)</span>
                            <span class='inline-block px-2 py-1 rounded bg-red-100 text-red-700 font-semibold'>Ada kendala (≥5)</span>
                        </div>
                        <table class='min-w-full text-xs border rounded overflow-hidden'>
                            <thead class='bg-gray-50'>
                                <tr>
                                    <th class='py-1 px-2 text-left font-bold text-gray-500'>Tanggal</th>
                                    <th class='py-1 px-2 text-center font-bold text-gray-500'>Login</th>
                                    <th class='py-1 px-2 text-center font-bold text-gray-500'>Logout</th>
                                    <th class='py-1 px-2 text-center font-bold text-gray-500'>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    data.history.forEach(row => {
                        let statusClass = row.status === 'Aman' ? 'bg-green-100 text-green-700' : (row.status === 'Perlu dicek' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                        statusTable += `<tr>
                            <td class='py-1 px-2'>${row.tanggal_id}</td>
                            <td class='py-1 px-2 text-center'>${row.login}</td>
                            <td class='py-1 px-2 text-center'>${row.logout}</td>
                            <td class='py-1 px-2 text-center'><span class='inline-block px-2 py-0.5 rounded font-semibold ${statusClass}'>${row.status}</span></td>
                        </tr>`;
                    });
                    statusTable += `</tbody></table></div>`;
                    // Sisipkan tabel status di bawah chart
                    modalChartWrap.insertAdjacentHTML('afterend', statusTable);
                })
                .catch(() => {
                    modalLoading.classList.add('hidden');
                    modalError.classList.remove('hidden');
                    modalChartWrap.style.display = 'none';
                });
        };
    });
}
function loadLogsRealtime() {
    fetch('logs-data.php')
        .then(res => res.json())
        .then(data => {
            if (!Array.isArray(data)) return;
            const tbody = document.getElementById('logsTbody');
            tbody.innerHTML = data.map(renderLogRow).join('');
            rebindDetailButtons();
        });
}
setInterval(loadLogsRealtime, 3000);
window.addEventListener('DOMContentLoaded', loadLogsRealtime);
</script>
</html>
