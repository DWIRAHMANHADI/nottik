<?php
// admin/settings.php - Halaman pengaturan WhatsApp untuk admin
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/Auth.php';
require_once '../WhatsAppClient.php';

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

// Ambil pengaturan WhatsApp
$whatsappSettings = $db->fetchOne("SELECT * FROM whatsapp_settings LIMIT 1");

// Inisialisasi WhatsApp client
$whatsapp = new WhatsAppClient(
    $whatsappSettings['api_url'] ?? DEFAULT_WHATSAPP_API_URL,
    $whatsappSettings['api_user'] ?? DEFAULT_WHATSAPP_API_USER,
    $whatsappSettings['api_pass'] ?? DEFAULT_WHATSAPP_API_PASS
);

// Pesan status
$statusMessage = '';
$errorMessage = '';
$successMessage = '';

// Proses update pengaturan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $apiUrl = $_POST['api_url'] ?? '';
    $apiUser = $_POST['api_user'] ?? '';
    $apiPass = $_POST['api_pass'] ?? '';
    
    // Validasi input
    if (empty($apiUrl)) {
        $errorMessage = 'URL API WhatsApp harus diisi';
    } else {
        // Update pengaturan
        if ($whatsappSettings) {
            $db->update(
                'whatsapp_settings',
                [
                    'api_url' => $apiUrl,
                    'api_user' => $apiUser,
                    'api_pass' => $apiPass
                ],
                'id = ?',
                [$whatsappSettings['id']]
            );
        } else {
            $db->insert('whatsapp_settings', [
                'api_url' => $apiUrl,
                'api_user' => $apiUser,
                'api_pass' => $apiPass,
                'connection_status' => 'disconnected'
            ]);
        }
        
        // Refresh data
        $whatsappSettings = $db->fetchOne("SELECT * FROM whatsapp_settings LIMIT 1");
        $successMessage = 'Pengaturan berhasil diperbarui';
    }
}

// Proses cek status koneksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_connection'])) {
    if (!$whatsappSettings) {
        $errorMessage = 'Pengaturan WhatsApp belum dikonfigurasi';
    } else {
        // Set kredensial API
        $whatsapp->setApiCredentials(
            $whatsappSettings['api_url'] ?? '',
            $whatsappSettings['api_user'] ?? '',
            $whatsappSettings['api_pass'] ?? ''
        );
        
        // Cek status koneksi
        $status = $whatsapp->checkStatus();
        
        if ($status) {
            // Update status koneksi
            $db->update(
                'whatsapp_settings',
                [
                    'connection_status' => 'connected',
                    'last_checked' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$whatsappSettings['id']]
            );
            
            // Refresh data
            $whatsappSettings = $db->fetchOne("SELECT * FROM whatsapp_settings LIMIT 1");
            $successMessage = 'WhatsApp terhubung dengan baik';
        } else {
            // Update status koneksi
            $db->update(
                'whatsapp_settings',
                [
                    'connection_status' => 'disconnected',
                    'last_checked' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$whatsappSettings['id']]
            );
            
            // Refresh data
            $whatsappSettings = $db->fetchOne("SELECT * FROM whatsapp_settings LIMIT 1");
            $errorMessage = 'Gagal terhubung ke WhatsApp. Pastikan pengaturan sudah benar dan WhatsApp sudah di-scan.';
        }
    }
}

// Proses reset koneksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_connection'])) {
    if (!$whatsappSettings) {
        $errorMessage = 'Pengaturan WhatsApp belum dikonfigurasi';
    } else {
        // Set kredensial API
        $whatsapp->setApiCredentials(
            $whatsappSettings['api_url'] ?? '',
            $whatsappSettings['api_user'] ?? '',
            $whatsappSettings['api_pass'] ?? ''
        );
        
        // Reset koneksi
        $reset = $whatsapp->resetConnection();
        
        if ($reset) {
            // Update status koneksi
            $db->update(
                'whatsapp_settings',
                [
                    'connection_status' => 'disconnected',
                    'last_checked' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$whatsappSettings['id']]
            );
            
            // Refresh data
            $whatsappSettings = $db->fetchOne("SELECT * FROM whatsapp_settings LIMIT 1");
            $successMessage = 'Koneksi WhatsApp berhasil direset. Silakan scan QR code untuk menghubungkan kembali.';
        } else {
            $errorMessage = 'Gagal mereset koneksi WhatsApp.';
        }
    }
}

