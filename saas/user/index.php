<?php
// user/index.php - Dashboard pengguna untuk sistem SaaS
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/Auth.php';

// Mulai sesi
session_start();

// Inisialisasi Auth
$auth = new Auth();

// Cek apakah pengguna sudah login
$userData = $auth->isLoggedIn();
if (!$userData) {
    // Redirect ke halaman login
    header('Location: ../login.php');
    exit;
}

// Inisialisasi database
$db = Database::getInstance();

// Ambil pengaturan pengguna
$userSettings = $db->fetchOne("SELECT * FROM user_settings WHERE user_id = ?", [$userData['user_id']]);

// Ambil data statistik log
$today = date('Y-m-d');
$todayLoginCount = $db->fetchColumn(
    "SELECT COUNT(*) FROM pppoe_logs WHERE user_id = ? AND event_type = 'login' AND event_date = ?", 
    [$userData['user_id'], $today]
);
$todayLogoutCount = $db->fetchColumn(
    "SELECT COUNT(*) FROM pppoe_logs WHERE user_id = ? AND event_type = 'logout' AND event_date = ?", 
    [$userData['user_id'], $today]
);

// Ambil log terbaru (10 terakhir)
$recentLogs = $db->fetchAll(
    "SELECT * FROM pppoe_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10", 
    [$userData['user_id']]
);

// Proses update pengaturan
$updateMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $groupId = $_POST['group_id'] ?? '';
    
    // Update pengaturan
    $db->update(
        'user_settings',
        ['group_id' => $groupId],
        'user_id = ?',
        [$userData['user_id']]
    );
    
    // Refresh data
    $userSettings = $db->fetchOne("SELECT * FROM user_settings WHERE user_id = ?", [$userData['user_id']]);
    $updateMessage = 'Pengaturan berhasil diperbarui';
}

// Ambil script Mikrotik dengan token pengguna
$mikrotikScript = file_get_contents('../mikrotik_script.txt');
$mikrotikScript = str_replace('rahasia123', $userSettings['token'] ?? 'TOKEN_TIDAK_DITEMUKAN', $mikrotikScript);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WhatsApp Notification Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-green-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <span class="font-bold text-xl">WhatsApp Notification Panel</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="hidden md:inline-block"><?php echo htmlspecialchars($userData['name']); ?></span>
                <a href="../logout.php" class="hover:underline">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6">
        <!-- Welcome Message -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Selamat Datang, <?php echo htmlspecialchars($userData['name']); ?>!</h1>
            <p class="text-gray-600">Kelola notifikasi WhatsApp untuk Mikrotik Anda di sini.</p>
        </div>

        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Login Today -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Login Hari Ini</p>
                        <p class="text-2xl font-bold"><?php echo $todayLoginCount; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Logout Today -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-500 mr-4">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Logout Hari Ini</p>
                        <p class="text-2xl font-bold"><?php echo $todayLogoutCount; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Status -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Status Akun</p>
                        <p class="text-2xl font-bold"><?php echo ucfirst($userData['status']); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Token -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                        <i class="fas fa-key"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Token</p>
                        <p class="text-lg font-bold truncate" title="<?php echo htmlspecialchars($userSettings['token'] ?? 'Tidak ada'); ?>">
                            <?php echo htmlspecialchars($userSettings['token'] ?? 'Tidak ada'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Settings -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Pengaturan</h2>
                
                <?php if ($updateMessage): ?>
                    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                        <?php echo $updateMessage; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-4">
                    <div>
                        <label for="group_id" class="block text-gray-700 font-medium mb-2">ID Grup WhatsApp</label>
                        <input type="text" id="group_id" name="group_id" 
                               value="<?php echo htmlspecialchars($userSettings['group_id'] ?? ''); ?>" 
                               placeholder="Contoh: 120363408186330281@g.us" 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <p class="text-sm text-gray-500 mt-1">ID grup WhatsApp yang akan menerima notifikasi</p>
                    </div>
                    
                    <input type="hidden" name="update_settings" value="1">
                    <button type="submit" class="bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition duration-200">
                        Simpan Pengaturan
                    </button>
                </form>
            </div>

            <!-- Mikrotik Script -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Script Mikrotik</h2>
                <p class="text-gray-600 mb-4">Copy script berikut ke Mikrotik Anda untuk mengaktifkan notifikasi:</p>
                
                <div class="relative">
                    <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto"><?php echo htmlspecialchars($mikrotikScript); ?></pre>
                    <button id="copyBtn" class="absolute top-2 right-2 bg-green-600 text-white p-2 rounded hover:bg-green-700">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <p class="text-sm text-gray-500 mt-2">Script ini sudah berisi token unik Anda.</p>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Log Aktivitas Terbaru</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left">Tanggal</th>
                            <th class="py-2 px-4 text-left">Waktu</th>
                            <th class="py-2 px-4 text-left">Event</th>
                            <th class="py-2 px-4 text-left">Username</th>
                            <th class="py-2 px-4 text-left">IP / Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentLogs)): ?>
                            <tr>
                                <td colspan="5" class="py-4 px-4 text-center text-gray-500">Belum ada data log</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentLogs as $log): ?>
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($log['event_date']); ?></td>
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($log['event_time']); ?></td>
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
                <a href="logs.php" class="text-green-600 hover:underline">Lihat Semua Log</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 mt-6">
        <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
            &copy; <?php echo date('Y'); ?> WhatsApp Notification Panel SaaS. All rights reserved.
        </div>
    </footer>

    <script>
        // Script untuk copy to clipboard
        document.getElementById('copyBtn').addEventListener('click', function() {
            const scriptText = document.querySelector('pre').textContent;
            navigator.clipboard.writeText(scriptText).then(function() {
                alert('Script berhasil disalin!');
            }, function() {
                alert('Gagal menyalin script');
            });
        });
    </script>
</body>
</html>
