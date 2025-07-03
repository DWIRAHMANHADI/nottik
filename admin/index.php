<?php
// admin/index.php - Dashboard admin untuk sistem SaaS
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/Auth.php';

// Mulai sesi
session_start();

// Inisialisasi Auth
$auth = new Auth();

// Cek apakah admin sudah login
if (!$auth->isAdminLoggedIn()) {
    // Redirect ke halaman login
    header('Location: ../login.php');
    exit;
}

// Inisialisasi database
$db = Database::getInstance();

// Ambil data pengguna
$users = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");

// Ambil statistik
$totalUsers = count($users);
$pendingUsers = $db->fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'pending'");
$activeUsers = $db->fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'active'");
$suspendedUsers = $db->fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'suspended'");

// Ambil log aktivitas terbaru (10 terakhir)
$recentLogs = $db->fetchAll(
    "SELECT p.*, u.name as user_name FROM pppoe_logs p 
     JOIN users u ON p.user_id = u.id 
     ORDER BY p.created_at DESC LIMIT 10"
);

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

// Proses update status pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $userId = $_POST['user_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    if ($userId && in_array($status, ['pending', 'active', 'suspended'])) {
        $db->update('users', ['status' => $status], 'id = ?', [$userId]);
        
        // Refresh data
        $users = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");
        $pendingUsers = $db->fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'pending'");
        $activeUsers = $db->fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'active'");
        $suspendedUsers = $db->fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'suspended'");
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - WhatsApp Notification Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-indigo-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <span class="font-bold text-xl">Admin Dashboard</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="hover:underline"><i class="fas fa-tachometer-alt mr-1"></i> Dashboard</a>
                <a href="statistik.php" class="hover:underline"><i class="fas fa-chart-bar mr-1"></i> Statistik</a>
                <a href="settings.php" class="hover:underline">
                    <i class="fas fa-cog mr-1"></i> Pengaturan
                </a>
                <a href="profile.php" class="hover:underline">
                    <i class="fas fa-user mr-1"></i> Profil
                </a>
                <a href="../logout.php" class="hover:underline">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6">
        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Users -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Total Pengguna</p>
                        <p class="text-2xl font-bold"><?php echo $totalUsers; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Pending Users -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Menunggu Approval</p>
                        <p class="text-2xl font-bold"><?php echo $pendingUsers; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Active Users -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Pengguna Aktif</p>
                        <p class="text-2xl font-bold"><?php echo $activeUsers; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Suspended Users -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-500 mr-4">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Pengguna Diblokir</p>
                        <p class="text-2xl font-bold"><?php echo $suspendedUsers; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-users text-indigo-400"></i>Daftar Pengguna</h2>
            <div class="overflow-x-auto bg-white rounded-xl shadow">
                <table class="min-w-full text-sm">
                    <thead class="bg-indigo-50">
                        <tr>
                            <th class="py-3 px-4 text-left font-bold text-gray-700">ID</th>
                            <th class="py-3 px-4 text-left font-bold text-gray-700">Nama</th>
                            <th class="py-3 px-4 text-left font-bold text-gray-700">HP</th>
                            <th class="py-3 px-4 text-left font-bold text-gray-700">Email</th>
                            <th class="py-3 px-4 text-left font-bold text-gray-700">Tanggal Daftar</th>
                            <th class="py-3 px-4 text-left font-bold text-gray-700">Status</th>
                            <th class="py-3 px-4 text-left font-bold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-gray-500">Belum ada pengguna terdaftar</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr class="border-b last:border-b-0 hover:bg-indigo-50/50 transition <?php echo ($user['status'] == 'suspended') ? 'bg-red-50/30' : 'bg-white'; ?>">
                            <td class="py-2 px-4 font-mono text-xs text-gray-500"><?php echo $user['id']; ?></td>
                            <td class="py-2 px-4 font-semibold text-gray-800">
                                <div class="flex items-center gap-2">
                                    <?php if ($user['status'] == 'active'): ?>
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-green-100 text-green-600 text-base shadow"><i class="fas fa-user-check"></i></span>
                                    <?php elseif ($user['status'] == 'pending'): ?>
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-yellow-100 text-yellow-600 text-base shadow"><i class="fas fa-user-clock"></i></span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-red-100 text-red-600 text-base shadow"><i class="fas fa-user-slash"></i></span>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($user['name']); ?></span>
                                </div>
                            </td>
                            <td class="py-2 px-4 text-green-700 font-mono"><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td class="py-2 px-4 text-blue-700 font-mono"><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                            <td class="py-2 px-4 text-gray-700"><?php echo tanggal_indo(substr($user['created_at'],0,10)); ?></td>
                            <td class="py-2 px-4">
                                <?php if ($user['status'] == 'active'): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Aktif</span>
                                <?php elseif ($user['status'] == 'pending'): ?>
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">Menunggu</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">Diblokir</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-2 px-4">
                                <div class="flex gap-2">
                                    <a href="view_user.php?id=<?php echo $user['id']; ?>" class="inline-block px-3 py-1.5 rounded bg-indigo-600 text-white text-xs font-semibold shadow hover:bg-indigo-700 transition text-center" title="Lihat Detail"><i class="fas fa-eye mr-1"></i>Detail</a>
                                    <form method="POST" class="inline-block">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <?php if ($user['status'] != 'active'): ?>
                                            <button type="submit" name="status" value="active" class="inline-block px-3 py-1.5 rounded bg-green-100 text-green-800 font-semibold text-xs shadow hover:bg-green-200 transition" title="Aktifkan"><i class="fas fa-check-circle mr-1"></i>Aktifkan</button>
                                        <?php endif; ?>
                                        <?php if ($user['status'] != 'suspended'): ?>
                                            <button type="submit" name="status" value="suspended" class="inline-block px-3 py-1.5 rounded bg-red-100 text-red-800 font-semibold text-xs shadow hover:bg-red-200 transition" title="Blokir"><i class="fas fa-ban mr-1"></i>Blokir</button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="bg-gradient-to-br from-white/90 to-indigo-50/60 rounded-xl shadow-lg p-6 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
            <h2 class="text-xl font-bold text-indigo-700 mb-4 flex items-center gap-2"><i class="fas fa-history text-indigo-400"></i>Log Aktivitas Terbaru</h2>
            <div class="space-y-2">
                <?php if (empty($recentLogs)): ?>
                    <div class="text-center text-gray-500 py-8">Belum ada data log</div>
                <?php else: ?>
                    <?php foreach ($recentLogs as $log): ?>
                        <div class="flex items-center bg-white/80 rounded-lg px-4 py-2 shadow-sm hover:bg-indigo-50/80 transition group">
                            <div class="flex-shrink-0 mr-3">
                                <?php if ($log['event_type'] == 'login'): ?>
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-600 text-lg shadow group-hover:scale-110 transition"><i class="fas fa-sign-in-alt"></i></span>
                                <?php else: ?>
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-600 text-lg shadow group-hover:scale-110 transition"><i class="fas fa-sign-out-alt"></i></span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-x-2 text-sm">
                                    <span class="font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($log['user_name']); ?>
                                    </span>
                                    <span class="text-gray-400">&bull;</span>
                                    <span class="text-gray-600 font-mono">
                                        <?php echo htmlspecialchars($log['username']); ?>
                                    </span>
                                    <span class="text-gray-400">&bull;</span>
                                    <span class="<?php echo $log['event_type'] == 'login' ? 'text-green-600' : 'text-red-600'; ?> font-semibold">
                                        <?php echo ucfirst($log['event_type']); ?>
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-x-2 text-xs mt-0.5 text-gray-500">
                                    <span><i class="far fa-calendar-alt mr-1"></i><?php echo tanggal_indo($log['event_date']); ?></span>
                                    <span><i class="far fa-clock mr-1"></i><?php echo htmlspecialchars($log['event_time']); ?></span>
                                    <?php if ($log['event_type'] == 'login'): ?>
                                        <span><i class="fas fa-network-wired mr-1"></i><?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?></span>
                                    <?php else: ?>
                                        <span><i class="fas fa-exclamation-circle mr-1"></i><?php echo htmlspecialchars($log['last_disconnect_reason'] ?? '-'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="mt-4 text-right">
                <a href="logs.php" class="inline-block px-4 py-2 rounded bg-indigo-600 text-white font-semibold shadow hover:bg-indigo-700 transition"><i class="fas fa-list mr-1"></i>Lihat Semua Log</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 mt-6">
        <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
            &copy; <?php echo date('Y'); ?> WhatsApp Notification Panel SaaS. All rights reserved.
        </div>
    </footer>
</body>
</html>
