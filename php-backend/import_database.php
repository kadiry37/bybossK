<?php
/**
 * Database Auto-Importer Script
 * Güvenlik amacıyla çalıştıktan sonra kendini kilitleyecektir.
 */

// Hataları ekrana bastır
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// config.php yükle
require_once __DIR__ . '/api/config.php';

// Güvenlik Kilidi Kontrolü
$lockFile = __DIR__ . '/import_database.lock';
$bypassLock = ($_GET['bypass'] ?? '') === '1';
if (file_exists($lockFile) && !$bypassLock) {
    die("HATA: Bu script daha önce çalıştırılmış ve kilitlenmiş. Tekrar çalıştırmak için 'import_database.lock' dosyasını sunucudan silmeniz gerekir veya URL sonuna '&bypass=1' ekleyebilirsiniz.");
}

// Güvenlik Anahtarı Kontrolü
$providedKey = $_GET['key'] ?? '';
if ($providedKey !== API_KEY) {
    die("HATA: Yetkisiz erişim. Lütfen doğru API Key'i 'key' parametresi ile geçin. Örn: import_database.php?key=" . API_KEY);
}

try {
    echo "Veritabanı bağlantısı kuruluyor...<br>";
    $db = Database::getInstance()->getConnection();
    echo "Bağlantı başarılı!<br>";
    
    $sqlFile = __DIR__ . '/sql/database.sql';
    if (!file_exists($sqlFile)) {
        die("HATA: sql/database.sql dosyası bulunamadı.");
    }
    
    echo "SQL dosyası okunuyor...<br>";
    $sql = file_get_contents($sqlFile);
    
    // SQL sorgularını noktalı virgüllere göre bölelim
    // Basit bölme yorum satırları ve tırnak içindeki noktalı virgüllerde sorun yaşatabilir.
    // O yüzden daha gelişmiş bir parser kullanalım veya satır satır işleyelim.
    $queries = [];
    $tempQuery = '';
    $lines = file($sqlFile);
    
    foreach ($lines as $line) {
        // Yorum satırlarını geç
        if (substr(trim($line), 0, 2) == '--' || substr(trim($line), 0, 1) == '#') {
            continue;
        }
        
        $tempQuery .= $line;
        
        // Satır sonunda noktalı virgül varsa sorguyu bitir
        if (preg_match('/;\s*$/', $line)) {
            $queries[] = $tempQuery;
            $tempQuery = '';
        }
    }
    
    echo "Sorgular yürütülüyor...<br>";
    $successCount = 0;
    $errorCount = 0;
    
    // Yabancı anahtar kontrollerini geçici olarak kapatalım
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    // Veritabanını açıkça seçelim
    try {
        $db->exec("USE `" . DB_NAME . "`;");
        echo "Veritabanı seçimi başarılı (" . htmlspecialchars(DB_NAME) . ")!<br>";
    } catch (PDOException $e) {
        echo "<h3 style='color: red;'>Veritabanı Seçim Hatası: " . htmlspecialchars($e->getMessage()) . "</h3>";
        echo "<p style='color: darkred; font-weight: bold;'>Lütfen DirectAdmin panelinize gidip '" . htmlspecialchars(DB_USER) . "' kullanıcısının '" . htmlspecialchars(DB_NAME) . "' veritabanına eklendiğinden ve TÜM YETKİLERE (All Privileges) sahip olduğundan emin olun.</p>";
        $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
        return;
    }
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        try {
            $db->exec($query);
            $successCount++;
        } catch (PDOException $e) {
            echo "<span style='color: red;'>Sorgu Hatası:</span> " . htmlspecialchars($e->getMessage()) . "<br>";
            echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ccc; max-height: 100px; overflow: auto;'>" . htmlspecialchars(substr($query, 0, 300)) . "...</pre><br>";
            $errorCount++;
        }
    }
    
    // Yabancı anahtar kontrollerini tekrar açalım
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    if ($errorCount === 0) {
        echo "<h3 style='color: green;'>Veritabanı başarıyla import edildi! ($successCount sorgu çalıştırıldı)</h3>";
        // Kilit dosyası oluştur
        file_put_contents($lockFile, date('Y-m-d H:i:s') . " tarihinde import edildi.");
        echo "Güvenlik kilidi oluşturuldu (import_database.lock).<br>";
    } else {
        echo "<h3 style='color: orange;'>İşlem tamamlandı fakat $errorCount adet sorguda hata oluştu. Başarılı sorgu sayısı: $successCount. Lütfen yukarıdaki hataları inceleyin.</h3>";
        // Kilit dosyası oluştur (kısmi başarı olsa da güvenlik için kilitleyelim)
        file_put_contents($lockFile, date('Y-m-d H:i:s') . " tarihinde kısmi hatalarla import edildi.");
        echo "Güvenlik kilidi oluşturuldu (import_database.lock).<br>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Genel Hata: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
