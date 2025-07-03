<?php
file_put_contents(__DIR__.'/webhook_test.txt', date('c')." | masuk webhook\n", FILE_APPEND);
file_put_contents(__DIR__.'/webhook_test.txt', date('c')." | sebelum raw\n", FILE_APPEND);
$raw = @file_get_contents('php://input');
file_put_contents(__DIR__.'/webhook_test.txt', date('c')." | sesudah raw\n", FILE_APPEND);
if ($raw === false) {
    file_put_contents(__DIR__.'/webhook_error.txt', date('c')." | gagal baca php://input\n", FILE_APPEND);
}
file_put_contents(__DIR__.'/webhook_raw.txt', $raw . "\n---\n", FILE_APPEND);
// Handler webhook pesan masuk dari API WhatsApp Multi-Device (aldinokemal)
// Fungsi: Jika ada pesan /nottik-id <token> di grup, kirim Group ID ke endpoint update-group-id.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';

// Ambil pengaturan WhatsApp API dari database (URL, user, pass)
$db = Database::getInstance();
$whatsappSettings = $db->fetchOne("SELECT * FROM whatsapp_settings LIMIT 1");
$apiUrl = $whatsappSettings['api_url'] ?? '';
$apiUser = $whatsappSettings['api_user'] ?? '';
$apiPass = $whatsappSettings['api_pass'] ?? '';

// Endpoint backend untuk update group_id user
$updateGroupIdUrl = rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']), '/') . '/user/api/update-group-id.php';

// LOG: Payload masuk
file_put_contents(__DIR__.'/webhook_log.txt', date('c') . ' | ' . file_get_contents('php://input') . PHP_EOL, FILE_APPEND);

// Ambil payload JSON dari API WhatsApp
$data = json_decode(file_get_contents('php://input'), true);
// LOG: Hasil parsing payload
file_put_contents(__DIR__.'/webhook_debug.txt', date('c') . "\n" . print_r($data, true) . "\n", FILE_APPEND);

// Parsing payload baru dari WhatsApp Gateway
if (!empty($data['message']['text'])) {
    $text = $data['message']['text'];
    $from = $data['from'] ?? '';
    $groupId = '';
    // Ambil groupId dari field 'from' jika ada 'in ...@g.us'
    if (preg_match('/in\s+([0-9]+@g\.us)/', $from, $gm)) {
        $groupId = $gm[1];
    } else {
        $groupId = $from;
    }
    $isGroup = (strpos($groupId, '@g.us') !== false);

    // Deteksi perintah /nottik-id <token>
    if ($isGroup && preg_match('/^\/nottik-id\s+([a-zA-Z0-9_-]{8,})$/', trim($text), $m)) {
        $token = $m[1];
        // Kirim ke endpoint backend update-group-id.php
        $payload = [
            'token' => $token,
            'group_id' => $groupId
        ];
        $ch = curl_init($updateGroupIdUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        // LOG: Response dari update-group-id.php
        file_put_contents(__DIR__.'/webhook_response.txt', date('c') . " | HTTP $httpcode | $response\n", FILE_APPEND);

        // Balas ke grup jika sukses/gagal (opsional)
        $reply = '';
        if ($httpcode === 200) {
            $reply = "Group ID berhasil diisi otomatis ke dashboard Nottik Anda!\n\nGroup ID: $groupId";
        } else {
            $reply = "Gagal mengisi Group ID ke dashboard Nottik Anda. Pastikan token benar dan coba lagi.";
        }

        // Kirim balasan ke grup via API WhatsApp Multi-Device
        $sendPayload = [
            'to' => $groupId,
            'message' => $reply
        ];
        $sendUrl = rtrim($apiUrl, '/') . '/send/message';
        $ch2 = curl_init($sendUrl);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_POST, true);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode("$apiUser:$apiPass")
        ]);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($sendPayload));
        $balasan = curl_exec($ch2);
        $balasanHttp = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);
        // LOG: Response dari kirim balasan WhatsApp
        file_put_contents(__DIR__.'/webhook_sendmsg.txt', date('c') . " | HTTP $balasanHttp | $balasan\n", FILE_APPEND);
    }
}