// Proses generate QR code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_qr'])) {
    if (!$whatsappSettings) {
        $errorMessage = 'Pengaturan WhatsApp belum dikonfigurasi';
    } else {
        // Set kredensial API
        $whatsapp->setApiCredentials(
            $whatsappSettings['api_url'] ?? '',
            $whatsappSettings['api_user'] ?? '',
            $whatsappSettings['api_pass'] ?? ''
        );
        
        // Generate QR code
        $qrCode = $whatsapp->generateQRCode();
        
        if ($qrCode) {
            $statusMessage = 'QR code berhasil dibuat. Silakan scan dengan WhatsApp Anda.';
        } else {
            $errorMessage = 'Gagal membuat QR code. Pastikan pengaturan sudah benar.';
        }
    }
}

// Status koneksi
$connectionStatus = 'Belum terhubung';
$statusClass = 'text-red-600';

if ($whatsappSettings) {
    if ($whatsappSettings['connection_status'] == 'connected') {
        $connectionStatus = 'Terhubung';
        $statusClass = 'text-green-600';
    } else {
        $connectionStatus = 'Terputus';
        $statusClass = 'text-red-600';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan WhatsApp - Admin Dashboard</title>
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
                <a href="index.php" class="hover:underline">
                    <i class="fas fa-users mr-1"></i> Pengguna
                </a>
                <a href="settings.php" class="hover:underline font-bold">
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
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Pengaturan WhatsApp</h1>
            <p class="text-gray-600 mb-6">Konfigurasi pengaturan WhatsApp untuk mengirim notifikasi dan OTP.</p>
            
            <?php if ($errorMessage): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($successMessage): ?>
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($statusMessage): ?>
                <div class="bg-blue-100 text-blue-700 p-3 rounded mb-4">
                    <?php echo $statusMessage; ?>
                </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Form Pengaturan -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Konfigurasi API</h2>
                    
                    <form method="POST" class="space-y-4">
                        <div>
                            <label for="api_url" class="block text-gray-700 font-medium mb-2">URL API WhatsApp</label>
                            <input type="text" id="api_url" name="api_url" 
                                   value="<?php echo htmlspecialchars($whatsappSettings['api_url'] ?? ''); ?>" 
                                   placeholder="Contoh: https://send.simpan.id" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        
                        <div>
                            <label for="api_user" class="block text-gray-700 font-medium mb-2">Username API</label>
                            <input type="text" id="api_user" name="api_user" 
                                   value="<?php echo htmlspecialchars($whatsappSettings['api_user'] ?? ''); ?>" 
                                   placeholder="Username API WhatsApp" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        
                        <div>
                            <label for="api_pass" class="block text-gray-700 font-medium mb-2">Password API</label>
                            <input type="password" id="api_pass" name="api_pass" 
                                   value="<?php echo htmlspecialchars($whatsappSettings['api_pass'] ?? ''); ?>" 
                                   placeholder="Password API WhatsApp" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        
                        <input type="hidden" name="update_settings" value="1">
                        <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition duration-200">
                            Simpan Pengaturan
                        </button>
                    </form>
                </div>
                
                <!-- Status dan Aksi -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Status Koneksi</h2>
                    
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <div class="flex items-center mb-4">
                            <div class="font-medium text-gray-700 mr-2">Status:</div>
                            <div class="font-bold <?php echo $statusClass; ?>"><?php echo $connectionStatus; ?></div>
                        </div>
                        
                        <?php if ($whatsappSettings && isset($whatsappSettings['last_checked'])): ?>
                            <div class="text-sm text-gray-600 mb-4">
                                Terakhir diperiksa: <?php echo date('d M Y H:i:s', strtotime($whatsappSettings['last_checked'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="space-y-2">
                            <form method="POST">
                                <button type="submit" name="check_connection" value="1" class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200 w-full">
                                    <i class="fas fa-sync-alt mr-2"></i> Periksa Koneksi
                                </button>
                            </form>
                            
                            <form method="POST">
                                <button type="submit" name="generate_qr" value="1" class="bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition duration-200 w-full">
                                    <i class="fas fa-qrcode mr-2"></i> Generate QR Code
                                </button>
                            </form>
                            
                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset koneksi WhatsApp?');">
                                <button type="submit" name="reset_connection" value="1" class="bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition duration-200 w-full">
                                    <i class="fas fa-power-off mr-2"></i> Reset Koneksi
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- QR Code Display (jika ada) -->
                    <?php if (isset($qrCode) && $qrCode): ?>
                        <?php if (is_array($qrCode) && isset($qrCode['error'])): ?>
                            <div class="mt-4">
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Gagal membuat QR code!</strong>
                                    <span class="block sm:inline"><?php echo htmlspecialchars($qrCode['error']); ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                        <div class="mt-4">
                            <h3 class="font-bold text-gray-800 mb-2">QR Code</h3>
                            <p class="text-sm text-gray-600 mb-2">Scan QR code ini dengan WhatsApp di ponsel Anda untuk menghubungkan.</p>
                            <div class="bg-white p-4 border rounded-lg flex justify-center">
                                <?php if (strpos($qrCode, 'data:image') === 0): ?>
                                    <!-- Tampilkan QR code sebagai gambar jika formatnya data:image -->
                                    <img src="<?php echo $qrCode; ?>" alt="WhatsApp QR Code" style="width:220px; height:220px; object-fit:contain; display:block; margin:auto; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.07);">
                                <?php else: ?>
                                    <!-- Tampilkan gambar langsung jika URL mengarah ke QR code image -->
                                    <div class="text-center p-4">
                                        <div class="text-blue-600 font-bold mb-2">Memuat QR Code dari API WhatsApp</div>
                                        <p class="text-gray-700 mb-2">URL API: <span class="font-mono text-xs bg-gray-100 p-1 rounded"><?php echo htmlspecialchars($whatsappSettings['api_url'] ?? DEFAULT_WHATSAPP_API_URL); ?></span></p>
                                        <div class="border rounded-lg overflow-hidden" style="width:220px; height:220px; margin:auto; background:#fff;display:flex;align-items:center;justify-content:center;">
                                            <img src="<?php echo $qrCode; ?>" alt="WhatsApp QR Code" style="width:220px; height:220px; object-fit:contain; display:block;">
                                        </div>
                                        <p class="text-gray-600 mt-2 text-xs">Jika QR code tidak muncul, pastikan URL API dapat diakses dan kredensial sudah benar.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Panduan Penggunaan -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Panduan Penggunaan</h2>
            
            <div class="space-y-4">
                <div class="border-l-4 border-indigo-500 pl-4 py-2">
                    <h3 class="font-bold text-gray-800 mb-1">1. Konfigurasi API WhatsApp</h3>
                    <p class="text-gray-600">Isi URL API, username, dan password yang Anda dapatkan dari penyedia layanan WhatsApp API.</p>
                </div>
                
                <div class="border-l-4 border-indigo-500 pl-4 py-2">
                    <h3 class="font-bold text-gray-800 mb-1">2. Generate QR Code</h3>
                    <p class="text-gray-600">Klik tombol "Generate QR Code" untuk mendapatkan QR code yang perlu di-scan.</p>
                </div>
                
                <div class="border-l-4 border-indigo-500 pl-4 py-2">
                    <h3 class="font-bold text-gray-800 mb-1">3. Scan QR Code dengan WhatsApp</h3>
                    <p class="text-gray-600">Buka WhatsApp di ponsel Anda, pilih menu "WhatsApp Web", dan scan QR code yang ditampilkan.</p>
                </div>
                
                <div class="border-l-4 border-indigo-500 pl-4 py-2">
                    <h3 class="font-bold text-gray-800 mb-1">4. Periksa Koneksi</h3>
                    <p class="text-gray-600">Klik tombol "Periksa Koneksi" untuk memastikan WhatsApp sudah terhubung dengan baik.</p>
                </div>
                
                <div class="border-l-4 border-indigo-500 pl-4 py-2">
                    <h3 class="font-bold text-gray-800 mb-1">5. Reset Koneksi (Jika Diperlukan)</h3>
                    <p class="text-gray-600">Jika Anda ingin mengganti perangkat atau mengalami masalah, klik "Reset Koneksi" untuk memutus koneksi saat ini.</p>
                </div>
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
