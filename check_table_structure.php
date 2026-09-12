<?php
require_once 'backend/config.php';

// Check if users table has verified column
$stmt = $pdo->query("DESCRIBE users");
$columns = $stmt->fetchAll();

echo "Users Table Structure:\n";
echo "======================\n";
foreach ($columns as $col) {
    echo $col['Field'] . " - " . $col['Type'] . " - " . ($col['Null'] ? 'NULL' : 'NOT NULL') . "\n";
}

// Check if verified and verification_status columns exist
$has_verified = false;
$has_verification_status = false;

foreach ($columns as $col) {
    if ($col['Field'] === 'verified') $has_verified = true;
    if ($col['Field'] === 'verification_status') $has_verification_status = true;
}

echo "\n\nColumn Check:\n";
echo "=============\n";
echo "Has 'verified' column: " . ($has_verified ? 'YES' : 'NO') . "\n";
echo "Has 'verification_status' column: " . ($has_verification_status ? 'YES' : 'NO') . "\n";

// If columns don't exist, add them
if (!$has_verified || !$has_verification_status) {
    echo "\n\nAdding missing columns...\n";
    
    if (!$has_verified) {
        $pdo->exec("ALTER TABLE users ADD COLUMN verified TINYINT(1) DEFAULT 0");
        echo "Added 'verified' column\n";
    }
    
    if (!$has_verification_status) {
        $pdo->exec("ALTER TABLE users ADD COLUMN verification_status VARCHAR(50) DEFAULT 'pending'");
        echo "Added 'verification_status' column\n";
    }
    
    echo "Columns added successfully!\n";
} else {
    echo "\nAll required columns exist!\n";
}
?>
