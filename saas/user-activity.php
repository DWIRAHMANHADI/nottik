<?php
// user-activity.php
require_once 'config.php';
require_once 'database.php';
header('Content-Type: application/json');

if (!isset($_GET['username']) || empty($_GET['username'])) {
    echo json_encode(['error' => 'Username required']);
    exit;
}

$username = $_GET['username'];
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Query jumlah login dan logout hari ini untuk username
$sql = "SELECT event_type, COUNT(*) as total FROM pppoe_logs WHERE username = ? AND event_date = ? GROUP BY event_type";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username, $date]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = ['login' => 0, 'logout' => 0];
foreach ($result as $row) {
    if ($row['event_type'] === 'login') {
        $data['login'] = (int)$row['total'];
    } elseif ($row['event_type'] === 'logout') {
        $data['logout'] = (int)$row['total'];
    }
}


// Kirim juga tanggal yang dicek
$data['date'] = $date;

// Info tambahan: total aktivitas hari ini
$data['total'] = $data['login'] + $data['logout'];

echo json_encode($data);
