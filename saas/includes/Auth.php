<?php
// includes/Auth.php - Kelas untuk mengelola autentikasi pengguna

require_once 'config.php';
require_once 'database.php';
require_once __DIR__ . '/../WhatsAppClient.php';
require_once 'PhoneUtils.php';

class Auth {
    private $db;
    private $whatsapp;

    public function __construct() {
        $this->db = Database::getInstance();
        
        // Ambil pengaturan WhatsApp dari database atau gunakan default
        $settings = $this->db->fetchOne("SELECT * FROM whatsapp_settings LIMIT 1");
        
        if ($settings) {
            $this->whatsapp = new WhatsAppClient(
                $settings['api_url'] ?? DEFAULT_WHATSAPP_API_URL,
                $settings['api_user'] ?? DEFAULT_WHATSAPP_API_USER,
                $settings['api_pass'] ?? DEFAULT_WHATSAPP_API_PASS
            );
        } else {
            // Gunakan nilai default jika tidak ada pengaturan di database
            $this->whatsapp = new WhatsAppClient(
                DEFAULT_WHATSAPP_API_URL,
                DEFAULT_WHATSAPP_API_USER,
                DEFAULT_WHATSAPP_API_PASS
            );
        }
    }

    // Normalisasi nomor telepon ke format standar +62xxx
    public function normalizePhone($phone) {
        return PhoneUtils::normalize($phone);
    }

