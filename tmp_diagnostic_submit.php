<?php
require_once 'backend/config.php';

$pdo = getPDO();

echo "=== Exam Submission Diagnostic ===\n\n";

// Check if there's a pending exam attempt
$stmt = $pdo->prepare("
    SELECT ea.id, ea.user_id, ea.exam_id, ea.status, e.title, e.passing_score
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id = e.id
    WHERE ea.status IN ('pending', 'in_progress', 'verification')
    LIMIT 1
");
$stmt->execute();
$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if ($attempt) {
    echo "Found pending attempt:\n";
    echo "  Attempt ID: " . $attempt['id'] . "\n";
    echo "  User ID: " . $attempt['user_id'] . "\n";
    echo "  Exam ID: " . $attempt['exam_id'] . "\n";
    echo "  Exam Title: " . $attempt['title'] . "\n";
    echo "  Status: " . $attempt['status'] . "\n";
    echo "  Passing Score: " . $attempt['passing_score'] . "\n\n";

    // Create simulated submission
    $testPayload = [
        'attempt_id' => $attempt['id'],
        'score' => 75,
        'total_questions' => 20,
        'correct_answers' => 15,
        'time_taken_seconds' => 1800
    ];

    echo "Simulated submission payload:\n";
    echo json_encode($testPayload, JSON_PRETTY_PRINT) . "\n\n";

    // Check institution candidates table
    echo "=== Checking institution_candidates table ===\n";
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM institution_candidates 
        WHERE user_id = ? AND exam_id = ?
    ");
    $stmt->execute([$attempt['user_id'], $attempt['exam_id']]);
    $candCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Existing candidates for this user/exam: " . $candCount['count'] . "\n\n";

    // Check certificates
    echo "=== Checking certificates table ===\n";
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM certificates 
        WHERE exam_attempt_id = ?
    ");
    $stmt->execute([$attempt['id']]);
    $certCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Existing certificates for this attempt: " . $certCount['count'] . "\n\n";

    // Try simulated submission through direct function call
    echo "=== Testing saveExamResultsRecord function ===\n";
    
    // Check function exists
    if (function_exists('saveExamResultsRecord')) {
        echo "Function saveExamResultsRecord exists\n";
    } else {
        echo "ERROR: Function saveExamResultsRecord does NOT exist\n";
    }

} else {
    echo "ERROR: No pending exam attempts found\n";
    echo "\nAvailable attempts by status:\n";
    
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count
        FROM exam_attempts
        GROUP BY status
    ");
    $stmt->execute();
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($statuses as $row) {
        echo "  " . $row['status'] . ": " . $row['count'] . "\n";
    }
}

echo "\n=== Check database connectivity ===\n";
try {
    $result = $pdo->query("SELECT 1")->fetch();
    echo "✓ Database connected and responding\n";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}
?>
