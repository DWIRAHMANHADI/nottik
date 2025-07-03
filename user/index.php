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
    "SELECT * FROM pppoe_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", 
    [$userData['user_id']]
);

// Proses update pengaturan
$updateMessage = '';

// Ambil daftar admin
$adminList = $db->fetchAll("SELECT name, phone FROM admins");
require_once '../includes/PhoneUtils.php';
// Ambil script Mikrotik dengan token pengguna
$mikrotikScript = file_get_contents('../mikrotik_script.txt');
$mikrotikScript = str_replace('rahasia123', $userSettings['token'] ?? 'TOKEN_TIDAK_DITEMUKAN', $mikrotikScript);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - (Nottik) Notification Mikrotik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-green-600 text-white shadow-md">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center">
            <span class="font-bold text-xl">(Nottik) Notification Mikrotik</span>
        </div>
        <div class="flex items-center space-x-4">
            <a href="help.php" class="hover:underline"><i class="fas fa-question-circle mr-1"></i> Bantuan</a>
            <span class="hidden md:inline-block"><?php echo htmlspecialchars($userData['name']); ?></span>
            <a href="./statistik.php" class="hover:underline">
            <i class="fas fa-chart-bar mr-1"></i> Statistik
            </a>
            <a href="../logout.php" class="hover:underline">
                <i class="fas fa-sign-out-alt mr-1"></i> Logout
            </a>
        </div>
    </div>
</nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6">
<?php if ($userData['status'] === 'suspended'): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-8 rounded-lg text-center mb-8">
        <i class="fas fa-ban fa-3x mb-4"></i>
        <h2 class="text-2xl font-bold mb-2">Akun Anda Ditangguhkan</h2>
        <p class="mb-2">Akun Anda sedang <span class="font-bold uppercase">suspend</span> oleh admin.</p>
        <p>Seluruh fitur notifikasi, pengaturan, dan script Mikrotik tidak dapat diakses sementara.</p>
        <p class="mt-4">Silakan hubungi admin untuk informasi lebih lanjut atau reaktivasi akun.</p>
    </div>
<?php else: ?>
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
                <?php if ($userData['status'] === 'pending'): ?>
                    <div class="bg-yellow-100 border-l-4 border-yellow-400 text-yellow-800 p-6 rounded text-center">
                        <i class="fas fa-lock fa-2x mb-2"></i><br>
                        <span class="font-bold">Akun Anda masih dalam status <span class="uppercase">pending</span>.</span><br>
                        Pengaturan ID Grup WhatsApp dan informasi admin akan tersedia setelah akun Anda aktif.
                    </div>
                <?php else: ?>
                    <?php if ($updateMessage): ?>
                        <div class="bg-blue-100 text-blue-700 p-3 rounded mb-4">
                            <?php echo $updateMessage; ?>
                        </div>
                    <?php endif; ?>
                    <!-- Form Settings Tanpa Simpan Group ID -->
<form class="space-y-4">
                        <div>
                            <label for="group_id" class="block text-gray-700 font-medium mb-2">ID Grup WhatsApp</label>
                            <?php if (empty($userSettings['group_id'])): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-4 flex items-center gap-2">
        <i class="fas fa-exclamation-circle fa-lg"></i>
        <span><b>Group ID Anda masih kosong.</b> Ikuti langkah di bawah untuk mengaktifkan notifikasi.</span>
    </div>
<?php endif; ?>
<input type="text" id="groupIdField" name="group_id" value="<?php echo isset($userSettings['group_id']) ? htmlspecialchars($userSettings['group_id']) : ''; ?>" class="form-input w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-gray-100" readonly disabled>
<div id="toastGroupId" class="fixed top-6 right-6 z-50 hidden bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg font-semibold text-sm flex items-center gap-2 animate-fadeIn">
    <i class="fas fa-info-circle"></i> Group ID berhasil terisi otomatis!
