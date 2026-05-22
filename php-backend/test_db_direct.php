<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Detailed Database Diagnostics</h2>";

require_once __DIR__ . '/api/config.php';

echo "<h3>Configured Credentials:</h3>";
echo "Host: " . DB_HOST . "<br>";
echo "Database: " . DB_NAME . "<br>";
echo "User: " . DB_USER . "<br>";

try {
    // 1. Connect without dbname first
    echo "Connecting to MySQL server (without database)...<br>";
    $dsn_no_db = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn_no_db, DB_USER, DB_PASS);
    echo "Connected successfully to server!<br>";
    
    // 2. List visible databases
    echo "Listing visible databases:<br><ul>";
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($databases as $db) {
        echo "<li>" . htmlspecialchars($db) . "</li>";
    }
    echo "</ul>";
    
    // 3. Try to select the configured database
    $targetDb = DB_NAME;
    echo "Attempting to select database: " . htmlspecialchars($targetDb) . "...<br>";
    try {
        $pdo->exec("USE `" . str_replace("`","``",$targetDb) . "`");
        echo "<span style='color: green; font-weight: bold;'>Successfully switched to database " . htmlspecialchars($targetDb) . "!</span><br>";
        
        // Try creating a dummy table to verify write permissions
        echo "Testing table creation...<br>";
        $pdo->exec("CREATE TABLE IF NOT EXISTS test_perm (id INT PRIMARY KEY)");
        echo "<span style='color: green;'>Table creation test passed!</span><br>";
        $pdo->exec("DROP TABLE test_perm");
        echo "<span style='color: green;'>Table drop test passed!</span><br>";
        
    } catch (PDOException $e) {
        echo "<span style='color: red; font-weight: bold;'>Failed to select or use database: " . htmlspecialchars($e->getMessage()) . "</span><br>";
        echo "This usually means:<br>";
        echo "1. The database does not exist.<br>";
        echo "2. The database user does not have permission/privileges for this database. (In DirectAdmin, you must add the user to the database with ALL PRIVILEGES).<br>";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red; font-weight: bold;'>Connection failed: " . htmlspecialchars($e->getMessage()) . "</span><br>";
}
