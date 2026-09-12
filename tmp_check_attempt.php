<?php
require 'backend/config.php';
$attempt_id = 33;
$stmt = $pdo->prepare('SELECT * FROM exam_attempts WHERE id = ?');
$stmt->execute([$attempt_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    print_r($row);
} else {
    echo "No attempt row found for id=$attempt_id\n";
}
