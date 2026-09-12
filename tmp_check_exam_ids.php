<?php
require_once 'backend/config.php';

$pdo = getPDO();

// Check exam_id values in certificates
$stmt = $pdo->prepare("
    SELECT 
        c.certificate_id,
        c.student_name,
        c.exam_id,
        c.score,
        e.title as exam_title,
        ea.exam_id as attempt_exam_id
    FROM certificates c
    LEFT JOIN exams e ON e.id = c.exam_id
    LEFT JOIN exam_attempts ea ON ea.id = c.exam_attempt_id
    WHERE c.status = 'pending'
    LIMIT 3
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== Checking exam_id values ===\n\n";
foreach ($rows as $row) {
    echo "Certificate: " . $row['certificate_id'] . "\n";
    echo "  Student: " . ($row['student_name'] ?: 'NULL') . "\n";
    echo "  Exam ID in cert: " . ($row['exam_id'] ?: 'NULL') . "\n";
    echo "  Exam Title: " . ($row['exam_title'] ?: 'NULL') . "\n";
    echo "  Exam ID from attempt: " . ($row['attempt_exam_id'] ?: 'NULL') . "\n";
    echo "---\n";
}

// Check the exam_attempts table
echo "\nCheck 3 exams that should have titles:\n";
$stmt2 = $pdo->prepare("
    SELECT DISTINCT e.id, e.title FROM exams e LIMIT 3
");
$stmt2->execute();
$exams = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach ($exams as $exam) {
    echo "  Exam " . $exam['id'] . ": " . $exam['title'] . "\n";
}
?>
