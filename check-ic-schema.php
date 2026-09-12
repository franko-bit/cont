<?php
require_once __DIR__ . '/backend/config.php';
$pdo = getPDO();

echo "Institution Candidates Columns:\n";
$result = $pdo->query('DESCRIBE institution_candidates');
while($row = $result->fetch()) {
    echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>
