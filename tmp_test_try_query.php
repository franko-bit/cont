<?php
require_once 'backend/config.php';

$pdo = getPDO();

echo "=== Testing try.php certificate query ===\n\n";

// The exact query from try.php
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

echo "Total certificates (pending/approved): " . count($certificateRows) . "\n\n";

$pending = array_filter($certificateRows, fn($c) => $c['status'] === 'pending');
$approved = array_filter($certificateRows, fn($c) => $c['status'] === 'approved');

echo "Pending: " . count($pending) . "\n";
echo "Approved: " . count($approved) . "\n\n";

echo "=== Sample Certificate Data ===\n";
if (!empty($certificateRows)) {
    $first = $certificateRows[0];
    foreach ($first as $key => $val) {
        echo "$key: " . (is_null($val) ? 'NULL' : $val) . "\n";
    }
}
?>
