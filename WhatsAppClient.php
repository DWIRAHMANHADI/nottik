<?php
// WhatsAppClient.php
// Wrapper sederhana untuk sdk-php-whatsapp-web-multidevice

require_once __DIR__ . '/vendor/autoload.php';

use SdkWhatsappWebMultiDevice\Configuration;
use SdkWhatsappWebMultiDevice\Api\AppApi;
use SdkWhatsappWebMultiDevice\Api\UserApi;
use SdkWhatsappWebMultiDevice\Api\GroupApi;
use SdkWhatsappWebMultiDevice\Api\MessageApi; // <-- Tambahkan ini

class WhatsAppClient {
    private $config;
    private $appApi;
    private $userApi;
    private $groupApi;
    private $messageApi;

    public function __construct($apiUrl, $user, $pass) {
        $this->config = Configuration::getDefaultConfiguration()
            ->setHost(rtrim($apiUrl, '/'))
            ->setUsername($user)
            ->setPassword($pass);
        $this->appApi = new AppApi(null, $this->config);
        $this->userApi = new UserApi(null, $this->config);
        $this->groupApi = new GroupApi(null, $this->config);
        // Inisialisasi messageApi langsung dari SDK
        $this->messageApi = new MessageApi(null, $this->config);
    }
    
    /**
     * Kirim pesan WhatsApp langsung ke endpoint /send/message (tanpa SDK)
     * @param string $to Nomor tujuan (format internasional tanpa +)
     * @param string $message Isi pesan
     * @return bool True jika sukses, false jika gagal
     */
    public function sendMessage($to, $message) {
        // Normalisasi nomor Indonesia: 08xxxx -> 628xxxx
        $to = preg_replace('/^08/', '628', $to);
        error_log('WhatsApp sendMessage $to: ' . $to);
        $apiUrl = rtrim($this->config->getHost(), '/') . '/send/message';
        $username = $this->config->getUsername();
        $password = $this->config->getPassword();

        $payload = [
            'phone' => $to,
            'message' => $message
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200) {
            return true;
        } else {
            error_log('WhatsApp sendMessage error: ' . $response . ' | CURL error: ' . $curlError);
            return false;
        }
    }

    /**
     * Set kredensial API
     * @param string $apiUrl URL API WhatsApp
     * @param string $user Username API
     * @param string $pass Password API
     * @return void
     */
    public function setApiCredentials($apiUrl, $user, $pass) {
        $this->config = Configuration::getDefaultConfiguration()
            ->setHost(rtrim($apiUrl, '/'))
            ->setUsername($user)
            ->setPassword($pass);
        $this->appApi = new AppApi(null, $this->config);
        $this->userApi = new UserApi(null, $this->config);
        $this->groupApi = new GroupApi(null, $this->config);
    }

