<?php
require_once __DIR__ . '/config.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "<h3>Starting SEO Columns Migration...</h3>";

    $tables = [
        'product_translations',
        'project_translations',
        'service_translations',
        'blog_translations'
    ];

    foreach ($tables as $table) {
        // Tablo var mı kontrol et
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            
            // seo_title var mı kontrol et
            $colStmt = $db->query("SHOW COLUMNS FROM `$table` LIKE 'seo_title'");
            if ($colStmt->rowCount() == 0) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN `seo_title` VARCHAR(255) DEFAULT ''");
                echo "Added seo_title to $table <br>";
            }

            // seo_description var mı kontrol et
            $colStmt = $db->query("SHOW COLUMNS FROM `$table` LIKE 'seo_description'");
            if ($colStmt->rowCount() == 0) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN `seo_description` TEXT");
                echo "Added seo_description to $table <br>";
            }

            // seo_keywords var mı kontrol et
            $colStmt = $db->query("SHOW COLUMNS FROM `$table` LIKE 'seo_keywords'");
            if ($colStmt->rowCount() == 0) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN `seo_keywords` VARCHAR(500) DEFAULT ''");
                echo "Added seo_keywords to $table <br>";
            }
        }
    }

    echo "<h3>Migration Completed Successfully! You can now delete this file.</h3>";

} catch (Exception $e) {
    echo "<h3>Migration Error: " . $e->getMessage() . "</h3>";
}
