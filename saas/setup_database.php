<?php
// setup_database.php - Script untuk membuat dan mengisi database
require_once 'includes/config.php';

// Koneksi ke MySQL tanpa memilih database
try {
    $pdo = new PDO('mysql:host=' . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Setup Database WhatsApp Notification Panel SaaS</h2>";
    
    // Buat database jika belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "<p>✅ Database <strong>" . DB_NAME . "</strong> berhasil dibuat atau sudah ada.</p>";
    
    // Pilih database
    $pdo->exec("USE " . DB_NAME);
    
    // Buat tabel users
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone VARCHAR(20) NOT NULL UNIQUE,
        normalized_phone VARCHAR(20) NOT NULL UNIQUE COMMENT 'Format standar +62xxx',
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) DEFAULT NULL,
        status ENUM('pending', 'active', 'suspended') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "<p>✅ Tabel <strong>users</strong> berhasil dibuat.</p>";
    
    // Buat tabel admins
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "<p>✅ Tabel <strong>admins</strong> berhasil dibuat.</p>";
    
    // Buat tabel otp_codes
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS otp_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone VARCHAR(20) NOT NULL,
        code VARCHAR(6) NOT NULL,
        is_used BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL,
        INDEX (phone, code)
    )");
    echo "<p>✅ Tabel <strong>otp_codes</strong> berhasil dibuat.</p>";
    
    // Buat tabel user_settings
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS user_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        group_id VARCHAR(100) DEFAULT NULL COMMENT 'WhatsApp Group ID',
        token VARCHAR(100) DEFAULT NULL COMMENT 'Token untuk validasi request dari Mikrotik',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<p>✅ Tabel <strong>user_settings</strong> berhasil dibuat.</p>";
    
    // Buat tabel whatsapp_settings
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS whatsapp_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        api_url VARCHAR(255) DEFAULT NULL,
        api_user VARCHAR(100) DEFAULT NULL,
        api_pass VARCHAR(255) DEFAULT NULL,
        connection_status ENUM('connected', 'disconnected') DEFAULT 'disconnected',
        last_checked TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "<p>✅ Tabel <strong>whatsapp_settings</strong> berhasil dibuat.</p>";
    
    // Buat tabel pppoe_logs
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS pppoe_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL COMMENT 'ID pengguna yang memiliki log ini',
        event_type ENUM('login','logout') NOT NULL,
        username VARCHAR(100) NOT NULL,
        ip_address VARCHAR(100) DEFAULT NULL,
        caller_id VARCHAR(100) DEFAULT NULL,
        uptime VARCHAR(100) DEFAULT NULL,
        service VARCHAR(50) DEFAULT NULL,
        last_disconnect_reason VARCHAR(255) DEFAULT NULL,
        last_logout VARCHAR(255) DEFAULT NULL,
        last_caller_id VARCHAR(255) DEFAULT NULL,
        active_client INT DEFAULT 0,
        event_date DATE NOT NULL,
        event_time TIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<p>✅ Tabel <strong>pppoe_logs</strong> berhasil dibuat.</p>";
    
    // Buat tabel user_sessions
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        session_id VARCHAR(255) NOT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        user_agent TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<p>✅ Tabel <strong>user_sessions</strong> berhasil dibuat.</p>";
    
    // Cek apakah sudah ada admin
    $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
    $adminCount = $stmt->fetchColumn();
    
    if ($adminCount == 0) {
        // Insert default admin (password: password123)
        $hashedPassword = password_hash('password123', PASSWORD_BCRYPT, ['cost' => 10]);
        $pdo->exec("INSERT INTO admins (username, password, name) VALUES ('admin', '$hashedPassword', 'Administrator')");
        echo "<p>✅ Admin default berhasil dibuat (username: <strong>admin</strong>, password: <strong>password123</strong>).</p>";
    } else {
        echo "<p>ℹ️ Admin sudah ada di database.</p>";
    }
    
    // Cek apakah sudah ada pengaturan WhatsApp
    $stmt = $pdo->query("SELECT COUNT(*) FROM whatsapp_settings");
    $settingsCount = $stmt->fetchColumn();
    
    if ($settingsCount == 0) {
        // Insert default WhatsApp settings
        $pdo->exec("INSERT INTO whatsapp_settings (api_url, api_user, api_pass) VALUES ('https://send.simpan.id', 'admin', 'Saputra@110509')");
        echo "<p>✅ Pengaturan WhatsApp default berhasil dibuat.</p>";
    } else {
        echo "<p>ℹ️ Pengaturan WhatsApp sudah ada di database.</p>";
    }
    
    echo "<p><strong>Setup database selesai!</strong> Sekarang Anda dapat menggunakan aplikasi WhatsApp Notification Panel SaaS.</p>";
    echo "<p><a href='login.php' style='display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Kembali ke Login</a></p>";
    
} catch (PDOException $e) {
    die("<p style='color: red;'>Error: " . $e->getMessage() . "</p>");
}
?>
