<?php
require_once 'backend/config.php';

$pdo = getPDO();

// Check exam_attempts for our certificates
$stmt = $pdo->prepare("
    SELECT 
        ea.id,
        ea.exam_id,
        ea.user_id,
        ea.score,
        e.title as exam_title,
        c.certificate_id,
        c.student_name
    FROM exam_attempts ea
    LEFT JOIN exams e ON e.id = ea.exam_id
    LEFT JOIN certificates c ON c.exam_attempt_id = ea.id
    WHERE c.certificate_id IS NOT NULL
    LIMIT 5
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== Exam Attempts with Certificates ===\n\n";
foreach ($rows as $row) {
    echo "Attempt ID: " . $row['id'] . "\n";
    echo "  Exam ID: " . ($row['exam_id'] ?: 'NULL') . "\n";
    echo "  Exam Title: " . ($row['exam_title'] ?: 'NULL') . "\n";
    echo "  Certificate: " . $row['certificate_id'] . "\n";
    echo "  Student: " . ($row['student_name'] ?: 'NULL') . "\n";
    echo "  Score: " . $row['score'] . "\n";
    echo "---\n";
}

// Check how many exam_attempts are missing exam_id
$stmt2 = $pdo->prepare("
    SELECT COUNT(*) as missing_exam_id FROM exam_attempts WHERE exam_id IS NULL
");
$stmt2->execute();
$count = $stmt2->fetchColumn();
echo "\nTotal exam_attempts with NULL exam_id: " . $count . "\n";
?>
