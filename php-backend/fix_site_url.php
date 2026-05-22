<?php
/**
 * Fix site_url in database to remove /tr/ prefix
 * Run once then delete: /fix_site_url.php
 */
require_once __DIR__ . '/api/config.php';

$db = Database::getInstance()->getConnection();

// Update site_url to root
$stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_url'");
$stmt->execute(['https://deckklips.com.tr/']);

// Also check and fix any other /tr/ references in settings
$stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_value LIKE '%/tr/%'");
$rows = $stmt->fetchAll();
$count = 0;
foreach ($rows as $row) {
    $newVal = str_replace('/tr/', '/', $row['setting_value']);
    $upd = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    $upd->execute([$newVal, $row['setting_key']]);
    $count++;
}

echo "site_url updated to https://deckklips.com.tr/<br>";
echo "Fixed {$count} other settings containing /tr/<br>";
echo "<strong>DELETE THIS FILE NOW!</strong>";
