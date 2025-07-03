<?php
// Endpoint: user/logs-data.php
// Return log aktivitas terbaru (20 baris) untuk user yang sedang login (JSON)
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/Auth.php';

session_start();
$auth = new Auth();
$userData = $auth->isLoggedIn();
if (!$userData) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = Database::getInstance();
$logs = $db->fetchAll(
    "SELECT * FROM pppoe_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 20",
    [$userData['user_id']]
);

function format_tanggal_indo($tanggal) {
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tgl = date('j', strtotime($tanggal));
    $bln = $bulan[(int)date('n', strtotime($tanggal))];
    $thn = date('Y', strtotime($tanggal));
    return $tgl . ' ' . $bln . ' ' . $thn;
}

// Format output agar langsung siap render tabel
$out = [];
foreach ($logs as $log) {
    $out[] = [
        'tanggal' => format_tanggal_indo($log['event_date']),
        'waktu' => htmlspecialchars($log['event_time']),
        'event_type' => $log['event_type'],
        'username' => htmlspecialchars($log['username']),
        'ip_address' => htmlspecialchars($log['ip_address'] ?? '-'),
        'last_disconnect_reason' => htmlspecialchars($log['last_disconnect_reason'] ?? '-')
    ];
}
echo json_encode($out);
