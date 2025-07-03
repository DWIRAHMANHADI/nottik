<?php
// Endpoint: user-activity-history.php
// Return login/logout count for the last 7 days for a user
require_once 'config.php';
require_once 'database.php';
header('Content-Type: application/json');

if (!isset($_GET['username']) || empty($_GET['username'])) {
    echo json_encode(['error' => 'Username required']);
    exit;
}

$username = $_GET['username'];
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

// Format output: array of {date, login, logout}
$data = [];
foreach ($dates as $d) {
    $data[$d] = ['date' => $d, 'login' => 0, 'logout' => 0];
}
foreach ($result as $row) {
    $type = $row['event_type'];
    $date = $row['event_date'];
    $data[$date][$type] = (int)$row['total'];
}
// Re-index
$data = array_values($data);
echo json_encode($data);
