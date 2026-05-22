-- ============================================
-- DECK KLİPS - Database Schema V2
-- Full Admin Panel Support
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. SETTINGS (Key-Value)
-- ============================================
DROP TABLE IF EXISTS settings;
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value LONGTEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. PROJECTS
-- ============================================
DROP TABLE IF EXISTS projects;
CREATE TABLE projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    category ENUM('architecture','furniture','other') NOT NULL DEFAULT 'other',
    main_image VARCHAR(500) DEFAULT '',
    gallery_images TEXT,
    description TEXT,
    year INT,
    location VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('active','draft') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    seo_title VARCHAR(255) DEFAULT '',
    seo_description VARCHAR(500) DEFAULT '',
    seo_keywords VARCHAR(500) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. SERVICES
-- ============================================
DROP TABLE IF EXISTS services;
CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    icon VARCHAR(100) DEFAULT 'Building2',
    short_description VARCHAR(500),
    long_description TEXT,
    status ENUM('active','draft') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    seo_title VARCHAR(255) DEFAULT '',
    seo_description VARCHAR(500) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. PRODUCTS (Expanded)
-- ============================================
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    category VARCHAR(100) NOT NULL DEFAULT 'metal-deck-klips',
    main_image VARCHAR(500) DEFAULT '',
    gallery_images TEXT,
    short_description VARCHAR(500),
    long_description TEXT,
    specifications TEXT,
    features TEXT,
    price DECIMAL(10,2) DEFAULT NULL,
    currency VARCHAR(10) DEFAULT 'TRY',
    stock_status ENUM('in_stock','out_of_stock','pre_order') DEFAULT 'in_stock',
    status ENUM('active','draft') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    seo_title VARCHAR(255) DEFAULT '',
    seo_description VARCHAR(500) DEFAULT '',
    seo_keywords VARCHAR(500) DEFAULT '',
    show_specifications TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. PRODUCT CATEGORIES
-- ============================================
DROP TABLE IF EXISTS product_categories;
CREATE TABLE product_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(500) DEFAULT '',
    sort_order INT DEFAULT 0,
    status ENUM('active','draft') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. BLOG POSTS
-- ============================================
DROP TABLE IF EXISTS blog_posts;
CREATE TABLE blog_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    category_id INT DEFAULT NULL,
    featured_image VARCHAR(500) DEFAULT '',
    excerpt TEXT,
    content LONGTEXT,
    author VARCHAR(255) DEFAULT 'Admin',
    status ENUM('published','draft') DEFAULT 'draft',
    views INT DEFAULT 0,
    seo_title VARCHAR(255) DEFAULT '',
    seo_description VARCHAR(500) DEFAULT '',
    seo_keywords VARCHAR(500) DEFAULT '',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. BLOG CATEGORIES
-- ============================================
DROP TABLE IF EXISTS blog_categories;
CREATE TABLE blog_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. CONTACTS
-- ============================================
DROP TABLE IF EXISTS contacts;
CREATE TABLE contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    subject VARCHAR(255),
    message TEXT,
    status ENUM('new','read','replied') DEFAULT 'new',
    admin_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. MEDIA LIBRARY
