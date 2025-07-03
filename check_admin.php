<?php
// check_admin.php - Script untuk memeriksa data admin
require_once 'includes/config.php';

try {
    // Koneksi ke database
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Pemeriksaan Data Admin</h2>";
    
    // Cek apakah tabel admins ada
    $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>Tabel admins tidak ditemukan!</p>";
        
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
        echo "<p>✅ Tabel admins berhasil dibuat.</p>";
    } else {
        echo "<p>✅ Tabel admins sudah ada.</p>";
    }
    
    // Cek apakah ada data admin
    $stmt = $pdo->query("SELECT * FROM admins");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($admins) == 0) {
        echo "<p style='color: orange;'>Tidak ada data admin di database.</p>";
        
        // Buat admin baru
        $hashedPassword = password_hash('password123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password, name) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $hashedPassword, 'Administrator']);
        
        echo "<p>✅ Admin baru berhasil dibuat:</p>";
        echo "<ul>";
        echo "<li>Username: admin</li>";
        echo "<li>Password: password123</li>";
        echo "<li>Password Hash: " . $hashedPassword . "</li>";
        echo "</ul>";
    } else {
        echo "<p>✅ Data admin ditemukan:</p>";
        
        foreach ($admins as $admin) {
            echo "<div style='margin-bottom: 20px; padding: 10px; border: 1px solid #ccc;'>";
            echo "<p><strong>ID:</strong> " . $admin['id'] . "</p>";
            echo "<p><strong>Username:</strong> " . $admin['username'] . "</p>";
            echo "<p><strong>Password Hash:</strong> " . $admin['password'] . "</p>";
            echo "<p><strong>Nama:</strong> " . $admin['name'] . "</p>";
            
            // Cek apakah password 'password123' cocok dengan hash yang ada
            $passwordMatch = password_verify('password123', $admin['password']);
            if ($passwordMatch) {
                echo "<p style='color: green;'>✅ Password 'password123' cocok dengan hash yang ada.</p>";
            } else {
                echo "<p style='color: red;'>❌ Password 'password123' TIDAK cocok dengan hash yang ada.</p>";
                
                // Update password admin
                $newHashedPassword = password_hash('password123', PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $stmt->execute([$newHashedPassword, $admin['id']]);
                
                echo "<p>✅ Password admin telah diperbarui:</p>";
                echo "<ul>";
                echo "<li>Password Baru: password123</li>";
                echo "<li>Hash Baru: " . $newHashedPassword . "</li>";
                echo "</ul>";
            }
            
            echo "</div>";
        }
    }
    
    echo "<p><a href='login.php' style='display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Kembali ke Login</a></p>";
    
} catch (PDOException $e) {
    die("<p style='color: red;'>Error: " . $e->getMessage() . "</p>");
}
?>
