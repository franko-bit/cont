<?php
require_once 'backend/config.php';

$pdo = getPDO();

echo "=== COMPREHENSIVE EXAM SUBMISSION FLOW TEST ===\n\n";

// Step 1: Get a pending exam attempt
echo "STEP 1: Finding pending exam attempt...\n";
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
    echo "ERROR: No pending attempt found\n";
    exit(1);
}

echo "✓ Found attempt ID: {$attempt['id']}, User: {$attempt['user_id']}, Exam: {$attempt['title']}\n\n";

// Step 2: Prepare submission payload
echo "STEP 2: Preparing submission payload...\n";
$score = 82;
$passed = $score >= $attempt['passing_score'];
$correct_answers = 16;
$total_questions = 20;
$time_taken = 1200;

$payload = [
    'attempt_id' => $attempt['id'],
    'score' => $score,
    'total_questions' => $total_questions,
    'correct_answers' => $correct_answers,
    'time_taken_seconds' => $time_taken
];

echo "✓ Payload: " . json_encode($payload) . "\n\n";

// Step 3: Update exam_attempts status and score
echo "STEP 3: Updating exam_attempts table...\n";
$certificate_id = $passed ? 'CERT-' . strtoupper(str_replace('-', '', $attempt['language_pair'])) . '-' . date('Y') . '-' . str_pad($attempt['id'], 5, '0', STR_PAD_LEFT) : null;

$updateStmt = $pdo->prepare("UPDATE exam_attempts SET status = 'submitted', submitted_at = NOW(), graded_at = NOW(), score = ?, passed = ?, certificate_id = ? WHERE id = ?");
$result = $updateStmt->execute([$score, $passed ? 1 : 0, $certificate_id, $attempt['id']]);

if ($result && $updateStmt->rowCount() > 0) {
    echo "✓ Updated exam_attempts. Rows affected: {$updateStmt->rowCount()}\n";
    echo "  Status: submitted\n";
    echo "  Score: $score\n";
    echo "  Passed: " . ($passed ? 'YES' : 'NO') . "\n";
    echo "  Certificate ID: " . ($certificate_id ?: 'none') . "\n\n";
} else {
    echo "✗ FAILED to update exam_attempts\n\n";
    exit(1);
}

// Step 4: Check saveExamResultsRecord requirements
echo "STEP 4: Checking saveExamResultsRecord (certificate insertion)...\n";

// First check if certificates table has the required columns
$checkCols = $pdo->query("SHOW COLUMNS FROM certificates LIKE 'status'");
if (!$checkCols->fetch()) {
    echo "✗ MISSING 'status' column in certificates table\n";
    exit(1);
}
echo "✓ 'status' column exists\n";

// Step 5: Manually execute the INSERT from saveExamResultsRecord
echo "\nSTEP 5: Executing certificate INSERT...\n";

$studentName = null;
try {
    $nameStmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
    $nameStmt->execute([$attempt['user_id']]);
    $nameRow = $nameStmt->fetch();
    $studentName = $nameRow ? $nameRow['full_name'] : null;
} catch (Exception $e) {
    $studentName = null;
}

echo "  Student Name: " . ($studentName ?: 'NULL') . "\n";

$insertStmt = $pdo->prepare("
    INSERT INTO certificates (
        certificate_id, student_name, user_id, exam_attempt_id, exam_id, session_id,
        language_pair, level_number, score, passing_score, overall_score,
        total_questions, correct_answers, percentage, passed, passed_score,
        strengths, weaknesses, recommendations, graded_at, status,
        issued_at, created_at, updated_at, expires_at, certificate_url, institution_id, assessment_id
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL, ?, ?, NOW(), NOW(), NOW(), NULL, NULL, NULL, NULL
    )
    ON DUPLICATE KEY UPDATE
        student_name = VALUES(student_name),
        user_id = VALUES(user_id),
        exam_id = VALUES(exam_id),
        session_id = VALUES(session_id),
        language_pair = VALUES(language_pair),
        level_number = VALUES(level_number),
        score = VALUES(score),
        passing_score = VALUES(passing_score),
        overall_score = VALUES(overall_score),
        total_questions = VALUES(total_questions),
        correct_answers = VALUES(correct_answers),
        percentage = VALUES(percentage),
        passed = VALUES(passed),
        passed_score = VALUES(passed_score),
        graded_at = VALUES(graded_at),
        status = VALUES(status),
        certificate_id = VALUES(certificate_id),
        issued_at = COALESCE(issued_at, VALUES(issued_at)),
        updated_at = NOW()
");

$params = [
    $certificate_id,
    $studentName,
    $attempt['user_id'],
    $attempt['id'],
    $attempt['exam_id'],
    $attempt['id'],  // session_id
    $attempt['language_pair'],
    $attempt['level_number'] ?? 0,
    (float)$score,
    $attempt['passing_score'] ?? 0,
    (float)$score,  // overall_score
    (int)$total_questions,
    (int)$correct_answers,
    (float)$score,  // percentage
    $passed ? 1 : 0,
    $attempt['passing_score'] ?? 0,
    date('Y-m-d H:i:s'),  // graded_at
    $passed ? 'pending' : 'failed'  // status
];

echo "  Parameters: (" . implode(", ", array_map(function($v) { return is_null($v) ? 'NULL' : (is_numeric($v) ? $v : "'$v'"); }, $params)) . ")\n\n";

try {
    $result = $insertStmt->execute($params);
    if ($result) {
        echo "✓ INSERT successful\n";
        echo "  Rows affected: {$insertStmt->rowCount()}\n\n";
        
        // Verify certificate was inserted
        $verifyStmt = $pdo->prepare("SELECT * FROM certificates WHERE exam_attempt_id = ? LIMIT 1");
        $verifyStmt->execute([$attempt['id']]);
        $cert = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cert) {
            echo "✓ VERIFIED: Certificate inserted\n";
            echo "  Certificate ID: {$cert['certificate_id']}\n";
            echo "  Student: {$cert['student_name']}\n";
            echo "  Score: {$cert['score']}\n";
            echo "  Status: {$cert['status']}\n";
            echo "  Created: {$cert['created_at']}\n";
        } else {
            echo "✗ Verification FAILED: Certificate not found after insert\n";
        }
    } else {
        echo "✗ INSERT failed\n";
        echo "  Error: " . print_r($insertStmt->errorInfo(), true) . "\n";
    }
} catch (PDOException $e) {
    echo "✗ INSERT threw exception\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "  Code: " . $e->getCode() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