-- ============================================
DROP TABLE IF EXISTS media;
CREATE TABLE media (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_url VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT DEFAULT 0,
    width INT DEFAULT 0,
    height INT DEFAULT 0,
    alt_text VARCHAR(255) DEFAULT '',
    title VARCHAR(255) DEFAULT '',
    folder VARCHAR(100) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. PAGE SEO
-- ============================================
DROP TABLE IF EXISTS page_seo;
CREATE TABLE page_seo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    page_slug VARCHAR(100) NOT NULL UNIQUE,
    page_title VARCHAR(255) DEFAULT '',
    seo_title VARCHAR(255) DEFAULT '',
    seo_description VARCHAR(500) DEFAULT '',
    seo_keywords VARCHAR(500) DEFAULT '',
    og_image VARCHAR(500) DEFAULT '',
    canonical_url VARCHAR(500) DEFAULT '',
    schema_type VARCHAR(50) DEFAULT '',
    schema_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 11. ADMIN USERS
-- ============================================
DROP TABLE IF EXISTS admin_users;
CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    role ENUM('admin','editor') DEFAULT 'editor',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INDEXES
-- ============================================
CREATE INDEX idx_projects_category ON projects(category);
CREATE INDEX idx_projects_featured ON projects(featured);
CREATE INDEX idx_projects_status ON projects(status);
CREATE INDEX idx_services_status ON services(status);
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_stock ON products(stock_status);
CREATE INDEX idx_contacts_status ON contacts(status);
CREATE INDEX idx_blog_status ON blog_posts(status);
CREATE INDEX idx_blog_category ON blog_posts(category_id);
CREATE INDEX idx_blog_published ON blog_posts(published_at);
CREATE INDEX idx_media_folder ON media(folder);
CREATE INDEX idx_settings_group ON settings(setting_group);

-- ============================================
-- SEED DATA
-- ============================================

-- Site Settings
INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
-- General
('site_name', 'By Boss Mimarlık Mobilya', 'general'),
('site_tagline', 'Mimarlık ve Mobilya Çözümleri', 'general'),
('site_description', 'By Boss Mimarlık Mobilya, iç mimari, dekorasyon ve özel mobilya tasarımı çözümleri sunmaktadır.', 'general'),
('site_logo', '', 'general'),
('site_favicon', '', 'general'),
-- Contact
('site_email', 'info@bybossmimarlik.com', 'contact'),
('site_phone', '0 532 567 4537', 'contact'),
('site_phone_2', '', 'contact'),
('site_whatsapp', '905325674537', 'contact'),
('site_address', 'İstanbul, Türkiye', 'contact'),
('working_hours', 'Pazartesi - Cuma: 09:00 - 18:00', 'contact'),
('working_hours_weekend', 'Cumartesi: 09:00 - 14:00', 'contact'),
('google_maps_embed', '', 'contact'),
-- Social
('social_instagram', '', 'social'),
('social_facebook', '', 'social'),
('social_twitter', '', 'social'),
('social_linkedin', '', 'social'),
('social_youtube', '', 'social'),
('social_pinterest', '', 'social'),
('social_tiktok', '', 'social'),
-- Hero
('hero_title', 'Profesyonel Deck Montaj Çözümleri', 'hero'),
('hero_subtitle', 'Metal ve plastik deck klips ürünlerimiz ile güvenli, dayanıklı ve estetik deck montajı yapın. Yılların tecrübesi ile en kaliteli ürünleri sunuyoruz.', 'hero'),
('hero_button_text', 'Ürünleri Keşfet', 'hero'),
('hero_button_link', '#products', 'hero'),
('hero_image_1', '', 'hero'),
('hero_image_2', '', 'hero'),
('hero_image_3', '', 'hero'),
-- Stats
('stats_1_value', '500', 'stats'),
('stats_1_label', 'Mutlu Müşteri', 'stats'),
('stats_2_value', '10', 'stats'),
('stats_2_label', 'Yıllık Deneyim', 'stats'),
('stats_3_value', '50', 'stats'),
('stats_3_label', 'Ürün Çeşidi', 'stats'),
-- About
('about_title', 'Hakkımızda', 'about'),
('about_subtitle', 'Kalite ve Güvence', 'about'),
('about_text_1', 'Deck klips sektöründe yılların deneyimi ile müşterilerimize en kaliteli ürünleri sunuyoruz.', 'about'),
('about_text_2', 'Metal ve plastik deck klips ürünlerimiz uluslararası kalite standartlarına uygun olarak üretilmektedir.', 'about'),
('about_image_1', '', 'about'),
('about_image_2', '', 'about'),
('about_image_3', '', 'about'),
('about_image_4', '', 'about'),
('about_button_text', 'Daha Fazla', 'about'),
-- SEO Global
('seo_title', 'DECK Klips - Profesyonel Deck Montaj Çözümleri', 'seo'),
('seo_description', 'Metal ve plastik deck klips çeşitleri. Güvenli, dayanıklı ve estetik deck montaj ürünleri. Toptan ve perakende satış.', 'seo'),
('seo_keywords', 'deck klips, metal deck klips, plastik deck klips, deck montaj, ahşap deck, kompozit deck', 'seo'),
('seo_og_image', '', 'seo'),
('google_analytics', '', 'seo'),
('google_search_console', '', 'seo'),
-- AI
('ai_provider', '', 'ai'),
('ai_openai_key', '', 'ai'),
('ai_openai_model', 'gpt-4o-mini', 'ai'),
('ai_gemini_key', '', 'ai'),
('ai_gemini_model', 'gemini-2.0-flash', 'ai'),
('ai_anthropic_key', '', 'ai'),
('ai_anthropic_model', 'claude-sonnet-4-20250514', 'ai'),
('ai_language', 'tr', 'ai'),
('ai_tone', 'professional', 'ai');

-- Product Categories
INSERT INTO product_categories (name, slug, description, sort_order) VALUES
('Metal Deck Klips', 'metal-deck-klips', 'Paslanmaz çelik ve galvaniz metal deck klips çeşitleri', 1),
('Plastik Deck Klips', 'plastik-deck-klips', 'UV dayanımlı yüksek kalite plastik deck klips çeşitleri', 2);

-- Demo Products
INSERT INTO products (name, slug, category, main_image, short_description, long_description, specifications, features, status, sort_order) VALUES
('Metal Deck Klips Pro', 'metal-deck-klips-pro', 'metal-deck-klips',
 '', 'Profesyonel deck montajı için paslanmaz çelik klips',
 'Paslanmaz çelikten üretilen Metal Deck Klips Pro, exterior deck uygulamalarında uzun ömürlü ve güvenli montaj sağlar. Tüm hava koşullarına dayanıklı, korozyona karşı üstün koruma.',
 'Malzeme: 316 Paslanmaz Çelik\nBoyut: 50mm x 25mm\nKalınlık: 2mm\nPaket: 100 Adet/Kutu\nGaranti: 10 Yıl',
 'Korozyona dayanıklı\nKolay montaj\nTüm hava koşullarına uygun\n10 yıl garanti',
 'active', 1),
('Metal Deck Klips Ekonomik', 'metal-deck-klips-ekonomik', 'metal-deck-klips',
 '', 'Bütçe dostu galvaniz metal deck klips',
 'Galvaniz kaplamalı metal klips, standart deck uygulamaları için ekonomik çözüm. Kolay montaj ve güvenli tutuş sağlar.',
 'Malzeme: Galvaniz Çelik\nBoyut: 45mm x 22mm\nKalınlık: 1.5mm\nPaket: 200 Adet/Kutu\nGaranti: 5 Yıl',
 'Ekonomik fiyat\nGalvaniz kaplama\nToplu alıma uygun\n5 yıl garanti',
 'active', 2),
('Plastik Deck Klips Standart', 'plastik-deck-klips-standart', 'plastik-deck-klips',
 '', 'UV dayanımlı plastik deck klips',
 'Yüksek kaliteli UV stabilize polimerden üretilmiş, güneş ışınlarına karşı dayanıklı plastik deck klips.',
 'Malzeme: UV Stabilize Polimer\nBoyut: 40mm x 20mm\nRenk: Siyah / Kahverengi\nPaket: 250 Adet/Kutu\nGaranti: 3 Yıl',
 'UV dayanımlı\nHafif\nKolay montaj\nRenk seçenekleri',
 'active', 3),
('Plastik Deck Klips Premium', 'plastik-deck-klips-premium', 'plastik-deck-klips',
 '', 'Geliştirilmiş tasarım ile premium plastik klips',
 'Geliştirilmiş kanca sistemi ve takviyeli gövde yapısı ile üstün tutuş performansı.',
 'Malzeme: Takviyeli Polimer\nBoyut: 48mm x 24mm\nRenk: Siyah / Kahverengi / Gri\nPaket: 200 Adet/Kutu\nGaranti: 5 Yıl',
 'Takviyeli gövde\nGelişmiş kanca sistemi\nÇoklu renk seçeneği\n5 yıl garanti',
 'active', 4);

-- Services
INSERT INTO services (name, slug, icon, short_description, long_description, status, sort_order) VALUES
('Deck Montaj Hizmeti', 'deck-montaj-hizmeti', 'Building2',
 'Profesyonel ekibimizle anahtar teslim deck montajı',
 'Uzman ekibimiz ile ahşap ve kompozit deck montajı yapıyoruz. Projelendirme, malzeme seçimi ve uygulama sürecinin tamamında yanınızdayız.',
 'active', 1),
('Teknik Danışmanlık', 'teknik-danismanlik', 'MessageSquare',
 'Doğru ürün seçimi için uzman desteği',
 'Projeniz için en uygun deck klips tipini, malzemeyi ve montaj yöntemini belirlemek üzere teknik danışmanlık hizmeti sunuyoruz.',
 'active', 2),
('Toptan Satış', 'toptan-satis', 'Home',
 'Bayiler ve projeler için toptan fiyatlar',
 'Büyük projeler ve bayi ağımız için özel toptan satış fiyatları sunuyoruz. Özel fiyat teklifi almak için bizimle iletişime geçin.',
 'active', 3),
('Kargo & Lojistik', 'kargo-lojistik', 'Armchair',
 'Türkiye geneli hızlı ve güvenli teslimat',
 'Siparişlerinizi Türkiye genelinde hızlı ve güvenli bir şekilde teslim ediyoruz. Toplu siparişlerde ücretsiz kargo imkanı.',
 'active', 4);

-- Blog Categories
INSERT INTO blog_categories (name, slug, description, sort_order) VALUES
('Montaj Rehberi', 'montaj-rehberi', 'Deck montaj ipuçları ve rehberler', 1),
('Ürün İncelemeleri', 'urun-incelemeleri', 'Deck klips ürün karşılaştırmaları', 2),
('Sektör Haberleri', 'sektor-haberleri', 'Deck sektöründen gelişmeler', 3);

-- Demo Blog Post
INSERT INTO blog_posts (title, slug, category_id, excerpt, content, author, status, published_at) VALUES
('Deck Klips Nasıl Seçilir?', 'deck-klips-nasil-secilir', 1,
 'Projeniz için doğru deck klips seçimi yaparken dikkat etmeniz gereken 5 önemli kriter.',
 '<h2>Deck Klips Seçiminde Dikkat Edilmesi Gerekenler</h2>\n<p>Doğru deck klips seçimi, deck montajınızın ömrünü ve kalitesini doğrudan etkiler. İşte dikkat etmeniz gereken 5 önemli kriter:</p>\n<h3>1. Malzeme Kalitesi</h3>\n<p>Dış mekan uygulamalarında paslanmaz çelik veya UV dayanımlı plastik klipsler tercih edilmelidir.</p>\n<h3>2. Deck Tipi Uyumluluğu</h3>\n<p>Kullandığınız deck tipine (ahşap, kompozit, WPC) uygun klips seçimi yapılmalıdır.</p>\n<h3>3. İklim Koşulları</h3>\n<p>Bölgenizin iklim koşullarına uygun malzeme seçimi önemlidir.</p>\n<h3>4. Montaj Kolaylığı</h3>\n<p>Kolay montaj sağlayan klips tipleri iş gücü maliyetini düşürür.</p>\n<h3>5. Garanti Süresi</h3>\n<p>Uzun garanti süresi ürün kalitesinin bir göstergesidir.</p>',
 'Admin', 'published', NOW());

-- Page SEO
INSERT INTO page_seo (page_slug, page_title, seo_title, seo_description) VALUES
('home', 'Ana Sayfa', 'By Boss Mimarlık Mobilya - Mimarlık ve Mobilya Çözümleri', 'İç mimarlık, dekorasyon ve özel mobilya tasarımı çözümleri sunuyoruz.'),
('products', 'Ürünler', 'Mobilya Ürünlerimiz | By Boss Mimarlık Mobilya', 'Özel tasarım mobilya ürünlerimizi keşfedin.'),
('blog', 'Blog', 'Tasarım Günlüğü & Blog | By Boss Mimarlık Mobilya', 'İç mimari trendler, mobilya tasarım ipuçları ve güncel haberler.'),
('contact', 'İletişim', 'İletişim | By Boss Mimarlık Mobilya', 'Bizimle iletişime geçin.');

-- Admin User (password: admin123 - CHANGE IN PRODUCTION!)
INSERT INTO admin_users (username, password, email, name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'info@bybossmimarlik.com', 'Administrator', 'admin');

-- Timeline Table
CREATE TABLE IF NOT EXISTS timeline (
  id INT AUTO_INCREMENT PRIMARY KEY,
  year VARCHAR(10) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  image VARCHAR(500),
  display_order INT DEFAULT 0,
  is_active ENUM('active', 'draft') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demo Timeline Data
INSERT INTO timeline (year, title, description, display_order, is_active) VALUES
('2012', 'Kuruluş', 'By Boss Mimarlık Mobilya, mimarlık ve mobilya tasarımı faaliyetlerine başladı.', 1, 'active'),
('2016', 'Tasarım Ofisi', 'İlk büyük ölçekli tasarım projesine imza atıldı ve ofis genişletildi.', 2, 'active'),
('2019', 'Üretim Atölyesi', 'Kendi mobilya üretim atölyemiz faaliyete girdi.', 3, 'active'),
('2022', 'Markalaşma', 'Yurtiçi ve yurtdışı özel tasarım projelerle marka tescili yapıldı.', 4, 'active'),
('2025', 'Gelecek Vizyonu', 'Sürdürülebilir mimari ve akıllı mobilya çözümlerine geçiş yapıldı.', 5, 'active');

SET FOREIGN_KEY_CHECKS = 1;
