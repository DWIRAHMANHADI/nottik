<?php
// Endpoint: user/api/update-group-id.php
// Fungsi: Update field group_id user berdasarkan token, dipanggil oleh bot WhatsApp

require_once '../../includes/config.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

// Hanya boleh POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Ambil input JSON
$input = json_decode(file_get_contents('php://input'), true);
$token = trim($input['token'] ?? '');
$groupId = trim($input['group_id'] ?? '');

if (empty($token) || empty($groupId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Token dan group_id wajib diisi']);
    exit;
}

// Cari user berdasarkan token
$db = Database::getInstance();
$user = $db->fetchOne("SELECT user_id FROM user_settings WHERE token = ?", [$token]);

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'Token tidak valid']);
    exit;
}

// Update group_id user
$db->update('user_settings', ['group_id' => $groupId], 'user_id = ?', [$user['user_id']]);
// updated_at akan otomatis terisi oleh trigger database

// Kirim notifikasi WhatsApp ke grup
try {
    require_once '../../WhatsAppClient.php';
    // Ambil kredensial WhatsApp API dari tabel whatsapp_settings
    $waSettings = $db->fetchOne("SELECT api_url, api_user, api_pass FROM whatsapp_settings ORDER BY id DESC LIMIT 1");
    if ($waSettings && $waSettings['api_url'] && $waSettings['api_user'] && $waSettings['api_pass']) {
        $wa = new WhatsAppClient($waSettings['api_url'], $waSettings['api_user'], $waSettings['api_pass']);
        $pesan = "🚀 *Boom!* Group ID kamu ini ($groupId) udah sukses keisi otomatis di dashboard!\n\nSekarang copas script *Nottik* ke Mikrotik kamu, tinggal tempel, beres, enjoy 😎";
        $result = $wa->sendMessage($groupId, $pesan);
        if (!$result) {
            error_log('Gagal kirim WA ke grup ' . $groupId . ' | Pesan: ' . $pesan);
        }
    }
} catch (Exception $e) {
    error_log('Gagal kirim WA notifikasi Group ID ke grup: ' . $e->getMessage());
}

// Sukses
http_response_code(200);
echo json_encode(['success' => true, 'message' => 'Group ID berhasil diupdate', 'group_id' => $groupId]);
