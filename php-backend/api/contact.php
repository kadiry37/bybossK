<?php
/**
 * ATELIER NOIR - Contact Form API
 * Endpoint: /api/contact.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../admin/includes/mailer.php';

// CORS headers
set_cors_headers();

// OPTIONS request için
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Sadece POST izin ver
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

try {
    $db = Database::getInstance()->getConnection();

    // JSON input al
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        // Form data olarak dene
        $input = $_POST;
    }

    // Gerekli alanları kontrol et
    $required = ['name', 'email', 'message', 'cf_token'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            json_error("$field alanı zorunludur", 400);
        }
    }

    // Email validasyonu
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        json_error('Geçerli bir e-posta adresi girin', 400);
    }

    // Cloudflare Turnstile doğrulama
    $stmt_cf = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'turnstile_secret_key' LIMIT 1");
    $stmt_cf->execute();
    $cf_secret = $stmt_cf->fetchColumn();

    if (empty($cf_secret)) {
        $cf_secret = get_env('TURNSTILE_SECRET_KEY', '');
    }
    if (empty($cf_secret)) {
        // Varsayılan olarak TURNSTILE_SECRET_KEY ortam değişkeninden oku, yoksa config'den dene
        $cf_secret = defined('TURNSTILE_SECRET_KEY') ? TURNSTILE_SECRET_KEY : '';
    }

    if (!empty($cf_secret)) {
        $cf_verify = curl_init();
        curl_setopt_array($cf_verify, [
            CURLOPT_URL => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'secret'   => $cf_secret,
                'response' => $input['cf_token'],
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $cf_response = curl_exec($cf_verify);
        $cf_httpcode = curl_getinfo($cf_verify, CURLINFO_HTTP_CODE);
        curl_close($cf_verify);

        if ($cf_httpcode !== 200) {
            error_log("Turnstile API HTTP error: $cf_httpcode");
            json_error('CAPTCHA doğrulama servisi şu an kullanılamıyor. Lütfen daha sonra tekrar deneyin.', 503);
        }

        $cf_result = json_decode($cf_response, true);
        if (!$cf_result || !($cf_result['success'] ?? false)) {
            error_log("Turnstile CAPTCHA verification failed. Errors: " . json_encode($cf_result['error-codes'] ?? ['unknown']));
            json_error('CAPTCHA doğrulaması başarısız oldu. Lütfen tekrar deneyin.', 400);
        }
    } else {
        // Turnstile secret key tanımlanmamış — log'a yaz, yine de devam et (geliştirme modu)
        error_log("WARNING: TURNSTILE_SECRET_KEY is not set. CAPTCHA validation skipped.");
    }

    // Verileri temizle
    $name = sanitize_input($input['name']);
    $email = sanitize_input($input['email']);
    $phone = sanitize_input($input['phone'] ?? '');
    $subject = sanitize_input($input['subject'] ?? 'İletişim Formu');
    $message = sanitize_input($input['message']);

    // Veritabanına kaydet
    $stmt = $db->prepare("
        INSERT INTO contacts (name, email, phone, subject, message, status)
        VALUES (?, ?, ?, ?, ?, 'new')
    ");

    $stmt->execute([$name, $email, $phone, $subject, $message]);
    $contactId = $db->lastInsertId();

    // Email gönder
    try {
        $emailSubject = "[Yeni Mesaj] $subject - DECK Klips";
        $emailBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #c9a962;'>Panelden Yeni Mesaj</h2>
                <hr style='border: 0; border-top: 1px solid #eee;'>
                <p><strong>Ad Soyad:</strong> $name</p>
                <p><strong>E-posta:</strong> $email</p>
                <p><strong>Telefon:</strong> $phone</p>
                <p><strong>Konu:</strong> $subject</p>
                <hr style='border: 0; border-top: 1px solid #eee;'>
                <p><strong>Mesaj:</strong></p>
                <div style='background: #f9f9f9; padding: 15px; border-radius: 5px;'>
                    " . nl2br($message) . "
                </div>
                <hr style='border: 0; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #888;'>Bu mesaj DECK Klips web sitesi üzerinden gönderilmiştir.</p>
            </div>
        ";

        // Admin email'i veritabanından al
        $stmt_set = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'admin_email' LIMIT 1");
        $stmt_set->execute();
        $adminEmail = $stmt_set->fetchColumn() ?: 'info@ahtapotyapi.com';

        sendEmail($adminEmail, $emailSubject, $emailBody);
    } catch (Exception $e) {
        error_log("Notification Email Failed: " . $e->getMessage());
    }

    json_response([
        'success' => true,
        'message' => 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.',
        'id' => $contactId
    ]);

} catch (Exception $e) {
    error_log("Contact API Error: " . $e->getMessage());
    json_error('Mesaj gönderilirken bir hata oluştu. Lütfen tekrar deneyin.', 500);
}