    /**
     * Cek apakah device sudah online
     * Menggunakan metode appDevices() dari SDK resmi
     * @return bool
     */
    public function isDeviceOnline() {
        try {
            // Debug: Log sebelum memanggil appDevices
            error_log('WhatsApp isDeviceOnline: Memanggil appDevices()');
            
            // Panggil metode appDevices untuk mendapatkan status koneksi
            $devices = $this->appApi->appDevices();
            
            // Debug: Log respons dari appDevices
            error_log('WhatsApp isDeviceOnline Response: ' . json_encode($devices));
            
            // Cek format 1: Model DevicesResponse dengan getResults()
            if (is_object($devices) && method_exists($devices, 'getResults')) {
                $results = $devices->getResults();
                if (is_array($results) && !empty($results)) {
                    error_log('WhatsApp isDeviceOnline: Device online (format 1)');
                    return true;
                }
            }
            
            // Cek format 2: container['results']
            if (isset($devices->container['results']) && is_array($devices->container['results']) && !empty($devices->container['results'])) {
                error_log('WhatsApp isDeviceOnline: Device online (format 2)');
                return true;
            }
            
            // Cek format 3: results property
            if (isset($devices->results) && !empty($devices->results)) {
                error_log('WhatsApp isDeviceOnline: Device online (format 3)');
                return true;
            }
            
            // Cek format 4: data property
            if (isset($devices->data) && !empty($devices->data)) {
                error_log('WhatsApp isDeviceOnline: Device online (format 4)');
                return true;
            }
            
            // Cek format 5: container['data']
            if (isset($devices->container['data']) && !empty($devices->container['data'])) {
                error_log('WhatsApp isDeviceOnline: Device online (format 5)');
                return true;
            }
            
            // Cek format 6: status success
            if ((isset($devices->status) && $devices->status === 'success') || 
                (isset($devices->container['status']) && $devices->container['status'] === 'success')) {
                error_log('WhatsApp isDeviceOnline: Device online (format 6)');
                return true;
            }
            
            // Cek format 7: connected property
            if ((isset($devices->connected) && $devices->connected === true) || 
                (isset($devices->container['connected']) && $devices->container['connected'] === true)) {
                error_log('WhatsApp isDeviceOnline: Device online (format 7)');
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log('WhatsApp isDeviceOnline Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cek status koneksi WhatsApp
     * Alias untuk isDeviceOnline()
     * @return bool
     */
    public function checkStatus() {
        return $this->isDeviceOnline();
    }

    /**
     * Reset koneksi WhatsApp
     * Digunakan untuk mengatasi masalah "FOREIGN KEY constraint failed" setelah logout
     * @param string &$message Pesan status operasi (opsional)
     * @return bool
     */
    public function resetConnection(&$message = '') {
        try {
            $this->appApi->appLogout();
            if (is_string($message)) {
                $message .= "Logout berhasil. ";
            }
            return true;
        } catch (\Exception $e) {
            // Bisa log error jika mau
            if (is_string($message)) {
                $message .= "Error: " . $e->getMessage() . ". ";
            }
            return false;
        }
    }
    
    /**
     * Logout WhatsApp (memanggil endpoint appLogout dari SDK)
     * @return bool
     */
    public function logout() {
        try {
            $this->appApi->appLogout();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Generate QR code untuk koneksi WhatsApp
     * Menggunakan endpoint yang benar dari API aldinokemal
     * @return string|false URL gambar QR code atau false jika gagal
     */
    public function generateQRCode() {
        try {
            // Debug: Log sebelum memanggil API
            error_log('WhatsApp generateQRCode: Menggunakan endpoint API aldinokemal');
            
            // Cek dulu apakah sudah online
            if ($this->isDeviceOnline()) {
                error_log('WhatsApp generateQRCode: Device sudah online, tidak perlu QR code');
                return false;
            }
            
            // Dapatkan URL API dari konfigurasi
            $apiUrl = $this->config->getHost();
            $username = $this->config->getUsername();
            $password = $this->config->getPassword();
            
            error_log('WhatsApp generateQRCode: API URL: ' . $apiUrl);
            
            // Panggil endpoint /app/login secara langsung menggunakan CURL
            $loginUrl = rtrim($apiUrl, '/') . '/app/login';
            $ch = curl_init($loginUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            error_log('WhatsApp generateQRCode: HTTP ' . $httpCode . ' Response: ' . $response);
            if ($curlError) {
                error_log('WhatsApp generateQRCode: CURL error: ' . $curlError);
            }
            
            // DEBUG: Log response mentah dan hasil json_decode
            error_log('WhatsApp generateQRCode: RAW response: ' . $response);
            $json = json_decode($response, true);
            error_log('WhatsApp generateQRCode: JSON decoded: ' . print_r($json, true));
            
            if ($httpCode === 200 && $response) {
                if (isset($json['results']['qr_link']) && !empty($json['results']['qr_link'])) {
                    error_log('WhatsApp generateQRCode: QR code berhasil didapatkan dari results.qr_link');
                    return $json['results']['qr_link'];
                }
            }
            error_log('WhatsApp generateQRCode: Tidak mendapatkan qr_link dari API.');
            // Jika respons API ada, kembalikan pesan errornya
            if (!empty($json['message'])) {
                return ['error' => $json['message']];
            }
            return ['error' => 'Tidak mendapatkan qr_link dari API.'];
        } catch (\Exception $e) {
            // Log error untuk debugging
            error_log('WhatsApp generateQRCode Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Metode sederhana untuk mendapatkan daftar grup
     * @return array
     */
    public function getGroupsSimple() {
        try {
            $response = $this->groupApi->groupList();
            if (isset($response->container['results']) && is_array($response->container['results'])) {
                return $response->container['results'];
            }
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Memberikan instruksi untuk me-restart container WhatsApp
     * Digunakan untuk mengatasi masalah database yang tidak bisa diperbaiki dengan reset biasa
     * @param string &$message Pesan instruksi untuk pengguna
     * @return bool
     */
    public function needContainerRestart(&$message) {
        // Set flag untuk menampilkan instruksi restart container
        $_SESSION['need_container_restart'] = true;
        $_SESSION['container_restart_time'] = time();
        
        // Hapus semua data sesi terkait WhatsApp
        unset($_SESSION['active_group_name']);
        unset($_SESSION['api_rate_limited']);
        unset($_SESSION['force_refresh']);
        
        $message = "Untuk mengatasi masalah FOREIGN KEY constraint failed, container WhatsApp perlu di-restart.\n";
        $message .= "Silakan jalankan perintah berikut di terminal server Anda:\n";
        $message .= "docker restart whatsapp\n\n";
        $message .= "Setelah container di-restart, silakan refresh halaman ini dan scan QR code baru.";
        
        return true;
    }

    /**
     * Membangun URL dari komponen-komponennya
     * @param array $parts Komponen-komponen URL dari parse_url()
     * @return string URL yang sudah dibangun kembali
     */
    private function buildUrl($parts) {
        $scheme   = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host     = isset($parts['host']) ? $parts['host'] : '';
        $port     = isset($parts['port']) ? ':' . $parts['port'] : '';
        $user     = isset($parts['user']) ? $parts['user'] : '';
        $pass     = isset($parts['pass']) ? ':' . $parts['pass'] : '';
        $pass     = ($user || $pass) ? $pass . '@' : '';
        $path     = isset($parts['path']) ? $parts['path'] : '';
        $query    = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
        
        return $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
    }
    
    /**
     * Mendapatkan data devices mentah
     */
    public function getDevicesRaw() {
        try {
            return $this->appApi->appDevices();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Mendapatkan link QR code (jika belum login)
     * @return string|null
     */
    public function getQrCode(&$errorMsg = null) {
        if ($this->isDeviceOnline()) {
            $errorMsg = 'Sudah login. Tidak perlu QR code.';
            return null;
        }
        
        try {
            // Debug: Log sebelum memanggil appLogin
            error_log('WhatsApp getQrCode: Memanggil appLogin()');
            
            // Panggil metode appLogin untuk mendapatkan QR code
            $resp = $this->appApi->appLogin();
            
            // Debug: Log respons dari appLogin
            error_log('WhatsApp getQrCode Response: ' . json_encode($resp));
            
            // Cek format 1: Model LoginResponse dengan getResults() dan getQrLink()
            if (is_object($resp) && method_exists($resp, 'getResults')) {
                $results = $resp->getResults();
                if ($results && method_exists($results, 'getQrLink')) {
                    $qrLink = $results->getQrLink();
                    if ($qrLink) {
                        error_log('WhatsApp getQrCode: QR code berhasil didapatkan (format 1)');
                        return $qrLink;
                    }
                }
            }
            
            // Cek format 2: Properti results->qr_link
            if (isset($resp->results) && isset($resp->results->qr_link)) {
                error_log('WhatsApp getQrCode: QR code berhasil didapatkan (format 2)');
                return $resp->results->qr_link;
            }
            
            // Cek format 3: Container dengan qr
            if (isset($resp->container) && isset($resp->container['qr'])) {
                error_log('WhatsApp getQrCode: QR code berhasil didapatkan (format 3)');
                return 'data:image/png;base64,' . $resp->container['qr'];
            }
            
            // Cek format 4: Container dengan qr_code
            if (isset($resp->container) && isset($resp->container['qr_code'])) {
                error_log('WhatsApp getQrCode: QR code berhasil didapatkan (format 4)');
                return 'data:image/png;base64,' . $resp->container['qr_code'];
            }
            
            // Cek format 5: Properti qr_code langsung
            if (isset($resp->qr_code)) {
                error_log('WhatsApp getQrCode: QR code berhasil didapatkan (format 5)');
                return 'data:image/png;base64,' . $resp->qr_code;
            }
            
            // Cek format 6: Properti qr langsung
            if (isset($resp->qr)) {
                error_log('WhatsApp getQrCode: QR code berhasil didapatkan (format 6)');
                return 'data:image/png;base64,' . $resp->qr;
            }
            
            // Cek jika sudah login
            if ((isset($resp->container['code']) && $resp->container['code'] === 'ALREADY_LOGGED_IN') ||
                (is_object($resp) && method_exists($resp, 'getCode') && $resp->getCode() === 'ALREADY_LOGGED_IN')) {
                $errorMsg = 'Sudah login. Tidak perlu QR code.';
                error_log('WhatsApp getQrCode: Sudah login');
                return null;
            }
            
            // Jika tidak ada format yang cocok
            $errorMsg = isset($resp->container['message']) ? $resp->container['message'] : 'QR code tidak tersedia.';
            error_log('WhatsApp getQrCode Error: ' . $errorMsg);
            return null;
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            return null;
        }
    }

    /**
     * Mendapatkan daftar grup WhatsApp tanpa mengambil data anggota
     * @param string|null $errorMsg Error message jika gagal
     * @return array Array grup WhatsApp (hanya nama dan ID)
     */
    public function getGroupsMinimal(&$errorMsg = null) {
        try {
            // Gunakan endpoint yang hanya mengembalikan info dasar grup tanpa anggota
            // Ini mengurangi beban server dan menghindari rate limit
            $resp = $this->userApi->userMyGroups();
            
            // Inisialisasi array untuk menyimpan grup yang sudah difilter
            $filteredGroups = [];
            
            // Ekstrak data grup dari respons
            $groups = $this->extractGroupsFromResponse($resp, $errorMsg);
            
            // Filter data grup, hanya ambil nama dan ID
            foreach ($groups as $group) {
                $groupData = [];
                
                // Ekstrak ID grup
                if (is_object($group)) {
                    if (method_exists($group, 'getJid')) {
                        $groupData['jid'] = $group->getJid();
                    } elseif (property_exists($group, 'jid')) {
                        $groupData['jid'] = $group->jid;
                    }
                    
                    // Ekstrak nama grup
                    if (method_exists($group, 'getName')) {
                        $groupData['name'] = $group->getName();
                    } elseif (method_exists($group, 'getSubject')) {
                        $groupData['name'] = $group->getSubject();
                    } elseif (property_exists($group, 'name')) {
                        $groupData['name'] = $group->name;
                    } elseif (property_exists($group, 'subject')) {
                        $groupData['name'] = $group->subject;
                    }
                } elseif (is_array($group)) {
                    $groupData['jid'] = $group['jid'] ?? '';
                    $groupData['name'] = $group['name'] ?? $group['subject'] ?? '';
                }
                
                // Hanya tambahkan jika memiliki ID
                if (!empty($groupData['jid'])) {
                    $filteredGroups[] = $groupData;
                }
            }
            
            return $filteredGroups;
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            file_put_contents(__DIR__ . '/error_groups_minimal.log', $e->getMessage() . "\n" . $e->getTraceAsString());
            return [];
        }
    }
    
    /**
     * Fungsi helper untuk mengekstrak data grup dari berbagai format respons
     * @param mixed $resp Respons dari API
     * @param string|null $errorMsg Error message jika gagal
     * @return array Array grup WhatsApp
     */
    private function extractGroupsFromResponse($resp, &$errorMsg = null) {
        // Inisialisasi array kosong untuk menyimpan grup
        $groups = [];
        
        // KASUS 1: Jika respons adalah objek UserGroupResponse
        if (is_object($resp) && get_class($resp) === 'SdkWhatsappWebMultiDevice\Model\UserGroupResponse') {
            // Coba akses results menggunakan getter
            if (method_exists($resp, 'getResults')) {
                $results = $resp->getResults();
                
                // Jika results adalah objek yang memiliki data
                if (is_object($results) && method_exists($results, 'getData')) {
                    $data = $results->getData();
                    if (is_array($data)) {
                        return $data; // Ini adalah array grup yang kita cari
                    }
                }
            }
            
            // Jika tidak bisa mengakses dengan getter, coba akses properti langsung
            if (property_exists($resp, 'results')) {
                $results = $resp->results;
                
                // Jika results adalah objek yang memiliki data
                if (is_object($results) && property_exists($results, 'data')) {
                    $data = $results->data;
                    if (is_array($data)) {
                        return $data; // Ini adalah array grup yang kita cari
                    }
                }
                
                // Jika results adalah array langsung
                if (is_array($results)) {
                    return $results; // Ini mungkin array grup langsung
                }
            }
            
            // Jika tidak bisa mengakses results, coba akses data langsung
            if (property_exists($resp, 'data')) {
                $data = $resp->data;
                if (is_array($data)) {
                    return $data; // Ini mungkin array grup langsung
                }
            }
            
            // Jika tidak bisa mengakses dengan cara di atas, kembalikan objek dalam array
            // Ini untuk mengatasi kasus di mana objek UserGroupResponse adalah grup itu sendiri
            return [$resp];
        }
        
        // KASUS 2: Jika respons adalah array langsung
        if (is_array($resp)) {
            return $resp; // Kembalikan array langsung
        }
        
        // KASUS 3: Jika respons adalah objek lain
        if (is_object($resp)) {
            // Coba akses results jika ada
            if (property_exists($resp, 'results')) {
                $results = $resp->results;
                if (is_array($results)) {
                    return $results;
                }
                if (is_object($results) && property_exists($results, 'data')) {
                    $data = $results->data;
                    if (is_array($data)) {
                        return $data;
                    }
                }
            }
            
            // Coba akses data jika ada
            if (property_exists($resp, 'data')) {
                $data = $resp->data;
                if (is_array($data)) {
                    return $data;
                }
            }
            
            // Jika tidak bisa mengakses dengan cara di atas, kembalikan objek dalam array
            return [$resp];
        }
        
        // Jika tidak ada grup yang ditemukan
        $errorMsg = 'Tidak dapat menemukan data grup dalam respons API.';
        return [];
    }
    
    /**
     * Mendapatkan daftar grup WhatsApp yang diikuti pengguna (metode lama)
     * @param string|null $errorMsg Error message jika gagal
     * @return array Array grup WhatsApp
     * @deprecated Gunakan getGroupsMinimal() untuk mengurangi beban server
     */
    public function getGroups(&$errorMsg = null) {
        // DISABLED: Fungsi ini telah dinonaktifkan untuk menghindari pengambilan data kontak grup
        // yang dapat menyebabkan rate limit dan beban server yang tinggi
        $errorMsg = 'Fungsi getGroups() telah dinonaktifkan untuk menghindari pengambilan data kontak grup. Gunakan getGroupsMinimal() sebagai gantinya.';
        
        // Alihkan ke metode getGroupsMinimal() yang lebih efisien
        return $this->getGroupsMinimal($errorMsg);
    }
}
