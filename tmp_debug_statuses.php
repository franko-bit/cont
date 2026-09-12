<?php
require_once 'backend/config.php';

$pdo = getPDO();

$cr = $pdo->prepare(
    'SELECT c.id, c.certificate_id, c.student_name, c.score, c.passing_score, c.language_pair, c.level_number,
            c.issued_at, c.exam_attempt_id, c.status, c.user_id,
            u.full_name, u.email, u.school_name, e.title AS exam_title, e.title AS assessment_title
     FROM certificates c
     LEFT JOIN users u ON u.id = c.user_id
     LEFT JOIN exams e ON e.id = c.exam_id
     WHERE c.status IN ("pending", "approved")
     ORDER BY c.issued_at DESC
     LIMIT 100'
);
$cr->execute();
$certificateRows = $cr->fetchAll(PDO::FETCH_ASSOC);

echo "SQL Query result count: " . count($certificateRows) . "\n\n";

// Filter like JavaScript
$issued = array_filter($certificateRows, function($c) { return $c['status'] === 'approved'; });
$pending = array_filter($certificateRows, function($c) { return $c['status'] === 'pending'; });

echo "Issued (status='approved'): " . count($issued) . "\n";
echo "Pending (status='pending'): " . count($pending) . "\n";

// Check all statuses in database
$allStatusesStmt = $pdo->prepare("SELECT DISTINCT status, COUNT(*) as count FROM certificates GROUP BY status");
$allStatusesStmt->execute();
$statuses = $allStatusesStmt->fetchAll(PDO::FETCH_ASSOC);

echo "\nAll statuses in certificates table:\n";
foreach ($statuses as $row) {
    echo "  Status: '" . $row['status'] . "' - Count: " . $row['count'] . "\n";
}

// Let's also check if there's any data in another source
echo "\n\n=== Also checking candidates table ===\n";
$candidateStmt = $pdo->prepare("SELECT COUNT(*) FROM institution_candidates WHERE score IS NOT NULL");
$candidateStmt->execute();
echo "Institution candidates with scores: " . $candidateStmt->fetchColumn() . "\n";

?>
