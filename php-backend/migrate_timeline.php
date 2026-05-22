<?php
require_once __DIR__ . '/api/config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if columns exist
    $check = $db->query("SHOW COLUMNS FROM timeline LIKE 'title_en'");
    if ($check->rowCount() == 0) {
        $db->exec("ALTER TABLE timeline ADD COLUMN title_en VARCHAR(255) DEFAULT ''");
        $db->exec("ALTER TABLE timeline ADD COLUMN description_en TEXT");
        $db->exec("ALTER TABLE timeline ADD COLUMN title_ar VARCHAR(255) DEFAULT ''");
        $db->exec("ALTER TABLE timeline ADD COLUMN description_ar TEXT");
        echo "Columns added successfully.\n";
    } else {
        echo "Columns already exist.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
