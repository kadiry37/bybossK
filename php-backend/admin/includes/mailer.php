<?php
/**
 * BY BOSS Mimarl�k Mobilya - Secure Mailer Utility
 */

function sendEmail($to, $subject, $body, $altBody = '') {
    require_once __DIR__ . '/../../api/config.php';
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Get SMTP settings from DB
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_group = 'smtp'");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $host = $settings['smtp_host'] ?? '';
        $port = $settings['smtp_port'] ?? '587';
        $user = $settings['smtp_user'] ?? '';
        $pass = $settings['smtp_pass'] ?? '';
        $encryption = $settings['smtp_encryption'] ?? 'tls';
        $fromName = 'BY BOSS Mimarl�k Mobilya Bilgi';
        
        // SMTP Ayarları Eksikse mail() fonksiyonunu kullan (Fallback)
        if (empty($host) || empty($user) || empty($pass)) {
            $headers = "From: $fromName <$user>\r\n";
            $headers .= "Reply-To: $user\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            
            return mail($to, $subject, $body, $headers);
        }
        
        // Basit bir SMTP gönderimi simülasyonu (Production'da PHPMailer tavsiye edilir)
        // Şimdilik mail() üzerinden devam edip, SMTP konfigürasyonunu arayüzden yapmalarını bekleyeceğiz.
        
        $headers = "From: $fromName <$user>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        return mail($to, $subject, $body, $headers);
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

