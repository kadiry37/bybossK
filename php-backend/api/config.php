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
$GLOBALS['_CUSTOM_ENV'] = [];
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/../env.ini';
}
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $GLOBALS['_CUSTOM_ENV'][$name] = $value;
        }
    }
}

function get_env($key, $default = '') {
    if (isset($GLOBALS['_CUSTOM_ENV'][$key])) return $GLOBALS['_CUSTOM_ENV'][$key];
    $val = getenv($key);
    if ($val === false && isset($_ENV[$key])) $val = $_ENV[$key];
    return $val !== false ? $val : $default;
}

// ============================================
// VERİTABANI AYARLARI
// ============================================
// DirectAdmin'den bu bilgileri alabilirsiniz:
// Veritabanları > Veritabanı Oluştur

define('DB_HOST', get_env('DB_HOST', 'localhost'));
define('DB_NAME', get_env('DB_NAME', 'deckklip_atelier_noir'));
define('DB_USER', get_env('DB_USER', 'deckklip_deckklipscom_denemek1'));
define('DB_PASS', get_env('DB_PASS', ''));
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
define('API_KEY', 'deckklips-api-key-2026');
define('ADMIN_SESSION_NAME', 'atelier_admin');

// ============================================
// TURNSTILE CAPTCHA AYARLARI
// ============================================
define('TURNSTILE_SITE_KEY', '0x4AAAAAADNnjtllqKnfryZP');
define('TURNSTILE_SECRET_KEY', '0x4AAAAAADNnjsWYfdD8e0qQPVfWtdGlpI8');

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

// ============================================
// VERİTABANI BAĞLANTISI
// ============================================
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Production'da log'a yaz, detaylı hata verme
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Veritabanı bağlantı hatası");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection(): PDO {
        return $this->connection;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * JSON Response gönder
 */
function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Hata response
 */
function json_error($message, $status = 400) {
    json_response(['error' => $message], $status);
}

/**
 * CORS headers ekle
 */
function set_cors_headers() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if (in_array($origin, ALLOWED_ORIGINS)) {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        header("Access-Control-Allow-Origin: " . ALLOWED_ORIGINS[0]);
    }
    
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key");
    header("Access-Control-Allow-Credentials: true");
}

/**
 * API Key kontrolü
 */
function validate_api_key() {
    $headers = getallheaders();
    $apiKey = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? '';
    
    // GET request'ler için API key zorunlu değil (public data)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        return true;
    }
    
    // POST, PUT, DELETE için API key gerekli
    if ($apiKey !== API_KEY) {
        json_error('Unauthorized - Invalid API Key', 401);
    }
    
    return true;
}

/**
 * Input temizle
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Slug oluştur
 */
function create_slug($text) {
    $text = mb_strtolower($text, 'UTF-8');
    $text = str_replace(
        ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', ' '],
        ['c', 'g', 'i', 'o', 's', 'u', '-'],
        $text
    );
    $text = preg_replace('/[^a-z0-9-]/', '', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Upload klasörünü kontrol et
 */
function ensure_upload_dir() {
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
    return UPLOAD_PATH;
}

/**
 * GitHub Build'i tetikle
 */
function trigger_github_build($force = false) {
    if (!$force) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_deploy'");
            $stmt->execute();
            $autoDeploy = $stmt->fetchColumn();
            if ($autoDeploy === '0') {
                error_log(date('Y-m-d H:i:s') . " - GitHub Dispatch - Skipped (Auto Deploy Disabled)\n", 3, __DIR__ . '/github_webhook.log');
                return true;
            }
        } catch (Exception $e) {
            // If table doesn't exist or setting missing, default to true
        }
    }

    if (defined('GITHUB_TOKEN') && GITHUB_TOKEN !== 'BURAYA_ALDIGINIZ_KODU_YAZIN' && !empty(GITHUB_TOKEN)) {
        $url = "https://api.github.com/repos/" . GITHUB_REPO_OWNER . "/" . GITHUB_REPO_NAME . "/dispatches";
        
        $data = json_encode([
            'event_type' => 'content-update'
        ]);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . GITHUB_TOKEN,
            'Accept: application/vnd.github.v3+json',
            'Content-Type: application/json',
            'User-Agent: PHP-Webhook-Client'
        ]);
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        // curl_close($ch); // Deprecated in PHP 8.5+
        
        // Log the result for debugging
        error_log(date('Y-m-d H:i:s') . " - GitHub Dispatch - Status: $status | Error: $error | Response: $response\n", 3, __DIR__ . '/github_webhook.log');
        
        return $status === 204;
    }
    error_log(date('Y-m-d H:i:s') . " - GitHub Dispatch - GITHUB_TOKEN not defined or empty\n", 3, __DIR__ . '/github_webhook.log');
    return false;
}
