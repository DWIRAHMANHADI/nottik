<?php
// register.php - Halaman pendaftaran untuk sistem SaaS
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/Auth.php';

// Mulai sesi
session_start();

// Cek jika sudah login
$auth = new Auth();
if ($auth->isLoggedIn()) {
    // Redirect ke dashboard
    header('Location: user/index.php');
    exit;
}

$error = '';
$success = '';

// Proses pendaftaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // Validasi input
    if (empty($phone) || empty($name)) {
        $error = 'Nomor HP dan nama harus diisi';
    } else {
        // Proses pendaftaran
        $result = $auth->register($phone, $name, $email);
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - WhatsApp Notification Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-green-600">WhatsApp Notification Panel</h1>
            <p class="text-gray-600">Daftar untuk mendapatkan notifikasi dari Mikrotik</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
                <p class="mt-2">Silakan <a href="login.php" class="text-green-600 font-bold">login</a> untuk melanjutkan.</p>
            </div>
        <?php else: ?>
            <form method="POST" action="" class="space-y-4">
                <div>
                    <label for="phone" class="block text-gray-700 font-medium mb-2">Nomor HP</label>
                    <input type="text" id="phone" name="phone" placeholder="Masukkan nomor HP (contoh: 081234567890)" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                    <p class="text-sm text-gray-500 mt-1">Format: 08xxx, 628xxx, +628xxx, atau 8xxx</p>
                </div>
                
                <div>
                    <label for="name" class="block text-gray-700 font-medium mb-2">Nama Lengkap</label>
                    <input type="text" id="name" name="name" placeholder="Masukkan nama lengkap" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                
                <div>
                    <label for="email" class="block text-gray-700 font-medium mb-2">Email (Opsional)</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan email" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <div>
                    <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition duration-200">
                        Daftar
                    </button>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-gray-600">Sudah punya akun? <a href="login.php" class="text-green-600 font-bold">Login</a></p>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
