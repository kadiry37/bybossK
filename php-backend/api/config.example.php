<?php
/**
 * ATELIER NOIR - Database Configuration
 * 
 * Bu dosyayı hosting'inize yükledikten sonra,
 * veritabanı bilgilerinizi güncelleyin.
 */

// ============================================
// ENVIRONMENT VARIABLES LOADER
// ============================================
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            putenv(trim($name) . '=' . trim($value));
            $_ENV[trim($name)] = trim($value);
        }
    }
}

function get_env($key, $default = '') {
    $val = getenv($key);
    if ($val === false && isset($_ENV[$key])) $val = $_ENV[$key];
    return $val !== false ? $val : $default;
}

// ============================================
// VERİTABANI AYARLARI
// ============================================
define('DB_HOST', get_env('DB_HOST', 'localhost'));
define('DB_NAME', get_env('DB_NAME', 'veritabani_adi'));
define('DB_USER', get_env('DB_USER', 'veritabani_kullanici'));
define('DB_PASS', get_env('DB_PASS', 'veritabani_sifre'));
define('DB_CHARSET', 'utf8mb4');

// ============================================
// SITE AYARLARI
// ============================================
define('SITE_URL', 'https://bybossmimarlik.com');
define('SITE_NAME', 'By Boss Mimarlık Mobilya');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// ============================================
// GÜVENLİK AYARLARI
// ============================================
define('API_KEY', 'api-key-buraya');
define('ADMIN_SESSION_NAME', 'atelier_admin');

// ============================================
// TURNSTILE CAPTCHA AYARLARI
// ============================================
define('TURNSTILE_SITE_KEY', 'site-key-buraya');
define('TURNSTILE_SECRET_KEY', 'secret-key-buraya');

// ============================================
// GITHUB WEBHOOK AYARLARI (Otomatik Build İçin)
// ============================================
define('GITHUB_TOKEN', get_env('GITHUB_TOKEN', ''));
define('GITHUB_REPO_OWNER', get_env('GITHUB_REPO_OWNER', ''));
define('GITHUB_REPO_NAME', get_env('GITHUB_REPO_NAME', ''));

// ============================================
// CORS AYARLARI (Astro frontend için)
// ============================================
define('ALLOWED_ORIGINS', [
    'http://localhost:4321',
    'http://localhost:3000',
    'https://bybossmimarlik.com',
    'https://www.bybossmimarlik.com'
]);
