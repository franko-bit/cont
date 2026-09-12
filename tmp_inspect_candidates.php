<?php
require 'backend/config.php';
$stmt = $pdo->prepare('SELECT * FROM institution_candidates WHERE user_id = ? ORDER BY created_at DESC LIMIT 20');
$stmt->execute([1]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
var_export($rows);
?>
