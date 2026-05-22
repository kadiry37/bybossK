<?php
/**
 * DECK KLİPS - Admin Password Reset Utility
 * Güvenlik amacıyla işiniz bittikten sonra bu dosyayı sunucudan silmeniz önerilir.
 */

// Hataları ekrana bastır
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// config.php yükle
require_once __DIR__ . '/api/config.php';

// Güvenlik Anahtarı Kontrolü
$providedKey = $_GET['key'] ?? '';
if ($providedKey !== API_KEY) {
    die("HATA: Yetkisiz erişim. Lütfen doğru API Key'i 'key' parametresi ile geçin. Örn: reset_admin.php?key=" . API_KEY);
}

// Şifre parametresi kontrolü (varsayılan: byboss2026!)
$newPassword = $_GET['password'] ?? 'byboss2026!';

try {
    echo "Veritabanına bağlanılıyor...<br>";
    $db = Database::getInstance()->getConnection();
    echo "Bağlantı başarılı!<br>";
    
    // Veritabanını seç
    $db->exec("USE `" . DB_NAME . "`;");
    
    // admin_users tablosunda 'admin' kullanıcısı var mı kontrol et
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
    if ($admin) {
        // Var olan kullanıcının şifresini güncelle
        $update = $db->prepare("UPDATE admin_users SET password = ?, email = 'info@ahtapotyapi.com', role = 'admin' WHERE username = 'admin'");
        $update->execute([$hashedPassword]);
        echo "<h3 style='color: green;'>BAŞARILI: 'admin' kullanıcısının şifresi başarıyla '" . htmlspecialchars($newPassword) . "' olarak güncellendi!</h3>";
    } else {
        // Yeni admin kullanıcısı oluştur
        $insert = $db->prepare("INSERT INTO admin_users (username, password, email, name, role) VALUES ('admin', ?, 'info@ahtapotyapi.com', 'Administrator', 'admin')");
        $insert->execute([$hashedPassword]);
        echo "<h3 style='color: green;'>BAŞARILI: 'admin' kullanıcısı mevcut değildi, yeni oluşturuldu ve şifresi '" . htmlspecialchars($newPassword) . "' olarak ayarlandı!</h3>";
    }
    
    echo "<p style='color: red; font-weight: bold;'>ÖNEMLİ GÜVENLİK UYARISI: Lütfen şifrenizi sıfırlayıp giriş yaptıktan sonra bu dosyayı (reset_admin.php) sunucudan silin!</p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>HATA: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
