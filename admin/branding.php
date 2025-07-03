<?php
// admin/branding.php - Pengaturan Branding Tenant oleh Admin
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/Auth.php';
require_once '../includes/tenant.php';

session_start();
$auth = new Auth();
if (!$auth->isAdminLoggedIn()) {
    header('Location: ../login.php');
    exit;
}
$db = Database::getInstance();
$tenant = new Tenant($db);

// Asumsikan id tenant = id admin (bisa disesuaikan jika struktur multi-tenant berbeda)
$tenantId = $_SESSION['admin_id'];
$branding = $tenant->getById($tenantId);

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $warna = trim($_POST['warna'] ?? '#16a34a');
    $slogan = trim($_POST['slogan'] ?? '');
    // Logo upload
    $logo = $branding['logo'] ?? '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $logoName = 'logo-tenant-' . $tenantId . '.' . $ext;
        $dest = '../assets/' . $logoName;
        move_uploaded_file($_FILES['logo']['tmp_name'], $dest);
        $logo = 'assets/' . $logoName;
    }
    if ($nama === '') {
        $error = 'Nama tenant tidak boleh kosong';
    } else {
        $tenant->updateBranding($tenantId, [
            'nama' => $nama,
            'warna' => $warna,
            'slogan' => $slogan,
            'logo' => $logo
        ]);
        $success = 'Branding berhasil diperbarui!';
        $branding = $tenant->getById($tenantId);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Branding Tenant</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-green-700 text-white px-4 py-3 font-bold">Pengaturan Branding Tenant</nav>
    <div class="container mx-auto px-4 py-8 max-w-xl">
        <div class="bg-white shadow rounded-lg p-8">
            <h2 class="text-xl font-bold mb-4">Branding Tenant</h2>
            <?php if ($error): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block font-semibold mb-1">Nama Tenant/ISP</label>
                    <input type="text" name="nama" value="<?php echo htmlspecialchars($branding['nama'] ?? ''); ?>" class="w-full border px-3 py-2 rounded" required>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Warna Tema Utama</label>
                    <input type="color" name="warna" value="<?php echo htmlspecialchars($branding['warna'] ?? '#16a34a'); ?>" class="w-16 h-10 border rounded">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Slogan/Deskripsi</label>
                    <input type="text" name="slogan" value="<?php echo htmlspecialchars($branding['slogan'] ?? ''); ?>" class="w-full border px-3 py-2 rounded">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Logo Tenant</label>
                    <input type="file" name="logo" accept="image/*" class="block">
                    <?php if (!empty($branding['logo'])): ?>
                        <img src="../<?php echo htmlspecialchars($branding['logo']); ?>" alt="Logo" class="h-16 mt-2">
                    <?php endif; ?>
                </div>
                <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-6 py-2 rounded font-bold">Simpan</button>
            </form>
        </div>
    </div>
</body>
</html>
