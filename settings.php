<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once 'config.php';
require_once __DIR__ . '/WhatsAppClient.php';

// Simpan informasi grup aktif
$activeGroupId = defined('WHATSAPP_GROUP_ID') ? WHATSAPP_GROUP_ID : '';
$activeGroupName = ''; // Akan diisi nanti jika grup ditemukan

// Inisialisasi variabel status
$showLogout = false; // Nilai awal, akan diperbarui nanti

// Coba dapatkan nama grup aktif dari cache atau session jika tersedia
$activeGroupName = isset($_SESSION['active_group_name']) ? $_SESSION['active_group_name'] : '';

// Jika nama grup tidak ada di session, coba ambil dari parameter URL (jika ada)
if (empty($activeGroupName) && isset($_GET['group_name'])) {
    $activeGroupName = urldecode($_GET['group_name']);
    // Simpan ke session untuk penggunaan berikutnya
    $_SESSION['active_group_name'] = $activeGroupName;
}

// Jika masih kosong dan ada ID grup aktif, coba ambil dari API hanya jika diperlukan
// dan tidak ada error rate limit sebelumnya
if (empty($activeGroupName) && !empty($activeGroupId) && class_exists('WhatsAppClient') && 
    (!isset($_SESSION['api_rate_limited']) || (time() - $_SESSION['api_rate_limited']) > 300)) { // Tunggu 5 menit setelah rate limit
    
    try {
        $waClient = new WhatsAppClient(WHATSAPP_API_URL, WHATSAPP_API_USER, WHATSAPP_API_PASS);
        // Gunakan getGroupsMinimal untuk menghindari pengambilan data kontak grup
        $groups = $waClient->getGroupsMinimal($errorMsg);
        
        if (is_array($groups)) {
            foreach ($groups as $group) {
                // Ekstrak ID grup
                $groupId = '';
                if (is_object($group)) {
                    // Coba dapatkan ID dengan berbagai metode
                    if (method_exists($group, 'getJid')) {
                        $groupId = $group->getJid();
                    } elseif (property_exists($group, 'jid')) {
                        $groupId = $group->jid;
                    } elseif (method_exists($group, 'getId')) {
                        $groupId = $group->getId();
                    } elseif (property_exists($group, 'id')) {
                        $groupId = $group->id;
                    }
                    
                    // Jika ID cocok dengan grup aktif, ambil namanya
                    if ($groupId === $activeGroupId) {
                        if (method_exists($group, 'getName')) {
                            $activeGroupName = $group->getName();
                        } elseif (property_exists($group, 'name')) {
                            $activeGroupName = $group->name;
                        } elseif (method_exists($group, 'getSubject')) {
                            $activeGroupName = $group->getSubject();
                        } elseif (property_exists($group, 'subject')) {
                            $activeGroupName = $group->subject;
                        }
                        // Simpan ke session untuk penggunaan berikutnya
                        $_SESSION['active_group_name'] = $activeGroupName;
                        break;
                    }
                } elseif (is_array($group)) {
                    // Jika grup adalah array
                    $groupId = $group['jid'] ?? $group['id'] ?? '';
                    if ($groupId === $activeGroupId) {
                        $activeGroupName = $group['name'] ?? $group['subject'] ?? '';
                        // Simpan ke session untuk penggunaan berikutnya
                        $_SESSION['active_group_name'] = $activeGroupName;
                        break;
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Periksa apakah error adalah rate limit
        if (strpos($e->getMessage(), 'rate-overlimit') !== false || strpos($e->getMessage(), '429') !== false) {
            // Catat waktu rate limit untuk menghindari permintaan berlebihan
            $_SESSION['api_rate_limited'] = time();
        }
        // Abaikan error saat mencoba mendapatkan nama grup
    }
}

// Cek status online
$isOnline = false;
try {
    $waClient = new WhatsAppClient(WHATSAPP_API_URL, WHATSAPP_API_USER, WHATSAPP_API_PASS);
    $isOnline = $waClient->isDeviceOnline();
} catch (Exception $e) {
    $isOnline = false;
}

// Untuk status dot
function renderStatusDot($isOnline) {
    if ($isOnline) {
        return "<span class='dot online'></span> <span class='status-text'>Online</span>";
    } else {
        return "<span class='dot offline'></span> <span class='status-text'>Offline</span>";
    }
}

// Handle form submit
$message = '';

// Cek apakah ada pesan dari parameter URL
if (isset($_GET['message']) && !empty($_GET['message'])) {
    $message = urldecode($_GET['message']);
}

// Cek apakah perlu force refresh setelah reset
$forceShowQr = false;
if (isset($_SESSION['force_refresh']) && $_SESSION['force_refresh'] === true) {
    $forceShowQr = true;
    // Reset flag setelah digunakan
    $_SESSION['force_refresh'] = false;
    
    // Tambahkan parameter showqr=1 ke URL jika belum ada
    if (!isset($_GET['showqr'])) {
        header('Location: settings.php?showqr=1');
        exit;
    }
}

// Cek apakah ada parameter reset_container untuk me-restart container WhatsApp
if (isset($_GET['reset_container']) && $_GET['reset_container'] === '1') {
    // Flag untuk menampilkan instruksi restart container di halaman
    $needContainerRestart = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        require_once __DIR__ . '/WhatsAppClient.php';
        $waClient = new WhatsAppClient(
            defined('WHATSAPP_API_URL') ? WHATSAPP_API_URL : '',
            defined('WHATSAPP_API_USER') ? WHATSAPP_API_USER : '',
            defined('WHATSAPP_API_PASS') ? WHATSAPP_API_PASS : ''
        );
        
        // Reset session data terkait grup
        unset($_SESSION['active_group_name']);
        unset($_SESSION['api_rate_limited']);
        
        $resetMessage = '';
        
        if ($_POST['action'] === 'reset_generate_qr') {
            // Reset/Logout & Generate QR normal
            try {
                $waClient->logout();
                $resetMessage = 'Berhasil logout dari WhatsApp.';
            } catch (Exception $e) {
                $resetMessage = 'Gagal logout: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'force_reset') {
            // Coba reset koneksi terlebih dahulu
            $resetSuccess = $waClient->resetConnection($resetMessage);
            
            // Jika sudah pernah mencoba reset sebelumnya dan masih gagal, sarankan restart container
            if (isset($_SESSION['last_reset']) && (time() - $_SESSION['last_reset'] < 300)) {
                // Jika dalam 5 menit terakhir sudah pernah reset dan masih bermasalah
                // Sarankan untuk me-restart container
                $waClient->needContainerRestart($resetMessage);
                header('Location: settings.php?reset_container=1&message=' . urlencode($resetMessage));
                exit;
            }
        }
        
        sleep(2); // beri jeda agar backend reset
        header('Location: settings.php?showqr=1&message=' . urlencode($resetMessage));
        exit;
    }
    $new_url = trim($_POST['whatsapp_url'] ?? '');
    $new_user = trim($_POST['whatsapp_user'] ?? '');
    $new_pass = trim($_POST['whatsapp_pass'] ?? '');
    if ($new_url && $new_user && $new_pass) {
        $config = file_get_contents(__DIR__.'/config.php');
        $config = preg_replace(
            "/define\('WHATSAPP_API_URL',\s*'.*?'\);/",
            "define('WHATSAPP_API_URL', '" . addslashes($new_url) . "');",
            $config
        );
        $config = preg_replace(
            "/define\('WHATSAPP_API_USER',\s*'.*?'\);/",
            "define('WHATSAPP_API_USER', '" . addslashes($new_user) . "');",
            $config
        );
        $config = preg_replace(
            "/define\('WHATSAPP_API_PASS',\s*'.*?'\);/",
            "define('WHATSAPP_API_PASS', '" . addslashes($new_pass) . "');",
            $config
        );
        file_put_contents(__DIR__.'/config.php', $config);
        header('Location: settings.php?success=1');
        exit;
    }
}

$whatsappUrl = defined('WHATSAPP_API_URL') ? WHATSAPP_API_URL : '';
$whatsappUser = defined('WHATSAPP_API_USER') ? WHATSAPP_API_USER : '';
$whatsappPass = defined('WHATSAPP_API_PASS') ? WHATSAPP_API_PASS : '';

// Inisialisasi WhatsAppClient
$waClient = new WhatsAppClient($whatsappUrl, $whatsappUser, $whatsappPass);

// Endpoint AJAX untuk cek status device (untuk auto-refresh QR)
if (isset($_GET['check_device'])) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['error' => 'Session expired']);
        exit;
    }
    $isOnline = $waClient->isDeviceOnline();
    $devicesRaw = $waClient->getDevicesRaw();
    echo json_encode([
        'online' => $isOnline,
        'devices' => $devicesRaw,
        'timestamp' => time()
    ]);
    exit;
}

// Ambil data devices sekali saja
$devices = $waClient->getDevicesRaw();
// Cek status device (apakah WhatsApp sudah login) berdasarkan isi $devices
$isLoggedIn = false;
if (is_object($devices)) {
    // Gunakan getter jika ada
    if (method_exists($devices, 'getResults')) {
        $results = $devices->getResults();
        if (is_array($results) && count($results) > 0) {
            $isLoggedIn = true;
        }
    } elseif (property_exists($devices, 'container') && isset($devices->container['results']) && is_array($devices->container['results']) && count($devices->container['results']) > 0) {
        // Fallback: akses property container
        $isLoggedIn = true;
    }
}
// Hanya salah satu modal yang boleh aktif
$showQr = isset($_GET['showqr']);
$showGroups = isset($_GET['showgroups']);
// Jika sudah login, paksa $showQr = false
if ($isLoggedIn) {
    $showQr = false;
}
// Jangan paksa $showGroups = false di sini, agar modal tetap bisa muncul walau device belum login.
$qrError = null;
$groupsError = null;

// Jika sudah login, tidak perlu QR code sama sekali
$qr = ($showQr && !$isLoggedIn) ? $waClient->getQrCode($qrError) : null;

// Hanya ambil daftar grup jika sudah login
// Gunakan getGroupsMinimal untuk menghindari pengambilan data anggota grup yang tidak perlu
$groups = ($showGroups && $isLoggedIn) ? $waClient->getGroupsMinimal($groupsError) : [];
// Jika $showGroups true tapi bukan login, kosongkan error agar tidak tampil error QR di modal grup
if ($showGroups && !$isLoggedIn) {
    $groupsError = null;
}

// Variabel $showLogout akan didefinisikan nanti setelah $deviceCount dan $alreadyLoggedIn tersedia
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Hapus auto refresh global untuk meningkatkan kinerja -->
    <title>Pengaturan WhatsApp API</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    .dot {
        height: 14px;
        width: 14px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 7px;
        vertical-align: middle;
        box-shadow: 0 0 6px #8882;
    }
    .dot.online {
        background-color: #34d058;
        animation: blink 1s infinite;
        box-shadow: 0 0 8px #34d05888;
    }
    .dot.offline {
        background-color: #aaa;
    }
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    .status-row { margin-bottom: 18px; font-size: 1.1em; }
    .status-text { font-weight: 600; }
    .badge-dev {
        display: inline-block;
        background: #e6fbe7;
        color: #1a7f37;
        border-radius: 16px;
        padding: 3px 12px 3px 7px;
        font-size: 0.98em;
        margin: 0 7px 7px 0;
        font-weight: 500;
        box-shadow: 0 1px 3px #0001;
        border: 1px solid #b2e2c2;
        vertical-align: middle;
        transition: background 0.2s;
    }
    .badge-dev.offline {
        background: #f2f2f2;
        color: #888;
        border: 1px solid #ddd;
    }
    .device-id {
        color: #388e3c;
        font-size: 0.93em;
        font-weight: 400;
        margin-left: 3px;
    }
    /* Notifikasi Toast */
    .toast {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 9999;
        max-width: 24rem;
        transform: translateX(110%);
        transition: transform 0.3s ease-in-out;
    }
    .toast.show {
        transform: translateX(0);
    }
    @keyframes progress {
        0% { width: 100%; }
        100% { width: 0%; }
    }
    .toast-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background-color: rgba(255, 255, 255, 0.5);
        animation: progress 5s linear forwards;
    }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Toast Notification -->
    <div id="toast" class="toast rounded-lg shadow-lg overflow-hidden">
        <div class="flex items-center p-4 bg-green-600 text-white">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <div>
                <p class="font-bold" id="toast-title">Sukses!</p>
                <p class="text-sm" id="toast-message">Grup berhasil disimpan.</p>
            </div>
            <button onclick="hideToast()" class="ml-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="toast-progress"></div>
    </div>
