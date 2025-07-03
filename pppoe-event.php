<?php
// pppoe-event.php - Endpoint untuk menerima notifikasi dari Mikrotik (Multi-tenant)
require_once 'includes/config.php';
require_once 'includes/database.php';

// Inisialisasi koneksi database
$db = Database::getInstance();
$pdo = $db->getConnection();

// Validasi token
if (!isset($_POST['token'])) {
    http_response_code(403);
    echo "Forbidden - Token Required";
    exit;
}

$token = $_POST['token'];

// Cari user berdasarkan token
$userData = $db->fetchOne(
    "SELECT u.*, us.group_id, us.token 
     FROM users u 
     JOIN user_settings us ON u.id = us.user_id 
     WHERE us.token = ? AND u.status = 'active'", 
    [$token]
);

if (!$userData) {
    http_response_code(403);
    echo "Forbidden - Invalid Token";
    exit;
}

// Ambil jenis event
$event = $_POST['event'] ?? 'unknown';
$user = $_POST['user'] ?? 'Unknown';

$date = date('Y-m-d');
$time = date('H:i:s');

// Siapkan variabel
$message = "";

if ($event === 'login') {
    $ip = $_POST['ip'] ?? 'Unknown';
    $caller = $_POST['caller'] ?? 'Unknown';
    $uptime = $_POST['uptime'] ?? 'Unknown';
    $service = $_POST['service'] ?? 'Unknown';
    $active = $_POST['active'] ?? '0';

    $message = "🔥 *Login Detected*\n\n";
    $message .= "*📅 Tanggal:* $date\n";
    $message .= "*⏰ Jam:* $time\n";
    $message .= "*🧑 Username:* `$user`\n";
    $message .= "*🌐 IP Address:* $ip\n";
    $message .= "*📞 Caller ID:* $caller\n";
    $message .= "*⏱ Uptime:* $uptime\n";
    $message .= "*🛠 Service:* $service\n";
    $message .= "*📊 Total Active Client:* `$active Client`\n";

    // Simpan ke database dengan user_id
    $db->insert('pppoe_logs', [
        'user_id' => $userData['id'],
        'event_type' => 'login',
        'username' => $user,
        'ip_address' => $ip,
        'caller_id' => $caller,
        'uptime' => $uptime,
        'service' => $service,
        'active_client' => $active,
        'event_date' => $date,
        'event_time' => $time
    ]);

} elseif ($event === 'logout') {
    $lastDisc = $_POST['lastdisc'] ?? 'Unknown';
    $lastLogout = $_POST['lastlogout'] ?? 'Unknown';
    $lastCall = $_POST['lastcall'] ?? 'Unknown';
    $active = $_POST['active'] ?? '0';

    $message = "⛔️ *Logout Detected*\n\n";
    $message .= "*📅 Tanggal:* $date\n";
    $message .= "*⏰ Jam:* $time\n";
    $message .= "*🧑 User:* `$user`\n";
    $message .= "*❌ Last Reason:* $lastDisc\n";
    $message .= "*🔕 Last Logout:* $lastLogout\n";
    $message .= "*📞 Last Caller ID:* $lastCall\n";
    $message .= "*📊 Total Active Client:* `$active Client`\n";

    // Simpan ke database dengan user_id
    $db->insert('pppoe_logs', [
        'user_id' => $userData['id'],
        'event_type' => 'logout',
        'username' => $user,
        'last_disconnect_reason' => $lastDisc,
        'last_logout' => $lastLogout,
        'last_caller_id' => $lastCall,
        'active_client' => $active,
        'event_date' => $date,
        'event_time' => $time
    ]);

} else {
    http_response_code(400);
    echo "Bad Request - Unknown Event";
    exit;
}

// Ambil pengaturan WhatsApp dari database
$whatsappSettings = $db->fetchOne("SELECT * FROM whatsapp_settings LIMIT 1");

if (!$whatsappSettings || $whatsappSettings['connection_status'] != 'connected') {
    // Jika tidak ada koneksi WhatsApp, hanya simpan ke database
    echo "OK - $event data disimpan (WhatsApp tidak terhubung)";
    exit;
}

// Kirim notifikasi ke WhatsApp Group pengguna
$groupId = $userData['group_id'] ?? null;

if (!$groupId) {
    echo "OK - $event data disimpan (Group ID tidak dikonfigurasi)";
    exit;
}

// Kirim WhatsApp
$data = [
    'phone' => $groupId,
    'message' => $message,
];

// Gunakan endpoint khusus untuk pengiriman pesan
$ch = curl_init($whatsappSettings['api_url'] . '/send/message');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

// Tambahkan autentikasi
$credentials = $whatsappSettings['api_user'] . ':' . $whatsappSettings['api_pass'];
$auth = base64_encode($credentials);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . $auth,
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Respon balik
if ($httpCode == 200) {
    echo "OK - $event pesan dikirim & database disimpan";
} else {
    echo "OK - $event data disimpan (Gagal kirim WhatsApp: $httpCode)";
}
?>
