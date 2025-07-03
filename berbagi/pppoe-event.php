<?php
// pppoe-event.php
require 'config.php';
require 'database.php';

// Validasi token
if (!isset($_POST['token']) || $_POST['token'] !== SECRET_TOKEN) {
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
$logFile = "";

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
    $message .= "*📊 Total Active CLient:* `$active Client`\n";

    $logFile = 'login-history.log';

    // Simpan ke database
    $stmt = $pdo->prepare("INSERT INTO pppoe_logs (event_type, username, ip_address, caller_id, uptime, service, active_client, event_date, event_time) VALUES ('login', ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user, $ip, $caller, $uptime, $service, $active, $date, $time]);

} elseif ($event === 'logout') {
    $lastDisc = $_POST['lastdisc'] ?? 'Unknown';
    $lastLogout = $_POST['lastlogout'] ?? 'Unknown';
    $lastCall = $_POST['lastcall'] ?? 'Unknown';
    $active = $_POST['active'] ?? '0';

    $message = "📢 *Logout Detected*\n\n";
    $message .= "*📅 Tanggal:* $date\n";
    $message .= "*⏰ Jam:* $time\n";
    $message .= "*🧑 User:* `$user`\n";
    $message .= "*❌ Last Reason:* $lastDisc\n";
    $message .= "*🔕 Last Logout:* $lastLogout\n";
    $message .= "*📞 Last Caller ID:* $lastCall\n";
    $message .= "*📊 Total Active Client:* `$active Client`\n";

    $logFile = 'logout-history.log';

    // Simpan ke database
    $stmt = $pdo->prepare("INSERT INTO pppoe_logs (event_type, username, last_disconnect_reason, last_logout, last_caller_id, active_client, event_date, event_time) VALUES ('logout', ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user, $lastDisc, $lastLogout, $lastCall, $active, $date, $time]);

} else {
    http_response_code(400);
    echo "Bad Request - Unknown Event";
    exit;
}

// Simpan juga ke file log bila di aktifkan
//file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Event: $event | User: $user\n", FILE_APPEND);

// Kirim WhatsApp
$data = [
    'phone' => WHATSAPP_GROUP_ID,
    'message' => $message,
];

// Gunakan endpoint khusus untuk pengiriman pesan
$ch = curl_init(WHATSAPP_SEND_MESSAGE_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
// --- Tambahkan AUTHENTICATION di sini ---
$credentials = WHATSAPP_API_USER . ':' . WHATSAPP_API_PASS;
$auth = base64_encode($credentials);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . $auth,
    'Content-Type: application/x-www-form-urlencoded'
]);
// --- selesai tambahan AUTH ---
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Respon balik
if ($httpCode == 200) {
    echo "OK - $event pesan dikirim & database disimpan";
} else {
    echo "Error - Gagal kirim $event pesan";
}
?>
