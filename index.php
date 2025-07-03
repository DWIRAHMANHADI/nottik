<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Notification(Nottik)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-white/80 backdrop-blur border-b border-gray-200 sticky top-0 z-30">
        <div class="container mx-auto flex justify-between items-center px-4 py-3">
            <span class="font-bold text-xl text-green-700 tracking-tight">(Nottik) Notification Mikrotik</span>
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
        <h2 class="text-2xl font-extrabold text-center text-green-700 mb-10 tracking-tight">Fitur Unggulan Nottik</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center">
                <div class="bg-green-100 text-green-700 rounded-full p-4 mb-4">
                    <i class="fas fa-chart-bar fa-lg"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Statistik & Grafik Aktivitas</h3>
                <p class="text-gray-500 text-center">Pantau grafik event user mikrotik secara real-time, lengkap dengan visual yang mudah dipahami.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center">
                <div class="bg-red-100 text-red-700 rounded-full p-4 mb-4">
                    <i class="fas fa-user-times fa-lg"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Deteksi User Bermasalah</h3>
                <p class="text-gray-500 text-center">Daftar user dengan aktivitas mencurigakan (misal: modem rusak, adaptor lemah) langsung terdeteksi dan mudah dipantau.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center">
                <div class="bg-yellow-100 text-yellow-700 rounded-full p-4 mb-4">
                    <i class="fas fa-list-ol fa-lg"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Keamanan & Token Unik</h3>
                <p class="text-gray-500 text-center">Setiap user punya token unik, autentikasi aman, dan perlindungan dari akses tidak sah.</p></div>
            <div class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center">
                <div class="bg-green-100 text-green-700 rounded-full p-4 mb-4">
                    <i class="fab fa-whatsapp fa-lg"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Notifikasi WhatsApp Real-Time</h3>
                <p class="text-gray-500 text-center">Setiap event langsung dikirim ke WhatsApp secara otomatis dan instan.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center">
                <div class="bg-blue-100 text-blue-700 rounded-full p-4 mb-4">
                    <i class="fas fa-info-circle fa-lg"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Detail Riwayat Aktivitas</h3>
                <p class="text-gray-500 text-center">Lihat detail riwayat aktivitas user, grafik harian, dan status event dengan tampilan modal interaktif.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center">
                <div class="bg-gray-100 text-gray-700 rounded-full p-4 mb-4">
                    <i class="fas fa-laptop-code fa-lg"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Tampilan Modern & Responsive</h3>
                <p class="text-gray-500 text-center">UI stylish berbasis Modern, support mobile & desktop, serta navigasi mudah.</p>
            </div>
        </div>
    </section>
    <!-- CTA Section -->
    <section class="bg-green-50 py-12 px-4">

        <div class="max-w-3xl mx-auto text-center">
            <div class="inline-block bg-green-200 text-green-800 text-xs font-bold px-3 py-1 rounded-full mb-3 animate-pulse">
                Sudah dipakai oleh banyak ISP lokal!
            </div>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-green-800 mb-4">Coba Nottik Sekarang, Gratis!</h2>
            <p class="text-lg text-gray-700 mb-8">Daftarkan akun Anda dan nikmati kemudahan monitoring serta notifikasi otomatis aktivitas pelanggan PPPoE Mikrotik. Tidak perlu install aplikasi tambahan, cukup daftar dan gunakan!</p>
            <a href="register.php" class="inline-block bg-green-700 hover:bg-green-800 text-white text-lg font-bold px-8 py-4 rounded-xl shadow-lg transition">Daftar Gratis Sekarang <i class="fas fa-arrow-right ml-2"></i></a>
            <div class="mt-8 text-gray-500 text-sm flex flex-col sm:flex-row gap-2 justify-center items-center">
                <span><i class="fas fa-star text-yellow-400 mr-1"></i>"Sangat membantu monitoring user, WhatsApp selalu update!"</span>
                <span class="hidden sm:inline">&bull;</span>
                <span><i class="fas fa-star text-yellow-400 mr-1"></i>"User bermasalah langsung ketahuan, mantap!"</span>
            </div>
        </div>
    </section>
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 text-gray-500 py-4 text-center text-sm flex flex-col items-center gap-2">
        <div>
            &copy; <?php echo date('Y'); ?> Nottik Notification Panel &mdash; All rights reserved.
        </div>
        <a href="update-log.php" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-xs font-semibold transition" style="margin-top:2px;">
            <i class="fas fa-history"></i> Lihat Log Update Aplikasi
        </a>
    </footer>
    <!-- Floating Bantuan Button -->
    <a href="help.php" class="fixed z-40 bottom-6 right-6 md:bottom-8 md:right-8 bg-green-600 hover:bg-green-700 text-white rounded-full shadow-xl w-16 h-16 flex items-center justify-center transition group" title="Bantuan" style="box-shadow:0 4px 24px 0 rgba(16,185,129,0.25);">
        <span class="sr-only">Bantuan</span>
        <i class="fas fa-question-circle text-3xl"></i>
        <span class="absolute opacity-0 group-hover:opacity-100 group-focus:opacity-100 bg-gray-900 text-white text-xs rounded px-3 py-1 ml-20 transition pointer-events-none whitespace-nowrap shadow-lg" style="top:50%;transform:translateY(-50%);">Butuh Bantuan?</span>
    </a>
</body>
</html>
