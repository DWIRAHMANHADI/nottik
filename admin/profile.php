<?php
// admin/profile.php - Ganti nama & password admin
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/Auth.php';

session_start();
$auth = new Auth();
if (!$auth->isAdminLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$db = Database::getInstance();
$adminId = $_SESSION['admin_id'];
$admin = $db->fetchOne("SELECT * FROM admins WHERE id = ?", [$adminId]);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['name'] ?? '');
    $newPass = $_POST['password'] ?? '';
    $newPass2 = $_POST['password2'] ?? '';
    
    if ($newName === '') {
        $error = 'Nama admin tidak boleh kosong';
    } else if ($newPass !== '' && $newPass !== $newPass2) {
        $error = 'Konfirmasi password tidak cocok';
    } else {
        // Update nama
        $db->update('admins', ['name' => $newName], 'id = ?', [$adminId]);
        // Update password jika diisi
        if ($newPass !== '') {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $db->update('admins', ['password' => $hash], 'id = ?', [$adminId]);
        }
        $success = 'Profil berhasil diperbarui';
        // Refresh data
        $admin = $db->fetchOne("SELECT * FROM admins WHERE id = ?", [$adminId]);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - Ganti Nama & Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-indigo-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <span class="font-bold text-xl">Admin Dashboard</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="settings.php" class="hover:underline"><i class="fas fa-cog mr-1"></i> Pengaturan</a>
                <a href="profile.php" class="hover:underline font-bold underline"><i class="fas fa-user mr-1"></i> Profil</a>
                <a href="../logout.php" class="hover:underline"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-8 max-w-lg">
        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Profil Admin</h2>
            <?php if ($error): ?>
                <div class="mb-4 text-red-600 bg-red-100 rounded px-4 py-2"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="mb-4 text-green-700 bg-green-100 rounded px-4 py-2"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Nama Admin</label>
                    <input type="text" name="name" class="w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                </div>
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Password Baru</label>
                    <input type="password" name="password" class="w-full border rounded px-3 py-2" placeholder="Kosongkan jika tidak ingin ganti">
                </div>
                <div class="mb-6">
                    <label class="block mb-1 font-semibold">Konfirmasi Password Baru</label>
                    <input type="password" name="password2" class="w-full border rounded px-3 py-2" placeholder="Ulangi password baru">
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded font-bold hover:bg-indigo-700 transition">Simpan Perubahan</button>
                <a href="index.php" class="ml-4 text-gray-500 hover:underline">Kembali ke Dashboard</a>
            </form>
        </div>
    </div>
</body>
</html>
