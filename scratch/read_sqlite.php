<?php
$dbFile = __DIR__ . '/../db/custom.db';
if (!file_exists($dbFile)) {
    die("File not found at: $dbFile\n");
}

try {
    $db = new SQLite3($dbFile);
    echo "Successfully opened SQLite database!\n";
    
    // List tables
    $tablesquery = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    echo "Tables:\n";
    while ($table = $tablesquery->fetchArray(SQLITE3_ASSOC)) {
        echo "- " . $table['name'] . "\n";
        
        // Print row count
        $count = $db->querySingle("SELECT COUNT(*) FROM " . $table['name']);
        echo "  Rows: $count\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
