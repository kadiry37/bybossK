<?php
require 'php-backend/api/config.php';
try {
    $db = Database::getInstance()->getConnection();
    $count = $db->query("SELECT COUNT(*) FROM timeline")->fetchColumn();
    echo "Timeline row count: " . $count . "\n";
    $items = $db->query("SELECT id, title FROM timeline")->fetchAll();
    print_r($items);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
