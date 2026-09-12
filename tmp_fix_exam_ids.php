<?php
require_once 'backend/config.php';

$pdo = getPDO();

echo "=== Updating certificates with exam_id from exam_attempts ===\n\n";

// Update certificates to get exam_id from exam_attempts
$stmt = $pdo->prepare("
    UPDATE certificates c
    INNER JOIN exam_attempts ea ON ea.id = c.exam_attempt_id
    SET c.exam_id = ea.exam_id
    WHERE c.exam_id IS NULL AND ea.exam_id IS NOT NULL
");
$stmt->execute();

$count = $stmt->rowCount();
echo "Updated $count certificates with exam_id\n\n";

// Verify the update
$stmt2 = $pdo->prepare("
    SELECT 
        c.certificate_id,
        c.student_name,
        c.exam_id,
        c.score,
        e.title as exam_title
    FROM certificates c
    LEFT JOIN exams e ON e.id = c.exam_id
    WHERE c.status = 'pending'
    LIMIT 3
");
$stmt2->execute();
$rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

echo "=== Verification ===\n";
foreach ($rows as $row) {
    echo "Certificate: " . $row['certificate_id'] . "\n";
    echo "  Student: " . ($row['student_name'] ?: 'NULL') . "\n";
    echo "  Exam ID: " . ($row['exam_id'] ?: 'NULL') . "\n";
    echo "  Exam Title: " . ($row['exam_title'] ?: 'NULL') . "\n";
    echo "  Score: " . $row['score'] . "%\n";
    echo "---\n";
}
?>
