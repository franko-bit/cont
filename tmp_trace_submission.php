<?php
require_once 'backend/config.php';

$pdo = getPDO();

echo "=== Detailed Submission Flow Trace ===\n\n";

// Get a pending attempt
$stmt = $pdo->prepare("
    SELECT ea.*, e.title, e.id as exam_id, e.language_pair, e.level_number, e.passing_score
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id = e.id
    WHERE ea.status IN ('pending', 'in_progress', 'verification')
    LIMIT 1
");
$stmt->execute();
$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
    die("No pending attempt found\n");
}

echo "Attempt Details:\n";
echo "  ID: " . $attempt['id'] . "\n";
echo "  User ID: " . $attempt['user_id'] . "\n";
echo "  Exam ID: " . $attempt['exam_id'] . "\n";
echo "  Exam Language Pair: " . $attempt['language_pair'] . "\n";
echo "  Exam Level: " . $attempt['level_number'] . "\n";
echo "  Passing Score: " . $attempt['passing_score'] . "\n\n";

// Check if user has an institution_candidate record for this exam
echo "=== Finding matching institution_candidate ===\n";

$stmt = $pdo->prepare("
    SELECT ic.id, ic.assessment_id, ic.status, ic.score, ic.exam_attempt_id
    FROM institution_candidates ic
    WHERE ic.user_id = ?
    ORDER BY ic.created_at DESC
    LIMIT 5
");
$stmt->execute([$attempt['user_id']]);
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Institution candidates for user {$attempt['user_id']}:\n";
if (empty($candidates)) {
    echo "  (none found)\n";
} else {
    foreach ($candidates as $ic) {
        echo "  ID {$ic['id']}: assessment_id={$ic['assessment_id']}, status={$ic['status']}, " .
             "score={$ic['score']}, exam_attempt_id={$ic['exam_attempt_id']}\n";
    }
}

echo "\n=== Checking institution_assessments ===\n";

// Get the assessment IDs and check their language pair/level
$stmt = $pdo->prepare("
    SELECT ia.id, ia.language_pair, ia.exam_level
    FROM institution_assessments ia
    WHERE ia.language_pair = ? AND ia.exam_level = ?
    LIMIT 3
");
$stmt->execute([$attempt['language_pair'], $attempt['level_number']]);
$assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Institution assessments matching language_pair='{$attempt['language_pair']}' and level={$attempt['level_number']}:\n";
if (empty($assessments)) {
    echo "  (none found)\n";
} else {
    foreach ($assessments as $ia) {
        echo "  ID {$ia['id']}: language_pair={$ia['language_pair']}, exam_level={$ia['exam_level']}\n";
    }
}

echo "\n=== Testing linkInstitutionCandidateToAttempt query ===\n";

// Run the exact query that linkInstitutionCandidateToAttempt uses
$testQuery = "
    SELECT ic.id
    FROM institution_candidates ic
    JOIN institution_assessments ia ON ic.assessment_id = ia.id
    JOIN exams e ON e.language_pair = ia.language_pair AND e.level_number = ia.exam_level
    WHERE ic.user_id = ? AND (ic.exam_attempt_id IS NULL OR ic.exam_attempt_id = 0) AND e.id = ?
    ORDER BY ic.created_at DESC
    LIMIT 1
";

$stmt = $pdo->prepare($testQuery);
$stmt->execute([$attempt['user_id'], $attempt['exam_id']]);
$matchedCandidate = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Query result:\n";
if ($matchedCandidate) {
    echo "  Found candidate ID: " . $matchedCandidate['id'] . "\n";
    echo "  This candidate WOULD be updated when submitLessonExam runs\n";
} else {
    echo "  ⚠️ NO MATCH FOUND - institution_candidates will NOT be updated!\n";
    echo "  This explains why 'Completed work' isn't showing new submissions\n";
}

echo "\n=== Checking certificates table ===\n";

$stmt = $pdo->prepare("
    SELECT COUNT(*) as count FROM certificates
    WHERE exam_attempt_id = ?
");
$stmt->execute([$attempt['id']]);
$certCount = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Existing certificates for attempt {$attempt['id']}: " . $certCount['count'] . "\n";

?>
