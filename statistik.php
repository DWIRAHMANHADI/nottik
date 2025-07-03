<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
// statistik.php
require_once 'config.php';
require_once 'database.php';

$today = date('Y-m-d');
$thisMonth = date('Y-m');

// --- Grafik Login/Logout Hari Ini (per jam) ---
$hourLabels = [];
$loginHourData = [];
$logoutHourData = [];
for ($h = 0; $h < 24; $h++) {
    $hour = str_pad($h, 2, '0', STR_PAD_LEFT);
    $hourLabels[] = $hour . ':00';
    $stmtL = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ? AND HOUR(event_time) = ?");
    $stmtL->execute([$today, $h]);
    $loginHourData[] = (int)$stmtL->fetchColumn();
    $stmtO = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ? AND HOUR(event_time) = ?");
    $stmtO->execute([$today, $h]);
    $logoutHourData[] = (int)$stmtO->fetchColumn();
}
// --- Persentase Hari Ini vs Kemarin ---
$yesterday = date('Y-m-d', strtotime('-1 day'));
$loginTodayTotal = array_sum($loginHourData);
$logoutTodayTotal = array_sum($logoutHourData);
$stmtL = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ?");
$stmtL->execute([$yesterday]);
$loginYesterday = (int)$stmtL->fetchColumn();
$stmtO = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ?");
$stmtO->execute([$yesterday]);
$logoutYesterday = (int)$stmtO->fetchColumn();
$loginTodayPct = $loginYesterday ? (($loginTodayTotal - $loginYesterday) / $loginYesterday) * 100 : ($loginTodayTotal ? 100 : 0);
$logoutTodayPct = $logoutYesterday ? (($logoutTodayTotal - $logoutYesterday) / $logoutYesterday) * 100 : ($logoutTodayTotal ? 100 : 0);
// --- END Persentase Hari Ini ---


// --- Grafik Login/Logout 1 Bulan Terakhir (per hari) ---
$monthLabels = [];
$loginMonthData = [];
$logoutMonthData = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $monthLabels[] = date('d M', strtotime($date));
    $stmtL = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ?");
    $stmtL->execute([$date]);
    $loginMonthData[] = (int)$stmtL->fetchColumn();
    $stmtO = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ?");
    $stmtO->execute([$date]);
    $logoutMonthData[] = (int)$stmtO->fetchColumn();
}

// --- Grafik 7 hari terakhir (sudah ada) ---
$labels = [];
$loginData = [];
$logoutData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d M', strtotime($date));
    $stmtL = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ?");
    $stmtL->execute([$date]);
    $loginData[] = (int)$stmtL->fetchColumn();
    $stmtO = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ?");
    $stmtO->execute([$date]);
    $logoutData[] = (int)$stmtO->fetchColumn();
}
// --- Persentase 7 hari terakhir vs 7 hari sebelumnya ---
$login7 = array_sum($loginData);
$logout7 = array_sum($logoutData);
$login7prev = 0;
$logout7prev = 0;
for ($i = 13; $i >= 7; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmtL = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ?");
    $stmtL->execute([$date]);
    $login7prev += (int)$stmtL->fetchColumn();
    $stmtO = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ?");
    $stmtO->execute([$date]);
    $logout7prev += (int)$stmtO->fetchColumn();
}
$login7Pct = $login7prev ? (($login7 - $login7prev) / $login7prev) * 100 : ($login7 ? 100 : 0);
$logout7Pct = $logout7prev ? (($logout7 - $logout7prev) / $logout7prev) * 100 : ($logout7 ? 100 : 0);
// --- END Persentase 7 hari ---
// --- Persentase 1 bulan terakhir vs 1 bulan sebelumnya ---
$loginMonthSum = array_sum($loginMonthData);
$logoutMonthSum = array_sum($logoutMonthData);
$loginMonthPrev = 0;
$logoutMonthPrev = 0;
for ($i = 59; $i >= 30; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmtL = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'login' AND event_date = ?");
    $stmtL->execute([$date]);
    $loginMonthPrev += (int)$stmtL->fetchColumn();
    $stmtO = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ?");
    $stmtO->execute([$date]);
    $logoutMonthPrev += (int)$stmtO->fetchColumn();
}
$loginMonthPct = $loginMonthPrev ? (($loginMonthSum - $loginMonthPrev) / $loginMonthPrev) * 100 : ($loginMonthSum ? 100 : 0);
$logoutMonthPct = $logoutMonthPrev ? (($logoutMonthSum - $logoutMonthPrev) / $logoutMonthPrev) * 100 : ($logoutMonthSum ? 100 : 0);
// --- END Persentase 1 bulan ---

