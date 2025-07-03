<?php
// admin/logs.php - Daftar lengkap log aktivitas PPPoE
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
// Ambil semua user untuk dropdown filter
$allUsers = $db->fetchAll('SELECT id, name FROM users ORDER BY name');

// Filter
$filterUser = isset($_GET['user_id']) ? intval($_GET['user_id']) : '';
$filterStart = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filterEnd = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$filterUsername = isset($_GET['username']) ? $_GET['username'] : '';
$filterEvent = isset($_GET['event_type']) ? $_GET['event_type'] : '';

// Dropdown username: ambil username unik dari log 7 hari terakhir
$usernameOptions = $db->fetchAll("SELECT DISTINCT username FROM pppoe_logs WHERE event_date >= ? ORDER BY username", [date('Y-m-d', strtotime('-7 days'))]);

$where = [];
$params = [];
if ($filterUser) {
    $where[] = 'p.user_id = ?';
    $params[] = $filterUser;
}
if ($filterStart) {
    $where[] = 'p.event_date >= ?';
    $params[] = $filterStart;
}
if ($filterEnd) {
    $where[] = 'p.event_date <= ?';
    $params[] = $filterEnd;
}
if ($filterUsername) {
    $where[] = 'p.username = ?';
    $params[] = $filterUsername;
}
if ($filterEvent && in_array($filterEvent, ['login','logout'])) {
    $where[] = 'p.event_type = ?';
    $params[] = $filterEvent;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 30;
$offset = ($page - 1) * $perPage;

// Total logs untuk pagination
$countSql = "SELECT COUNT(*) FROM pppoe_logs p 
    JOIN users u ON p.user_id = u.id $whereSql";
$totalLogs = $db->fetchColumn($countSql, $params);
$totalPages = ceil($totalLogs / $perPage);

// Query log dengan filter dan paginasi
$logs = $db->fetchAll(
    "SELECT p.*, u.name as user_name FROM pppoe_logs p 
     JOIN users u ON p.user_id = u.id 
     $whereSql
     ORDER BY p.created_at DESC LIMIT ? OFFSET ?",
    array_merge($params, [$perPage, $offset])
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Log Aktivitas - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-indigo-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <span class="font-bold text-xl">Admin Dashboard</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="hover:underline"><i class="fas fa-tachometer-alt mr-1"></i> Dashboard</a>
                <a href="statistik.php" class="hover:underline"><i class="fas fa-chart-bar mr-1"></i> Statistik</a>
                <a href="logs.php" class="hover:underline font-bold underline"><i class="fas fa-list mr-1"></i> Semua Log</a>
                <a href="../logout.php" class="hover:underline"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-list text-indigo-500 mr-2"></i>Semua Log Aktivitas PPPoE</h2>
    <!-- Filter Form -->
<form method="get" class="flex flex-wrap items-end gap-3 mb-4 bg-white rounded-lg shadow-sm p-4">
    <div>
        <label class="block text-xs text-gray-600 mb-1">User</label>
        <select name="user_id" class="border rounded px-2 py-1 text-sm">
            <option value="">Semua</option>
            <?php foreach ($allUsers as $u): ?>
                <option value="<?php echo $u['id']; ?>" <?php if ($filterUser==$u['id']) echo 'selected'; ?>><?php echo htmlspecialchars($u['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-600 mb-1">Username PPPoE</label>
        <select name="username" class="border rounded px-2 py-1 text-sm">
            <option value="">Semua</option>
            <?php foreach ($usernameOptions as $opt): ?>
                <option value="<?php echo htmlspecialchars($opt['username']); ?>" <?php if ($filterUsername==$opt['username']) echo 'selected'; ?>><?php echo htmlspecialchars($opt['username']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-600 mb-1">Event</label>
        <select name="event_type" class="border rounded px-2 py-1 text-sm">
            <option value="">Semua</option>
            <option value="login" <?php if($filterEvent==='login') echo 'selected'; ?>>Login</option>
            <option value="logout" <?php if($filterEvent==='logout') echo 'selected'; ?>>Logout</option>
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-600 mb-1">Dari Tanggal</label>
        <input type="date" name="start_date" value="<?php echo htmlspecialchars($filterStart); ?>" class="border rounded px-2 py-1 text-sm">
    </div>
    <div>
        <label class="block text-xs text-gray-600 mb-1">Sampai Tanggal</label>
        <input type="date" name="end_date" value="<?php echo htmlspecialchars($filterEnd); ?>" class="border rounded px-2 py-1 text-sm">
    </div>
    <div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition text-sm"><i class="fas fa-filter mr-1"></i>Filter</button>
    </div>
</form>
    <div class="bg-white rounded-lg shadow-md p-6 mb-4">
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs rounded-xl shadow-lg overflow-hidden">
                <thead class="bg-indigo-50 sticky top-0 z-10">
                    <tr>
                        <th class="py-3 px-4 text-left font-bold text-gray-700">Tanggal</th>
                        <th class="py-3 px-4 text-left font-bold text-gray-700">Jam</th>
                        <th class="py-3 px-4 text-left font-bold text-gray-700">Nama User</th>
                        <th class="py-3 px-4 text-left font-bold text-gray-700">Event</th>
                        <th class="py-3 px-4 text-left font-bold text-gray-700">Username</th>
                        <th class="py-3 px-4 text-left font-bold text-gray-700">IP / Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
<?php
// Helper: format tanggal ke Indonesia
function tanggal_indo($tgl) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $exp = explode('-', $tgl);
    if (count($exp) === 3) {
        return ltrim($exp[2], '0') . ' ' . $bulan[(int)$exp[1]] . ' ' . $exp[0];
    } else {
        return $tgl;
    }
}
// Query user+username+date yang bermasalah (login >= 5 dalam 1 hari, 7 hari terakhir)
$problemLogArr = [];
$problemRows = $db->fetchAll("SELECT user_id, username, event_date, COUNT(id) as total_login FROM pppoe_logs WHERE event_type = 'login' AND event_date >= ? GROUP BY user_id, username, event_date HAVING total_login >= 5", [date('Y-m-d', strtotime('-6 days'))]);
foreach ($problemRows as $row) {
    $problemLogArr[$row['user_id'].'|'.$row['username'].'|'.$row['event_date']] = true;
}
?>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="py-4 px-4 text-center text-gray-500">Belum ada data log</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $isProblem = isset($problemLogArr[$log['user_id'].'|'.$log['username'].'|'.$log['event_date']]);
                            ?>
                            <tr class="transition hover:bg-indigo-50 <?php if($isProblem) echo 'bg-pink-50/80'; ?>">
                                <td class="py-2.5 px-4 font-mono text-gray-600"><?php echo tanggal_indo($log['event_date']); ?></td>
                                <td class="py-2.5 px-4 font-mono text-blue-700"><?php echo htmlspecialchars($log['event_time']); ?></td>
                                <td class="py-2.5 px-4 text-gray-800 font-semibold">
                                    <div class="flex items-center gap-2">
                                        <span><?php echo htmlspecialchars($log['user_name']); ?></span>
                                        <?php if($isProblem): ?>
                                            <span class="ml-2 px-2 py-0.5 rounded-full bg-pink-500 text-white text-[10px] font-bold shadow align-middle animate-pulse">Sering Bermasalah</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-2.5 px-4">
                                    <?php if ($log['event_type'] == 'login'): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-50 text-green-700 font-semibold text-xs"><i class="fas fa-sign-in-alt"></i>Login</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-50 text-red-700 font-semibold text-xs"><i class="fas fa-sign-out-alt"></i>Logout</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2.5 px-4 font-mono text-indigo-700"><?php echo htmlspecialchars($log['username']); ?></td>
                                <td class="py-2.5 px-4">
                                    <?php 
                                    if ($log['event_type'] == 'login') {
                                        echo '<span class="inline-flex items-center gap-1"><i class="fas fa-network-wired text-green-400"></i> ' . htmlspecialchars($log['ip_address'] ?? '-') . '</span>';
                                    } else {
                                        echo '<span class="inline-flex items-center gap-1"><i class="fas fa-exclamation-circle text-pink-400"></i> ' . htmlspecialchars($log['last_disconnect_reason'] ?? '-') . '</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="flex justify-between items-center mt-4">
            <div class="text-xs text-gray-500">Halaman <?php echo $page; ?> dari <?php echo $totalPages; ?></div>
            <div class="space-x-1">
                <?php 
                $queryStr = http_build_query(array_merge($_GET, ['page' => max(1, $page-1)]));
                if ($page > 1): ?>
                    <a href="logs.php?<?php echo $queryStr; ?>" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200">&laquo; Prev</a>
                <?php endif; ?>
                <?php 
                $queryStr = http_build_query(array_merge($_GET, ['page' => $page+1]));
                if ($page < $totalPages): ?>
                    <a href="logs.php?<?php echo $queryStr; ?>" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <a href="index.php" class="inline-block mt-2 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition"><i class="fas fa-arrow-left mr-1"></i>Kembali ke Dashboard</a>
</div>
    <footer class="bg-white py-4 mt-6">
        <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
            &copy; <?php echo date('Y'); ?> WhatsApp Notification Panel SaaS. All rights reserved.
        </div>
    </footer>
</body>
</html>
