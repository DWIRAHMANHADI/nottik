<?php
session_start();
header('Content-Type: application/json');

// Ambil ID grup dan nama grup dari POST
$groupId = isset($_POST['group_id']) ? trim($_POST['group_id']) : '';
$groupName = isset($_POST['group_name']) ? trim($_POST['group_name']) : '';

// Simpan nama grup ke session untuk penggunaan berikutnya
if (!empty($groupName)) {
    $_SESSION['active_group_name'] = $groupName;
}

$configFile = __DIR__ . '/config.php';
$configContent = file_get_contents($configFile);
$pattern = "/define\s*\(\s*['\"]WHATSAPP_GROUP_ID['\"]\s*,\s*['\"].*['\"]\s*\)\s*;/";
$replacement = "define('WHATSAPP_GROUP_ID', '$groupId');";
if (preg_match($pattern, $configContent)) {
    $newContent = preg_replace($pattern, $replacement, $configContent);
} else {
    $newContent = str_replace('?>', "$replacement\n?>\n", $configContent);
}
file_put_contents($configFile, $newContent);
echo json_encode(['success' => true, 'message' => "Grup berhasil disimpan ke config.php"]);
?>
