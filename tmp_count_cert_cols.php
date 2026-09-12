<?php
require_once 'backend/config.php';

$pdo = getPDO();

// Get exact column count
$stmt = $pdo->query('DESCRIBE certificates');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== All Columns in certificates table ===\n";
echo "Total: " . count($cols) . " columns\n\n";

$names = [];
foreach ($cols as $col) {
    $names[] = $col['Field'];
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}

echo "\n=== Column count breakdown ===\n";
echo "Named columns in INSERT: 18\n";
echo "Functions/Literals in VALUES: NOW(), NOW(), NULL, NULL, NULL, NULL = 6\n";
echo "Total values: 24\n";
echo "Actual columns in table: " . count($names) . "\n";
echo "\nMissing column: " . (count($names) > 24 ? 'status' : 'unknown') . "\n";
?>
