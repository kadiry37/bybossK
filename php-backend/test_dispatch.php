<?php
require_once __DIR__ . '/api/config.php';

$url = "https://api.github.com/repos/" . GITHUB_REPO_OWNER . "/" . GITHUB_REPO_NAME . "/dispatches";
$data = json_encode(['event_type' => 'content-update']);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . GITHUB_TOKEN,
    'Accept: application/vnd.github.v3+json',
    'Content-Type: application/json',
    'User-Agent: PHP-Webhook-Client'
]);

// to see the output including headers
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
// curl_close($ch); // Deprecated in PHP 8.5+

echo "Status: " . $status . "\n";
echo "Error: " . $error . "\n";
echo "Response: " . $response . "\n";
