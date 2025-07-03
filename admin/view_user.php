<?php
// admin/view_user.php - Detail User untuk Admin
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/Auth.php';
require_once '../includes/PhoneUtils.php';

session_start();

// Cek login admin
$auth = new Auth();
if (!$auth->isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Ambil ID user dari query string
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($userId <= 0) {
    echo '<div style="padding:2rem;color:red">ID user tidak valid.</div>';
    exit;
}

$db = Database::getInstance();
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
$userSettings = $db->fetchOne("SELECT * FROM user_settings WHERE user_id = ?", [$userId]);

if (!$user) {
    echo '<div style="padding:2rem;color:red">User tidak ditemukan.</div>';
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-indigo-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <span class="font-bold text-xl">Admin Dashboard</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="settings.php" class="hover:underline">
                    <i class="fas fa-cog mr-1"></i> Pengaturan
                </a>
                <a href="../logout.php" class="hover:underline">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-6">
        <a href="index.php" class="text-green-700 hover:underline mb-4 inline-block"><i class="fas fa-arrow-left"></i> Kembali ke daftar user</a>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Detail User</h2>
            <table class="w-full text-sm mb-4">
                <tr>
                    <td class="font-semibold py-1 pr-4">Nama</td>
                    <td class="py-1">: <?php echo htmlspecialchars($user['name']); ?></td>
                </tr>
                <tr>
                    <td class="font-semibold py-1 pr-4">Nomor HP</td>
                    <td class="py-1">: <?php echo htmlspecialchars(PhoneUtils::format($user['phone'])); ?></td>
                </tr>
                <tr>
                    <td class="font-semibold py-1 pr-4">Status</td>
                    <td class="py-1">: <span class="<?php echo $user['status']==='pending'?'text-yellow-600':'text-green-600'; ?> font-semibold"><?php echo htmlspecialchars($user['status']); ?></span></td>
                </tr>
                <tr>
                    <td class="font-semibold py-1 pr-4">Email</td>
                    <td class="py-1">: <?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <td class="font-semibold py-1 pr-4">Tanggal Daftar</td>
                    <td class="py-1">: <?php echo htmlspecialchars($user['created_at'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <td class="font-semibold py-1 pr-4">Group ID</td>
                    <td class="py-1">: <?php echo htmlspecialchars($userSettings['group_id'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <td class="font-semibold py-1 pr-4">Token</td>
                    <td class="py-1">: <span class="bg-gray-100 px-2 py-1 rounded text-xs select-all"><?php echo htmlspecialchars($userSettings['token'] ?? '-'); ?></span></td>
                </tr>
            </table>

            <a href="index.php" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700"><i class="fas fa-users"></i> Kembali ke Daftar User</a>
        </div>
    </div>
</body>
</html>
