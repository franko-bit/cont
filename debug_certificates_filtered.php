<?php
/**
 * Debug: Check certificates filtered by institution_id
 */
require_once './backend/config.php';

echo "=== DEBUGGING CERTIFICATES BY INSTITUTION ===\n\n";

// Assume institution_id = 1 (from try.php)
$institutionId = 1;

echo "Checking for institution_id = $institutionId\n\n";

// Query from try.php
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

echo "Certificates found for institution_id = $institutionId: " . count($certificateRows) . "\n\n";

if (empty($certificateRows)) {
    echo "❌ NO CERTIFICATES FOUND\n\n";
    
    // Debug: check what institution_id values exist in institution_candidates
    echo "Institution IDs in institution_candidates:\n";
    $instIds = $pdo->query("SELECT DISTINCT institution_id FROM institution_candidates ORDER BY institution_id")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($instIds as $id) {
        $count = $pdo->prepare("SELECT COUNT(*) FROM institution_candidates WHERE institution_id = ?")->execute([$id]);
        $count = $pdo->prepare("SELECT COUNT(*) FROM institution_candidates WHERE institution_id = ?")->fetchColumn();
        echo "  Institution $id: (checking...)\n";
    }
    
    // Check candidates with exam_attempt_id
    echo "\nCandidates with exam_attempt_id:\n";
    $withAttempt = $pdo->query("
        SELECT id, exam_attempt_id, full_name, institution_id
        FROM institution_candidates
        WHERE exam_attempt_id IS NOT NULL
        ORDER BY institution_id
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($withAttempt as $row) {
        echo sprintf("  Candidate %d: Attempt %d | Inst: %d | Name: %s\n",
            $row['id'],
            $row['exam_attempt_id'],
            $row['institution_id'],
            $row['full_name']
        );
    }
} else {
    echo "  FOUND CERTIFICATES:\n";
    foreach ($certificateRows as $row) {
        echo sprintf("  %s | %s | Score: %.0f | Candidate: %s\n",
            $row['certificate_id'],
            $row['assessment_title'] ?: 'N/A',
            $row['score'],
            $row['candidate_name']
        );
    }
}

?>
