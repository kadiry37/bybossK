<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Debug Env & DB Config</h2>";

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo ".env file exists!<br>";
    echo "Content of .env (masked):<br><pre>";
    $lines = file($envFile);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($name, $val) = explode('=', $line, 2);
            if (trim($name) === 'DB_PASS' || trim($name) === 'GITHUB_TOKEN') {
                echo trim($name) . "=*******\n";
            } else {
                echo trim($name) . "=" . trim($val) . "\n";
            }
        }
    }
    echo "</pre>";
} else {
    echo ".env file DOES NOT exist at: " . htmlspecialchars($envFile) . "<br>";
}

require_once __DIR__ . '/api/config.php';

echo "<h3>Config constants:</h3>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "DB_USER: " . DB_USER . "<br>";
echo "DB_PASS: " . (empty(DB_PASS) ? "EMPTY" : "NOT EMPTY") . "<br>";

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    echo "DSN used: " . htmlspecialchars($dsn) . "<br>";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    echo "PDO connection to DSN successful!<br>";
    
    // Check selected database
    $currentDb = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "Selected DB according to SELECT DATABASE(): " . htmlspecialchars($currentDb) . "<br>";
} catch (Exception $e) {
    echo "PDO Error: " . htmlspecialchars($e->getMessage()) . "<br>";
}
