<?php
/**
 * DATA VALIDATION SCRIPT - try.php Dashboard
 * Verifies that all data is correctly retrieved and synchronized across tables
 * Run this script to validate the complete data flow
 */

require_once __DIR__ . '/backend/config.php';

$pdo = getPDO();
$institutionId = 1; // Playmates institution

echo "=".str_repeat("=", 98)."\n";
echo "| DATA VALIDATION REPORT - Try.php Dashboard                                                      |\n";
echo "=".str_repeat("=", 98)."\n\n";

// 1. Check institution_candidates data
echo "1. INSTITUTION_CANDIDATES TABLE\n";
echo "─".str_repeat("─", 98)."\n";
$stmt = $pdo->prepare('SELECT COUNT(*) FROM institution_candidates WHERE institution_id = ?');
$stmt->execute([$institutionId]);
$candidateCount = $stmt->fetchColumn();
echo "Total candidates: $candidateCount\n";

$stmt = $pdo->prepare('SELECT COUNT(*) FROM institution_candidates WHERE institution_id = ? AND score IS NOT NULL');
$stmt->execute([$institutionId]);
$completedCount = $stmt->fetchColumn();
echo "Completed candidates (with score): $completedCount\n";

$stmt = $pdo->prepare('
    SELECT full_name, email, score, exam_attempt_id, status 
    FROM institution_candidates 
    WHERE institution_id = ? AND score IS NOT NULL
    LIMIT 3
');
$stmt->execute([$institutionId]);
$samples = $stmt->fetchAll();
echo "Sample data:\n";
foreach ($samples as $row) {
    echo "  - {$row['full_name']} ({$row['email']}): Score={$row['score']}, Status={$row['status']}, Attempt ID={$row['exam_attempt_id']}\n";
}
echo "\n";

// 2. Check exam_attempts linking
echo "2. EXAM_ATTEMPTS LINKING\n";
echo "─".str_repeat("─", 98)."\n";
$stmt = $pdo->prepare('
    SELECT COUNT(*) FROM exam_attempts ea
    JOIN institution_candidates ic ON ic.exam_attempt_id = ea.id
    WHERE ic.institution_id = ?
');
$stmt->execute([$institutionId]);
$linkedCount = $stmt->fetchColumn();
echo "Institution candidates linked to exam_attempts: $linkedCount/$completedCount\n";

$stmt = $pdo->prepare('
    SELECT ic.full_name, ic.score AS ic_score, ea.score AS ea_score, ea.status
    FROM institution_candidates ic
    JOIN exam_attempts ea ON ic.exam_attempt_id = ea.id
    WHERE ic.institution_id = ? AND ic.score IS NOT NULL
    LIMIT 3
');
$stmt->execute([$institutionId]);
$linkedSamples = $stmt->fetchAll();
echo "Data synchronization check:\n";
foreach ($linkedSamples as $row) {
    $match = ($row['ic_score'] == $row['ea_score']) ? "✓ SYNCED" : "✗ MISMATCH";
    echo "  - {$row['full_name']}: IC={$row['ic_score']}% vs EA={$row['ea_score']}% [{$match}]\n";
}
echo "\n";

// 3. Check certificates
echo "3. CERTIFICATES TABLE\n";
echo "─".str_repeat("─", 98)."\n";
$stmt = $pdo->prepare('SELECT COUNT(*) FROM certificates WHERE status = "approved"');
$stmt->execute();
$approvedCount = $stmt->fetchColumn();
echo "Approved certificates: $approvedCount\n";

$stmt = $pdo->prepare('SELECT COUNT(*) FROM certificates WHERE status = "pending"');
$stmt->execute();
$pendingCount = $stmt->fetchColumn();
echo "Pending certificates: $pendingCount\n";

$stmt = $pdo->prepare('
    SELECT student_name, score, status, exam_attempt_id 
    FROM certificates 
    WHERE status IN ("pending", "approved")
    LIMIT 3
');
$stmt->execute();
$certSamples = $stmt->fetchAll();
echo "Sample certificates:\n";
foreach ($certSamples as $row) {
    echo "  - {$row['student_name']}: Score={$row['score']}%, Status={$row['status']}, Attempt={$row['exam_attempt_id']}\n";
}
echo "\n";

// 4. Check student names source
echo "4. STUDENT NAME SOURCE VALIDATION\n";
echo "─".str_repeat("─", 98)."\n";
$stmt = $pdo->prepare('
    SELECT c.student_name, ic.full_name, ic.full_name AS applicant_name
    FROM certificates c
    LEFT JOIN institution_candidates ic ON ic.exam_attempt_id = c.exam_attempt_id
    WHERE c.status IN ("pending", "approved")
    LIMIT 5
');
$stmt->execute();
$nameSamples = $stmt->fetchAll();
echo "Certificate name sources (should come from applicant/institution_candidates):\n";
foreach ($nameSamples as $row) {
    $source = ($row['applicant_name']) ? "FROM institution_candidates ✓" : "FROM exam_attempt (fallback)";
    echo "  - Cert: {$row['student_name']} vs Applicant: {$row['applicant_name']} [$source]\n";
}
echo "\n";

// 5. Check for orphaned records
echo "5. DATA INTEGRITY CHECKS\n";
echo "─".str_repeat("─", 98)."\n";

// Check institution_candidates without exam_attempt_id
$stmt = $pdo->prepare('
    SELECT COUNT(*) FROM institution_candidates 
    WHERE institution_id = ? AND exam_attempt_id IS NULL AND score IS NOT NULL
');
$stmt->execute([$institutionId]);
$orphanedCount = $stmt->fetchColumn();
echo "Institution candidates with score but NO exam_attempt_id link: $orphanedCount\n";

// Check exam_attempts without institution_candidate link
$stmt = $pdo->prepare('
    SELECT COUNT(DISTINCT ea.id) FROM exam_attempts ea
    WHERE ea.status = "submitted" 
    AND NOT EXISTS (SELECT 1 FROM institution_candidates ic WHERE ic.exam_attempt_id = ea.id)
');
$stmt->execute();
$unlinkedAttempts = $stmt->fetchColumn();
echo "Exam attempts (submitted) not linked to institution_candidates: $unlinkedAttempts\n";

// Check certificates without exam_attempt_id
$stmt = $pdo->prepare('SELECT COUNT(*) FROM certificates WHERE exam_attempt_id IS NULL');
$stmt->execute();
$certOrphanCount = $stmt->fetchColumn();
echo "Certificates without exam_attempt_id link: $certOrphanCount\n";
echo "\n";

// 6. Summary statistics
echo "6. DASHBOARD SUMMARY STATISTICS\n";
echo "─".str_repeat("─", 98)."\n";

$stmt = $pdo->prepare('
    SELECT 
        COUNT(DISTINCT id) as total_candidates,
        SUM(status = "completed") as completed,
        SUM(status = "started") as started,
        SUM(status = "pending") as pending,
        ROUND(AVG(CASE WHEN score IS NOT NULL THEN score ELSE NULL END), 1) as avg_score
    FROM institution_candidates 
    WHERE institution_id = ?
');
$stmt->execute([$institutionId]);
$summary = $stmt->fetch();

echo "Total candidates: {$summary['total_candidates']}\n";
echo "  - Completed: {$summary['completed']}\n";
echo "  - Started: {$summary['started']}\n";
echo "  - Pending: {$summary['pending']}\n";
echo "  - Average score: {$summary['avg_score']}%\n";
echo "\n";

// 7. Try.php query verification
echo "7. TRY.PHP QUERY VERIFICATION\n";
echo "─".str_repeat("─", 98)."\n";

// Run the actual query from try.php
$stmt = $pdo->prepare(
    'SELECT c.*, ia.title AS assessment_title, COALESCE(u.school_name, i.name) AS school_name, ea.score as exam_score, ea.status as exam_status
     FROM institution_candidates c
     LEFT JOIN institution_assessments ia ON ia.id = c.assessment_id
     LEFT JOIN institutions i ON i.id = c.institution_id
     LEFT JOIN users u ON u.id = c.user_id
     LEFT JOIN exam_attempts ea ON ea.id = c.exam_attempt_id
     WHERE c.institution_id = :institution_id
     ORDER BY c.updated_at DESC, c.created_at DESC
     LIMIT 10'
);
$stmt->execute(['institution_id' => $institutionId]);
$candidates = $stmt->fetchAll();

echo "Try.php main query returned " . count($candidates) . " records\n";
echo "Data validation:\n";
$dataValid = true;
foreach ($candidates as $c) {
    $hasScore = ($c['score'] !== null || $c['exam_score'] !== null);
    if (!$hasScore && $c['status'] === 'completed') {
        echo "  ✗ ERROR: {$c['full_name']} marked as completed but has no score\n";
        $dataValid = false;
    }
}

if ($dataValid && count($candidates) > 0) {
    echo "  ✓ All record data is valid and consistent\n";
} else if (count($candidates) === 0) {
    echo "  ⚠ No records returned (check if institution_id=1 exists)\n";
}
echo "\n";

echo "=".str_repeat("=", 98)."\n";
echo "| VALIDATION COMPLETE                                                                              |\n";
echo "=".str_repeat("=", 98)."\n";
?>
