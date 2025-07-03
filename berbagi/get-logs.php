<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require_once 'config.php';
require_once 'database.php';
header('Content-Type: application/json');

// Ambil filter dari GET
$filterStart = isset($_GET['start']) && $_GET['start'] !== '' ? $_GET['start'] : '';
$filterEnd = isset($_GET['end']) && $_GET['end'] !== '' ? $_GET['end'] : '';
$filterEvent = isset($_GET['event']) && $_GET['event'] !== '' ? $_GET['event'] : '';
$filterUsername = isset($_GET['username']) && $_GET['username'] !== '' ? $_GET['username'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = isset($_GET['perPage']) && in_array((int)$_GET['perPage'], [20,50,100]) ? (int)$_GET['perPage'] : 20;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
if ($filterStart && $filterEnd) {
    $where[] = 'event_date BETWEEN :start AND :end';
    $params[':start'] = $filterStart;
    $params[':end'] = $filterEnd;
} elseif ($filterStart) {
    $where[] = 'event_date >= :start';
    $params[':start'] = $filterStart;
} elseif ($filterEnd) {
    $where[] = 'event_date <= :end';
    $params[':end'] = $filterEnd;
}
if ($filterEvent && in_array($filterEvent, ['login','logout'])) {
    $where[] = 'event_type = :event';
    $params[':event'] = $filterEvent;
}
if ($filterUsername) {
    $where[] = 'username LIKE :username';
    $params[':username'] = "%$filterUsername%";
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Hitung total log
$totalLogsStmt = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs $whereSql");
foreach ($params as $k => $v) { $totalLogsStmt->bindValue($k, $v); }
$totalLogsStmt->execute();
$totalLogs = $totalLogsStmt->fetchColumn();
$totalPages = ceil($totalLogs / $perPage);

// Ambil data log
$stmt = $pdo->prepare("SELECT * FROM pppoe_logs $whereSql ORDER BY id DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kirim response JSON
$response = [
    'logs' => $logs,
    'totalLogs' => $totalLogs,
    'totalPages' => $totalPages,
    'page' => $page,
    'perPage' => $perPage,
    'offset' => $offset
];
echo json_encode($response);
