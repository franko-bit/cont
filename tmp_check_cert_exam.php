<?php
require_once 'backend/config.php';

$pdo = getPDO();

// Check a specific certificate
echo "=== Certificate Attempt 33 Details ===\n";
$cert = $pdo->prepare("SELECT * FROM certificates WHERE exam_attempt_id = 33");
$cert->execute();
$row = $cert->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo "Certificate ID: " . ($row['certificate_id'] ?? 'NULL') . "\n";
    echo "Student Name: " . ($row['student_name'] ?? 'NULL') . "\n";
    echo "User ID: " . ($row['user_id'] ?? 'NULL') . "\n";
    echo "Exam ID: " . ($row['exam_id'] ?? 'NULL') . "\n";
    echo "Score: " . ($row['score'] ?? 'NULL') . "\n";
    echo "Status: " . ($row['status'] ?? 'NULL') . "\n";
    
    // Check the exam
    if ($row['exam_id']) {
        $exam = $pdo->prepare("SELECT id, title FROM exams WHERE id = ?");
        $exam->execute([$row['exam_id']]);
        $examRow = $exam->fetch();
        echo "Exam Title: " . ($examRow['title'] ?? 'NOT FOUND') . "\n";
    } else {
        echo "Exam ID is NULL - check exam_attempts\n";
        $attempt = $pdo->prepare("SELECT exam_id FROM exam_attempts WHERE id = 33");
        $attempt->execute();
        $attemptRow = $attempt->fetch();
        echo "Exam Attempt 33 has exam_id: " . ($attemptRow['exam_id'] ?? 'NULL') . "\n";
    }
} else {
    echo "No certificate found for attempt 33\n";
}

// Check if exam table has any records
echo "\n=== Exam Table ===\n";
$exams = $pdo->query("SELECT COUNT(*) FROM exams");
echo "Total exams: " . $exams->fetchColumn() . "\n";

$exams = $pdo->query("SELECT id, title FROM exams LIMIT 3");
foreach ($exams as $e) {
    echo "  - " . $e['id'] . ": " . $e['title'] . "\n";
}
?>
