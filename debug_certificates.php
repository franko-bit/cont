<?php
/**
 * Debug: Check certificates and institution_candidates relationship
 */
require_once './backend/config.php';

echo "=== DEBUGGING CERTIFICATES & INSTITUTION_CANDIDATES ===\n\n";

// Check certificates
echo "CERTIFICATES TABLE\n";
echo str_repeat("─", 60) . "\n";
$certs = $pdo->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
echo "Total certificates: $certs\n\n";

if ($certs > 0) {
    echo "Sample certificates:\n";
    $samples = $pdo->query("
        SELECT id, certificate_id, exam_attempt_id, user_id, score, created_at
        FROM certificates
        ORDER BY created_at DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($samples as $row) {
        echo sprintf("  ID:%d | Cert:%s | Attempt:%d | User:%d | Score:%.0f\n",
            $row['id'],
            $row['certificate_id'],
            $row['exam_attempt_id'],
            $row['user_id'],
            $row['score']
        );
    }
}

// Check institution_candidates
echo "\n\nINSTITUTION_CANDIDATES TABLE\n";
echo str_repeat("─", 60) . "\n";
$candidates = $pdo->query("SELECT COUNT(*) FROM institution_candidates")->fetchColumn();
echo "Total institution_candidates: $candidates\n\n";

if ($candidates > 0) {
    echo "Sample candidates:\n";
    $samples = $pdo->query("
        SELECT id, exam_attempt_id, full_name, email, status, score, assessment_id, institution_id
        FROM institution_candidates
        ORDER BY created_at DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($samples as $row) {
        echo sprintf("  ID:%d | Attempt:%s | Name:%s | Status:%s | Inst:%s\n",
            $row['id'],
            $row['exam_attempt_id'] ?: 'NULL',
            $row['full_name'],
            $row['status'],
            $row['institution_id'] ?: 'NULL'
        );
    }
}

// Check the JOIN relationship
echo "\n\nJOIN RELATIONSHIP TEST\n";
echo str_repeat("─", 60) . "\n";

$joined = $pdo->query("
    SELECT c.certificate_id, ic.id AS candidate_id, ic.full_name, c.exam_attempt_id, ic.exam_attempt_id AS ic_attempt_id
    FROM certificates c
    INNER JOIN institution_candidates ic ON ic.exam_attempt_id = c.exam_attempt_id
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($joined)) {
    echo "❌ NO RESULTS from JOIN\n\n";
    
    // Check if there are any exam_attempt_id matches at all
    echo "Checking for exam_attempt_id matches:\n";
    $certAttempts = $pdo->query("SELECT DISTINCT exam_attempt_id FROM certificates WHERE exam_attempt_id IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
    echo "  Attempt IDs in certificates: " . implode(", ", array_slice($certAttempts, 0, 5)) . "\n";
    
    $candAttempts = $pdo->query("SELECT DISTINCT exam_attempt_id FROM institution_candidates WHERE exam_attempt_id IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
    echo "  Attempt IDs in institution_candidates: " . implode(", ", array_slice($candAttempts, 0, 5)) . "\n";
    
    $intersection = array_intersect($certAttempts, $candAttempts);
    echo "  Matching IDs: " . (empty($intersection) ? "NONE" : implode(", ", $intersection)) . "\n";
} else {
    echo "  JOIN WORKS - Found " . count($joined) . " matches\n";
    foreach ($joined as $row) {
        echo sprintf("  Cert: %s | Candidate: %d (%s) | Attempt: %d\n",
            $row['certificate_id'],
            $row['candidate_id'],
            $row['full_name'],
            $row['exam_attempt_id']
        );
    }
}

?>