    // Mendaftarkan pengguna baru
    public function register($phone, $name, $email = null) {
        $normalizedPhone = $this->normalizePhone($phone);
        
        // Cek apakah nomor telepon sudah terdaftar
        $existingUser = $this->db->fetchOne("SELECT * FROM users WHERE normalized_phone = ?", [$normalizedPhone]);
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'Nomor telepon sudah terdaftar'
            ];
        }
        
        // Simpan data pengguna baru
        $userId = $this->db->insert('users', [
            'phone' => $phone,
            'normalized_phone' => $normalizedPhone,
            'name' => $name,
            'email' => $email,
            'status' => 'pending'
        ]);
        
        // Buat pengaturan default untuk pengguna
        $this->db->insert('user_settings', [
            'user_id' => $userId,
            'token' => $this->generateToken()
        ]);
        
        return [
            'success' => true,
            'message' => 'Pendaftaran berhasil, silakan login',
            'user_id' => $userId
        ];
    }

    // Generate token acak untuk pengguna
    private function generateToken($length = 10) {
        return bin2hex(random_bytes($length));
    }

    // Generate kode OTP
    public function generateOTP($phone) {
        $normalizedPhone = $this->normalizePhone($phone);
        
        // Cek apakah pengguna terdaftar
        $user = $this->db->fetchOne("SELECT * FROM users WHERE normalized_phone = ?", [$normalizedPhone]);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Nomor telepon tidak terdaftar'
            ];
        }
        
        // Generate kode OTP acak
        $otp = str_pad(rand(0, 999999), OTP_LENGTH, '0', STR_PAD_LEFT);
        $expiryTime = date('Y-m-d H:i:s', time() + OTP_EXPIRY);
        
        // Simpan kode OTP ke database
        $this->db->insert('otp_codes', [
            'phone' => $normalizedPhone,
            'code' => $otp,
            'expires_at' => $expiryTime
        ]);
        
        // Kirim OTP melalui WhatsApp
        $this->sendOTPViaWhatsApp($normalizedPhone, $otp, $user['name']);
        
        return [
            'success' => true,
            'message' => 'Kode OTP telah dikirim ke WhatsApp Anda',
            'otp' => $otp // Untuk pengembangan saja, hapus di produksi
        ];
    }

    // Kirim kode OTP melalui WhatsApp
    private function sendOTPViaWhatsApp($phone, $otp, $userName) {
        // Ambil pengaturan WhatsApp dari database
        $settings = $this->db->fetchOne("SELECT * FROM whatsapp_settings LIMIT 1");
        
        if (!$settings || $settings['connection_status'] != 'connected') {
            // Jika tidak ada koneksi WhatsApp, hanya tampilkan OTP di layar (untuk pengembangan)
            return false;
        }
        
        // Cari admin untuk mengirim OTP
        $admin = $this->db->fetchOne("SELECT * FROM admins LIMIT 1");
        
        if (!$admin) {
            return false;
        }
        
        // Siapkan pesan OTP
        $message = "Kode OTP untuk login WhatsApp Notification Panel:\n\n";
        $message .= "*{$otp}*\n\n";
        $message .= "Kode berlaku selama " . (OTP_EXPIRY / 60) . " menit.\n";
        $message .= "Jangan berikan kode ini kepada siapapun.";
        
        // Kirim pesan OTP langsung ke nomor pengguna
        $this->whatsapp->setApiCredentials(
            $settings['api_url'] ?? DEFAULT_WHATSAPP_API_URL,
            $settings['api_user'] ?? DEFAULT_WHATSAPP_API_USER,
            $settings['api_pass'] ?? DEFAULT_WHATSAPP_API_PASS
        );
        
        $result = $this->whatsapp->sendMessage($phone, $message);
        
        // Log aktivitas pengiriman OTP
        $this->logOTPActivity($phone, $result);
        
        return $result;
    }

    // Log aktivitas pengiriman OTP
    private function logOTPActivity($phone, $success) {
        $logFile = ROOT_PATH . '/otp-activity.log';
        $timestamp = date('Y-m-d H:i:s');
        $status = $success ? 'SUCCESS' : 'FAILED';
        $logMessage = "[{$timestamp}] {$status} - OTP sent to {$phone}\n";
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    // Verifikasi kode OTP
    public function verifyOTP($phone, $otp) {
        $normalizedPhone = $this->normalizePhone($phone);
        
        // Cek apakah OTP valid dan belum kadaluarsa
        $otpData = $this->db->fetchOne(
            "SELECT * FROM otp_codes WHERE phone = ? AND code = ? AND is_used = 0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1",
            [$normalizedPhone, $otp]
        );
        
        if (!$otpData) {
            return [
                'success' => false,
                'message' => 'Kode OTP tidak valid atau sudah kadaluarsa'
            ];
        }
        
        // Tandai OTP sebagai sudah digunakan
        $this->db->update('otp_codes', ['is_used' => 1], 'id = ?', [$otpData['id']]);
        
        // Ambil data pengguna
        $user = $this->db->fetchOne("SELECT * FROM users WHERE normalized_phone = ?", [$normalizedPhone]);
        
        // Buat sesi untuk pengguna
        $sessionId = $this->createSession($user['id']);
        
        return [
            'success' => true,
            'message' => 'Login berhasil',
            'user' => $user,
            'session_id' => $sessionId
        ];
    }

    // Buat sesi untuk pengguna
    private function createSession($userId) {
        $sessionId = bin2hex(random_bytes(32));
        $expiryTime = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        
        $this->db->insert('user_sessions', [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'expires_at' => $expiryTime
        ]);
        
        // Set session cookie
        setcookie(SESSION_NAME, $sessionId, time() + SESSION_LIFETIME, '/', '', false, true);
        
        return $sessionId;
    }

    // Cek apakah pengguna sudah login
    public function isLoggedIn() {
        if (!isset($_COOKIE[SESSION_NAME])) {
            return false;
        }
        
        $sessionId = $_COOKIE[SESSION_NAME];
        
        // Cek apakah sesi valid dan belum kadaluarsa
        $session = $this->db->fetchOne(
            "SELECT s.*, u.* FROM user_sessions s 
            JOIN users u ON s.user_id = u.id 
            WHERE s.session_id = ? AND s.expires_at > NOW()",
            [$sessionId]
        );
        
        if (!$session) {
            return false;
        }
        
        return $session;
    }

    // Logout pengguna
    public function logout() {
        if (!isset($_COOKIE[SESSION_NAME])) {
            return true;
        }
        
        $sessionId = $_COOKIE[SESSION_NAME];
        
        // Hapus sesi dari database
        $this->db->delete('user_sessions', 'session_id = ?', [$sessionId]);
        
        // Hapus cookie sesi
        setcookie(SESSION_NAME, '', time() - 3600, '/', '', false, true);
        
        return true;
    }

    // Login admin
    public function adminLogin($username, $password) {
        $admin = $this->db->fetchOne("SELECT * FROM admins WHERE username = ?", [$username]);
        
        if (!$admin || !password_verify($password, $admin['password'])) {
            return [
                'success' => false,
                'message' => 'Username atau password salah'
            ];
        }
        
        // Set session admin
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name'] = $admin['name'];
        
        return [
            'success' => true,
            'message' => 'Login berhasil',
            'admin' => $admin
        ];
    }

    // Cek apakah admin sudah login
    public function isAdminLoggedIn() {
        return isset($_SESSION['admin_id']);
    }

    // Logout admin
    public function adminLogout() {
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_name']);
        
        return true;
    }
}
