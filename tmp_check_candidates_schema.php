<?php
require_once 'backend/config.php';

$pdo = getPDO();

echo "=== institution_candidates Table Schema ===\n";
$stmt = $pdo->query("DESCRIBE institution_candidates");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")" . ($col['Null'] === 'NO' ? " NOT NULL" : "") . "\n";
}

echo "\n=== Sample Data ===\n";
$stmt = $pdo->query("SELECT * FROM institution_candidates LIMIT 3");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    echo json_encode($row) . "\n";
}
?>
