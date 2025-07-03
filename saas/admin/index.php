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
                <a href="settings.php" class="hover:underline">
                    <i class="fas fa-cog mr-1"></i> Pengaturan
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
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Daftar Pengguna</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left">ID</th>
                            <th class="py-2 px-4 text-left">Nama</th>
                            <th class="py-2 px-4 text-left">Nomor HP</th>
                            <th class="py-2 px-4 text-left">Email</th>
                            <th class="py-2 px-4 text-left">Status</th>
                            <th class="py-2 px-4 text-left">Terdaftar</th>
                            <th class="py-2 px-4 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="py-4 px-4 text-center text-gray-500">Belum ada pengguna terdaftar</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="py-2 px-4"><?php echo $user['id']; ?></td>
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                                    <td class="py-2 px-4">
                                        <?php if ($user['status'] == 'active'): ?>
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Aktif</span>
                                        <?php elseif ($user['status'] == 'pending'): ?>
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Menunggu</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Diblokir</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 px-4"><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                    <td class="py-2 px-4">
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="update_status" value="1">
                                            
                                            <?php if ($user['status'] != 'active'): ?>
                                                <button type="submit" name="status" value="active" class="text-green-600 hover:text-green-800 mr-2" title="Aktifkan">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($user['status'] != 'suspended'): ?>
                                                <button type="submit" name="status" value="suspended" class="text-red-600 hover:text-red-800 mr-2" title="Blokir">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <a href="view_user.php?id=<?php echo $user['id']; ?>" class="text-blue-600 hover:text-blue-800" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Log Aktivitas Terbaru</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left">Tanggal</th>
                            <th class="py-2 px-4 text-left">Waktu</th>
                            <th class="py-2 px-4 text-left">Pengguna</th>
                            <th class="py-2 px-4 text-left">Event</th>
                            <th class="py-2 px-4 text-left">Username</th>
                            <th class="py-2 px-4 text-left">IP / Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentLogs)): ?>
                            <tr>
                                <td colspan="6" class="py-4 px-4 text-center text-gray-500">Belum ada data log</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentLogs as $log): ?>
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($log['event_date']); ?></td>
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($log['event_time']); ?></td>
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($log['user_name']); ?></td>
                                    <td class="py-2 px-4">
                                        <?php if ($log['event_type'] == 'login'): ?>
                                            <span class="text-green-600 font-medium">Login</span>
                                        <?php else: ?>
                                            <span class="text-red-600 font-medium">Logout</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($log['username']); ?></td>
                                    <td class="py-2 px-4">
                                        <?php 
                                        if ($log['event_type'] == 'login') {
                                            echo htmlspecialchars($log['ip_address'] ?? '-');
                                        } else {
                                            echo htmlspecialchars($log['last_disconnect_reason'] ?? '-');
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 text-right">
                <a href="logs.php" class="text-indigo-600 hover:underline">Lihat Semua Log</a>
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
