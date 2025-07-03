<?php
// Endpoint: user-activity-history.php
// Return login/logout count for the last 7 days for a user
require_once 'includes/config.php';
require_once 'includes/database.php';
header('Content-Type: application/json');

if (!isset($_GET['username']) || empty($_GET['username'])) {
    echo json_encode(['error' => 'Username required']);
    exit;
}

$username = $_GET['username'];

// Pastikan koneksi PDO
if (!isset($pdo) || !$pdo) {
    if (class_exists('Database')) {
        $pdo = Database::getInstance()->getConnection();
    }
}
// Ambil 7 hari terakhir
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
}
$placeholders = implode(',', array_fill(0, count($dates), '?'));

// Query login/logout per hari
$sql = "SELECT event_date, event_type, COUNT(*) as total FROM pppoe_logs WHERE username = ? AND event_date IN ($placeholders) GROUP BY event_date, event_type";
$params = array_merge([$username], $dates);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format output: array of {date, login, logout, status}
$data = [];
$total_login = 0;
$total_logout = 0;
$bulanIndo = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
foreach ($dates as $d) {
    // Format tanggal Indonesia
    $tglArr = explode('-', $d);
    $tanggal_id = $tglArr[2] . ' ' . $bulanIndo[(int)$tglArr[1]-1] . ' ' . $tglArr[0];
    $data[$d] = ['date' => $d, 'tanggal_id' => $tanggal_id, 'login' => 0, 'logout' => 0, 'status' => 'Aman'];
}
foreach ($result as $row) {
    $type = $row['event_type'];
    $date = $row['event_date'];
    $data[$date][$type] = (int)$row['total'];
}
// Hitung status dan total login/logout
foreach ($data as &$row) {
    $login = $row['login'];
    $logout = $row['logout'];
    $total_login += $login;
    $total_logout += $logout;
    if ($login <= 3) {
        $row['status'] = 'Aman';
    } elseif ($login == 4) {
        $row['status'] = 'Perlu dicek';
    } elseif ($login >= 5) {
        $row['status'] = 'Ada kendala';
    }
}
unset($row);
// Output profile + data harian
$output = [
    'profile' => [
        'username' => $username,
        'total_login' => $total_login,
        'total_logout' => $total_logout
    ],
    'history' => array_values($data)
];
echo json_encode($output);
