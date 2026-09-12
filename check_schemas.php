<?php
require_once 'backend/config.php';
$pdo = getPDO();

echo "=== exam_attempts TABLE SCHEMA ===\n";
$stmt = $pdo->query("DESCRIBE exam_attempts");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($cols as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")" . ($col['Null'] === 'NO' ? " NOT NULL" : "") . "\n";
}

echo "\n=== institution_candidates TABLE SCHEMA ===\n";
$stmt = $pdo->query("DESCRIBE institution_candidates");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($cols as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")" . ($col['Null'] === 'NO' ? " NOT NULL" : "") . "\n";
}
?>
