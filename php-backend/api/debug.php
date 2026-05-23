<?php 
require_once 'config.php'; 
echo '<pre>'; 
echo 'GITHUB_TOKEN length: ' . (defined('GITHUB_TOKEN') ? strlen(GITHUB_TOKEN) : 'NOT DEFINED') . "\n"; 
echo 'GITHUB_REPO_OWNER: ' . (defined('GITHUB_REPO_OWNER') ? GITHUB_REPO_OWNER : 'NOT DEFINED') . "\n"; 
echo 'GITHUB_REPO_NAME: ' . (defined('GITHUB_REPO_NAME') ? GITHUB_REPO_NAME : 'NOT DEFINED') . "\n"; 
if (file_exists(__DIR__ . '/github_webhook.log')) { 
    echo "\nLOGS:\n" . file_get_contents(__DIR__ . '/github_webhook.log'); 
} else { 
    echo "\nNo webhook log found."; 
} 
echo '</pre>';
