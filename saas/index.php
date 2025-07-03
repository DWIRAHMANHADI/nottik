<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once 'config.php';
require_once 'database.php';

// Filter tanggal, event, dan username
$filterStart = isset($_GET['start']) && $_GET['start'] !== '' ? $_GET['start'] : '';
$filterEnd = isset($_GET['end']) && $_GET['end'] !== '' ? $_GET['end'] : '';
$filterEvent = isset($_GET['event']) && $_GET['event'] !== '' ? $_GET['event'] : '';
$filterUsername = isset($_GET['username']) && $_GET['username'] !== '' ? $_GET['username'] : '';

// Pagination setup
$perPage = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
if ($filterStart && $filterEnd) {
    $where[] = 'event_date BETWEEN :start AND :end';
    $params[':start'] = $filterStart;
    $params[':end'] = $filterEnd;
} elseif ($filterStart) {
    $where[] = 'event_date >= :start';
    $params[':start'] = $filterStart;
} elseif ($filterEnd) {
    $where[] = 'event_date <= :end';
    $params[':end'] = $filterEnd;
}
if ($filterEvent && in_array($filterEvent, ['login','logout'])) {
    $where[] = 'event_type = :event';
    $params[':event'] = $filterEvent;
}
if ($filterUsername) {
    $where[] = 'username LIKE :username';
    $params[':username'] = "%$filterUsername%";
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Hitung total log
$totalLogsStmt = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs $whereSql");
foreach ($params as $k => $v) { $totalLogsStmt->bindValue($k, $v); }
$totalLogsStmt->execute();
$totalLogs = $totalLogsStmt->fetchColumn();

// Ambil data log
$stmt = $pdo->prepare("SELECT * FROM pppoe_logs $whereSql ORDER BY id DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalPages = ceil($totalLogs / $perPage);

// Statistik ringkas
$today = date('Y-m-d');
$thisMonth = date('Y-m');
$loginToday = $pdo->query("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = '$today'")->fetchColumn();
$logoutToday = $pdo->query("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = '$today'")->fetchColumn();
$userToday = $pdo->query("SELECT COUNT(DISTINCT username) FROM pppoe_logs WHERE event_date = '$today'")->fetchColumn();
$totalMonth = $pdo->query("SELECT COUNT(*) FROM pppoe_logs WHERE event_date LIKE '$thisMonth%'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Detail PPPoE</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <nav class="flex flex-col md:flex-row md:justify-between md:items-center mb-8 gap-2 md:gap-0">
            <div class="font-bold text-xl text-indigo-700">Data Detail PPPoE</div>
            <div class="flex gap-2">
                <a href="statistik.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">Statistik</a>
                <a href="settings.php" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 transition">Pengaturan</a>
                <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Logout</a>
            </div>
        </nav>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-green-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <span class="text-2xl font-bold text-green-600"><?php echo $loginToday; ?></span>
        <span class="text-sm text-gray-700">Login Hari Ini</span>
    </div>
    <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7-7l-7 7 7 7" />
        </svg>
        <span class="text-2xl font-bold text-red-600"><?php echo $logoutToday; ?></span>
        <span class="text-sm text-gray-700">Logout Hari Ini</span>
    </div>
    <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-blue-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm-6 6a9 9 0 1112 0H9z" />
        </svg>
        <span class="text-2xl font-bold text-blue-600"><?php echo $userToday; ?></span>
        <span class="text-sm text-gray-700">User Unik Hari Ini</span>
    </div>
    <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-indigo-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-2xl font-bold text-indigo-600"><?php echo $totalMonth; ?></span>
        <span class="text-sm text-gray-700">Total Log Bulan Ini</span>
    </div>
</div>
<div class="mb-6">
    <div class="bg-white rounded-xl shadow-lg p-6 border border-indigo-100">
        <div class="mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
            <h3 class="text-lg font-bold text-indigo-700 tracking-wide">Filter &amp; Pencarian Log</h3>
        </div>
        <?php
// Ambil daftar username untuk autocomplete
$tmpstmt = $pdo->query("SELECT DISTINCT username FROM pppoe_logs ORDER BY username ASC");
$usernamesAuto = $tmpstmt->fetchAll(PDO::FETCH_COLUMN);
?>
        <datalist id="usernamesAuto">
            <?php foreach ($usernamesAuto as $u): ?>
                <option value="<?= htmlspecialchars($u) ?>">
            <?php endforeach; ?>
        </datalist>
        <form id="filterForm" method="get" class="flex flex-col md:flex-row flex-wrap gap-4 items-end w-full">
            <div class="w-full md:w-auto">
                <label class="block text-xs md:text-sm font-semibold mb-1">Tanggal Mulai</label>
                <input type="date" id="start" name="start" value="<?php echo htmlspecialchars($filterStart); ?>" class="border rounded px-2 py-1 md:px-4 md:py-2 text-xs md:text-sm w-full">
            </div>
            <div class="w-full md:w-auto">
                <label class="block text-xs md:text-sm font-semibold mb-1">Tanggal Akhir</label>
                <input type="date" id="end" name="end" value="<?php echo htmlspecialchars($filterEnd); ?>" class="border rounded px-2 py-1 md:px-4 md:py-2 text-xs md:text-sm w-full">
            </div>
            <div class="w-full md:w-auto">
                <label class="block text-xs md:text-sm font-semibold mb-1">Event</label>
                <select id="event" name="event" class="border rounded px-2 py-1 md:px-4 md:py-2 text-xs md:text-sm w-full">
                    <option value="">Semua</option>
                    <option value="login" <?php if($filterEvent=='login') echo 'selected'; ?>>Login</option>
                    <option value="logout" <?php if($filterEvent=='logout') echo 'selected'; ?>>Logout</option>
                </select>
            </div>
            <div class="w-full md:w-auto">
                <label class="block text-xs md:text-sm font-semibold mb-1">Username</label>
                <input type="text" id="username" name="username" list="usernamesAuto" value="<?php echo htmlspecialchars($filterUsername); ?>" placeholder="Cari username..." class="border rounded px-2 py-1 md:px-4 md:py-2 text-xs md:text-sm w-full">
            </div>
            <button type="submit" class="w-full md:w-auto bg-indigo-500 text-white px-4 py-2 rounded text-xs md:text-sm font-semibold hover:bg-indigo-600 shadow">Cari</button>
            <a href="index.php" class="w-full md:w-auto bg-gray-300 text-gray-700 px-4 py-2 rounded text-xs md:text-sm font-semibold hover:bg-gray-400 shadow">Reset</a>
        </form>
    </div>
</div>
<div class="bg-white rounded-lg shadow-md p-6 overflow-x-auto">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Log Terbaru</h2>
        <div class="flex items-center gap-2">
            <label for="perPage" class="text-xs font-semibold">Tampilkan</label>
            <select id="perPage" class="border rounded px-2 py-1 text-xs">
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span class="text-xs">/ halaman</span>
        </div>
    </div>
    <table id="logTable" class="min-w-full divide-y divide-gray-200">
                <thead>
    <tr class="bg-gradient-to-r from-indigo-100 via-white to-indigo-100 text-indigo-700 text-xs uppercase border-b-2 border-indigo-300">
        <th class="px-3 py-3 font-bold text-center"><span class="inline-flex items-center gap-1"><svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" /></svg>No</span></th>
        <th class="px-3 py-3 font-bold text-center"><span class="inline-flex items-center gap-1"><svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" /></svg>Event</span></th>
        <th class="px-3 py-3 font-bold text-center"><span class="inline-flex items-center gap-1"><svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm-6 6a9 9 0 1112 0H9z" /></svg>Username</span></th>
        <th class="px-3 py-3 font-bold text-center"><span class="inline-flex items-center gap-1"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>IP Address</span></th>
        <th class="px-3 py-3 font-bold text-center"><span class="inline-flex items-center gap-1"><svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm0 8a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2zm8-8a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zm0 8a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>Caller ID</span></th>
        <th class="px-3 py-3 font-bold text-center"><span class="inline-flex items-center gap-1"><svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 21m5.25-4l.75 4m-8.25 0h12a2 2 0 002-2v-7a2 2 0 00-2-2h-4l-2-4-2 4H5a2 2 0 00-2 2v7a2 2 0 002 2z" /></svg>Active</span></th>
        <th class="px-3 py-3 font-bold text-center"><span class="inline-flex items-center gap-1"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>Tanggal</span></th>
        <th class="px-3 py-3 font-bold text-center"><span class="inline-flex items-center gap-1"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Waktu</span></th>
        <th class="px-3 py-3 font-bold text-center">Aksi</th>
    </tr>
</thead>
                <tbody>
</tbody>
            </table>
<!-- Modal Detail User Activity -->
<style>
@keyframes modal-pop {
  0% { opacity: 0; transform: scale(0.95); }
  100% { opacity: 1; transform: scale(1); }
}
.animate-modal-pop { animation: modal-pop 0.3s cubic-bezier(0.4,0,0.2,1); }
</style>
<div id="modalDetail" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-xs relative">
        <button id="closeModalDetail" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
        <h3 class="text-lg font-semibold mb-4">Riwayat Aktivitas User</h3>
        <div id="modalDetailContent" class="text-sm text-gray-700">
            <div class="flex items-center justify-center py-8 text-gray-400">Memuat...</div>
        </div>
    </div>
</div>
<script>
// Helper untuk ambil parameter filter dari form
function getFilterParams() {
    const params = new URLSearchParams();
    params.set('start', document.getElementById('start').value);
    params.set('end', document.getElementById('end').value);
    params.set('event', document.getElementById('event').value);
    params.set('username', document.getElementById('username').value);
    params.set('perPage', document.getElementById('perPage').value);
    params.set('page', window.currentPage || 1);
    return params.toString();
}

function renderPagination(totalPages, page) {
    const container = document.querySelector('.flex.justify-center.mt-6.gap-2');
    if (!container) return;
    let html = '';
    if (page > 1) {
        html += `<a href="#" class="pagination-link px-3 py-1 rounded bg-indigo-500 text-white hover:bg-indigo-600" data-page="${page-1}">&laquo; Prev</a>`;
    }
    for (let i = 1; i <= totalPages; i++) {
        html += `<a href="#" class="pagination-link px-3 py-1 rounded ${i==page?'bg-indigo-700 text-white':'bg-gray-200 text-indigo-700 hover:bg-indigo-300'}" data-page="${i}"> ${i} </a>`;
    }
    if (page < totalPages) {
        html += `<a href="#" class="pagination-link px-3 py-1 rounded bg-indigo-500 text-white hover:bg-indigo-600" data-page="${page+1}">Next &raquo;</a>`;
    }
    container.innerHTML = html;
}

function renderLogTable(logs, offset = 0) {
    const tbody = document.querySelector('#logTable tbody');
    tbody.innerHTML = '';
    if (logs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-gray-400">Tidak ada data</td></tr>';
        return;
    }
    logs.forEach((row, i) => {
        const nomor = offset + i + 1;
        tbody.innerHTML += `
        <tr class="border-b hover:bg-gray-50 transition">
            <td class="px-3 py-2 text-xs text-gray-700">${nomor}</td>
            <td class="px-3 py-2 text-xs text-center">
                ${row.event_type === 'login' ? `
                    <span class="inline-flex items-center justify-center min-w-[90px] min-h-[30px] px-3 py-1 text-xs font-semibold rounded bg-green-100 text-green-700 border border-green-300 gap-1">
                        <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4 mr-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 12h14M12 5l7 7-7 7'/></svg>
                        Login
                    </span>` :
                row.event_type === 'logout' ? `
                    <span class="inline-flex items-center justify-center min-w-[90px] min-h-[30px] px-3 py-1 text-xs font-semibold rounded bg-red-100 text-red-700 border border-red-300 gap-1">
                        <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4 mr-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 12H5m7-7l-7 7 7 7'/></svg>
                        Logout
                    </span>` :
                `<span class=\"inline-flex items-center justify-center min-w-[90px] min-h-[30px] px-3 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-700 border border-gray-300 gap-1\">${row.event_type ?? ''}</span>`}
            </td>
            <td class="px-3 py-2 text-xs text-center">
                ${row.username ? `<span class=\"inline-flex items-center justify-center min-w-[90px] min-h-[30px] px-3 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-700 border border-blue-300 gap-1\"><svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4 mr-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0zm-6 6a9 9 0 1112 0H9z' /></svg>${row.username}</span>` : ''}
            </td>
            <td class="px-3 py-2 text-xs text-center">
                ${row.ip_address ? `<span class=\"inline-flex items-center justify-center min-w-[90px] min-h-[30px] px-3 py-1 text-xs font-semibold rounded bg-blue-50 text-blue-800 border border-blue-200 gap-1\"><svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4 mr-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4v16m8-8H4' /></svg>${row.ip_address}</span>` : ''}
            </td>
            <td class="px-3 py-2 text-xs text-center">
                ${row.caller_id ? `<span class=\"inline-flex items-center justify-center min-w-[90px] min-h-[30px] px-3 py-1 text-xs font-semibold rounded bg-purple-100 text-purple-700 border border-purple-300 gap-1\"><svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4 mr-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm0 8a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2zm8-8a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zm0 8a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' /></svg>${row.caller_id}</span>` : ''}
            </td>
            <td class="px-3 py-2 text-xs text-center">
                ${row.active_client ? `<span class=\"inline-flex items-center justify-center min-w-[90px] min-h-[30px] px-3 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-700 border border-yellow-300 gap-1\"><svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4 mr-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9.75 17L9 21m5.25-4l.75 4m-8.25 0h12a2 2 0 002-2v-7a2 2 0 00-2-2h-4l-2-4-2 4H5a2 2 0 00-2 2v7a2 2 0 002 2z' /></svg>${row.active_client}</span>` : ''}
            </td>
            <td class="px-3 py-2 text-xs text-center">
                ${row.event_date ? `<span class=\"inline-flex items-center justify-center min-w-[90px] min-h-[30px] px-3 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-700 border border-gray-300 gap-1\"><svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4 mr-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' /></svg>${row.event_date}</span>` : ''}
            </td>
            <td class="px-3 py-2 text-xs text-center">
                ${row.event_time ? `<span class=\"inline-flex items-center justify-center min-w-[90px] min-h-[30px] px-3 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-700 border border-gray-300 gap-1\"><svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4 mr-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>${row.event_time}</span>` : ''}
            </td>
            <td class="px-3 py-2 text-xs text-center">
                ${row.username ? `<button class='detail-btn bg-indigo-500 hover:bg-indigo-600 text-white text-xs px-3 py-1 rounded shadow flex items-center justify-center gap-1' data-username='${row.username}'>
  <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16h-1v-4h-1m1-4h.01M12 20.5c4.142 0 7.5-3.358 7.5-7.5S16.142 5.5 12 5.5 4.5 8.858 4.5 13s3.358 7.5 7.5 7.5z' />
  </svg>
  Detail
</button>` : ''}
            </td>
        </tr>`;
    });
}

function fetchLogsAndUpdate() {
    const params = getFilterParams();
    fetch('get-logs.php?' + params)
        .then(async res => {
            const text = await res.text();
            try {
                const data = JSON.parse(text);
                if (data.logs) {
                    renderLogTable(data.logs, data.offset || 0);
                    renderPagination(data.totalPages || 1, data.page || 1);
                }
            } catch (e) {
                // Jika gagal parse JSON, kemungkinan session expired
                alert('Session expired atau terjadi error. Silakan login ulang.');
                window.location.href = 'login.php';
            }
        });
}

// Dropdown perPage dinamis
const perPageSelect = document.getElementById('perPage');
if (perPageSelect) {
    perPageSelect.addEventListener('change', function() {
        window.currentPage = 1;
        fetchLogsAndUpdate();
    });
}

// Pagination dinamis
document.addEventListener('click', function(e) {
    if (e.target.matches('.pagination-link')) {
        e.preventDefault();
        const page = parseInt(e.target.dataset.page);
        if (!isNaN(page)) {
            window.currentPage = page;
            fetchLogsAndUpdate();
        }
    }
});

// Modal detail user activity
const modalDetail = document.getElementById('modalDetail');
const modalDetailContent = document.getElementById('modalDetailContent');
const closeModalDetail = document.getElementById('closeModalDetail');

document.addEventListener('click', function(e) {
    // Tombol Detail
    if (e.target.classList.contains('detail-btn')) {
        const username = e.target.getAttribute('data-username');
        if (!username) return;
        modalDetail.classList.remove('hidden');
        modalDetailContent.innerHTML = '<div class="flex items-center justify-center py-8 text-gray-400">Memuat...</div>';
        // Fetch aktivitas user hari ini
        fetch('user-activity.php?username=' + encodeURIComponent(username))
            .then(res => {
                if (!res.ok) throw new Error('HTTP status ' + res.status);
                return res.json();
            })
            .then(data => {
                if (data.error) {
                    modalDetailContent.innerHTML = '<div class="text-red-500">' + data.error + '</div>';
                } else {
                    modalDetailContent.innerHTML = `
                        <div class="flex flex-col items-center p-2">
                            <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center mb-2 shadow-lg animate-modal-pop">
                                <span class="text-2xl font-bold text-indigo-600">${username.slice(0,2).toUpperCase()}</span>
                            </div>
                            <div class="mb-1 text-lg font-bold text-indigo-700">${username}</div>
                            <div class="mb-2">
    <span class="inline-flex items-center px-2 py-1 rounded font-bold text-xs transition 
        ${data.logout <= 2 ? 'bg-green-100 text-green-700' : data.logout == 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'}"
        title="Status logout hari ini berdasarkan jumlah logout yang terdeteksi.">
        <span class="mr-1">
            ${data.logout <= 2 ? '✔️' : data.logout == 3 ? '⚠️' : '❌'}
        </span> 
        ${data.logout <= 2 ? 'Aman' : data.logout == 3 ? 'Perlu Diperhatikan' : 'Ada Kendala'}
    </span>
</div>
                            <div class="mb-2 text-xs text-gray-500 flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>${data.date}</span>
                            </div>
                            <div class="w-full flex flex-col gap-2">
                                <div class="flex items-center justify-between bg-green-50 rounded px-3 py-2">
                                    <span class="flex items-center gap-1 font-semibold text-green-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/></svg>Login hari ini</span>
                                    <span class="text-green-700 font-bold text-lg">${data.login}</span>
                                </div>
                                <div class="flex items-center justify-between bg-red-50 rounded px-3 py-2">
                                    <span class="flex items-center gap-1 font-semibold text-red-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7-7l-7 7 7 7"/></svg>Logout hari ini</span>
                                    <span class="text-red-700 font-bold text-lg">${data.logout}</span>
                                </div>
                            </div>
                            <div class="w-full border-t my-3"></div>
                            <div class="w-full flex items-center justify-between">
                                <span class="text-xs text-gray-400">Total aktivitas hari ini</span>
                                <span class="text-indigo-700 font-bold text-lg">${data.total}</span>
                            </div>
                            <div class="w-full mt-4">
    <canvas id="userActivityChart" height="60"></canvas>
    <div class="text-xs text-gray-400 mt-1 text-center">Grafik login/logout 7 hari terakhir</div>
    <div class="text-xs text-gray-500 mt-1 text-center italic">Angka di atas bar menunjukkan jumlah login/logout per hari.</div>
    <div class="text-xs text-gray-500 mt-1 text-center italic">Angka di atas bar menunjukkan jumlah login/logout per hari.</div>
</div>
                        </div>
                    `;
                    // Fetch grafik data
                    fetch('user-activity-history.php?username=' + encodeURIComponent(username))
                        .then(res => res.json())
                        .then(chartData => {
                            setTimeout(() => {
                                // Kosongkan parent sebelum tambah canvas baru
                                const parent = modalDetailContent.querySelector('.w-full.mt-4');
                                if (parent) {
                                    parent.innerHTML = '';
                                    parent.style.height = '';
                                    parent.style.overflow = '';
                                }
                                // Tambahkan canvas baru
                                const newCanvas = document.createElement('canvas');
                                newCanvas.id = 'userActivityChart';
                                newCanvas.width = 400;
                                newCanvas.height = 120;
                                newCanvas.style.width = '100%';
                                newCanvas.style.height = '120px';
                                if (parent) parent.appendChild(newCanvas);
                                // Tambahkan keterangan grafik
                                const caption1 = document.createElement('div');
                                caption1.className = 'text-xs text-gray-400 mt-1 text-center';
                                caption1.textContent = 'Grafik login/logout 7 hari terakhir';
                                parent.appendChild(caption1);
                                const caption2 = document.createElement('div');
                                caption2.className = 'text-xs text-gray-500 mt-1 text-center italic';
                                caption2.textContent = 'Angka di atas bar menunjukkan jumlah login/logout per hari.';
                                parent.appendChild(caption2);
                                const ctx = newCanvas.getContext('2d');
                                if (window.userActivityChartInst) window.userActivityChartInst.destroy();
                                window.userActivityChartInst = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.map(d => d.date.slice(5)),
        datasets: [
            {
                label: 'Login',
                data: chartData.map(d => d.login),
                backgroundColor: 'rgba(16,185,129,0.7)',
                borderRadius: 4,
            },
            {
                label: 'Logout',
                data: chartData.map(d => d.logout),
                backgroundColor: 'rgba(239,68,68,0.7)',
                borderRadius: 4,
            }
        ]
    },
    options: {
        plugins: {
            legend: { display: true },
            datalabels: {
                anchor: 'end',
                align: 'end',
                color: '#222',
                font: { weight: 'bold', size: 12 },
                formatter: function(value) {
                    return value > 0 ? value : '';
                }
            }
        },
        scales: {
            x: { grid: {display:false} },
            y: { beginAtZero: true, grid: {display:false}, suggestedMax: 15 }
        },
        responsive: false,
        maintainAspectRatio: true,
    },
    plugins: [ChartDataLabels]
});
                            }, 0);
                        });
                    // Animasi modal
                    const modalBox = modalDetail.querySelector('div');
                    if (modalBox) modalBox.classList.add('animate-modal-pop');
                }
            })
            .catch(err => {
                modalDetailContent.innerHTML = '<div class="text-red-500">Gagal mengambil data aktivitas user: ' + err + '</div>';
            });
    }
    // Tutup modal
    if (e.target === modalDetail || e.target === closeModalDetail) {
        modalDetail.classList.add('hidden');
    }
});

// Polling tiap 5 detik
setInterval(fetchLogsAndUpdate, 5000);
// Juga jalankan sekali saat load
fetchLogsAndUpdate();
</script>
        </div>
        <!-- Pagination -->
        <?php
// Build filter query string for pagination links
$filterQuery = '';
if ($filterStart) $filterQuery .= '&start=' . urlencode($filterStart);
if ($filterEnd) $filterQuery .= '&end=' . urlencode($filterEnd);
if ($filterEvent) $filterQuery .= '&event=' . urlencode($filterEvent);
if ($filterUsername) $filterQuery .= '&username=' . urlencode($filterUsername);
?>
<div class="flex justify-center mt-6 gap-2">
    <?php if($page > 1): ?>
        <a href="#" class="pagination-link px-3 py-1 rounded bg-indigo-500 text-white hover:bg-indigo-600" data-page="<?php echo $page-1; ?>">&laquo; Prev</a>
    <?php endif; ?>
    <?php for($i=1;$i<=$totalPages;$i++): ?>
        <a href="#" class="pagination-link px-3 py-1 rounded <?php echo $i==$page?'bg-indigo-700 text-white':'bg-gray-200 text-indigo-700 hover:bg-indigo-300'; ?>" data-page="<?php echo $i; ?>"> <?php echo $i; ?> </a>
    <?php endfor; ?>
    <?php if($page < $totalPages): ?>
        <a href="#" class="pagination-link px-3 py-1 rounded bg-indigo-500 text-white hover:bg-indigo-600" data-page="<?php echo $page+1; ?>">Next &raquo;</a>
    <?php endif; ?>
</div>        </div>
    </div>
<script>
// Client-side search/filter
const searchInput = document.getElementById('searchInput');
if(searchInput){
  searchInput.addEventListener('keyup', function() {
    var filter = this.value.toLowerCase();
    var rows = document.querySelectorAll('tbody tr');
    rows.forEach(function(row) {
      var text = row.textContent.toLowerCase();
      row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
    });
  });
}
</script>
</body>
</html>
