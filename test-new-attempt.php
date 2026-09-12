<?php
require_once 'backend/config.php';

echo "=== Testing New Exam Attempt Flow ===\n";

// Simulate User 3 loading exam
$user_id = 3;
$pair = 'en-rw';
$level = 1;
$direction = 'en';

// Step 1: Check for existing pending attempts
echo "\n1. Check existing pending attempts for User $user_id:\n";
$stmt = $pdo->prepare("
    SELECT ea.id, e.language_pair, e.level_number, ea.status, ea.created_at
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id = e.id
    WHERE ea.user_id = ? AND e.language_pair = ? AND e.level_number = ?
    AND ea.status IN ('pending', 'verification', 'in_progress')
    ORDER BY ea.created_at DESC
");
$stmt->execute([$user_id, $pair, $level]);
$attempts = $stmt->fetchAll();
echo "   Found: " . count($attempts) . " pending/in-progress attempts\n";
foreach ($attempts as $a) {
    echo "   - Attempt {$a['id']}: {$a['status']} (created: {$a['created_at']})\n";
}

// Step 2: Check for submitted attempts
echo "\n2. Check submitted attempts for User $user_id:\n";
$stmt = $pdo->prepare("
    SELECT ea.id, e.language_pair, e.level_number, ea.status, ea.score, ea.certificate_id
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id = e.id
    WHERE ea.user_id = ? AND e.language_pair = ? AND e.level_number = ?
    AND ea.status = 'submitted'
    ORDER BY ea.created_at DESC LIMIT 3
");
$stmt->execute([$user_id, $pair, $level]);
$attempts = $stmt->fetchAll();
echo "   Found: " . count($attempts) . " submitted attempts\n";
foreach ($attempts as $a) {
    echo "   - Attempt {$a['id']}: Score {$a['score']}%, Cert: " . ($a['certificate_id'] ? $a['certificate_id'] : 'NULL') . "\n";
}

// Step 3: Create new attempt
echo "\n3. Creating NEW attempt for User $user_id:\n";
$stmt = $pdo->prepare("SELECT id FROM exams WHERE language_pair = ? AND level_number = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$pair, $level]);
$exam = $stmt->fetch();

if ($exam) {
    $stmt = $pdo->prepare("
        INSERT INTO exam_attempts (user_id, exam_id, status, verification_status, ip_address, user_agent)
        VALUES (?, ?, 'pending', 'pending', ?, ?)
    ");
    $stmt->execute([$user_id, $exam['id'], $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', $_SERVER['HTTP_USER_AGENT'] ?? 'CLI']);
    $new_attempt_id = $pdo->lastInsertId();
    echo "   ✓ Created new attempt_id: $new_attempt_id\n";
    
    // Step 4: Verify it
    echo "\n4. Verify new attempt:\n";
    $stmt = $pdo->prepare("SELECT id, exam_id, user_id, status FROM exam_attempts WHERE id = ?");
    $stmt->execute([$new_attempt_id]);
    $new_attempt = $stmt->fetch();
    if ($new_attempt) {
        echo "   ✓ Attempt found: status={$new_attempt['status']}, exam_id={$new_attempt['exam_id']}\n";
    }
} else {
    echo "   ✗ No exam found for $pair level $level\n";
}

echo "\n=== Test Complete ===\n";
?>