<div class="max-w-xl mx-auto bg-white rounded-lg shadow-lg p-6 mt-10">
    <div class="flex justify-between items-center mb-6">
        <a href="index.php" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition shadow-md flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            Dashboard
        </a>
        <h2 class="text-2xl font-bold text-indigo-700 text-center flex-1">Pengaturan WhatsApp</h2>
        <div class="w-[120px]"></div> <!-- Spacer untuk menjaga judul tetap di tengah -->
    </div>
    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4 text-center">URL API berhasil disimpan!</div>
    <?php endif; ?>
    
    <?php if (isset($needContainerRestart) && $needContainerRestart): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
            <p class="font-bold">Perhatian: Container WhatsApp Perlu Di-restart</p>
            <p class="mb-2">Untuk mengatasi masalah "FOREIGN KEY constraint failed", container WhatsApp perlu di-restart.</p>
            <div class="bg-gray-100 p-3 rounded mt-2 font-mono text-sm">
                docker restart whatsapp
            </div>
            <p class="mt-2">Setelah container di-restart, silakan refresh halaman ini dan scan QR code baru.</p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['saved']) && $_GET['saved'] === '1'): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p class="font-bold">Sukses!</p>
            <p>Grup WhatsApp berhasil disimpan sebagai grup default.</p>
            <?php if (!empty($activeGroupName)): ?>
                <p class="mt-2">Grup aktif saat ini: <strong><?php echo htmlspecialchars($activeGroupName); ?></strong></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Informasi Grup WhatsApp Aktif -->
    <?php if ($isLoggedIn): // Tampilkan hanya jika WhatsApp sudah login ?>
    <div class="bg-blue-50 p-4 rounded-lg mb-4">
        <h3 class="text-lg font-semibold text-blue-700 mb-2">Informasi Grup WhatsApp</h3>
        <?php if (!empty($activeGroupId)): ?>
            <p>Grup aktif: <strong><?php echo htmlspecialchars($activeGroupName ?: 'Tidak diketahui'); ?></strong></p>
            <p class="text-sm text-gray-600">ID: <?php echo htmlspecialchars($activeGroupId); ?></p>
        <?php else: ?>
            <p class="text-yellow-600">Belum ada grup WhatsApp yang dipilih sebagai default.</p>
            <a href="settings.php?showgroups=1" class="text-blue-600 hover:underline">Pilih grup default</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php
    $devices = $waClient->getDevicesRaw();
    $deviceCount = 0;
    // Handle aksi logout
    $logoutMsg = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
        try {
            $waClient->logout();
            $logoutMsg = '<div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4 text-center">Berhasil logout WhatsApp.</div>';
        } catch (Exception $e) {
            $logoutMsg = '<div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-center">Gagal logout: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
    if (!empty($logoutMsg)) echo $logoutMsg;

    $qrError = null;
    $qr = $waClient->getQrCode($qrError);
    $alreadyLoggedIn = false;
    if (
        isset($qrError) &&
        (stripos($qrError, 'ALREADY_LOGGED_IN') !== false || stripos($qrError, 'already logged in') !== false)
    ) {
        $alreadyLoggedIn = true;
    }
    
    // Tentukan status logout berdasarkan device yang terdaftar
    $showLogout = false;
    if ($deviceCount > 0 || $alreadyLoggedIn) {
        $showLogout = true;
    }
    if (
        isset($devices['container']['results']) &&
        is_array($devices['container']['results']) &&
        count($devices['container']['results']) > 0
    ) {
        $deviceCount = count($devices['container']['results']);
        echo "<div class='status-row' style='margin-bottom:10px;'><b>Device Terdaftar:</b></div>";
        echo "<div style='margin-bottom:18px;'>";
        foreach ($devices['container']['results'] as $dev) {
            $info = $dev->container;
            echo "<span class='badge-dev'><span class='dot online'></span>" .
                htmlspecialchars($info['name']) .
                " <span class='device-id'>[" . htmlspecialchars($info['device']) . "]</span></span> ";
        }
        echo "</div>";
        // Sudah ada device, sembunyikan QR code dan error
    } elseif ($alreadyLoggedIn) {
        // Device sudah login walau data kosong
        echo "<div class='status-row' style='margin-bottom:18px;'><span class='badge-dev'><span class='dot online'></span>Device sudah login</span></div>";
    } else {
        // Tidak ada device dan belum login
        echo "<div class='status-row' style='margin-bottom:18px;'><span class='badge-dev offline'><span class='dot offline'></span>Tidak ada device terdaftar</span></div>";
    }
    ?>
    <?php
    // Tombol Logout & sembunyikan QR code jika sudah login
    // $showLogout sudah didefinisikan di awal file
    if ($showLogout) {
        // Satu tombol untuk reset dan scan QR code baru
        echo '<form method="post" style="margin-bottom:18px;display:inline-block;">';
        echo '<input type="hidden" name="action" value="force_reset">';
        echo '<button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded font-semibold">Reset & Scan QR Code Baru</button>';
        echo '</form>';
        // Tombol Ambil Daftar Grup selalu tampil jika sudah login
        if ($showLogout) {
            echo '<a href="?showgroups=1" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded font-semibold ml-2" style="display:inline-block;">Ambil Daftar Grup</a>';
        }
    }
    ?>
    <?php if (!$showLogout) { // Tampilkan QR code hanya jika belum login ?>
    <form method="post" class="space-y-5 mb-8">
        <div>
            <label for="whatsapp_url" class="block mb-1 font-semibold text-gray-700">WhatsApp API URL</label>
            <input type="text" id="whatsapp_url" name="whatsapp_url" value="<?php echo htmlspecialchars($whatsappUrl); ?>" class="w-full px-3 py-2 border rounded" required>
        </div>
        <div>
            <label for="whatsapp_user" class="block mb-1 font-semibold text-gray-700">WhatsApp API Username</label>
            <input type="text" id="whatsapp_user" name="whatsapp_user" value="<?php echo htmlspecialchars($whatsappUser); ?>" class="w-full px-3 py-2 border rounded" required>
        </div>
        <div>
            <label for="whatsapp_pass" class="block mb-1 font-semibold text-gray-700">WhatsApp API Password</label>
            <input type="password" id="whatsapp_pass" name="whatsapp_pass" value="<?php echo htmlspecialchars($whatsappPass); ?>" class="w-full px-3 py-2 border rounded" required>
        </div>
        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded">Simpan Pengaturan</button>
    </form>
    <?php } ?>

    <?php if ($whatsappUrl): ?>
        <?php if ($showLogout): ?>
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4 text-center">Status: <b>Sudah login WhatsApp</b>. <br>Pilih dan Simpan ID Grup yang akan dikirim notifikasi PPPOE.</div>
        <?php else: ?>
            <div class="bg-yellow-100 text-yellow-700 px-4 py-2 rounded mb-4 text-center">Status: <b>Belum login WhatsApp</b>. Silakan scan QR code untuk login.</div>
        <?php endif; ?>
        <div class="flex flex-col md:flex-row gap-4 justify-center mb-6">
            <a href="?showqr=1" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-center font-semibold<?php echo ($showLogout ? ' opacity-50 pointer-events-none cursor-not-allowed' : ''); ?>" <?php if ($showLogout) echo 'tabindex="-1" aria-disabled="true" onclick="return false;"'; ?>>Tampilkan QR Code</a>
        </div>
    <?php endif; ?>

    <?php
    // INFO OUTPUT
    echo "<!-- Status: showGroups=" . ($showGroups ? '1' : '0') . ", isLoggedIn=" . ($isLoggedIn ? '1' : '0') . ", jumlah_grup=" . (is_array($groups) ? count($groups) : '0') . " -->";
    // Tidak perlu menampilkan struktur objek secara mentah ke user
    if ($showGroups): ?>
        <!-- Modal Grup WhatsApp -->
        <div id="modal-grup" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-40">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl mx-4 relative max-h-[90vh] flex flex-col">
                <!-- Header Modal -->
                <div class="flex justify-between items-center mb-4 pb-2 border-b">
                    <h2 class="text-xl font-bold">Daftar Grup WhatsApp</h2>
                    <button onclick="window.location.href=window.location.pathname" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
                </div>
                
                <!-- Konten Modal -->
                <?php if (!$isLoggedIn): ?>
                    <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-2">Device belum login. Silakan login terlebih dahulu.</div>
                <?php elseif (!empty($groups) && is_array($groups)): ?>
                    <div class="p-2 flex-1 overflow-hidden flex flex-col">
                        <!-- Informasi Grup -->
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-bold text-lg">Daftar Grup WhatsApp</h3>
                            <div class="text-sm text-gray-600">Jumlah grup: <b><?php echo is_array($groups) ? count($groups) : 0; ?></b></div>
                        </div>
                        
                        <!-- Fitur Pencarian -->
                        <div class="mb-3">
                            <input type="text" id="search-grup" placeholder="Cari grup..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        
                        <?php
                        // Hitung jumlah grup yang valid
                        $jumlahGrup = is_array($groups) ? count($groups) : 0;
                        
                        if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
                        <!-- Debug info untuk admin -->
                        <div class="bg-gray-100 p-2 mb-4 text-xs overflow-auto" style="max-height: 100px;">
                            <pre><?php
                            foreach ($groups as $idx => $g) {
                                echo "$idx: " . (is_object($g) ? get_class($g) : gettype($g)) . "\n";
                            }
                            ?></pre>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Daftar grup dengan scrolling -->
                        <div class="overflow-y-auto flex-1 border border-gray-200 rounded-md">
                            <ul id="grup-list" class="divide-y divide-gray-200">
                            <?php if ($jumlahGrup > 0): ?>
                                <?php 
                                $displayedCount = 0;
                                foreach ($groups as $idx => $group): 
                                    // Ekstrak informasi grup
                                    $groupName = '';
                                    $groupId = '';
                                    $isParticipant = false;
                                    
                                    // Cek apakah ini objek participant (yang harus dilewati)
                                    if (is_object($group) && strpos(get_class($group), 'ParticipantsInner') !== false) {
                                        continue; // Lewati objek participant
                                    }
                                    
                                    // Jika objek, coba berbagai cara untuk mendapatkan nama dan ID
                                    if (is_object($group)) {
                                        // Cara 1: Coba dengan getter standar
                                        if (method_exists($group, 'getJid')) {
                                            $groupId = $group->getJid();
                                        }
                                        if (method_exists($group, 'getName')) {
                                            $groupName = $group->getName();
                                        } elseif (method_exists($group, 'getSubject')) {
                                            $groupName = $group->getSubject();
                                        }
                                        
                                        // Cara 2: Coba akses properti langsung
                                        if (empty($groupName)) {
                                            if (property_exists($group, 'name') && is_string($group->name)) {
                                                $groupName = $group->name;
                                            } elseif (property_exists($group, 'subject') && is_string($group->subject)) {
                                                $groupName = $group->subject;
                                            }
                                        }
                                        
                                        if (empty($groupId)) {
                                            if (property_exists($group, 'jid') && is_string($group->jid)) {
                                                $groupId = $group->jid;
                                            } elseif (property_exists($group, 'id') && is_string($group->id)) {
                                                $groupId = $group->id;
                                            }
                                        }
                                        
                                        // Cara 3: Coba akses container dengan getter
                                        if ((empty($groupName) || empty($groupId)) && method_exists($group, 'getContainer')) {
                                            try {
                                                $c = $group->getContainer();
                                                if (is_array($c)) {
                                                    if (empty($groupName)) {
                                                        $groupName = $c['name'] ?? $c['subject'] ?? '';
                                                    }
                                                    if (empty($groupId)) {
                                                        $groupId = $c['jid'] ?? $c['id'] ?? '';
                                                    }
                                                }
                                            } catch (Exception $e) {
                                                // Ignore errors accessing container
                                            }
                                        }
                                        
                                        // Cara 4: Coba konversi ke array
                                        if ((empty($groupName) || empty($groupId))) {
                                            try {
                                                $arr = (array)$group;
                                                if (empty($groupName)) {
                                                    $groupName = $arr['name'] ?? $arr['subject'] ?? '';
                                                }
                                                if (empty($groupId)) {
                                                    $groupId = $arr['jid'] ?? $arr['id'] ?? '';
                                                }
                                            } catch (Exception $e) {
                                                // Ignore errors during conversion
                                            }
                                        }
                                    }
                                    // Jika array, coba akses kunci yang umum
                                    elseif (is_array($group)) {
                                        $groupName = $group['name'] ?? $group['subject'] ?? '';
                                        $groupId = $group['jid'] ?? $group['id'] ?? '';
                                    }
                                    
                                    // Hanya tampilkan jika memiliki nama atau ID
                                    if (!empty($groupName) || !empty($groupId)):
                                        $displayedCount++;
                                        // Buat ID unik untuk setiap grup
                                        $uniqueId = 'group-' . $idx;
                                ?>
                                    <li class="group-item py-3 px-4 hover:bg-gray-50 transition-colors duration-150 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                                        <div class="flex-1">
                                            <div class="font-medium text-green-800">
                                                <?php echo htmlspecialchars($groupName ?: '(Tanpa Nama)'); ?>
                                                <?php if ($groupId === $activeGroupId): ?>
                                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full ml-2">Aktif</span>
                                                    <?php $activeGroupName = $groupName; // Simpan nama grup aktif ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex items-center mt-1">
                                                <input type="text" id="<?php echo $uniqueId; ?>" value="<?php echo htmlspecialchars($groupId); ?>" readonly class="text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded border border-gray-300 w-full md:w-auto flex-1" onclick="this.select();">
                                                <div class="flex">
                                                    <button onclick="copyToClipboard('<?php echo $uniqueId; ?>')" class="ml-2 bg-gray-200 hover:bg-gray-300 text-gray-700 px-2 py-1 rounded-l text-sm flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                                        </svg>
                                                        Salin
                                                    </button>
                                                    <button onclick="saveToConfig('<?php echo htmlspecialchars($groupId); ?>')" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded-r text-sm flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Simpan
                                                    </button>
                                                </div>
                                            </div>
                                            <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
                                            <div class="text-xs text-gray-400 mt-1">Index: <?php echo $idx; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php 
                                    endif;
                                endforeach; 
                                
                                // Jika tidak ada grup yang valid untuk ditampilkan
                                if ($displayedCount === 0):
                                ?>
                                    <li class="py-4 px-4 text-gray-500 text-center">
                                        Tidak ada grup yang dapat ditampilkan.
                                    </li>
                                <?php endif; ?>
                            <?php else: ?>
                                <li class="py-4 px-4 text-gray-500 text-center">
                                    Tidak ada grup yang dapat ditampilkan.
                                </li>
                            <?php endif; ?>
                            </ul>
                        </div>
                        
                        <!-- Script untuk fitur pencarian, copy, dan simpan ke config -->
                        <script>
                        // Fungsi untuk menyalin ID grup ke clipboard
                        function copyToClipboard(elementId) {
                            const element = document.getElementById(elementId);
                            element.select();
                            document.execCommand('copy');
                            
                            // Tampilkan notifikasi berhasil disalin
                            const button = element.nextElementSibling.querySelector('button:first-child');
                            const originalText = button.innerHTML;
                            button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>Tersalin!';
                            button.classList.remove('bg-gray-200', 'hover:bg-gray-300');
                            button.classList.add('bg-green-200', 'text-green-800');
                            
                            setTimeout(() => {
                                button.innerHTML = originalText;
                                button.classList.remove('bg-green-200', 'text-green-800');
                                button.classList.add('bg-gray-200', 'hover:bg-gray-300');
                            }, 2000);
                        }
                        
                        // Fungsi untuk menampilkan toast notification
                        function showToast(title, message, type = 'success') {
                            const toast = document.getElementById('toast');
                            const toastTitle = document.getElementById('toast-title');
                            const toastMessage = document.getElementById('toast-message');
                            
                            // Set konten toast
                            toastTitle.textContent = title;
                            toastMessage.textContent = message;
                            
                            // Set warna berdasarkan tipe
                            const toastContent = toast.querySelector('div');
                            if (type === 'success') {
                                toastContent.classList.remove('bg-red-600');
                                toastContent.classList.add('bg-green-600');
                            } else if (type === 'error') {
                                toastContent.classList.remove('bg-green-600');
                                toastContent.classList.add('bg-red-600');
                            }
                            
                            // Tampilkan toast
                            toast.classList.add('show');
                            
                            // Otomatis sembunyikan setelah 5 detik
                            setTimeout(hideToast, 5000);
                        }
                        
                        // Fungsi untuk menyembunyikan toast
                        function hideToast() {
                            const toast = document.getElementById('toast');
                            toast.classList.remove('show');
                        }
                        
                        // Fungsi untuk menyimpan grup ke config.php
                        function saveToConfig(groupId) {
                            // Dapatkan nama grup dari elemen HTML
                            const groupItem = event.target.closest('.group-item');
                            const groupName = groupItem ? groupItem.querySelector('.font-medium').textContent.trim() : '';
                            
                            // Tampilkan konfirmasi
                            if (!confirm('Simpan grup ini ke config.php sebagai grup default?')) {
                                return;
                            }
                            
                            // Kirim request AJAX untuk menyimpan ke config
                            fetch('save_minimal.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'group_id=' + encodeURIComponent(groupId) + '&group_name=' + encodeURIComponent(groupName)
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Tampilkan notifikasi sukses dengan toast
                                    showToast('Sukses!', 'Grup "' + groupName + '" berhasil disimpan sebagai grup default', 'success');
                                    // Refresh halaman untuk menampilkan perubahan setelah 1 detik
                                    setTimeout(() => {
                                        window.location.href = 'settings.php?showgroups=1&saved=1';
                                    }, 1000);
                                } else {
                                    // Tampilkan pesan error dengan toast
                                    showToast('Error!', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                showToast('Error!', 'Terjadi kesalahan saat menyimpan grup.', 'error');
                            });
                        }
                        
                        // Fungsi pencarian grup
                        document.getElementById('search-grup').addEventListener('input', function() {
                            const searchTerm = this.value.toLowerCase();
                            const groupItems = document.querySelectorAll('#grup-list .group-item');
                            
                            groupItems.forEach(item => {
                                const groupName = item.querySelector('.font-medium').textContent.toLowerCase();
                                const groupId = item.querySelector('input').value.toLowerCase();
                                
                                if (groupName.includes(searchTerm) || groupId.includes(searchTerm)) {
                                    item.style.display = '';
                                } else {
                                    item.style.display = 'none';
                                }
                            });
                        });
                        </script>
                        
                        <!-- Link Debug Mode selalu ditampilkan -->
                        <div class="mt-3 text-xs text-gray-500 text-right">
                            <a href="?showgroups=1&debug=1" class="text-blue-500 hover:underline">Debug Mode</a>
                        </div>
                    </div>
                    </div>
                <?php else: ?>
                    <div class="bg-red-100 text-red-700 px-3 py-2 rounded text-sm">
                        <?php echo nl2br(htmlspecialchars($groupsError)); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>


    <?php if ($showQr && !$isLoggedIn): ?>
        <div class="mb-8 text-center">
            <h5 class="font-semibold mb-2">QR Code WhatsApp</h5>
            <?php if ($qr): ?>
                <script>
                // Auto-refresh untuk cek status device dengan exponential backoff
                let checkCount = 0;
                let initialDelay = 2000; // Mulai dari 2 detik
                let maxDelay = 10000; // Maksimal 10 detik
                let currentDelay = initialDelay;
                
                // Tampilkan indikator status pengecekan
                const statusIndicator = document.createElement('div');
                statusIndicator.className = 'text-xs text-gray-500 text-center mt-2';
                statusIndicator.innerHTML = 'Menunggu koneksi WhatsApp... <span id="check-status">⏳</span>';
                document.querySelector('img[alt="WhatsApp QR Code"]').after(statusIndicator);
                
                // Animasi indikator
                const indicators = ['⏳', '⌛', '⏳'];
                let indicatorIndex = 0;
                setInterval(() => {
                    document.getElementById('check-status').textContent = indicators[indicatorIndex];
                    indicatorIndex = (indicatorIndex + 1) % indicators.length;
                }, 800);
                
                function checkDeviceStatus() {
                    fetch('settings.php?check_device=1')
                        .then(res => res.json())
                        .then(data => {
                            if (data.online === true) {
                                // Tampilkan notifikasi sukses sebelum refresh
                                statusIndicator.innerHTML = '<span class="text-green-600 font-semibold">✓ Berhasil terhubung! Memuat halaman...</span>';
                                setTimeout(() => window.location.reload(), 1000);
                                return;
                            }
                            
                            // Jika belum online, jadwalkan pengecekan berikutnya dengan delay yang meningkat
                            checkCount++;
                            if (checkCount > 3) { // Setelah 3 kali pengecekan, mulai tingkatkan delay
                                currentDelay = Math.min(currentDelay * 1.5, maxDelay);
                            }
                            setTimeout(checkDeviceStatus, currentDelay);
                        })
                        .catch(err => {
                            console.error('Error checking device status:', err);
                            // Jika error, coba lagi dengan delay yang lebih lama
                            currentDelay = Math.min(currentDelay * 2, maxDelay);
                            setTimeout(checkDeviceStatus, currentDelay);
                        });
                }
                
                // Mulai pengecekan pertama setelah 2 detik
                setTimeout(checkDeviceStatus, initialDelay);
                </script>
                <?php if (preg_match('/^https?:\/\//', $qr)): ?>
                    <?php $qrUrl = $qr . (strpos($qr, '?') === false ? '?' : '&') . 't=' . time(); ?>
                    <img src="<?php echo htmlspecialchars($qrUrl); ?>" alt="WhatsApp QR Code" class="rounded shadow mx-auto w-56 max-w-full"/>
                <?php else: ?>
                    <img src="data:image/png;base64,<?php echo $qr; ?>" alt="WhatsApp QR Code" class="rounded shadow mx-auto w-56 max-w-full"/>
                <?php endif; ?>
                <div class="text-xs text-gray-500 text-center mt-2">Scan dengan aplikasi WhatsApp Anda</div>
                <?php if (isset($_SESSION['qr_duration']) && $_SESSION['qr_duration'] > 0): ?>
                    <div class="text-xs text-red-500 text-center mt-1">Kode QR ini akan kedaluwarsa dalam <span id="qr-timer"><?php echo $_SESSION['qr_duration']; ?></span> detik.</div>
                    <script>
                        let qrTimer = <?php echo $_SESSION['qr_duration']; ?>;
                        let qrInterval = setInterval(function() {
                            qrTimer--;
                            if (qrTimer <= 0) {
                                clearInterval(qrInterval);
                                // reload hanya bagian QR code
                                window.location.href = window.location.pathname + '?showqr=1';
                            } else {
                                document.getElementById('qr-timer').innerText = qrTimer;
                            }
                        }, 1000);
                    </script>
                    <?php unset($_SESSION['qr_duration']); ?>
                <?php endif; ?>
            <?php else: ?>
                <div class="bg-yellow-100 text-yellow-700 px-3 py-2 rounded text-sm">QR Code tidak tersedia. Pastikan WhatsApp API berjalan.</div>
                <?php if ($qrError): ?>
                    <div class="bg-red-100 text-red-700 px-3 py-2 rounded text-sm mt-2">Error: <?php echo $qrError; ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
