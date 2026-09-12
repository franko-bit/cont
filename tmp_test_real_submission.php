<?php
require_once 'backend/config.php';

$pdo = getPDO();

echo "=== Testing Real Exam Submission ===\n\n";

// Find a user who IS an institution_candidate
$stmt = $pdo->prepare("
    SELECT DISTINCT ic.user_id, ic.id as candidate_id, u.full_name, u.school_name, u.email, ia.id as assessment_id, e.id as exam_id
    FROM institution_candidates ic
    JOIN users u ON ic.user_id = u.id
    JOIN institution_assessments ia ON ic.assessment_id = ia.id
    JOIN exams e ON e.language_pair = ia.language_pair AND e.level_number = ia.exam_level
    WHERE ic.status != 'completed' AND ic.exam_attempt_id IS NULL
    LIMIT 1
");
$stmt->execute();
$testUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$testUser) {
    echo "Finding any institution_candidate...\n";
    $stmt = $pdo->prepare("SELECT * FROM institution_candidates WHERE exam_attempt_id IS NULL LIMIT 1");
    $stmt->execute();
    $testUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$testUser) {
        echo "ERROR: No suitable test candidate found\n";
        echo "\nAll institution candidates:\n";
        $stmt = $pdo->query("SELECT id, user_id, full_name, email, status, exam_attempt_id FROM institution_candidates LIMIT 5");
        foreach ($stmt->fetchAll() as $c) {
            echo json_encode($c) . "\n";
        }
        exit;
    }
}

echo "Test candidate found:\n";
echo "  ID: " . $testUser['candidate_id'] . "\n";
echo "  User: " . ($testUser['full_name'] ?: 'Unknown') . " (" . $testUser['user_id'] . ")\n";
echo "  Email: " . $testUser['email'] . "\n";
echo "  School: " . ($testUser['school_name'] ?: 'None') . "\n\n";

// Check if they have a pending exam attempt
$stmt = $pdo->prepare("
    SELECT ea.id, ea.status, e.title, e.id as exam_id
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id = e.id
    WHERE ea.user_id = ? AND ea.status IN ('pending', 'in_progress', 'verification')
    LIMIT 1
");
$stmt->execute([$testUser['user_id']]);
$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if ($attempt) {
    echo "Existing pending attempt found:\n";
    echo "  ID: " . $attempt['id'] . "\n";
    echo "  Status: " . $attempt['status'] . "\n";
    echo "  Exam: " . $attempt['title'] . "\n\n";
    
    $attemptId = $attempt['id'];
} else {
    echo "No pending attempt - would need to start exam first\n";
    echo "Recommendation: Start an exam from the frontend, then submit it\n";
    exit;
}

// Simulate what would happen if they submit with score 85
echo "=== Simulating Submission ===\n\n";

$testPayload = [
    'attempt_id' => $attemptId,
    'score' => 85,
    'total_questions' => 20,
    'correct_answers' => 17,
    'time_taken_seconds' => 1200
];

echo "Would send to backend:\n";
echo json_encode($testPayload, JSON_PRETTY_PRINT) . "\n\n";

// Check what the backend would do
$stmt = $pdo->prepare("
    SELECT ea.id, ea.user_id, ea.exam_id, e.passing_score, e.language_pair, e.level_number
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id = e.id
    WHERE ea.id = ?
");
$stmt->execute([$attemptId]);
$backendAttempt = $stmt->fetch(PDO::FETCH_ASSOC);

if ($backendAttempt) {
    $passed = $testPayload['score'] >= $backendAttempt['passing_score'];
    $certificate_id = $passed ? 'CERT-' . strtoupper(str_replace('-', '', $backendAttempt['language_pair'])) . '-' . date('Y') . '-' . str_pad($attemptId, 5, '0', STR_PAD_LEFT) : null;
    
    echo "Backend logic:\n";
    echo "  Score: " . $testPayload['score'] . "\n";
    echo "  Passing threshold: " . $backendAttempt['passing_score'] . "\n";
    echo "  Result: " . ($passed ? "PASSED" : "FAILED") . "\n";
    echo "  Certificate ID: " . ($certificate_id ?: "none") . "\n";
}

?>
