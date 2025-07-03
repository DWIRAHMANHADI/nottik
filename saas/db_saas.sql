-- Database untuk WhatsApp Notification Panel SaaS
CREATE DATABASE IF NOT EXISTS wa_notification_saas;

USE wa_notification_saas;

-- Tabel users untuk menyimpan data pengguna
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL UNIQUE,
    normalized_phone VARCHAR(20) NOT NULL UNIQUE COMMENT 'Format standar +62xxx',
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    status ENUM('pending', 'active', 'suspended') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel admins untuk menyimpan data admin
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel otp_codes untuk menyimpan kode OTP
CREATE TABLE otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    code VARCHAR(6) NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    INDEX (phone, code)
);

-- Tabel user_settings untuk menyimpan pengaturan pengguna
CREATE TABLE user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    group_id VARCHAR(100) DEFAULT NULL COMMENT 'WhatsApp Group ID',
    token VARCHAR(100) DEFAULT NULL COMMENT 'Token untuk validasi request dari Mikrotik',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel whatsapp_settings untuk menyimpan pengaturan WhatsApp admin
CREATE TABLE whatsapp_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_url VARCHAR(255) DEFAULT NULL,
    api_user VARCHAR(100) DEFAULT NULL,
    api_pass VARCHAR(255) DEFAULT NULL,
    connection_status ENUM('connected', 'disconnected') DEFAULT 'disconnected',
    last_checked TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel pppoe_logs untuk menyimpan log dari Mikrotik (multi-tenant)
CREATE TABLE pppoe_logs (
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
);

-- Tabel user_sessions untuk menyimpan sesi login pengguna
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin
INSERT INTO admins (username, password, name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');
-- Password: password123 (hashed with bcrypt)

-- Insert default WhatsApp settings
INSERT INTO whatsapp_settings (api_url, api_user, api_pass) VALUES
('https://send.simpan.id', 'admin', 'Saputra@110509');
