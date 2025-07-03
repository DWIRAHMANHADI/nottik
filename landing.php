<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Notification Panel - SaaS Multi-Tenant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-white/80 backdrop-blur border-b border-gray-200 sticky top-0 z-30">
        <div class="container mx-auto flex justify-between items-center px-4 py-3">
            <span class="font-bold text-xl text-green-700 tracking-tight">WA Notification Panel</span>
            <div class="flex flex-col gap-2 w-full max-w-xs md:flex-row md:gap-2 md:w-auto">
                <a href="login.php" class="inline-block px-4 py-2 rounded-lg text-green-700 border border-green-700 hover:bg-green-50 font-semibold transition w-full md:w-auto text-center"><i class="fas fa-sign-in-alt mr-1"></i> Login</a>
                <a href="register.php" class="inline-block px-4 py-2 rounded-lg bg-green-700 text-white hover:bg-green-800 font-semibold transition w-full md:w-auto text-center"><i class="fas fa-user-plus mr-1"></i> Daftar</a>
            </div>
        </div>
    </nav>
    <!-- Hero Section -->
    <section class="flex-1 flex flex-col-reverse md:flex-row items-center justify-center gap-8 md:gap-12 px-4 py-10 md:py-16 max-w-7xl w-full mx-auto">
        <div class="w-full md:w-1/2 max-w-xl text-center md:text-left mb-8 md:mb-0">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-gray-900 mb-4 leading-tight">
                Notifikasi WhatsApp Otomatis<br>
                <span class="text-green-700">Untuk PPPOE Mikrotik</span>
            </h1>
            <p class="text-base sm:text-lg text-gray-600 mb-8">Pantau aktivitas pelanggan PPPoE Mikrotik Anda secara real-time. Dengan Grafik dan Diagram, aman, mudah digunakan, dan terintegrasi dengan WhatsApp.</p>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center md:justify-start w-full">
                <a href="register.php" class="bg-green-700 hover:bg-green-800 text-white px-6 py-3 rounded-lg font-bold shadow transition w-full sm:w-auto text-center"><i class="fas fa-user-plus mr-2"></i> Daftar Gratis</a>
                <a href="login.php" class="bg-white border border-green-700 text-green-700 hover:bg-green-50 px-6 py-3 rounded-lg font-bold shadow transition w-full sm:w-auto text-center"><i class="fas fa-sign-in-alt mr-2"></i> Login</a>
            </div>
        </div>
        <div class="w-full md:w-1/2 flex justify-center mb-8 md:mb-0">
            <!-- Modern SVG Illustration -->
            <svg width="280" height="200" viewBox="0 0 340 260" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full max-w-xs md:max-w-sm h-auto">
                <rect x="20" y="40" width="300" height="180" rx="24" fill="#ECFDF5"/>
                <rect x="60" y="80" width="220" height="100" rx="16" fill="#D1FAE5"/>
                <rect x="110" y="110" width="120" height="40" rx="8" fill="#6EE7B7"/>
                <circle cx="170" cy="150" r="12" fill="#10B981"/>
                <rect x="140" y="170" width="60" height="12" rx="6" fill="#10B981"/>
                <rect x="60" y="60" width="40" height="12" rx="6" fill="#A7F3D0"/>
                <rect x="240" y="60" width="40" height="12" rx="6" fill="#A7F3D0"/>
                <rect x="60" y="190" width="40" height="12" rx="6" fill="#A7F3D0"/>
                <rect x="240" y="190" width="40" height="12" rx="6" fill="#A7F3D0"/>
                <rect x="30" y="30" width="30" height="30" rx="8" fill="#10B981"/>
                <rect x="280" y="30" width="30" height="30" rx="8" fill="#10B981"/>
            </svg>
        </div>
    </section>
    <!-- Feature Section -->
    <section class="container mx-auto px-4 pb-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center">
                <div class="bg-green-100 text-green-700 rounded-full p-4 mb-4">
                    <i class="fas fa-users-cog fa-lg"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Statistik Grafik Pengguna</h3>
                <p class="text-gray-500 text-center">Statistik Grafik Pengguna PPPoE Mikrotik Anda secara real-time dan mudah diakses.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center">
                <div class="bg-green-100 text-green-700 rounded-full p-4 mb-4">
                    <i class="fas fa-bolt fa-lg"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Notifikasi WhatsApp Real-Time</h3>
                <p class="text-gray-500 text-center">Notifikasi login/logout dikirim langsung ke WhatsApp Anda secara otomatis dan instan.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center">
                <div class="bg-green-100 text-green-700 rounded-full p-4 mb-4">
                    <i class="fas fa-shield-alt fa-lg"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Keamanan Modern</h3>
                <p class="text-gray-500 text-center">Token unik per user, autentikasi OTP WhatsApp, dan pengaturan group ID untuk privasi maksimal.</p>
            </div>
        </div>
    </section>
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 text-gray-500 py-4 text-center text-sm">
        &copy; <?php echo date('Y'); ?> WhatsApp Notification Panel &mdash; All rights reserved.
    </footer>
</body>
</html>