</div>
                            <small class="text-gray-500">Group ID hanya dapat diisi otomatis melalui perintah WhatsApp, tidak bisa diubah manual.</small>
                            <div class="text-sm text-gray-700 mt-1">
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-2">
    <div class="flex items-center mb-2">
        <span class="text-green-600 mr-2"><i class="fas fa-robot fa-lg"></i></span>
        <span class="font-semibold">Isi Otomatis ID Grup WhatsApp</span>
    </div>
    <ol class="list-decimal ml-6 space-y-1 text-sm">
        <li>
            <span class="font-semibold">Bikin grup baru dan undang nomor sender ke grup WhatsApp Anda:</span>
            <ul class="list-disc ml-6 mt-1">
                <?php if (!empty($adminList)): ?>
                    <?php foreach ($adminList as $admin): ?>
                        <li class="mb-1">
                            <span class="font-semibold"><?php echo htmlspecialchars($admin['name']); ?>:</span>
                            <span class="text-blue-700">
                                <?php echo htmlspecialchars(PhoneUtils::format($admin['phone'] ?? '', true)); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="text-gray-500">Belum ada admin/sender terdaftar.</li>
                <?php endif; ?>
            </ul>
            <span class="text-xs text-gray-500">(Nomor di atas wajib diundang agar notifikasi bisa dikirim ke grup secara real time)</span>
        </li>
        <li>
            <span class="font-semibold">Salin & kirim perintah berikut ke grup WhatsApp Anda:</span><br>
            <div class="bg-gray-100 px-2 py-1 rounded mt-1 font-mono text-xs select-all" id="nottikIdCmd">
                /nottik-id <?php echo htmlspecialchars($userSettings['token'] ?? 'TOKEN_TIDAK_DITEMUKAN'); ?>
            </div>
            <button onclick="copyNottikId()" class="mt-2 px-2 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700">Salin Perintah</button>
        </li>
        <li>
            Tunggu beberapa detik, field <span class="font-semibold">ID Grup WhatsApp</span> di dashboard ini akan terisi otomatis.
        </li>
    </ol>
    <div class="mt-2 text-yellow-700 text-xs">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        Jangan bagikan token Anda ke orang lain.
    </div>
</div>
<script>
function copyNottikId() {
    const el = document.getElementById('nottikIdCmd');
    navigator.clipboard.writeText(el.innerText.trim());
    alert('Perintah berhasil disalin!');
}
</script>
                                <!-- Daftar admin -->
                                <div class="bg-white border border-gray-200 rounded-lg p-4 mt-2">
                                    <div class="flex items-center mb-2">
                                        <span class="text-blue-600 mr-2"><i class="fas fa-user-shield fa-lg"></i></span>
                                        <span class="font-semibold">Daftar Nomor WhatsApp Sender:</span>
                                    </div>
                                    <ul class="list-disc ml-6">
                                        <?php if (!empty($adminList)): ?>
                                            <?php foreach ($adminList as $admin): ?>
                                                <li class="mb-1">
                                                    <span class="font-semibold"><?php echo htmlspecialchars($admin['name']); ?>:</span>
                                                    <span class="text-blue-700">
                                                        <?php echo htmlspecialchars(PhoneUtils::format($admin['phone'] ?? '', true)); ?>
                                                    </span>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li class="text-gray-500">Belum ada admin terdaftar.</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- Tombol simpan untuk group_id dihilangkan karena field ini hanya bisa diisi otomatis oleh sistem -->
                    </form>
                <?php endif; ?>
            </div>

            <!-- Mikrotik Script -->
            <div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Script Mikrotik</h2>
    <p class="text-gray-600 mb-4">Copy masing-masing script berikut ke bagian PPP Profile Script Mikrotik Anda:</p>
    <?php if ($userData['status'] === 'pending'): ?>
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded mb-2 text-center">
            <i class="fas fa-lock mr-1"></i> Akun Anda masih <span class="font-bold">pending</span>.<br>
            Script Mikrotik akan muncul setelah akun Anda aktif.
        </div>
    <?php else:
        // Pisahkan script menjadi dua bagian: #ON UP dan #ON DOWN
        $onUp = '';
        $onDown = '';
        if (preg_match('/(#ON UP.*?)(#ON DOWN|$)/is', $mikrotikScript, $m)) {
            $onUp = trim($m[1]);
        }
        if (preg_match('/(#ON DOWN.*)/is', $mikrotikScript, $m)) {
            $onDown = trim($m[1]);
        }
    ?>
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-up text-green-500"></i>
            <span class="font-semibold text-gray-700">Script #ON UP</span>
        </div>
        <div class="relative">
            <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto" id="onUpScript"><?php echo htmlspecialchars($onUp); ?></pre>
            <button onclick="copyScript('onUpScript')" class="absolute top-2 right-2 bg-green-600 text-white p-2 rounded hover:bg-green-700" title="Copy Script #ON UP">
                <i class="fas fa-copy"></i>
            </button>
        </div>
    </div>
    <div>
        <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-down text-red-500"></i>
            <span class="font-semibold text-gray-700">Script #ON DOWN</span>
        </div>
        <div class="relative">
            <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto" id="onDownScript"><?php echo htmlspecialchars($onDown); ?></pre>
            <button onclick="copyScript('onDownScript')" class="absolute top-2 right-2 bg-green-600 text-white p-2 rounded hover:bg-green-700" title="Copy Script #ON DOWN">
                <i class="fas fa-copy"></i>
            </button>
        </div>
    </div>
    <script>
    function copyScript(id) {
        const el = document.getElementById(id);
        if (!el) return;
        const text = el.innerText;
        navigator.clipboard.writeText(text).then(function() {
            // Optionally show notification
        });
    }
    </script>
    <p class="text-sm text-gray-500 mt-2">Script ini sudah berisi token unik Anda.</p>
    <?php endif; ?>
