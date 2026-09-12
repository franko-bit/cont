<?php
require_once 'backend/config.php';

echo "=== EXAM RESULTS TABLE SCHEMA ===\n";
$stmt = $pdo->query("DESCRIBE exam_results");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo "{$col['Field']} ({$col['Type']}) - Key: {$col['Key']}\n";
}

echo "\n=== Exam Results Data ===\n";
$stmt = $pdo->query("SELECT * FROM exam_results ORDER BY id DESC LIMIT 3");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($results) == 0) {
    echo "No exam results found!\n";
} else {
    foreach ($results as $r) {
        echo "Result " . $r['id'] . ":\n";
        foreach ($r as $k => $v) {
            echo "  $k = $v\n";
        }
        echo "\n";
    }
}

echo "=== Why exam_attempts has score 0? ===\n";
$stmt = $pdo->query("SELECT id, user_id, score, passed, status FROM exam_attempts WHERE score = 0.00 LIMIT 3");
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($attempts as $a) {
    echo "Attempt {$a['id']}: User {$a['user_id']}, Score: {$a['score']}, Passed: {$a['passed']}, Status: {$a['status']}\n";
}

echo "\n=== Good submissions (score > 0) ===\n";
$stmt = $pdo->query("SELECT id, user_id, score, passed, certificate_id FROM exam_attempts WHERE score > 0 ORDER BY submitted_at DESC LIMIT 5");
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($attempts as $a) {
    echo "Attempt {$a['id']}: User {$a['user_id']}, Score: {$a['score']}, Passed: {$a['passed']}, Cert: {$a['certificate_id']}\n";
}
?>
