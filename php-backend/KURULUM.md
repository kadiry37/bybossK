# ATELIER NOIR - Kurulum Rehberi

## 📋 Gereksinimler

- DirectAdmin veya cPanel Hosting
- MySQL 5.7+ veritabanı
- PHP 7.4+ desteği

---

## 🚀 Kurulum Adımları

### 1. Veritabanı Oluşturma

1. Hosting kontrol panelinize (DirectAdmin) giriş yapın
2. **Veritabanları** > **Veritabanı Oluştur** tıklayın
3. Veritabanı adı: `atelier_noir` (veya istediğiniz isim)
4. Kullanıcı adı ve şifre belirleyin
5. **phpMyAdmin** açın
6. Sol menüden oluşturduğunuz veritabanını seçin
7. **İçe Aktar** sekmesine tıklayın
8. `sql/database.sql` dosyasını seçin ve içe aktarın

### 2. API Dosyalarını Yükleme

1. **Dosya Yöneticisi** açın
2. `public_html` klasörüne girin
3. `api` klasörü oluşturun
4. Aşağıdaki dosyaları yükleyin:
   ```
   api/
   ├── config.php
   ├── hero.php
   ├── projects.php
   ├── services.php
   ├── products.php
   ├── contact.php
   └── settings.php
   ```

### 3. config.php Düzenleme

`api/config.php` dosyasını düzenleyin:

```php
// Veritabanı bilgilerini güncelleyin
define('DB_HOST', 'localhost');
define('DB_NAME', 'atelier_noir');     // Veritabanı adınız
define('DB_USER', 'db_user');          // Veritabanı kullanıcı adı
define('DB_PASS', 'db_password');      // Veritabanı şifreniz

// Site URL'nizi güncelleyin
define('SITE_URL', 'https://yourdomain.com');

// API Key değiştirin (güvenlik için)
define('API_KEY', 'your-secret-api-key-here');

// CORS ayarlarını güncelleyin
define('ALLOWED_ORIGINS', [
    'https://yourdomain.com',
    'https://www.yourdomain.com'
]);
```

### 4. Astro Frontend Derleme

Yerel bilgisayarınızda:

```bash
# Proje dizinine gidin
cd astro-project

# Bağımlılıkları yükleyin
npm install

# .env dosyası oluşturun
echo "API_URL=/api" > .env

# Production build alın
npm run build
```

### 5. Frontend Dosyalarını Yükleme

1. `astro-project/dist/` klasöründeki **tüm dosyaları** yükleyin:
   - `index.html`
   - `_astro/` klasörü
   - Diğer dosyalar

2. Dosya yapısı şu şekilde olmalı:
   ```
   public_html/
   ├── index.html
   ├── _astro/
   │   └── ... (CSS, JS dosyaları)
   ├── api/
   │   ├── config.php
   │   ├── hero.php
   │   ├── projects.php
   │   ├── services.php
   │   ├── products.php
   │   ├── contact.php
   │   └── settings.php
   └── uploads/
       └── ... (yüklenen görseller)
   ```

### 6. .htaccess Ayarları (Önemli!)

`public_html` klasörüne `.htaccess` dosyası oluşturun:

```apache
# RewriteEngine
RewriteEngine On

# HTTPS yönlendirme
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# www yönlendirme
RewriteCond %{HTTP_HOST} !^www\. [NC]
RewriteRule ^(.*)$ https://www.%{HTTP_HOST}/$1 [R=301,L]

# Astro SPA routing
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^/api/
RewriteRule ^ /index.html [L]

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json
</IfModule>

# Cache kontrol
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

### 7. API Test

Tarayıcınızda test edin:
- `https://yourdomain.com/api/hero.php`
- `https://yourdomain.com/api/projects.php`
- `https://yourdomain.com/api/services.php`

JSON çıktısı görmelisiniz.

---

## 🔧 Admin Panel Kullanımı

### Admin Giriş Bilgileri

- **URL:** `https://yourdomain.com/admin/`
- **Kullanıcı:** `admin`
- **Şifre:** `admin123`

⚠️ **ÖNEMLİ:** İlk girişten sonra şifrenizi değiştirin!

### phpMyAdmin ile Veri Düzenleme

1. DirectAdmin > **phpMyAdmin**
2. Veritabanınızı seçin
3. Tablodan düzenlemek istediğiniz kaydı seçin

**Tablolar:**
- `settings` - Site ayarları (hero, iletişim, sosyal medya)
- `projects` - Projeler
- `services` - Hizmetler
- `products` - Ürünler (Deck Klips)
- `contacts` - İletişim formu mesajları

---

## 📁 Dosya Yapısı

```
public_html/
├── index.html              ← Ana sayfa (Astro build)
├── _astro/                 ← CSS, JS assets
├── api/                    ← PHP Backend API
│   ├── config.php          ← Veritabanı ayarları
│   ├── hero.php            ← Hero endpoint
│   ├── projects.php        ← Projeler endpoint
│   ├── services.php        ← Hizmetler endpoint
│   ├── products.php        ← Ürünler endpoint
│   ├── contact.php         ← İletişim formu
│   └── settings.php        ← Site ayarları
├── uploads/                ← Yüklenen görseller
├── robots.txt              ← SEO
├── sitemap.xml             ← SEO
└── .htaccess               ← Apache ayarları
```

---

## 🛠️ Sorun Giderme

### "Veritabanı bağlantı hatası"
- `config.php` dosyasındaki bilgileri kontrol edin
- Veritabanı kullanıcısının izinleri olduğundan emin olun

### "CORS hatası"
- `config.php` dosyasındaki `ALLOWED_ORIGINS` ayarını kontrol edin
- Site URL'nizin listede olduğundan emin olun

### "500 Internal Server Error"
- PHP hata loglarını kontrol edin
- Dosya izinlerini kontrol edin (644 dosyalar, 755 klasörler)

### Sayfa boş görünüyor
- `_astro` klasörünün yüklendiğinden emin olun
- `.htaccess` dosyasının olduğunu kontrol edin

---

## 📞 Destek

Sorunlar için: destek@ateliernoir.com
