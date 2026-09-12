<?php
require_once 'backend/config.php';

echo "=== Checking Attempts 40-45 ===\n";
$stmt = $pdo->prepare("SELECT id, user_id, status, score, certificate_id, submitted_at FROM exam_attempts WHERE id IN (45, 44, 43, 42, 41, 40) ORDER BY id DESC");
$stmt->execute([]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $r) {
    echo "Attempt {$r['id']}: ";
    echo "User {$r['user_id']}, ";
    echo "Status: {$r['status']}, ";
    echo "Score: {$r['score']}, ";
    echo "Cert: " . ($r['certificate_id'] ?? 'NULL');
    if ($r['submitted_at']) {
        echo " (Submitted: {$r['submitted_at']})";
    }
    echo "\n";
}

echo "\n=== Recent Successful Submissions (score > 0, status='submitted') ===\n";
$stmt = $pdo->prepare("
    SELECT id, user_id, status, score, certificate_id, submitted_at
    FROM exam_attempts
    WHERE status = 'submitted' AND score > 0
    ORDER BY submitted_at DESC
    LIMIT 5
");
$stmt->execute([]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($results) == 0) {
    echo "No recent successful submissions!\n";
} else {
    foreach ($results as $r) {
        echo "Attempt {$r['id']}: User {$r['user_id']}, Score {$r['score']}%, Cert: " . ($r['certificate_id'] ?? 'NULL') . "\n";
    }
}

echo "\n=== Pending/In-Progress Attempts (all users) ===\n";
$stmt = $pdo->query("
    SELECT id, user_id, status, COUNT(*) as count
    FROM exam_attempts
    WHERE status IN ('pending', 'in_progress')
    GROUP BY user_id, status
    ORDER BY user_id, status
");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $r) {
    echo "User {$r['user_id']}: {$r['count']} {$r['status']} attempts\n";
}
?>
