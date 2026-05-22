<?php
require_once __DIR__ . '/api/config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if columns exist
    $query = "SHOW COLUMNS FROM `services` LIKE 'main_image'";
    $stmt = $db->query($query);
    $exists = $stmt->fetch();
    
    if (!$exists) {
        $db->exec("ALTER TABLE `services` ADD `main_image` VARCHAR(255) DEFAULT NULL;");
        echo "Added main_image column.\n";
    }

    $query = "SHOW COLUMNS FROM `services` LIKE 'gallery_images'";
    $stmt = $db->query($query);
    $exists = $stmt->fetch();
    
    if (!$exists) {
        $db->exec("ALTER TABLE `services` ADD `gallery_images` TEXT DEFAULT NULL;");
        echo "Added gallery_images column.\n";
    }

    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
