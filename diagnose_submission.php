<?php
require_once 'backend/config.php';

echo "=== EXAM DATA FLOW DIAGNOSTIC ===\n\n";

// Check exam_attempts table
echo "1. Recent Exam Attempts:\n";
$stmt = $pdo->query("SELECT id, user_id, exam_id, status, score, certificate_id, submitted_at FROM exam_attempts ORDER BY id DESC LIMIT 5");
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($attempts as $a) {
    $cert = $a['certificate_id'] ?: 'NULL';
    echo "  Attempt {$a['id']}: User {$a['user_id']}, Status: {$a['status']}, Score: {$a['score']}, Cert: {$cert}\n";
}

// Check certificates table
echo "\n2. Certificates Table:\n";
$stmt = $pdo->query("SELECT id, user_id, certificate_id, language_pair, score FROM certificates ORDER BY id DESC LIMIT 5");
$certs = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($certs as $c) {
    echo "  Cert {$c['id']}: User {$c['user_id']}, ID: {$c['certificate_id']}, Score: {$c['score']}\n";
}

// Check exam_results table
echo "\n3. Exam Results Table:\n";
$stmt = $pdo->query("SELECT id, user_id, attempt_id, score FROM exam_results ORDER BY id DESC LIMIT 5");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($results) . " rows\n";
foreach ($results as $r) {
    echo "  Result {$r['id']}: User {$r['user_id']}, Attempt {$r['attempt_id']}, Score: {$r['score']}\n";
}

// Check what's in the PHP error log
echo "\n4. Recent Backend Errors:\n";
$log_file = "c:/xampp/php/logs/php_error_log";
if (file_exists($log_file)) {
    $lines = file($log_file);
    $recent = array_slice($lines, -10);
    foreach ($recent as $line) {
        if (strpos($line, '[EXAM-SUBMIT]') !== false || strpos($line, 'Fatal') !== false) {
            echo "  " . trim($line) . "\n";
        }
    }
} else {
    echo "  Log file not found\n";
}

// Check if backend/exam-api.php has the submit function
echo "\n5. Backend Submission Function Check:\n";
$exam_api = file_get_contents('backend/exam-api.php');
if (strpos($exam_api, 'function submitLessonExam') !== false) {
    echo "  ✓ submitLessonExam() function exists\n";
} else {
    echo "  ✗ submitLessonExam() function MISSING!\n";
}

if (strpos($exam_api, "case 'submit_lessonexam'") !== false) {
    echo "  ✓ submit_lessonexam case exists in switch\n";
} else {
    echo "  ✗ submit_lessonexam case MISSING in switch!\n";
}

// Check if lessonexam.php calls the backend
echo "\n6. Frontend Submission Function Check:\n";
$lessonexam = file_get_contents('frontend/lessonexam.php');
if (strpos($lessonexam, 'function submitExamToBackend') !== false) {
    echo "  ✓ submitExamToBackend() function exists\n";
} else {
    echo "  ✗ submitExamToBackend() function MISSING!\n";
}

if (strpos($lessonexam, 'exam-api.php?action=submit_lessonexam') !== false) {
    echo "  ✓ Correct backend URL in submitExamToBackend()\n";
} else {
    echo "  ✗ Backend URL INCORRECT or MISSING!\n";
}

if (strpos($lessonexam, 'submitExamToBackend(score,') !== false) {
    echo "  ✓ submitExamToBackend() called from showCompletion()\n";
} else {
    echo "  ✗ submitExamToBackend() NOT called from showCompletion()!\n";
}
?>
