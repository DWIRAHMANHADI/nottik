<?php
// update-log.php - Halaman log update aplikasi WhatsApp Notification Panel SaaS
// Tampilkan changelog/riwayat update aplikasi secara kronologis

$updateLogs = [
    [
        'tanggal' => '2025-05-05',
        'judul' => 'Custom Notifikasi OTP & UX Alert Group ID',
        'deskripsi' => [
            'Notifikasi OTP WhatsApp kini lebih friendly dan mudah dibaca.',
            'Alert merah jika Group ID kosong di dashboard user.',
            'Instruksi setup Group ID lebih jelas dan step-by-step.'
        ]
    ],
    [
        'tanggal' => '2025-05-03',
        'judul' => 'WhatsApp Notification Panel v2.0 (SaaS Multi-Tenant)',
        'deskripsi' => [
            'Support multi-user & multi-admin (SaaS).',
            'Group ID otomatis, notifikasi ke grup, dan dashboard user baru.',
            'Admin panel untuk monitoring & pengaturan WhatsApp.'
        ]
    ],
    [
        'tanggal' => '2025-05-01',
        'judul' => 'Integrasi WhatsApp Multi-Device Gateway',
        'deskripsi' => [
            'Integrasi API WhatsApp.',
            'Fitur QR code login, status koneksi, dan reset dari admin panel.'
        ]
    ],
    [
        'tanggal' => '2025-04-30',
        'judul' => 'Initial Release',
        'deskripsi' => [
            'Fitur dasar notifikasi WhatsApp untuk Mikrotik.',
            'Skrip Mikrotik siap pakai, notifikasi realtime.'
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Update Aplikasi</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f7fafc; font-family: 'Segoe UI', Arial, sans-serif; }
        .log-container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px #0001; padding: 32px; }
        .log-title { font-size: 2rem; font-weight: bold; margin-bottom: 24px; color: #2563eb; }
        .log-item { border-left: 4px solid #2563eb; margin-bottom: 32px; padding-left: 20px; }
        .log-date { color: #64748b; font-size: 0.95rem; margin-bottom: 4px; }
        .log-headline { font-size: 1.2rem; font-weight: 600; margin-bottom: 6px; color: #1e293b; }
        .log-desc { margin-bottom: 0; color: #334155; font-size: 1rem; }
        ul { margin: 0 0 10px 18px; }
    </style>
</head>
<body>
    <div class="log-container">
        <div class="log-title"><i class="fas fa-history"></i> Log Update Aplikasi</div>
        <?php foreach ($updateLogs as $log): ?>
            <div class="log-item">
                <div class="log-date"><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars($log['tanggal']) ?></div>
                <div class="log-headline"><?= htmlspecialchars($log['judul']) ?></div>
                <ul class="log-desc">
                    <?php foreach ($log['deskripsi'] as $desc): ?>
                        <li><?= htmlspecialchars($desc) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
        <div style="text-align:center; color:#64748b; font-size:0.95rem; margin-top:28px;">
            <i class="fas fa-info-circle"></i> Halaman ini akan selalu diperbarui setiap ada update besar aplikasi.
        </div>
    </div>
</body>
</html>
