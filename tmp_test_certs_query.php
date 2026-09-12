<?php
require_once 'backend/config.php';

$pdo = getPDO();

// Test the new certificate query
$cr = $pdo->prepare(
  'SELECT c.id, c.certificate_id, c.student_name, c.score, c.passing_score, c.language_pair, c.level_number,
          c.issued_at, c.exam_attempt_id, c.status, c.user_id,
          u.full_name, u.email, u.school_name, e.title AS exam_title, e.title AS assessment_title
   FROM certificates c
   LEFT JOIN users u ON u.id = c.user_id
   LEFT JOIN exams e ON e.id = c.exam_id
   WHERE c.status IN ("pending", "approved")
   ORDER BY c.issued_at DESC
   LIMIT 5'
);
$cr->execute();
$certificateRows = $cr->fetchAll(PDO::FETCH_ASSOC);

echo "=== Certificates Query Result ===\n\n";
echo "Total certificates (pending/approved): " . count($certificateRows) . "\n\n";

foreach ($certificateRows as $cert) {
    echo "Certificate ID: " . ($cert['certificate_id'] ?? 'NULL') . "\n";
    echo "  Student: " . ($cert['student_name'] ?? 'NULL') . "\n";
    echo "  Score: " . ($cert['score'] ?? 'NULL') . "%\n";
    echo "  Exam: " . ($cert['exam_title'] ?? 'NULL') . "\n";
    echo "  Language: " . ($cert['language_pair'] ?? 'NULL') . "\n";
    echo "  Status: " . ($cert['status'] ?? 'NULL') . "\n";
    echo "  User Email: " . ($cert['email'] ?? 'NULL') . "\n";
    echo "  School: " . ($cert['school_name'] ?? 'NULL') . "\n";
    echo "\n";
}

// Test filtering by status
$pending = array_filter($certificateRows, fn($c) => $c['status'] === 'pending');
$approved = array_filter($certificateRows, fn($c) => $c['status'] === 'approved');

echo "\n=== Status Breakdown ===\n";
echo "Pending: " . count($pending) . "\n";
echo "Approved: " . count($approved) . "\n";

// Verify the manage-certificates endpoint would work
echo "\n=== Testing Approve Logic ===\n";
if (!empty($pending)) {
    $cert = reset($pending);
    echo "Would approve certificate: " . ($cert['certificate_id'] ?? 'NONE') . "\n";
    echo "For student: " . ($cert['student_name'] ?? 'UNKNOWN') . "\n";
}
?>
