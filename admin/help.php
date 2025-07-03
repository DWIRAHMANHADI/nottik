<?php
// admin/help.php - Dokumentasi, FAQ, dan Bantuan Admin/User
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
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-indigo-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <span class="font-bold text-xl">Nottik Admin</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="hover:underline"><i class="fas fa-tachometer-alt mr-1"></i> Dashboard</a>
                <a href="help.php" class="hover:underline font-bold underline"><i class="fas fa-question-circle mr-1"></i> Bantuan</a>
                <a href="../logout.php" class="hover:underline"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800"><i class="fas fa-question-circle text-indigo-500 mr-2"></i>Bantuan, FAQ & Dokumentasi</h2>
            <div class="mb-8">
                <h3 class="font-semibold text-lg mb-2">Apa itu Nottik Notification Panel?</h3>
                <p class="text-gray-700 mb-4">Nottik adalah sistem notifikasi otomatis berbasis WhatsApp untuk monitoring user PPPoE Mikrotik. Cocok untuk ISP lokal, warnet, RT/RW Net, dan komunitas jaringan.</p>
                <h3 class="font-semibold text-lg mb-2">Bagaimana cara kerja sistem OTP WhatsApp?</h3>
                <p class="text-gray-700 mb-4">Setiap kali user login, sistem akan mengirim kode OTP ke WhatsApp admin. Admin dapat meneruskan OTP ke user untuk login ke dashboard.</p>
                <h3 class="font-semibold text-lg mb-2">Bagaimana cara menambah user baru?</h3>
                <p class="text-gray-700 mb-4">User dapat mendaftar melalui halaman register. Admin dapat mengaktifkan/suspend user dari dashboard admin.</p>
                <h3 class="font-semibold text-lg mb-2">Bagaimana cara menghubungkan WhatsApp admin?</h3>
                <p class="text-gray-700 mb-4">Masuk ke menu <b>Pengaturan</b> &rarr; atur URL, username, password API WhatsApp, lalu scan QR code jika diperlukan. Pastikan status koneksi "connected".</p>
            </div>
            <div class="mb-8">
                <h3 class="font-semibold text-lg mb-2">FAQ (Pertanyaan Umum)</h3>
                <ul class="list-disc pl-6 text-gray-700 space-y-2">
                    <li><b>Q:</b> Apakah user bisa login tanpa WhatsApp?<br><b>A:</b> Tidak, OTP dikirim via WhatsApp admin untuk keamanan maksimal.</li>
                    <li><b>Q:</b> Bagaimana jika OTP tidak masuk?<br><b>A:</b> Cek status koneksi WhatsApp admin di menu Pengaturan. Jika gagal, OTP juga muncul di layar (untuk development/testing).</li>
                    <li><b>Q:</b> Apakah bisa monitoring lebih dari 1 Mikrotik?<br><b>A:</b> Bisa, selama semua PPPoE diarahkan ke server Nottik dan token user berbeda.</li>
                    <li><b>Q:</b> Bagaimana jika lupa password admin?<br><b>A:</b> Hubungi superadmin atau gunakan fitur reset password (jika tersedia).</li>
                </ul>
            </div>
            <div class="mb-8">
                <h3 class="font-semibold text-lg mb-2">Panduan Integrasi Mikrotik</h3>
                <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                    <li>Login ke dashboard user, salin script Mikrotik yang sudah berisi token unik.</li>
                    <li>Paste script ke Mikrotik (System > Scheduler atau Script).</li>
                    <li>Pastikan PPPoE log sudah terkirim ke server Nottik.</li>
                </ol>
            </div>
            <div class="mb-8">
                <h3 class="font-semibold text-lg mb-2">Butuh Bantuan Lebih Lanjut?</h3>
                <p class="text-gray-700">Silakan hubungi admin utama atau kirim email ke <b>support@nottik.id</b> untuk bantuan teknis.</p>
            </div>
        </div>
    </div>
</body>
</html>
