<?php
// includes/PhoneUtils.php - Kelas untuk mengelola format nomor telepon

class PhoneUtils {
    /**
     * Normalisasi nomor telepon ke format standar +62xxx
     * Mendukung format:
     * - 0812xxxxx
     * - 62812xxxxx
     * - +62812xxxxx
     * - 812xxxxx
     * 
     * @param string $phone Nomor telepon yang akan dinormalisasi
     * @return string Nomor telepon dalam format +62xxx
     */
    public static function normalize($phone) {
        // Hapus semua karakter non-numerik kecuali +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Jika nomor dimulai dengan +, simpan tanda +
        $hasPlus = substr($phone, 0, 1) === '+';
        
        // Hapus semua karakter non-numerik
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Jika nomor dimulai dengan 0, ganti dengan 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        // Jika nomor dimulai dengan 8, tambahkan 62
        if (strlen($phone) <= 12 && substr($phone, 0, 1) === '8') {
            $phone = '62' . $phone;
        }
        
        // Pastikan nomor dimulai dengan 62
        if (substr($phone, 0, 2) !== '62') {
            // Jika nomor tidak dimulai dengan 62 dan bukan format internasional lain,
            // maka kemungkinan format tidak valid
            return $phone;
        }
        
        // Tambahkan kembali tanda + jika sebelumnya ada
        return ($hasPlus ? '+' : '') . $phone;
    }
    
    /**
     * Validasi nomor telepon Indonesia
     * 
     * @param string $phone Nomor telepon yang akan divalidasi
     * @return bool True jika nomor valid, false jika tidak
     */
    public static function validate($phone) {
        $normalized = self::normalize($phone);
        
        // Nomor Indonesia harus diawali dengan 62 dan panjangnya antara 10-14 digit (termasuk kode negara)
        return preg_match('/^(\+?62)[0-9]{8,12}$/', $normalized) === 1;
    }
    
    /**
     * Format nomor telepon untuk tampilan
     * 
     * @param string $phone Nomor telepon yang akan diformat
     * @param bool $showCountryCode Tampilkan kode negara atau tidak
     * @return string Nomor telepon yang diformat
     */
    public static function format($phone, $showCountryCode = true) {
        $normalized = self::normalize($phone);
        
        // Hapus tanda + jika ada
        $normalized = ltrim($normalized, '+');
        
        if (!$showCountryCode && substr($normalized, 0, 2) === '62') {
            // Ganti 62 dengan 0
            return '0' . substr($normalized, 2);
        }
        
        return $normalized;
    }
}