// Definisi fungsi renderUserCol
if (!function_exists('renderUserCol')) {
function renderUserCol($userList, $userNameList, $badges, $maxLogin, $maxLogout, $start = 0, $end = 5) {
    echo "<ul class='space-y-4 text-sm'>";
    for ($i=$start; $i<$end && $i<count($userList); $i++) {
        $data = $userList[$i];
        $uname = $userNameList[$i];
        $badge = $badges[$i] ?? ($i+1);
        $loginVal = $data['login'];
        $logoutVal = $data['logout'];
        $loginPct = $maxLogin ? intval($loginVal/$maxLogin*100) : 0;
        $logoutPct = $maxLogout ? intval($logoutVal/$maxLogout*100) : 0;
        $tooltip = "Login: $loginVal, Logout: $logoutVal";
        echo "<li class='flex flex-col gap-1'>
        <div class='flex items-center gap-2'>
            <span class='text-lg'>{$badge}</span>
            <span class='flex-shrink-0 w-8 h-8 rounded-full bg-green-100 flex items-center justify-center font-bold text-green-700' title='User'>{$uname[0]}</span>
            <span class='font-semibold text-gray-800' title='{$uname}'>{$uname}</span>
            <span class='ml-auto font-mono text-xs text-green-700' title='Login'>{$loginVal}</span>
            <span class='font-mono text-xs text-red-700' title='Logout'>{$logoutVal}</span>
        </div>
        <div class='w-full h-2 bg-gray-200 rounded flex overflow-hidden' title='{$tooltip}'>
            <div class='h-2 bg-green-400' style='width:{$loginPct}%;'></div>
            <div class='h-2 bg-red-400' style='width:{$logoutPct}%;'></div>
        </div>
        </li>";
    }
    echo "</ul>";
}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik PPPoE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl md:text-3xl font-bold mb-8 text-center text-indigo-700">Statistik PPPoE</h1>
        <div class="flex justify-end mb-6">
          <a href="index.php" class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">
            &larr; Kembali ke Data Detail
          </a>
        </div>
        <!-- CARD: HARI INI & 7 HARI TERAKHIR -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div class="bg-white rounded-lg shadow-md p-3">
                <div class="flex flex-wrap justify-center gap-4 mb-2">
<?php
$todayAbs = [
    'Login' => $loginTodayTotal - $loginYesterday,
    'Logout' => $logoutTodayTotal - $logoutYesterday
];
$todayPrev = [
    'Login' => $loginYesterday,
    'Logout' => $logoutYesterday
];
$todayNow = [
    'Login' => $loginTodayTotal,
    'Logout' => $logoutTodayTotal
];
foreach ([
    ['label' => 'Login', 'pct' => $loginTodayPct, 'abs' => $todayAbs['Login'], 'prev' => $todayPrev['Login'], 'now' => $todayNow['Login']],
    ['label' => 'Logout', 'pct' => $logoutTodayPct, 'abs' => $todayAbs['Logout'], 'prev' => $todayPrev['Logout'], 'now' => $todayNow['Logout']]
] as $d) {
    $color = $d['pct'] > 0 ? 'green' : ($d['pct'] < 0 ? 'red' : 'gray');
    $icon = $d['pct'] > 0 ? '▲' : ($d['pct'] < 0 ? '▼' : '');
    $pct = number_format(abs($d['pct']), 1);
    $abs = ($d['abs'] > 0 ? '+' : ($d['abs'] < 0 ? '' : '')) . $d['abs'];
    $tooltip = $d['prev'] == 0 ? 'Tidak ada data pembanding periode sebelumnya' : ("Dibandingkan periode sebelumnya: {$d['prev']} → {$d['now']}");
    echo "<span class='inline-flex items-center px-2 py-1 rounded bg-{$color}-100 text-{$color}-700 font-semibold text-xs' title='{$tooltip}'><span class='mr-1'>{$icon}</span>{$d['label']}: {$pct}% ({$abs})</span>";
}
?>
            </div>
            <h2 class="text-lg font-medium mb-4 text-center">Grafik Login & Logout Hari Ini (per Jam)</h2>
            <canvas id="todayChart" height="100"></canvas>
            <div class="flex justify-center gap-2 mb-2">
              <span class="inline-flex items-center px-2 py-1 rounded bg-green-100 text-green-700 text-xs font-semibold">Login: <?php echo $loginTodayTotal; ?></span>
              <span class="inline-flex items-center px-2 py-1 rounded bg-red-100 text-red-700 text-xs font-semibold">Logout: <?php echo $logoutTodayTotal; ?></span>
            </div>
            <!-- TOP 10 USER LOGIN/LOGOUT HARI INI (DUAL BAR, 2 KOLOM) -->
            <div class="mt-6 grid md:grid-cols-2 gap-6">
<?php
// Top 10 Login Hari Ini
$topLoginToday = $pdo->prepare("SELECT username, COUNT(*) as total FROM pppoe_logs WHERE event_type = 'login' AND event_date = ? GROUP BY username ORDER BY total DESC LIMIT 10");
$topLoginToday->execute([$today]);
$topLoginToday = $topLoginToday->fetchAll(PDO::FETCH_ASSOC);
// Top 10 Logout Hari Ini
$topLogoutToday = $pdo->prepare("SELECT username, COUNT(*) as total FROM pppoe_logs WHERE event_type = 'logout' AND event_date = ? GROUP BY username ORDER BY total DESC LIMIT 10");
$topLogoutToday->execute([$today]);
$topLogoutToday = $topLogoutToday->fetchAll(PDO::FETCH_ASSOC);
// Gabungkan top login dan logout jadi 1 list user unik
$userSet = [];
foreach ($topLoginToday as $row) $userSet[$row['username']] = ['login' => $row['total'], 'logout' => 0];
foreach ($topLogoutToday as $row) {
    if (!isset($userSet[$row['username']])) $userSet[$row['username']] = ['login' => 0, 'logout' => $row['total']];
    else $userSet[$row['username']]['logout'] = $row['total'];
}
// Urutkan berdasarkan total login+logout desc, ambil 10 teratas
uasort($userSet, function($a, $b) {
    return ($b['login']+$b['logout']) <=> ($a['login']+$a['logout']);
});
$userSet = array_slice($userSet, 0, 10, true);
// Cari max untuk scaling bar
$maxLogin = 1; $maxLogout = 1;
foreach ($userSet as $u) {
    if ($u['login'] > $maxLogin) $maxLogin = $u['login'];
    if ($u['logout'] > $maxLogout) $maxLogout = $u['logout'];
}
$badges = ['🥇','🥈','🥉','4','5','6','7','8','9','10'];
$userList = array_values($userSet);
$userNameList = array_keys($userSet);

$userNameList = array_keys($userSet);
renderUserCol($userList, $userNameList, $badges, $maxLogin, $maxLogout, 0, 5);
renderUserCol($userList, $userNameList, $badges, $maxLogin, $maxLogout, 5, 10);
?>
            </div>
        </div>
            <div class="bg-white rounded-lg shadow-md p-3">
                <div class="flex flex-wrap justify-center gap-4 mb-2">
<?php
$login7Abs = $login7 - $login7prev;
$logout7Abs = $logout7 - $logout7prev;
foreach ([
    ['label' => 'Login', 'pct' => $login7Pct, 'abs' => $login7Abs, 'prev' => $login7prev, 'now' => $login7],
    ['label' => 'Logout', 'pct' => $logout7Pct, 'abs' => $logout7Abs, 'prev' => $logout7prev, 'now' => $logout7]
] as $d) {
    $color = $d['pct'] > 0 ? 'green' : ($d['pct'] < 0 ? 'red' : 'gray');
    $icon = $d['pct'] > 0 ? '▲' : ($d['pct'] < 0 ? '▼' : '');
    $pct = number_format(abs($d['pct']), 1);
    $abs = ($d['abs'] > 0 ? '+' : ($d['abs'] < 0 ? '' : '')) . $d['abs'];
    $tooltip = $d['prev'] == 0 ? 'Tidak ada data pembanding periode sebelumnya' : ("Dibandingkan periode sebelumnya: {$d['prev']} → {$d['now']}");
    echo "<span class='inline-flex items-center px-2 py-1 rounded bg-{$color}-100 text-{$color}-700 font-semibold text-xs' title='{$tooltip}'><span class='mr-1'>{$icon}</span>{$d['label']}: {$pct}% ({$abs})</span>";
}
?>
</div>
            <h2 class="text-lg font-medium mb-4 text-center">Grafik Login & Logout 7 Hari Terakhir</h2>
            <canvas id="statChart" height="100"></canvas>
            <div class="flex justify-center gap-2 mb-2">
              <span class="inline-flex items-center px-2 py-1 rounded bg-green-100 text-green-700 text-xs font-semibold">Login: <?php echo $login7; ?></span>
              <span class="inline-flex items-center px-2 py-1 rounded bg-red-100 text-red-700 text-xs font-semibold">Logout: <?php echo $logout7; ?></span>
            </div>
            <!-- TOP 10 USER LOGIN/LOGOUT 7 HARI TERAKHIR (DUAL BAR, 2 KOLOM) -->
            <div class="mt-6 grid md:grid-cols-2 gap-6">
<?php
$date7 = date('Y-m-d', strtotime('-6 days'));
// Top 10 Login 7 Hari
$topLogin7 = $pdo->prepare("SELECT username, COUNT(*) as total FROM pppoe_logs WHERE event_type = 'login' AND event_date >= ? AND event_date <= ? GROUP BY username ORDER BY total DESC LIMIT 10");
$topLogin7->execute([$date7, $today]);
$topLogin7 = $topLogin7->fetchAll(PDO::FETCH_ASSOC);
// Top 10 Logout 7 Hari
$topLogout7 = $pdo->prepare("SELECT username, COUNT(*) as total FROM pppoe_logs WHERE event_type = 'logout' AND event_date >= ? AND event_date <= ? GROUP BY username ORDER BY total DESC LIMIT 10");
$topLogout7->execute([$date7, $today]);
$topLogout7 = $topLogout7->fetchAll(PDO::FETCH_ASSOC);
// Gabungkan top login dan logout jadi 1 list user unik
$userSet = [];
foreach ($topLogin7 as $row) $userSet[$row['username']] = ['login' => $row['total'], 'logout' => 0];
foreach ($topLogout7 as $row) {
    if (!isset($userSet[$row['username']])) $userSet[$row['username']] = ['login' => 0, 'logout' => $row['total']];
    else $userSet[$row['username']]['logout'] = $row['total'];
}
// Urutkan berdasarkan total login+logout desc, ambil 10 teratas
uasort($userSet, function($a, $b) {
    return ($b['login']+$b['logout']) <=> ($a['login']+$a['logout']);
});
$userSet = array_slice($userSet, 0, 10, true);
// Cari max untuk scaling bar
$maxLogin = 1; $maxLogout = 1;
foreach ($userSet as $u) {
    if ($u['login'] > $maxLogin) $maxLogin = $u['login'];
    if ($u['logout'] > $maxLogout) $maxLogout = $u['logout'];
}
$badges = ['🥇','🥈','🥉','4','5','6','7','8','9','10'];
$userList = array_values($userSet);
$userNameList = array_keys($userSet);

$userNameList = array_keys($userSet);
renderUserCol($userList, $userNameList, $badges, $maxLogin, $maxLogout, 0, 5);
renderUserCol($userList, $userNameList, $badges, $maxLogin, $maxLogout, 5, 10);
?>
            </div>
        </div>
        <!-- CARD: 1 BULAN TERAKHIR -->
        <div class="bg-white rounded-lg shadow-md p-3 mb-4">
            <div class="flex flex-wrap justify-center gap-4 mb-2">
<?php
$loginMonthAbs = $loginMonthSum - $loginMonthPrev;
$logoutMonthAbs = $logoutMonthSum - $logoutMonthPrev;
foreach ([
    ['label' => 'Login', 'pct' => $loginMonthPct, 'abs' => $loginMonthAbs, 'prev' => $loginMonthPrev, 'now' => $loginMonthSum],
    ['label' => 'Logout', 'pct' => $logoutMonthPct, 'abs' => $logoutMonthAbs, 'prev' => $logoutMonthPrev, 'now' => $logoutMonthSum]
] as $d) {
    $color = $d['pct'] > 0 ? 'green' : ($d['pct'] < 0 ? 'red' : 'gray');
    $icon = $d['pct'] > 0 ? '▲' : ($d['pct'] < 0 ? '▼' : '');
    $pct = number_format(abs($d['pct']), 1);
    $abs = ($d['abs'] > 0 ? '+' : ($d['abs'] < 0 ? '' : '')) . $d['abs'];
    $tooltip = $d['prev'] == 0 ? 'Tidak ada data pembanding periode sebelumnya' : ("Dibandingkan periode sebelumnya: {$d['prev']} → {$d['now']}");
    echo "<span class='inline-flex items-center px-2 py-1 rounded bg-{$color}-100 text-{$color}-700 font-semibold text-xs' title='{$tooltip}'><span class='mr-1'>{$icon}</span>{$d['label']}: {$pct}% ({$abs})</span>";
}
?>
</div>
            <h2 class="text-lg font-medium mb-4 text-center">Grafik Login & Logout 1 Bulan Terakhir</h2>
            <canvas id="monthChart" height="100"></canvas>
            <div class="flex justify-center gap-2 mb-2">
              <span class="inline-flex items-center px-2 py-1 rounded bg-green-100 text-green-700 text-xs font-semibold">Login: <?php echo $loginMonthSum; ?></span>
              <span class="inline-flex items-center px-2 py-1 rounded bg-red-100 text-red-700 text-xs font-semibold">Logout: <?php echo $logoutMonthSum; ?></span>
            </div>
            <!-- TOP 5 USER LOGIN/LOGOUT 1 BULAN TERAKHIR (DUAL BAR) -->
            <div class="mt-6">
<?php
// Pastikan variabel sudah ada dan array
$date30 = date('Y-m-d', strtotime('-29 days'));
if (!isset($topLoginMonth)) {
    $topLoginMonth = $pdo->prepare("SELECT username, COUNT(*) as total FROM pppoe_logs WHERE event_type = 'login' AND event_date >= ? AND event_date <= ? GROUP BY username ORDER BY total DESC LIMIT 10");
    $topLoginMonth->execute([$date30, $today]);
    $topLoginMonth = $topLoginMonth->fetchAll(PDO::FETCH_ASSOC);
}
if (!isset($topLogoutMonth)) {
    $topLogoutMonth = $pdo->prepare("SELECT username, COUNT(*) as total FROM pppoe_logs WHERE event_type = 'logout' AND event_date >= ? AND event_date <= ? GROUP BY username ORDER BY total DESC LIMIT 10");
    $topLogoutMonth->execute([$date30, $today]);
    $topLogoutMonth = $topLogoutMonth->fetchAll(PDO::FETCH_ASSOC);
}
$userSet = [];
foreach ($topLoginMonth as $row) $userSet[$row['username']] = ['login' => $row['total'], 'logout' => 0];
foreach ($topLogoutMonth as $row) {
    if (!isset($userSet[$row['username']])) $userSet[$row['username']] = ['login' => 0, 'logout' => $row['total']];
    else $userSet[$row['username']]['logout'] = $row['total'];
}
// Urutkan berdasarkan total login+logout desc, ambil 10 teratas
uasort($userSet, function($a, $b) {
    return ($b['login']+$b['logout']) <=> ($a['login']+$a['logout']);
});
$userSet = array_slice($userSet, 0, 10, true);
// Cari max untuk scaling bar
$maxLogin = 1; $maxLogout = 1;
foreach ($userSet as $u) {
    if ($u['login'] > $maxLogin) $maxLogin = $u['login'];
    if ($u['logout'] > $maxLogout) $maxLogout = $u['logout'];
}
$badges = ['🥇','🥈','🥉','4','5','6','7','8','9','10'];
$userNameList = array_keys($userSet);

echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';
renderUserCol($userList, $userNameList, $badges, $maxLogin, $maxLogout, 0, 5);
renderUserCol($userList, $userNameList, $badges, $maxLogin, $maxLogout, 5, 10);
echo '</div>';
?>
            </div>
        </div>
        <!-- CARD: TIMELINE USER (GRAFIK PER JAM) -->
        <div class="bg-white rounded-lg shadow-md p-3 mb-4">
            <h2 class="text-lg font-medium mb-4 text-center">Timeline Aktivitas User (Grafik per Jam)</h2>
            <?php
            // Ambil semua username unik
            $stmt = $pdo->query("SELECT DISTINCT username FROM pppoe_logs ORDER BY username ASC");
            $usernames = $stmt->fetchAll(PDO::FETCH_COLUMN);
            // Pilihan user & tanggal
            $selectedUser = isset($_GET['timeline_user']) ? $_GET['timeline_user'] : (count($usernames) ? $usernames[0] : '');
            $selectedDate = isset($_GET['timeline_date']) ? $_GET['timeline_date'] : date('Y-m-d');
            // Query data per jam
            $timelineHourLabels = [];
            $timelineLoginHourData = [];
            $timelineLogoutHourData = [];
            for ($h = 0; $h < 24; $h++) {
                $hour = str_pad($h, 2, '0', STR_PAD_LEFT);
                $timelineHourLabels[] = $hour . ':00';
                $stmtL = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE username = ? AND event_type = 'login' AND event_date = ? AND HOUR(event_time) = ?");
                $stmtL->execute([$selectedUser, $selectedDate, $h]);
                $timelineLoginHourData[] = (int)$stmtL->fetchColumn();
                $stmtO = $pdo->prepare("SELECT COUNT(*) FROM pppoe_logs WHERE username = ? AND event_type = 'logout' AND event_date = ? AND HOUR(event_time) = ?");
                $stmtO->execute([$selectedUser, $selectedDate, $h]);
                $timelineLogoutHourData[] = (int)$stmtO->fetchColumn();
            }
            ?>
            <form method="GET" class="flex flex-col md:flex-row gap-4 mb-4 justify-center items-end w-full">
                <div class="w-full md:w-auto">
    <label for="timeline_user" class="block text-xs font-semibold mb-1">User</label>
    <select name="timeline_user" id="timeline_user" class="border rounded px-2 py-1 text-sm w-full">
        <?php foreach ($usernames as $u): ?>
            <option value="<?= htmlspecialchars($u) ?>" <?= $u == $selectedUser ? 'selected' : '' ?>><?= htmlspecialchars($u) ?></option>
        <?php endforeach; ?>
    </select>
</div>
                <div class="w-full md:w-auto">
    <label for="timeline_date" class="block text-xs font-semibold mb-1">Tanggal</label>
    <input type="date" name="timeline_date" id="timeline_date" class="border rounded px-2 py-1 text-sm w-full" value="<?= htmlspecialchars($selectedDate) ?>">
</div>
                <div class="w-full md:w-auto">
    <button type="submit" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded font-semibold text-sm">Tampilkan</button>
</div>
            </form>
            <canvas id="timelineChart" height="100"></canvas>
            <div class="flex justify-center gap-2 mb-2">
              <span class="inline-flex items-center px-2 py-1 rounded bg-green-100 text-green-700 text-xs font-semibold">Login: <?php echo array_sum($timelineLoginHourData); ?></span>
              <span class="inline-flex items-center px-2 py-1 rounded bg-red-100 text-red-700 text-xs font-semibold">Logout: <?php echo array_sum($timelineLogoutHourData); ?></span>
            </div>
        </div>
    </div>
    <script>
        // Grafik Hari Ini (per Jam)
        const ctxToday = document.getElementById('todayChart').getContext('2d');
        new Chart(ctxToday, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($hourLabels); ?>,
                datasets: [
                    {
                        label: 'Login',
                        data: <?php echo json_encode($loginHourData); ?>,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Logout',
                        data: <?php echo json_encode($logoutHourData); ?>,
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: false },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            title: function(context) {
                                return 'Jam: ' + context[0].label;
                            },
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { display: false } }
                }
            }
        });

        // Grafik 7 Hari Terakhir (tetap line)
        const ctx7 = document.getElementById('statChart').getContext('2d');
        new Chart(ctx7, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [
                    {
                        label: 'Login',
                        data: <?php echo json_encode($loginData); ?>,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: 'Logout',
                        data: <?php echo json_encode($logoutData); ?>,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        tension: 0.4,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: false },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            title: function(context) {
                                return 'Tanggal: ' + context[0].label;
                            },
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y;
                            }
                        }
                    }
                }
            }
        });

        // Grafik 1 Bulan Terakhir (per hari)
        const ctxMonth = document.getElementById('monthChart').getContext('2d');
        new Chart(ctxMonth, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthLabels); ?>,
                datasets: [
                    {
                        label: 'Login',
                        data: <?php echo json_encode($loginMonthData); ?>,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: 'Logout',
                        data: <?php echo json_encode($logoutMonthData); ?>,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        tension: 0.4,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: false },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            title: function(context) {
                                return 'Tanggal: ' + context[0].label;
                            },
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y;
                            }
                        }
                    }
                }
            }
        });
        // Grafik Timeline User
        const ctxTimeline = document.getElementById('timelineChart').getContext('2d');
        new Chart(ctxTimeline, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($timelineHourLabels); ?>,
                datasets: [
                    {
                        label: 'Login',
                        data: <?php echo json_encode($timelineLoginHourData); ?>,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Logout',
                        data: <?php echo json_encode($timelineLogoutHourData); ?>,
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: false },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            title: function(context) {
                                return 'Jam: ' + context[0].label;
                            },
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>
