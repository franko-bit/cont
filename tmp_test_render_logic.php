<?php
require_once 'backend/config.php';

$pdo = getPDO();

echo "=== Testing Certificate Rendering Logic ===\n\n";

// Get all certificates
$stmt = $pdo->prepare("
    SELECT c.id, c.certificate_id, c.student_name, c.score, c.passing_score, c.language_pair, c.level_number,
            c.issued_at, c.exam_attempt_id, c.status, c.user_id,
            u.full_name, u.email, u.school_name, e.title AS exam_title, e.title AS assessment_title
    FROM certificates c
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN exams e ON e.id = c.exam_id
    WHERE c.status IN ('pending', 'approved')
    ORDER BY c.issued_at DESC
    LIMIT 100
");
$stmt->execute();
$certificateRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total certificates retrieved: " . count($certificateRows) . "\n\n";

// Filter like JavaScript does
$issued = array_filter($certificateRows, function($c) { return $c['status'] === 'approved'; });
$pending = array_filter($certificateRows, function($c) { return $c['status'] === 'pending'; });

echo "Issued (approved): " . count($issued) . "\n";
echo "Pending: " . count($pending) . "\n\n";

echo "=== Pending Certificates ===\n";
foreach ($pending as $cert) {
    echo "ID: " . $cert['certificate_id'] . "\n";
    echo "Student: " . ($cert['student_name'] ?: 'NULL') . "\n";
    echo "Status: " . $cert['status'] . "\n";
    echo "Exam: " . ($cert['exam_title'] ?: 'NULL') . "\n";
    echo "---\n";
}

echo "\n=== HTML that should be generated ===\n";
if (!empty($pending)) {
    echo "Pending certificates exist, should render 'Awaiting Approval' section\n";
    echo "Template code will generate:\n";
    echo "  - HR separator\n";
    echo "  - Card title: 'Awaiting Approval' with badge: " . count($pending) . "\n";
    echo "  - Grid with " . count($pending) . " certificate cards\n";
}
?>
