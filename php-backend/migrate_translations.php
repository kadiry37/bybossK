<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

require_once __DIR__ . '/api/config.php';

echo "<h2>Multi-Language Migration Started...</h2>";
echo "<pre>";

try {
    $pdo = Database::getInstance()->getConnection();
    echo "Connected to database successfully.\n";
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 1. Internal SQL Schema
$queries = [
    "INSERT IGNORE INTO settings (setting_key, setting_value, setting_group) VALUES ('active_languages', 'tr,en,ar', 'general')",
    
    "CREATE TABLE IF NOT EXISTS product_translations (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS project_translations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        project_id INT NOT NULL,
        lang_code VARCHAR(10) NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        UNIQUE KEY idx_project_lang (project_id, lang_code),
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS service_translations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        service_id INT NOT NULL,
        lang_code VARCHAR(10) NOT NULL,
        name VARCHAR(255) NOT NULL,
        short_description VARCHAR(500),
        long_description TEXT,
        UNIQUE KEY idx_service_lang (service_id, lang_code),
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS blog_translations (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "INSERT IGNORE INTO settings (setting_key, setting_value, setting_group) 
     SELECT CONCAT(setting_key, '_en'), '', setting_group FROM settings WHERE setting_group IN ('hero', 'about', 'seo')",
     
    "INSERT IGNORE INTO settings (setting_key, setting_value, setting_group) 
     SELECT CONCAT(setting_key, '_ar'), '', setting_group FROM settings WHERE setting_group IN ('hero', 'about', 'seo')"
];

echo "Creating/Updating Tables...\n";
foreach ($queries as $query) {
    try {
        $pdo->exec($query);
        echo "Executed query successfully.\n";
    } catch (PDOException $e) {
        echo "Notice: " . $e->getMessage() . "\n";
    }
}

// 2. Migrate Data
$tables = [
    'products' => 'product_translations',
    'projects' => 'project_translations',
    'services' => 'service_translations',
    'blog_posts' => 'blog_translations'
];

foreach ($tables as $main => $trans) {
    try {
        echo "Migrating $main...";
        $stmt = $pdo->query("SELECT * FROM $main");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($main == 'products') {
            $ins = $pdo->prepare("INSERT IGNORE INTO $trans (product_id, lang_code, name, short_description, long_description, specifications, features) VALUES (?, 'tr', ?, ?, ?, ?, ?)");
            foreach ($rows as $r) $ins->execute([$r['id'], $r['name'], $r['short_description'], $r['long_description'], $r['specifications'], $r['features']]);
        } elseif ($main == 'projects') {
            $ins = $pdo->prepare("INSERT IGNORE INTO $trans (project_id, lang_code, name, description) VALUES (?, 'tr', ?, ?)");
            foreach ($rows as $r) $ins->execute([$r['id'], $r['name'], $r['description']]);
        } elseif ($main == 'services') {
            $ins = $pdo->prepare("INSERT IGNORE INTO $trans (service_id, lang_code, name, short_description, long_description) VALUES (?, 'tr', ?, ?, ?)");
            foreach ($rows as $r) $ins->execute([$r['id'], $r['name'], $r['short_description'], $r['long_description']]);
        } elseif ($main == 'blog_posts') {
            $ins = $pdo->prepare("INSERT IGNORE INTO $trans (blog_post_id, lang_code, title, excerpt, content, seo_title, seo_description, seo_keywords) VALUES (?, 'tr', ?, ?, ?, ?, ?, ?)");
            foreach ($rows as $r) $ins->execute([$r['id'], $r['title'], $r['excerpt'], $r['content'], $r['seo_title'], $r['seo_description'], $r['seo_keywords']]);
        }
        echo " Done (" . count($rows) . " rows).\n";
    } catch (Exception $e) {
        echo " Error: " . $e->getMessage() . "\n";
    }
}

echo "\nMigration Completed Successfully!\n";
echo "</pre>";
?>
