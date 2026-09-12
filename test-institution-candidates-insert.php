<?php
require 'backend/config.php';
$pdo = getPDO();

echo "Testing institution_candidates INSERT with REAL exam_attempt_ids...\n\n";

// Get real popcorn applications with valid data
$stmt = $pdo->query('
    SELECT pa.*, ea.id as exam_attempt_exists
    FROM popcorn_applications pa
    LEFT JOIN exam_attempts ea ON ea.id = pa.attempt_id
    WHERE pa.attempt_id > 0
    LIMIT 5
');
$applications = $stmt->fetchAll();

echo "Checking which popcorn applications can now be inserted:\n\n";

foreach ($applications as $app) {
    $institutionId = $app['institution_id'];
    $userId = $app['user_id'];
    $attemptId = $app['attempt_id'];
    $applicantName = $app['applicant_name'];
    $popcornCode = $app['popcorn_code'];
    
    echo "─────────────────────────────────────────\n";
    echo "Popcorn Code: $popcornCode\n";
    echo "Institution ID: $institutionId\n";
    echo "User ID: $userId\n";
    echo "Attempt ID: $attemptId\n";
    
    // Check if exam_attempt exists
    if (!$app['exam_attempt_exists']) {
        echo "✗ exam_attempt_id $attemptId does NOT exist in exam_attempts\n";
        continue;
    }
    
    echo "✓ exam_attempt_id exists\n";
    
    // Now check if already in institution_candidates
    $stmt = $pdo->prepare('SELECT id FROM institution_candidates WHERE exam_attempt_id = ? AND user_id = ?');
    $stmt->execute([$attemptId, $userId]);
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "✓ Already in institution_candidates (ID: {$exists['id']})\n";
        continue;
    }
    
    echo "✗ NOT in institution_candidates - attempting INSERT...\n";
    
    // Try the INSERT
    try {
        // First, get assessment_id if exists
        $assessmentStmt = $pdo->prepare('SELECT id FROM institution_assessments WHERE institution_id = ? LIMIT 1');
        $assessmentStmt->execute([$institutionId]);
        $assessment = $assessmentStmt->fetch();
        $assessmentId = $assessment ? (int)$assessment['id'] : null;
        
        if ($assessmentId) {
            $candStmt = $pdo->prepare('INSERT INTO institution_candidates (institution_id, assessment_id, user_id, full_name, email, exam_attempt_id, status, invited_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW()) ON DUPLICATE KEY UPDATE updated_at = NOW()');
            $candStmt->execute([
                $institutionId,
                $assessmentId,
                $userId,
                $applicantName,
                'test@example.com',
                $attemptId,
                'started',
            ]);
        } else {
            $candStmt = $pdo->prepare('INSERT INTO institution_candidates (institution_id, user_id, full_name, email, exam_attempt_id, status, invited_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())');
            $candStmt->execute([
                $institutionId,
                $userId,
                $applicantName,
                'test@example.com',
                $attemptId,
                'started',
            ]);
        }
        echo "✓ INSERT succeeded!\n";
        
    } catch (Exception $e) {
        echo "✗ INSERT failed: " . $e->getMessage() . "\n";
    }
}

echo "\n─────────────────────────────────────────\n";
echo "Final Count:\n";
$stmt = $pdo->query('SELECT COUNT(*) as cnt FROM institution_candidates');
$total = $stmt->fetch()['cnt'];
echo "Total institution_candidates now: $total\n";

// Count by institution
$stmt = $pdo->query('SELECT institution_id, COUNT(*) as cnt FROM institution_candidates GROUP BY institution_id');
$byInst = $stmt->fetchAll();
foreach ($byInst as $row) {
    echo "  - Institution {$row['institution_id']}: {$row['cnt']}\n";
}
?>
