<?php
require_once 'backend/config.php';

$pdo = getPDO();

echo "=== Recent Certificates Detail ===\n\n";

// Get the most recent certificate
$stmt = $pdo->prepare("
    SELECT c.id, c.certificate_id, c.student_name, c.user_id, c.exam_attempt_id, u.full_name, u.email
    FROM certificates c
    LEFT JOIN users u ON u.id = c.user_id
    ORDER BY c.issued_at DESC
    LIMIT 1
");
$stmt->execute();
$cert = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Most recent certificate:\n";
foreach ($cert as $k => $v) {
    echo "  $k: " . ($v ?? 'NULL') . "\n";
}

echo "\n=== Checking if student_name is being saved properly ===\n";

// Look at attempt 33
$stmt = $pdo->prepare("
    SELECT ea.id, ea.user_id, u.full_name, u.email, c.student_name
    FROM exam_attempts ea
    LEFT JOIN users u ON u.id = ea.user_id
    LEFT JOIN certificates c ON c.exam_attempt_id = ea.id
    WHERE ea.id = 33
");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo "Attempt 33:\n";
    echo "  Attempt ID: " . $row['id'] . "\n";
    echo "  User ID: " . $row['user_id'] . "\n";
    echo "  User Full Name: " . ($row['full_name'] ?? 'NULL') . "\n";
    echo "  Certificate Student Name: " . ($row['student_name'] ?? 'NULL') . "\n";
} else {
    echo "No data found for attempt 33\n";
}
?>
