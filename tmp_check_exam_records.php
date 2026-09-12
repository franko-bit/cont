<?php
require_once 'backend/config.php';
try {
    $stmt = $pdo->query('DESCRIBE exam_records');
    echo "exam_records table exists\n";
} catch (Exception $e) {
    echo "Table does not exist: " . $e->getMessage() . "\n";
}
?>
