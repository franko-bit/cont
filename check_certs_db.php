<?php
require_once 'backend/config.php';

echo "=== Recent Exam Attempts ===\n";
$stmt = $pdo->query('SELECT id, user_id, certificate_id, score, passed, submitted_at FROM exam_attempts WHERE score > 0 ORDER BY submitted_at DESC LIMIT 15');
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($attempts as $a) {
    $certId = $a['certificate_id'] ?: 'NULL';
    $passed = $a['passed'] ? 'YES' : 'NO';
    echo "Attempt {$a['id']}: User {$a['user_id']}, Score {$a['score']}%, Passed: {$passed}, Cert: {$certId}, Submitted: {$a['submitted_at']}\n";
}

echo "\n=== User 3's Recent Certificates ===\n";
$stmt = $pdo->prepare('SELECT * FROM certificates WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
$stmt->execute([3]);
$certs = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($certs) . " certificates for user 3\n";
foreach ($certs as $c) {
    echo "  - {$c['certificate_id']}: {$c['language_pair']}, Score {$c['score']}\n";
}

echo "\n=== Exam Results for User 3 ===\n";
$stmt = $pdo->prepare('SELECT * FROM exam_results WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$stmt->execute([3]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($results) . " exam results for user 3\n";
foreach ($results as $r) {
    echo "  - Attempt {$r['attempt_id']}: {$r['score']}%\n";
}
?>
