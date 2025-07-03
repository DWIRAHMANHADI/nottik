<?php
// login.php - Halaman login untuk sistem SaaS dengan OTP
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/Auth.php';

// Inisialisasi Auth
$auth = new Auth();

// Cek jika sudah login
if ($auth->isLoggedIn()) {
    header("Location: user/index.php");
    exit;
}

$error = '';
$success = '';
$showOTPForm = false;
$phone = '';

// Proses permintaan OTP
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_otp'])) {
    $phone = $_POST['phone'] ?? '';
    
    if (empty($phone)) {
        $error = 'Nomor HP harus diisi';
    } else {
        // Generate dan kirim OTP
        $result = $auth->generateOTP($phone);
        
        if ($result['success']) {
            $success = $result['message'];
            $showOTPForm = true;
        } else {
            $error = $result['message'];
        }
    }
}

// Proses verifikasi OTP
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_otp'])) {
    $phone = $_POST['phone'] ?? '';
    $otp = $_POST['otp'] ?? '';
    
    if (empty($phone) || empty($otp)) {
        $error = 'Nomor HP dan kode OTP harus diisi';
        $showOTPForm = true;
    } else {
        // Verifikasi OTP
        $result = $auth->verifyOTP($phone, $otp);
        
        if ($result['success']) {
            // Redirect ke dashboard pengguna
            header("Location: user/index.php");
            exit;
        } else {
            $error = $result['message'];
            $showOTPForm = true;
        }
    }
}

// Login admin (untuk kompatibilitas dengan sistem lama)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = $auth->adminLogin($username, $password);
    
    if ($result['success']) {
        header("Location: admin/index.php");
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WhatsApp Notification Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-green-600">WhatsApp Notification Panel</h2>
            <p class="text-gray-600">Login untuk mengakses dashboard</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-center"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4 text-center"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($showOTPForm): ?>
            <!-- Form Verifikasi OTP -->
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2">Nomor HP</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" readonly>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Kode OTP</label>
                    <input type="text" name="otp" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" required autofocus>
                    <p class="text-sm text-gray-500 mt-1">Masukkan kode OTP yang dikirim ke WhatsApp Anda</p>
                </div>
                <input type="hidden" name="verify_otp" value="1">
                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition">Verifikasi OTP</button>
                <div class="text-center mt-2">
                    <a href="login.php" class="text-sm text-green-600 hover:underline">Kembali ke login</a>
                </div>
            </form>
        <?php else: ?>
            <!-- Tab Navigation -->
            <div class="flex border-b mb-4">
                <button id="tab-user" class="flex-1 py-2 font-medium text-center border-b-2 border-green-500 text-green-600">Pengguna</button>
                <button id="tab-admin" class="flex-1 py-2 font-medium text-center text-gray-500">Admin</button>
            </div>
            
            <!-- Form Login Pengguna -->
            <div id="form-user" class="space-y-4">
                <form method="POST">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Nomor HP</label>
                        <input type="text" name="phone" placeholder="Masukkan nomor HP" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" required autofocus>
                        <p class="text-sm text-gray-500 mt-1">Format: 08xxx, 628xxx, +628xxx, atau 8xxx</p>
                    </div>
                    <input type="hidden" name="request_otp" value="1">
                    <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition">Kirim Kode OTP</button>
                    <div class="text-center mt-4">
                        <p class="text-gray-600">Belum punya akun? <a href="register.php" class="text-green-600 font-bold">Daftar</a></p>
                    </div>
                </form>
            </div>
            
            <!-- Form Login Admin -->
            <div id="form-admin" class="hidden space-y-4">
                <form method="POST">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Username</label>
                        <input type="text" name="username" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Password</label>
                        <input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" required>
                    </div>
                    <input type="hidden" name="admin_login" value="1">
                    <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition">Login Admin</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Tab switching functionality
        document.getElementById('tab-user').addEventListener('click', function() {
            document.getElementById('tab-user').classList.add('border-green-500', 'text-green-600');
            document.getElementById('tab-admin').classList.remove('border-green-500', 'text-green-600');
            document.getElementById('tab-admin').classList.add('text-gray-500');
            document.getElementById('form-user').classList.remove('hidden');
            document.getElementById('form-admin').classList.add('hidden');
        });
        
        document.getElementById('tab-admin').addEventListener('click', function() {
            document.getElementById('tab-admin').classList.add('border-green-500', 'text-green-600');
            document.getElementById('tab-user').classList.remove('border-green-500', 'text-green-600');
            document.getElementById('tab-user').classList.add('text-gray-500');
            document.getElementById('form-admin').classList.remove('hidden');
            document.getElementById('form-user').classList.add('hidden');
        });
    </script>
</body>
</html>
