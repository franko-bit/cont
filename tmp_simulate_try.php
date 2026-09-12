<?php
require_once 'backend/config.php';

$pdo = getPDO();

// Simulate what happens in try.php
$institutionId = 1;

// Get certificates
$certificateRows = [];
$stmt = $pdo->prepare("SHOW TABLES LIKE 'certificates'");
$stmt->execute();
if ($stmt->rowCount() > 0) {
    $cr = $pdo->prepare(
      'SELECT c.id, c.certificate_id, c.student_name, c.score, c.passing_score, c.language_pair, c.level_number,
              c.issued_at, c.exam_attempt_id, c.status, c.user_id,
              u.full_name, u.email, u.school_name, e.title AS exam_title, e.title AS assessment_title
       FROM certificates c
       LEFT JOIN users u ON u.id = c.user_id
       LEFT JOIN exams e ON e.id = c.exam_id
       WHERE c.status IN ("pending", "approved")
       ORDER BY c.issued_at DESC
       LIMIT 10'
    );
    $cr->execute();
    $certificateRows = $cr->fetchAll(PDO::FETCH_ASSOC);
}

echo "=== Certificate Rows Data ===\n";
echo "Count: " . count($certificateRows) . "\n\n";

// Output as JSON like try.php does
$json = json_encode($certificateRows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
echo "JSON output (first 500 chars):\n";
echo substr($json, 0, 500) . "...\n\n";

// Verify the filtering works like in JavaScript
$pending = array_filter($certificateRows, function($c) {
    return $c['status'] === 'pending';
});

$approved = array_filter($certificateRows, function($c) {
    return $c['status'] === 'approved';
});

echo "Pending count: " . count($pending) . "\n";
echo "Approved count: " . count($approved) . "\n\n";

if (!empty($pending)) {
    $first = reset($pending);
    echo "=== First Pending Certificate ===\n";
    echo "Certificate ID: " . ($first['certificate_id'] ?? 'NULL') . "\n";
    echo "Student: " . ($first['student_name'] ?? 'NULL') . "\n";
    echo "Score: " . ($first['score'] ?? 'NULL') . "\n";
    echo "Status: " . ($first['status'] ?? 'NULL') . "\n";
    echo "Exam Title: " . ($first['exam_title'] ?? 'NULL') . "\n";
    echo "Language Pair: " . ($first['language_pair'] ?? 'NULL') . "\n";
    echo "\nThis should display in 'Awaiting Approval' section\n";
}
?>
