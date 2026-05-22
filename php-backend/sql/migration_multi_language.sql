-- ============================================
-- MULTI-LANGUAGE (TR, EN, AR) MIGRATION
-- ============================================

-- 1. Add active languages setting
INSERT IGNORE INTO settings (setting_key, setting_value, setting_group) 
VALUES ('active_languages', 'tr,en,ar', 'general');

-- ============================================
-- TRANSLATION TABLES
-- ============================================

-- PRODUCT TRANSLATIONS
CREATE TABLE IF NOT EXISTS product_translations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    lang_code VARCHAR(10) NOT NULL,
    name VARCHAR(255) NOT NULL,
    short_description VARCHAR(500),
    long_description TEXT,
    specifications TEXT,
    features TEXT,
    UNIQUE KEY idx_product_lang (product_id, lang_code),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PROJECT TRANSLATIONS
CREATE TABLE IF NOT EXISTS project_translations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    lang_code VARCHAR(10) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    UNIQUE KEY idx_project_lang (project_id, lang_code),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SERVICE TRANSLATIONS
CREATE TABLE IF NOT EXISTS service_translations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_id INT NOT NULL,
    lang_code VARCHAR(10) NOT NULL,
    name VARCHAR(255) NOT NULL,
    short_description VARCHAR(500),
    long_description TEXT,
    UNIQUE KEY idx_service_lang (service_id, lang_code),
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- BLOG POST TRANSLATIONS
CREATE TABLE IF NOT EXISTS blog_translations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    blog_post_id INT NOT NULL,
    lang_code VARCHAR(10) NOT NULL,
    title VARCHAR(255) NOT NULL,
    excerpt TEXT,
    content LONGTEXT,
    seo_title VARCHAR(255) DEFAULT '',
    seo_description VARCHAR(500) DEFAULT '',
    seo_keywords VARCHAR(500) DEFAULT '',
    UNIQUE KEY idx_blog_lang (blog_post_id, lang_code),
    FOREIGN KEY (blog_post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CATEGORY TRANSLATIONS (Optional but good)
CREATE TABLE IF NOT EXISTS product_category_translations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    lang_code VARCHAR(10) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    UNIQUE KEY idx_prod_cat_lang (category_id, lang_code),
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SETTINGS / KEY-VALUE MIGRATION (We will handle this dynamically in PHP, but let's insert EN/AR placeholders for hero and about)
INSERT IGNORE INTO settings (setting_key, setting_value, setting_group) 
SELECT CONCAT(setting_key, '_en'), '', setting_group 
FROM settings 
WHERE setting_group IN ('hero', 'about', 'seo');

INSERT IGNORE INTO settings (setting_key, setting_value, setting_group) 
SELECT CONCAT(setting_key, '_ar'), '', setting_group 
FROM settings 
WHERE setting_group IN ('hero', 'about', 'seo');
