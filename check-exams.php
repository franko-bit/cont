<?php
require 'backend/config.php';
$stmt = $pdo->prepare('SELECT id, title FROM exams WHERE is_active = 1 LIMIT 10');
$stmt->execute();
foreach ($stmt->fetchAll() as $r) {
    echo $r['id'] . ': ' . $r['title'] . "\n";
}
?>
