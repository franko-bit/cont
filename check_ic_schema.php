<?php
require_once 'backend/config.php';
$pdo = getPDO();

$stmt = $pdo->query("DESCRIBE institution_candidates");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== institution_candidates TABLE SCHEMA ===\n";
foreach ($cols as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")" . ($col['Null'] === 'NO' ? " NOT NULL" : "") . "\n";
}
?>
