<?php
// user/help.php - Bantuan, FAQ, dan Dokumentasi untuk Pengguna Nottik
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bantuan & FAQ Pengguna - Nottik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-green-50 min-h-screen">
    <nav class="bg-green-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <span class="font-bold text-xl">Nottik User</span>
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
            <h2 class="text-2xl font-bold mb-6 text-green-700"><i class="fas fa-question-circle text-green-600 mr-2"></i>Bantuan & FAQ Pengguna</h2>
            <div class="mb-8">
                <h3 class="font-semibold text-lg mb-2">Apa itu Nottik?</h3>
                <p class="text-gray-700 mb-4">Nottik adalah aplikasi monitoring aktivitas PPPoE Mikrotik yang mengirim notifikasi otomatis ke WhatsApp Anda. Anda dapat memantau login/logout, grafik aktivitas, dan status user secara real-time.</p>
                <h3 class="font-semibold text-lg mb-2">Bagaimana cara login ke dashboard?</h3>
                <p class="text-gray-700 mb-4">Masukkan nomor HP Anda, lalu masukkan OTP yang dikirim ke WhatsApp admin. Jika belum menerima OTP, hubungi admin Anda.</p>
                <h3 class="font-semibold text-lg mb-2">Bagaimana cara melihat grafik dan log aktivitas?</h3>
                <p class="text-gray-700 mb-4">Setelah login, Anda dapat melihat grafik login/logout di menu Statistik dan riwayat aktivitas di dashboard utama.</p>
                <h3 class="font-semibold text-lg mb-2">Bagaimana cara mendapatkan notifikasi WhatsApp?</h3>
                <p class="text-gray-700 mb-4">Pastikan Anda sudah terdaftar dan aktif. Setiap login/logout akan otomatis dikirim ke WhatsApp Anda atau group yang sudah diatur oleh admin.</p>
            </div>
            <div class="mb-8">
                <h3 class="font-semibold text-lg mb-2">FAQ (Pertanyaan Umum)</h3>
                <ul class="list-disc pl-6 text-gray-700 space-y-2">
                    <li><b>Q:</b> Tidak bisa login?<br><b>A:</b> Pastikan nomor HP benar dan aktif. Jika OTP tidak masuk, hubungi admin Anda.</li>
                    <li><b>Q:</b> Bagaimana cara ganti nomor HP?<br><b>A:</b> Hubungi admin untuk update data nomor HP Anda.</li>
                    <li><b>Q:</b> Apakah data saya aman?<br><b>A:</b> Ya, setiap user punya token unik dan sistem autentikasi aman.</li>
                    <li><b>Q:</b> Bagaimana melihat detail aktivitas harian?<br><b>A:</b> Klik tombol "Detail" di dashboard untuk melihat grafik dan status harian Anda.</li>
                </ul>
            </div>
            <div class="mb-8">
                <h3 class="font-semibold text-lg mb-2">Panduan Penggunaan Singkat</h3>
                <ol class="list-decimal pl-6 text-gray-700 space-y-2">
                    <li>Login ke dashboard user dengan nomor HP dan OTP.</li>
                    <li>Lihat statistik dan grafik aktivitas di menu Statistik.</li>
                    <li>Periksa riwayat aktivitas dan status login/logout Anda.</li>
                    <li>Jika ada kendala, gunakan menu Bantuan atau hubungi admin Anda.</li>
                </ol>
            </div>
            <div class="mb-8">
                <h3 class="font-semibold text-lg mb-2">Butuh Bantuan Lebih Lanjut?</h3>
                <p class="text-gray-700">Silakan hubungi admin jaringan Anda atau email ke <b>support@nottik.id</b> untuk bantuan teknis.</p>
            </div>
        </div>
    </div>
</body>
</html>
