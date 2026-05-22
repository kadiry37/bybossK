<?php
/**
 * DECK KLİPS - Admin Panel v2.0
 * Full CMS with AI Integration
 */
session_start();
ob_start(); // Fix: Prevent 'headers already sent' errors during redirects
// Load dependencies
require_once __DIR__ . '/../api/config.php';
require_once __DIR__ . '/includes/functions.php';

// ============================================
// AUTHENTICATION
// ============================================
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Handle login
if (isset($_POST['login'])) {
    $user = sanitize_input($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
        $stmt->execute([$user]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($pass, $admin['password'])) {
            // Login success
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];
            
            // Last login update
            $update = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $update->execute([$admin['id']]);
            
            header('Location: ?page=dashboard');
            exit;
        } else {
            $loginError = 'Hatalı kullanıcı adı veya şifre!';
        }
    } catch (Exception $e) {
        $loginError = 'Bağlantı hatası: ' . $e->getMessage();
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: ?');
    exit;
}

// Show login page if not authenticated
if (!isLoggedIn()) {
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Giriş - DECK Klips</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
            body { background: #0f1117; color: #e2e8f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
            .login-card { background: #161925; border: 1px solid #1e2235; border-radius: 1rem; padding: 2.5rem; width: 100%; max-width: 420px; }
            .login-title { font-size: 1.75rem; font-weight: 700; text-align: center; margin-bottom: 0.5rem; }
            .login-sub { text-align: center; color: #8892b0; font-size: 0.875rem; margin-bottom: 2rem; }
            .form-group { margin-bottom: 1.25rem; }
            .form-label { display: block; color: #8892b0; font-size: 0.8rem; font-weight: 500; margin-bottom: 0.35rem; }
            .form-input { background: #0f1117; border: 1px solid #2a2f45; color: #e2e8f0; padding: 0.75rem 1rem; width: 100%; border-radius: 0.5rem; font-size: 0.9rem; }
            .form-input:focus { outline: none; border-color: #c9a962; }
            .btn-login { background: linear-gradient(135deg, #c9a962, #9a7633); color: #0a0a0a; width: 100%; padding: 0.85rem; border: none; border-radius: 0.5rem; font-size: 1rem; font-weight: 600; cursor: pointer; }
            .btn-login:hover { filter: brightness(1.1); }
            .error { background: rgba(220,38,38,0.15); border: 1px solid rgba(220,38,38,0.3); color: #f87171; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 0.875rem; text-align: center; }
        </style>
    </head>
    <body>
        <div class="login-card">
            <div class="login-title"><span style="color:#fff;">DECK</span> <span style="color:#c9a962;">Klips</span></div>
            <div class="login-sub">Admin Panel Girişi</div>
            <?php if (isset($loginError)): ?>
                <div class="error"><?php echo $loginError; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Kullanıcı Adı</label>
                    <input type="text" name="username" class="form-input" placeholder="admin" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Şifre</label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
                <button type="submit" name="login" class="btn-login">Güvenli Giriş Yap</button>
                
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="forgot-password.php" style="color: #c9a962; font-size: 0.8rem; text-decoration: none;">Şifremi Unuttum</a>
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ============================================
// HANDLE AJAX REQUESTS
// ============================================
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $page = $_GET['page'] ?? '';
    
    if ($page === 'ai' && $_POST['action'] === 'ai_generate') {
        set_time_limit(300); // 5 dakika süre tanı (AI gecikmelerini önlemek için)
        
        $prompt = $_POST['prompt'] ?? '';
        $type = $_POST['type'] ?? 'description';
        
        $aiPromptMap = [
            'description' => "Aşağıdaki ürün/konu için SEO uyumlu, son derece detaylı, alt başlıklar ve paragraflar içeren zengin bir açıklama yaz (en az 250-400 kelime, HTML formatında h2, h3, p etiketleri kullan). Bu ürünü satan, tedarik eden veya üreten bir firma sayfası gibi yaz. Ürünü aşırı övme, bunun yerine ürünün ne olduğunu, ne işe yaradığını, hangi alanlarda kullanıldığını ve teknik avantajlarını doyurucu şekilde açıkla. Ürün başlığı ile alakası olmayan ürünlerden (klips, montaj aparatı vb.) bahsetme. Asla [Marka Adı] gibi yer tutucular kullanma, marka yazman gerekirse 'DECK Klips' yaz. Markdown formatı (**, __, ### gibi) kesinlikle kullanma, sadece temiz HTML etiketleri kullan:\n\n",
            'short_description' => "Aşağıdaki ürün/konu için tedarikçi sayfasına uygun, kısa ve bilgilendirici bir özet yaz (1-2 cümle, maksimum 150 karakter). Ürünü aşırı övme, ne olduğunu kısaca açıkla. Asla [Marka Adı] gibi yer tutucular kullanma. Markdown formatı kullanma:\n\n",
            'seo_title' => "Aşağıdaki ürün/konu için SEO uyumlu bir sayfa başlığı yaz (maksimum 60 karakter). Asla [Marka Adı] gibi yer tutucular kullanma, marka ekleyeceksen doğrudan 'DECK Klips' yaz. Markdown kullanma:\n\n",
            'seo_description' => "Aşağıdaki ürün/konu için SEO uyumlu meta açıklama yaz (maksimum 155 karakter). Asla [Marka Adı] kullanma. Markdown kullanma:\n\n",
            'seo_keywords' => "Aşağıdaki ürün/konu için virgülle ayrılmış SEO anahtar kelimeleri üret (maksimum 5-8 kelime). Yalnızca verilen ürünle ilgili anahtar kelimeler kullan. Markdown kullanma:\n\n",
            'blog_content' => "Aşağıdaki konu hakkında SEO uyumlu, detaylı bir blog yazısı yaz (500-800 kelime, HTML formatında h2, h3, p etiketleri kullan). Asla [Marka Adı] gibi yer tutucular kullanma, doğrudan 'DECK Klips' yaz. Markdown formatı (**, __ gibi) KESİNLİKLE kullanma, sadece HTML kullan:\n\n",
            'blog_excerpt' => "Aşağıdaki blog yazısı için kısa bir özet yaz (2-3 cümle, max 150 karakter). Markdown kullanma:\n\n",
            'features' => "Aşağıdaki ürün için ürünün kalitesini, özelliklerini ve kullanım avantajlarını anlatan akıcı bir düz yazı (paragraf) yaz. Kesinlikle liste/maddeleme yapma, sadece akıcı bir paragraf metni üret. Markdown kullanma:\n\n",
            'specifications' => "Aşağıdaki ürün için teknik özellikler tablosu oluştur. Her satırda MUTLAKA 'Özellik Adı: Değer' formatında yaz. Her bir özellik yeni satırda olmalı. Örnek format:\nMalzeme: Paslanmaz Çelik\nBoyut: 50x30mm\nAğırlık: 25g\nRenk: Gümüş\nDüz metin paragrafı YAZMA, kesinlikle satır satır 'Anahtar: Değer' formatında yaz. Markdown kullanma:\n\n",
            'all_product' => "Aşağıdaki ürün için profesyonel bir tedarikçi/üretici ürün sayfası içeriği üret. Bu ürünü SATAN bir firma sayfası gibi yaz. ÖNEMLİ KURALLAR: 1) Ürünü aşırı övme, abartılı pazarlama dili kullanma. 2) Ürünün ne olduğunu, ne işe yaradığını, hangi alanlarda kullanıldığını bilgilendirici şekilde anlat. 3) Ürün başlığı ile ilgisi olmayan ürünlerden (klips, montaj aparatı vb.) bahsetme - yalnızca verilen ürüne odaklan. 4) Asla [Marka Adı] gibi yer tutucular kullanma, marka yazman gerekirse doğrudan 'DECK Klips' yaz. 5) specifications alanı MUTLAKA her satırda 'Özellik: Değer' formatında olmalı (her bir özellik \\n ile ayrılmış, düz paragraf YAZMA). 6) features alanı kesinlikle liste değil, akıcı bir düz yazı (paragraf) olmalı. Yanıtı SADECE geçerli bir JSON olarak ver. İçeriklerde Markdown (**, __, ### gibi) Kesinlikle kullanma, onun yerine sadece temiz HTML etiketleri kullan. Hiçbir açıklama metni veya markdown (```json) ekleme. JSON Formatı: {\"short_description\":\"... (tedarikçi sayfasına uygun kısa açıklama, maksimum 150 karakter)\",\"long_description\":\"... (ürünün detaylı açıklaması, kullanım alanları, teknik detaylar - EN AZ 150-250 KELİME UZUNLUĞUNDA, HTML formatında h2, h3, p etiketleri kullanan zengin ve bilgilendirici içerik)\",\"specifications\":\"Malzeme: ...\\nBoyut: ...\\nRenk: ...\\n... (MUTLAKA her satır 'Anahtar: Değer' formatında, düz paragraf yazma)\",\"features\":\"... (ürünün temel özelliklerini, kalitesini ve kullanım avantajlarını açıklayan akıcı bir düz yazı/paragraf, kesinlikle maddeleme/liste yapma)\",\"seo_title\":\"... (maksimum 60 karakter)\",\"seo_description\":\"... (maksimum 155 karakter)\",\"seo_keywords\":\"... (virgülle ayrılmış, 5-8 kelime)\"}. \n\nÜrün/Konu: ",
            'blog_all' => "Aşağıdaki blog başlığı/konusu için tam bir blog yazısı içeriği üret. Asla [Marka Adı] gibi yer tutucular kullanma, marka yazman gerekirse 'DECK Klips' yaz. Yanıtı SADECE geçerli bir JSON olarak ver. İçeriklerde Markdown (**, __) Kesinlikle kullanma. Hiçbir açıklama metni veya markdown (```json) ekleme. JSON Formatı: {\"excerpt\":\"... (kısa ve ilgi çekici özet, maksimum 150 karakter)\",\"content\":\"... (h2, h3 ve p etiketlerini kullanan zengin HTML içerik)\",\"seo_title\":\"... (maksimum 60 karakter)\",\"seo_description\":\"... (maksimum 155 karakter)\",\"seo_keywords\":\"... (virgülle ayrılmış 5-8 kelime)\"}. \n\nBlog Konusu: ",
            'all_project' => "Yalnızca sağlanan 'Proje Adı' konusuna odaklanarak profesyonel içerik üret. Örneğin çatı, pergola veya ahşap işleri ise sadece o konuyu anlat. İlgisi olmayan ürünlerden bahsetme. Asla [Marka Adı] gibi yer tutucular kullanma, marka yazman gerekirse 'DECK Klips' yaz. Yanıtı SADECE geçerli bir JSON olarak ver. İçeriklerde Markdown (**, __) Kesinlikle kullanma. Hiçbir açıklama metni veya markdown (```json) ekleme. JSON Formatı: {\"description\":\"... (projenin detaylı açıklaması, 150-300 kelime)\",\"seo_title\":\"... (maksimum 60 karakter)\",\"seo_description\":\"... (maksimum 155 karakter)\",\"seo_keywords\":\"... (virgülle ayrılmış 5-8 kelime)\"}. \n\nProje Adı: ",
            'all_service' => "Yalnızca sağlanan 'Hizmet Adı' konusuna odaklanarak profesyonel içerik üret. Farklı alakasız ürünler ekleme. Asla [Marka Adı] gibi yer tutucular kullanma, marka yazman gerekirse 'DECK Klips' yaz. Yanıtı SADECE geçerli bir JSON olarak ver. İçeriklerde Markdown (**, __) Kesinlikle kullanma. Hiçbir açıklama metni veya markdown (```json) ekleme. JSON Formatı: {\"short_description\":\"... (1-2 cümle kısa açıklama, maksimum 150 karakter)\",\"long_description\":\"... (detaylı açıklama, 200-400 kelime)\",\"seo_title\":\"... (maksimum 60 karakter)\",\"seo_description\":\"... (maksimum 155 karakter)\",\"seo_keywords\":\"... (virgülle ayrılmış 5-8 kelime)\"}. \n\nHizmet Adı: "
        ];
        
        $fullPrompt = ($aiPromptMap[$type] ?? $aiPromptMap['description']) . $prompt;
        $result = generateAIContent($fullPrompt, $type);
        echo json_encode($result);
        exit;
    }
    
    // File upload AJAX
    if ($page === 'media' && $_POST['action'] === 'upload') {
        if (isset($_FILES['file'])) {
            $folder = $_POST['folder'] ?? 'general';
            $result = handleUpload($_FILES['file'], $folder);
            echo json_encode($result);
        } else {
            echo json_encode(['error' => 'Dosya bulunamadı']);
        }
        exit;
    }
    
    exit;
}

// ============================================
// ROUTER - Load page
// ============================================
$page = $_GET['page'] ?? 'dashboard';
$allowedPages = [
    'dashboard', 'settings', 'hero', 'about', 'timeline',
    'products', 'product-edit', 'product-categories', 'categories',
    'projects', 'project-edit',
    'services', 'service-edit',
    'blog', 'blog-edit',
    'contacts', 'media', 'seo', 'ai', 'navigation'
];

if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}

// Include header
require_once __DIR__ . '/includes/header.php';

// Include page
$pageFile = __DIR__ . '/pages/' . $page . '.php';
if (file_exists($pageFile)) {
    require_once $pageFile;
} else {
    echo '<div class="card"><p>Bu sayfa henüz oluşturulmadı: ' . htmlspecialchars($page) . '</p></div>';
}

// Include footer
require_once __DIR__ . '/includes/footer.php';
