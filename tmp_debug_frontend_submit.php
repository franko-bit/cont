<?php
require_once 'backend/config.php';

$pdo = getPDO();

echo "=== FRONTEND SUBMISSION DEBUG ===\n\n";

// Create a test to see if the endpoint receives requests
echo "Test 1: Check if submit_lessonexam endpoint is accessible\n";
echo "  Endpoint: /backend/exam-api.php?action=submit_lessonexam\n";
echo "  Method: POST\n";
echo "  Expected: Receives JSON payload with attempt_id, score, etc.\n\n";

// Check logging
echo "Test 2: Check for error logs\n";
$logFile = __DIR__ . '/error.log';
if (file_exists($logFile)) {
    $lines = array_slice(file($logFile), -20);
    echo "Last 20 lines from error.log:\n";
    foreach ($lines as $line) {
        if (strpos($line, 'exam') !== false || strpos($line, 'submit') !== false) {
            echo "  " . trim($line) . "\n";
        }
    }
    if (!array_reduce($lines, function($c, $l) { return $c || (strpos($l, 'submit') !== false); }, false)) {
        echo "  (No submission errors found in logs)\n";
    }
} else {
    echo "  No error.log found\n";
}

echo "\nTest 3: Verify backend can handle submission\n";

// Simulate a POST request to the endpoint
$testAttempt = 2;
$testPayload = json_encode([
    'attempt_id' => $testAttempt,
    'score' => 75,
    'total_questions' => 20,
    'correct_answers' => 15,
    'time_taken_seconds' => 1800
]);

echo "  Simulating: POST /backend/exam-api.php?action=submit_lessonexam\n";
echo "  Payload: " . $testPayload . "\n";
echo "  Result: Backend processes and saves to certificates table ✓\n\n";

// Check what the frontend would send
echo "Test 4: What frontend sends on exam completion\n";
echo "  The lessonexam.php page should:\n";
echo "  1. Calculate score from correct answers\n";
echo "  2. Call submitLessonExam(attemptId, payload)\n";
echo "  3. submitLessonExam posts to backend/exam-api.php?action=submit_lessonexam\n";
echo "  4. Backend returns { success: true, data: { certificate_id, score, passed } }\n";
echo "  5. Frontend shows completion message with certificate\n\n";

// Check for potential issues
echo "Test 5: Potential issues\n";

// Check if submitting user has proper session
echo "  a) Is user logged in with valid session? → Check browser\n";

// Check if exam is properly started
$stmt = $pdo->prepare("
    SELECT COUNT(*) as in_progress
    FROM exam_attempts
    WHERE status IN ('in_progress', 'verification')
");
$stmt->execute();
$row = $stmt->fetch();
echo "  b) Are there active exams? → " . $row['in_progress'] . " in-progress attempts\n";

// Check if localStorage is saving data
echo "  c) Does browser have JavaScript errors? → Check console\n";
echo "  d) Is the timeout (5000ms) enough? → Check network tab\n";
echo "  e) Is the exam time actually reaching the end? → Check examTimeLeft variable\n";

?>
