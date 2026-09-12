<?php
/**
 * Comprehensive debug of try.php certificate data
 */
require_once './backend/config.php';

$institutionId = 1;
$pdo = getPDO();

echo "=== TRY.PHP CERTIFICATE DEBUG ===\n\n";

// 1. Check certificateRows (what would be in JavaScript)
echo "1. CERTIFICATE_ROWS DATA\n";
echo str_repeat("─", 60) . "\n";

$cr = $pdo->prepare(
  'SELECT c.certificate_id, c.student_name, c.score, c.passing_score, c.issued_at, c.exam_attempt_id,
          ic.id AS candidate_id, ic.full_name AS candidate_name, ic.assessment_id, ia.title AS assessment_title, i.name AS school_name
   FROM certificates c
   INNER JOIN institution_candidates ic ON ic.exam_attempt_id = c.exam_attempt_id
   LEFT JOIN institution_assessments ia ON ia.id = ic.assessment_id
   LEFT JOIN institutions i ON i.id = ic.institution_id
   WHERE ic.institution_id = ?
   ORDER BY c.issued_at DESC
   LIMIT 100'
);
$cr->execute([$institutionId]);
$certificateRows = $cr->fetchAll(PDO::FETCH_ASSOC);

echo "Total issued certificates: " . count($certificateRows) . "\n";
if (count($certificateRows) > 0) {
    foreach (array_slice($certificateRows, 0, 3) as $row) {
        echo sprintf("  - %s | %s | Score: %.0f\n", 
            $row['certificate_id'],
            $row['assessment_title'] ?: 'N/A',
            $row['score']
        );
    }
}

// 2. Check eligible/pending approvals
echo "\n2. PENDING APPROVALS DATA\n";
echo str_repeat("─", 60) . "\n";

$stmt = $pdo->prepare(
  'SELECT c.*, ia.title AS assessment_title, COALESCE(u.school_name, i.name) AS school_name
   FROM institution_candidates c
   LEFT JOIN institution_assessments ia ON ia.id = c.assessment_id
   LEFT JOIN institutions i ON i.id = c.institution_id
   LEFT JOIN users u ON u.id = c.user_id
   WHERE c.institution_id = :institution_id
   ORDER BY c.updated_at DESC, c.created_at DESC
   LIMIT 500'
);
$stmt->execute(['institution_id' => $institutionId]);
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filter for eligible candidates (have score but no certificate)
$issuedAttempts = array_map(fn($c) => (string)($c['exam_attempt_id'] ?? ''), $certificateRows);
$eligible = array_filter($candidates, function($c) use ($issuedAttempts) {
    $hasSourceType = true; // All these are institution_candidates
    $hasScore = $c['score'] !== null;
    $noCertId = !($c['certificate_id'] ?? null);
    $notIssued = !in_array((string)($c['exam_attempt_id'] ?? ''), $issuedAttempts);
    return $hasScore && $noCertId && $notIssued;
});

echo "Total eligible for approval: " . count($eligible) . "\n";
if (count($eligible) > 0) {
    foreach (array_slice($eligible, 0, 3) as $row) {
        echo sprintf("  - %s | Score: %.0f | Exam: %d\n",
            $row['full_name'],
            $row['score'],
            $row['exam_attempt_id'] ?: 0
        );
    }
} else {
    echo "No candidates pending approval\n";
    
    // Debug why
    echo "\nDebug info:\n";
    $withScore = array_filter($candidates, fn($c) => $c['score'] !== null);
    echo "  Candidates with score: " . count($withScore) . "\n";
    
    $withoutCert = array_filter($candidates, fn($c) => !($c['certificate_id'] ?? null));
    echo "  Candidates without certificate_id: " . count($withoutCert) . "\n";
    
    $withoutBoth = array_filter($candidates, fn($c) => $c['score'] !== null && !($c['certificate_id'] ?? null));
    echo "  Candidates with score + no cert_id: " . count($withoutBoth) . "\n";
    
    if (!empty($withoutBoth)) {
        echo "\nThese should be eligible:\n";
        foreach (array_slice($withoutBoth, 0, 3) as $row) {
            echo sprintf("    - %s | Score: %.0f | ExamAttemptId: %s\n",
                $row['full_name'],
                $row['score'],
                $row['exam_attempt_id'] ?: 'NULL'
            );
        }
    }
}

echo "\n3. SUMMARY\n";
echo str_repeat("─", 60) . "\n";
echo "What should appear in try.php:\n";
echo "  ✓ Issued certificates section: " . (count($certificateRows) > 0 ? count($certificateRows) . " certificates" : "empty") . "\n";
echo "  ✓ Pending approvals section: " . (count($eligible) > 0 ? count($eligible) . " candidates" : "empty") . "\n";

?>
