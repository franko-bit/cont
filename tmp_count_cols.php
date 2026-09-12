<?php
require_once 'backend/config.php';
$stmt = $pdo->query('DESCRIBE certificates');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total columns: " . count($cols) . "\n\n";
$names = [];
foreach ($cols as $col) {
    $names[] = $col['Field'];
}
echo implode(", ", $names) . "\n";
?>