</div>
        </div>

        <!-- Recent Logs -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mt-8">
            <div class="flex items-center mb-6">
    <div class="bg-green-100 text-green-700 rounded-full p-3 mr-3">
        <i class="fas fa-history fa-lg"></i>
    </div>
    <h2 class="text-2xl font-extrabold text-gray-800 tracking-tight flex items-center gap-2">
        Log Aktivitas Terbaru
        <?php
            $totalLog = $db->fetchColumn("SELECT COUNT(*) FROM pppoe_logs WHERE user_id = ?", [$userData['user_id']]);
        ?>
        <span class="ml-2 inline-flex items-center justify-center text-xs font-bold rounded-full bg-green-200 text-green-800 px-2 py-1" title="Total semua log event user ini">
            <?php echo number_format($totalLog); ?> Log
        </span>
    </h2>
</div>
            <div class="overflow-x-auto">
                <?php if (empty($recentLogs)): ?>
                    <div class="flex flex-col items-center justify-center py-10">
                        <svg width="56" height="56" fill="none" viewBox="0 0 56 56" class="mb-3"><circle cx="28" cy="28" r="28" fill="#F3F4F6"/><path d="M28 16v12l8 4" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <p class="text-gray-500 text-lg">Belum ada aktivitas tercatat.</p>
                    </div>
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
                        <?php
function format_tanggal_indo($tanggal) {
    setlocale(LC_TIME, 'id_ID.utf8', 'id_ID', 'IND');
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tgl = date('j', strtotime($tanggal));
    $bln = $bulan[(int)date('n', strtotime($tanggal))];
    $thn = date('Y', strtotime($tanggal));
    return $tgl . ' ' . $bln . ' ' . $thn;
}
?>
<?php foreach ($recentLogs as $log): ?>
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
            <div class="mt-4 text-right">
                <a href="logs.php" class="text-green-700 hover:underline font-semibold"><i class="fas fa-list mr-1"></i> Lihat Semua Log</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
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
// Script untuk copy to clipboard
if (document.getElementById('copyBtn')) {
    document.getElementById('copyBtn').addEventListener('click', function() {
        const scriptText = document.querySelector('pre').textContent;
        navigator.clipboard.writeText(scriptText).then(function() {
            alert('Script berhasil disalin!');
        }, function() {
            alert('Gagal menyalin script');
        });
    });
}

// Deklarasi variabel modal dan elemen terkait di scope global
const modal = document.getElementById('modalDetail');
const closeModalBtn = document.getElementById('closeModal');
const modalUsername = document.getElementById('modalUsername');
const modalChartWrap = document.getElementById('modalChartWrap');
const modalLoading = document.getElementById('modalLoading');
const modalError = document.getElementById('modalError');
let modalChart = null;

// Handler tombol detail (hanya lewat rebindDetailButtons)
// (event lama dihapus, cukup yang ini saja di bawah)

