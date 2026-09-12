<?php
/**
 * Add verification columns to users table
 * Run this script once: php database/add_verification_columns.php
 */

require_once 'backend/config.php';

$migrations = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS verified TINYINT(1) DEFAULT 0 COMMENT 'User has completed ID verification'",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_status VARCHAR(50) DEFAULT 'pending' COMMENT 'Verification status: pending, approved, declined'",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS didit_session_id VARCHAR(255) NULL COMMENT 'Didit API session ID for verification'",
];

echo "Running migrations...\n";
echo str_repeat("=", 50) . "\n";

foreach ($migrations as $i => $migration) {
    try {
        $pdo->exec($migration);
        echo "[✓] Migration " . ($i + 1) . " completed\n";
    } catch (Exception $e) {
        echo "[✗] Migration " . ($i + 1) . " failed: " . $e->getMessage() . "\n";
    }
}

echo str_repeat("=", 50) . "\n";
echo "Verification columns added to users table!\n";

// Display the updated table structure
echo "\n\nUsers Table Structure:\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->query("DESCRIBE users");
$columns = $stmt->fetchAll();

foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}

echo "\n✓ Setup complete!\n";
?>
