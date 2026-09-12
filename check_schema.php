<?php
require_once 'backend/config.php';

echo "=== Users Table Schema ===\n";
$stmt = $pdo->query("DESCRIBE users");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo "{$col['Field']} ({$col['Type']}) - Key: {$col['Key']}\n";
}

echo "\n=== Sample User Record ===\n";
$stmt = $pdo->query("SELECT * FROM users LIMIT 1");
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user) {
    foreach ($user as $k => $v) {
        echo "$k = $v\n";
    }
}
?>
