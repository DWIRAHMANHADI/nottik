<?php
// WhatsAppClient.php
// Wrapper sederhana untuk sdk-php-whatsapp-web-multidevice

require_once __DIR__ . '/vendor/autoload.php';

use SdkWhatsappWebMultiDevice\Configuration;
use SdkWhatsappWebMultiDevice\Api\AppApi;
use SdkWhatsappWebMultiDevice\Api\UserApi;
use SdkWhatsappWebMultiDevice\Api\GroupApi;

class WhatsAppClient {
    private $config;
    private $appApi;
    private $userApi;
    private $groupApi;

    public function __construct($apiUrl, $user, $pass) {
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
     * @return bool
     */
    public function isDeviceOnline() {
        try {
            $devices = $this->appApi->appDevices();
            // Jika results tidak kosong, anggap online
            if (
                isset($devices->container['results']) &&
                is_array($devices->container['results']) &&
                count($devices->container['results']) > 0
            ) {
                return true;
            }
        } catch (\Exception $e) {
            // Bisa log error jika mau
        }
        return false;
    }

    /**
     * Logout WhatsApp (memanggil endpoint appLogout dari SDK)
     * @return bool
     */
    public function logout() {
        try {
            $resp = $this->appApi->appLogout();
            // Sukses jika tidak error
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Reset koneksi WhatsApp secara paksa
     * Digunakan untuk mengatasi masalah "FOREIGN KEY constraint failed" setelah logout
     * Menggunakan kombinasi metode untuk memastikan reset database yang bersih
     * @param string &$message Pesan status operasi
     * @return bool
     */
    public function resetConnection(&$message = '') {
        try {
            // 1. Coba logout normal terlebih dahulu
            try {
                $this->appApi->appLogout();
                $message .= "Logout berhasil. ";
            } catch (\Exception $e) {
                $message .= "Logout normal gagal: {$e->getMessage()}. Melanjutkan dengan reset paksa. ";
            }
            
            // 2. Coba login ulang untuk memaksa pembuatan sesi baru
            try {
                // Ini akan memicu pembuatan QR code baru
                $this->appApi->appLogin();
                $message .= "Login ulang berhasil. ";
            } catch (\Exception $e) {
                $message .= "Login ulang gagal: {$e->getMessage()}. ";
            }
            
            // 3. Tambahkan flag untuk memaksa refresh penuh pada halaman
            $_SESSION['force_refresh'] = true;
            $_SESSION['last_reset'] = time();
            
            // 4. Hapus semua data sesi terkait WhatsApp
            unset($_SESSION['active_group_name']);
            unset($_SESSION['api_rate_limited']);
            
            $message .= "Reset koneksi selesai. Silakan scan QR code baru.";
            return true;
        } catch (\Exception $e) {
            $message = "Error saat reset koneksi: {$e->getMessage()}";
            return false;
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
            $resp = $this->appApi->appLogin();
            // Gunakan getter sesuai dokumentasi
            $results = is_object($resp) && method_exists($resp, 'getResults') ? $resp->getResults() : null;
            if ($results && method_exists($results, 'getQrLink')) {
                $qrLink = $results->getQrLink();
                // Simpan qr_duration ke session jika ada
                if (method_exists($results, 'getQrDuration')) {
                    $qrDuration = $results->getQrDuration();
                    if ($qrDuration && session_status() === PHP_SESSION_ACTIVE) {
                        $_SESSION['qr_duration'] = $qrDuration;
                    }
                }
                if ($qrLink) {
                    return $qrLink;
                }
            }
            // Cek error code ALREADY_LOGGED_IN
            if (is_object($resp) && method_exists($resp, 'getCode') && $resp->getCode() === 'ALREADY_LOGGED_IN') {
            }
            // Fallback: jika qr_link base64 image
            if (isset($resp->results) && isset($resp->results->qr_link)) {
                return $resp->results->qr_link;
            }
            if (isset($resp->container['code']) && $resp->container['code'] === 'ALREADY_LOGGED_IN') {
                $errorMsg = 'Sudah login.';
                return null;
            }
            $errorMsg = isset($resp->container['message']) ? $resp->container['message'] : 'QR code tidak tersedia.';
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
