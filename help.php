<?php
// help.php - Bantuan & FAQ Umum untuk Landing Page Nottik
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bantuan & FAQ - Nottik Notification Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-green-50 min-h-screen">
    <nav class="bg-white/80 backdrop-blur border-b border-gray-200 sticky top-0 z-30">
        <div class="container mx-auto flex justify-between items-center px-4 py-3">
            <span class="font-bold text-xl text-green-700 tracking-tight">(Nottik) Notification Mikrotik</span>
            <div class="flex flex-col gap-2 w-full max-w-xs md:flex-row md:gap-2 md:w-auto">
                <a href="login.php" class="inline-block px-4 py-2 rounded-lg text-green-700 border border-green-700 hover:bg-green-50 font-semibold transition w-full md:w-auto text-center"><i class="fas fa-sign-in-alt mr-1"></i> Login</a>
                <a href="register.php" class="inline-block px-4 py-2 rounded-lg bg-green-700 text-white hover:bg-green-800 font-semibold transition w-full md:w-auto text-center"><i class="fas fa-user-plus mr-1"></i> Daftar</a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold mb-6 text-green-700"><i class="fas fa-question-circle text-green-600 mr-2"></i>Bantuan & FAQ</h2>
            <div class="mb-8">
                <h3 class="font-semibold text-lg mb-2">Apa itu Nottik?</h3>
                <p class="text-gray-700 mb-4">Nottik adalah aplikasi monitoring aktivitas PPPoE Mikrotik yang mengirim notifikasi otomatis ke WhatsApp. Cocok untuk ISP lokal, RT/RW Net, dan komunitas jaringan.</p>
                <h3 class="font-semibold text-lg mb-2">Bagaimana cara mencoba Nottik?</h3>
                <p class="text-gray-700 mb-4">Klik tombol <b>Daftar</b> di pojok kanan atas, isi data Anda, dan ikuti petunjuk untuk integrasi dengan Mikrotik.</p>
                <h3 class="font-semibold text-lg mb-2">Apa saja fitur utama Nottik?</h3>
                <ul class="list-disc pl-6 text-gray-700 mb-4">
                    <li>Notifikasi WhatsApp otomatis login/logout</li>
                    <li>Statistik & grafik aktivitas user</li>
                    <li>Deteksi user bermasalah</li>
                    <li>Keamanan token unik</li>
                    <li>Dashboard modern & responsive</li>
                </ul>
            </div>
            <div class="mb-8">
                <h3 class="font-semibold text-lg mb-2">FAQ (Pertanyaan Umum)</h3>
                <ul class="list-disc pl-6 text-gray-700 space-y-2">
                    <li><b>Q:</b> Apakah Nottik gratis?<br><b>A:</b> Ya, Anda dapat mencoba Nottik secara gratis. Untuk fitur lanjutan, silakan hubungi kami.</li>
                    <li><b>Q:</b> Apakah data saya aman?<br><b>A:</b> Sangat aman, setiap user menggunakan token unik dan sistem autentikasi modern.</li>
                    <li><b>Q:</b> Bagaimana jika butuh bantuan teknis?<br><b>A:</b> Silakan hubungi admin jaringan Anda atau email ke <b>support@nottik.id</b>.</li>
                    <li><b>Q:</b> Apakah ada dokumentasi penggunaan?<br><b>A:</b> Ya, silakan lihat menu Bantuan di dashboard user atau admin untuk panduan detail.</li>
                </ul>
            </div>
            <div class="mb-8">
                <h3 class="font-semibold text-lg mb-2">Panduan Singkat</h3>
                <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                    <li>Daftar akun baru melalui tombol <b>Daftar</b></li>
                    <li>Login dengan nomor HP dan OTP WhatsApp</li>
                    <li>Ikuti petunjuk integrasi Mikrotik di dashboard user</li>
                    <li>Jika ada kendala, gunakan menu Bantuan atau hubungi support</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>