closeModalBtn.addEventListener('click', function() {
    modal.classList.add('hidden');
});
window.addEventListener('click', function(e) {
    if (e.target === modal) modal.classList.add('hidden');
});
</script>

    <script>
// Realtime polling log aktivitas terbaru (3 detik)
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
        `<td class=\"py-2 px-3 align-top\"><span class=\"font-semibold text-gray-800\"><i class=\"fas fa-user mr-1 text-gray-400\"></i>${log.username}</span> <button type=\"button\" class=\"ml-2 text-xs px-2 py-1 rounded bg-green-100 text-green-700 font-semibold hover:bg-green-200 focus:outline-none detail-btn\" data-username=\"${log.username}\"><i class=\"fas fa-chart-line mr-1\"></i>Detail</button></td>` +
        `<td class=\"py-2 px-3 align-top\">${ipOrReason}</td>` +
        `</tr>`;
}
function loadLogsRealtime() {
    fetch('logs-data.php')
        .then(res => res.json())
        .then(data => {
            if (!Array.isArray(data)) return;
            const tbody = document.getElementById('logsTbody');
            tbody.innerHTML = data.slice(0,5).map(renderLogRow).join('');
            rebindDetailButtons(); // Agar tombol Detail tetap aktif setelah refresh
        });
}
let lastLogTimestamp = null;
function showToastNotif(msg) {
    let toast = document.createElement('div');
    toast.className = 'fixed top-6 right-6 z-50 bg-yellow-400 text-yellow-900 px-6 py-3 rounded-lg shadow-lg font-semibold text-sm animate-bounceIn';
    toast.style.transition = 'opacity 0.3s';
    toast.innerHTML = `<i class=\"fas fa-bell mr-2\"></i>${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = 0; }, 2700);
    setTimeout(() => { toast.remove(); }, 3000);
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
            // Fetch data
            fetch(`../user-activity-history.php?username=${encodeURIComponent(username)}`)
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
            tbody.innerHTML = data.slice(0,5).map(renderLogRow).join('');
            rebindDetailButtons();
            if (data.length > 0) {
                let newest = data[0].waktu + ' ' + data[0].tanggal;
                if (lastLogTimestamp && newest !== lastLogTimestamp) {
                    showToastNotif('Ada aktivitas log baru!');
                }
                lastLogTimestamp = newest;
            }
        });
}
setInterval(loadLogsRealtime, 3000);
window.addEventListener('DOMContentLoaded', loadLogsRealtime);
</script>
<script>
// Toast notification otomatis jika Group ID baru saja terisi
(function() {
    const groupIdField = document.getElementById('groupIdField');
    if (!groupIdField) return;
    const groupId = groupIdField.value.trim();
    const storageKey = 'lastGroupId';
    const lastGroupId = localStorage.getItem(storageKey) || '';

    // Jika sebelumnya kosong dan sekarang ada isinya, tampilkan toast
    if (groupId && lastGroupId !== groupId) {
        showToastNotif('Group ID berhasil terisi otomatis!');
    }
    // Update localStorage dengan Group ID terbaru
    localStorage.setItem(storageKey, groupId);

    function showToastNotif(msg) {
        let toast = document.createElement('div');
        toast.className = 'fixed top-6 right-6 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg font-semibold text-sm animate-bounceIn';
        toast.style.transition = 'opacity 0.3s';
        toast.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${msg}`;
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.opacity = 0; }, 2700);
        setTimeout(() => { toast.remove(); }, 3000);
    }
})();
</script>
<script>
// Toast Group ID: hanya muncul jika field group_id berubah dari kosong menjadi terisi
(function() {
    const groupIdField = document.getElementById('groupIdField');
    const toast = document.getElementById('toastGroupId');
    if (!groupIdField || !toast) return;
    const current = groupIdField.value.trim();
    const storageKey = 'lastGroupId';
    const last = localStorage.getItem(storageKey) || '';
    // Jika sebelumnya kosong dan sekarang ada isinya, tampilkan toast
    if (last === '' && current !== '') {
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 4000);
    }
    // Update localStorage
    localStorage.setItem(storageKey, current);
})();
</script>
</body>
</html>
