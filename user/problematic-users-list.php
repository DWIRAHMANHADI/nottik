<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/Auth.php';

// Cek login user
$auth = new Auth();
$userData = $auth->isLoggedIn();
if (!$userData) {
    header('Location: ../login.php');
    exit;
}

// Ambil 7 hari terakhir
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
}

// Ambil user yang bermasalah (login atau logout >= 5) per hari
$problemUsers = [];
foreach ($dates as $d) {
    $rows = Database::getInstance()->fetchAll(
        "SELECT username, COUNT(CASE WHEN event_type='login' THEN 1 END) AS total_login, COUNT(CASE WHEN event_type='logout' THEN 1 END) AS total_logout FROM pppoe_logs WHERE event_date = ? GROUP BY username HAVING total_login >= 5 OR total_logout >= 5 ORDER BY username",
        [$d]
    );
    if ($rows && count($rows) > 0) {
        foreach ($rows as $row) {
            $problemUsers[] = [
                'date' => $d,
                'username' => $row['username'],
                'login' => $row['total_login'],
                'logout' => $row['total_logout']
            ];
        }
    }
}

?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar User Bermasalah (Login/Logout ≥ 5) - (NotMiK) Notification Mikrotik</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-green-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <span class="font-bold text-xl">(NotMiK) Notification Mikrotik</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="hover:underline">Dashboard</a>
                <a href="statistik.php" class="hover:underline">Statistik</a>
                <a href="problematic-users-list.php" class="hover:underline font-bold border-b-2 border-white">User Bermasalah</a>
                <a href="../logout.php" class="hover:underline"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-2xl font-bold mb-4 text-gray-800 flex items-center gap-2"><i class="fas fa-users-cog text-orange-500"></i> Daftar User Bermasalah <span class="text-base font-normal text-gray-500">(7 Hari Terakhir)</span></h2>

<?php
    $totalUser = count(array_unique(array_map(function($u){return $u['username'];}, $problemUsers)));
    $totalHari = count(array_unique(array_map(function($u){return $u['date'];}, $problemUsers)));
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-orange-100 border-l-4 border-orange-400 rounded p-4 flex items-center gap-3">
        <i class="fas fa-user-times fa-lg text-orange-500"></i>
        <div>
            <div class="text-xs text-gray-500">Total User Bermasalah</div>
            <div class="text-xl font-bold text-orange-700"><?php echo $totalUser; ?></div>
        </div>
    </div>
    <div class="bg-red-100 border-l-4 border-red-400 rounded p-4 flex items-center gap-3">
        <i class="fas fa-calendar-day fa-lg text-red-500"></i>
        <div>
            <div class="text-xs text-gray-500">Total Hari Bermasalah</div>
            <div class="text-xl font-bold text-red-700"><?php echo $totalHari; ?></div>
        </div>
    </div>
    <div class="bg-green-100 border-l-4 border-green-400 rounded p-4 flex items-center gap-3">
        <i class="fas fa-filter fa-lg text-green-500"></i>
        <div>
            <div class="text-xs text-gray-500">Periode</div>
            <div class="text-base font-semibold text-green-700">7 Hari Terakhir</div>
        </div>
    </div>
</div>
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <?php if (empty($problemUsers)): ?>
        <div class="flex flex-col items-center justify-center py-12">
            <span class="text-6xl mb-2">🎉</span>
            <div class="text-green-700 font-semibold">Tidak ada user yang login/logout ≥ 5 kali dalam 7 hari terakhir.</div>
        </div>
    <?php else: ?>
        <div class="font-semibold text-gray-700 mb-3 flex items-center gap-2"><i class="fas fa-exclamation-triangle text-orange-400"></i> User dengan aktivitas login/logout berlebihan:</div>
        <div class="overflow-x-auto">
        <table class="min-w-full text-xs border rounded overflow-hidden">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-2 px-3 text-left">Tanggal</th>
                    <th class="py-2 px-3 text-left">Username</th>
                    <th class="py-2 px-3 text-center">Login</th>
                    <th class="py-2 px-3 text-center">Logout</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($problemUsers as $i => $pu): ?>
                <tr class="<?php echo $i%2==0?'bg-white':'bg-orange-50'; ?> hover:bg-orange-100 transition">
                    <td class="py-1 px-3 font-semibold"><?php echo date('d M Y', strtotime($pu['date'])); ?></td>
                    <td class="py-1 px-3"><?php echo htmlspecialchars($pu['username']); ?></td>
                    <td class="py-1 px-3 text-center">
                        <span class="inline-block px-2 py-0.5 rounded font-bold <?php echo $pu['login']>=5?'bg-green-100 text-green-700':'bg-gray-100 text-gray-500'; ?>"><?php echo $pu['login']; ?></span>
                    </td>
                    <td class="py-1 px-3 text-center">
                        <span class="inline-block px-2 py-0.5 rounded font-bold <?php echo $pu['logout']>=5?'bg-red-100 text-red-700':'bg-gray-100 text-gray-500'; ?>"><?php echo $pu['logout']; ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
    <a href="index.php" class="text-green-700 hover:underline mt-4 inline-block"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
</div>

    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
