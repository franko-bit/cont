<?php
/**
 * DEBUG: Why isn't institution_candidates being populated?
 */
require_once __DIR__ . '/backend/config.php';

$pdo = getPDO();

echo "=".str_repeat("=", 100)."\n";
echo "| DEBUGGING: institution_candidates INSERT FAILURES\n";
echo "=".str_repeat("=", 100)."\n\n";

// 1. Check if popcorn_applications exist
echo "1. POPCORN APPLICATIONS\n";
echo "─".str_repeat("─", 100)."\n";
$stmt = $pdo->query('SELECT COUNT(*) as cnt FROM popcorn_applications');
$row = $stmt->fetch();
$popcornCount = $row['cnt'];
echo "Total popcorn applications: $popcornCount\n";

$stmt = $pdo->query('SELECT * FROM popcorn_applications ORDER BY applied_at DESC LIMIT 5');
$popcorns = $stmt->fetchAll();
echo "Recent popcorn applications:\n";
foreach ($popcorns as $p) {
    echo "  - Code: {$p['popcorn_code']}, Institution ID: {$p['institution_id']}, User ID: {$p['user_id']}, Attempt ID: {$p['attempt_id']}\n";
}
echo "\n";

// 2. Check institution_assessments for each institution
echo "2. INSTITUTION ASSESSMENTS (Required for INSERT)\n";
echo "─".str_repeat("─", 100)."\n";
$stmt = $pdo->query('SELECT DISTINCT institution_id FROM popcorn_applications WHERE institution_id IS NOT NULL');
$institutions = $stmt->fetchAll();

foreach ($institutions as $inst) {
    $instId = $inst['institution_id'];
    $stmt = $pdo->prepare('SELECT COUNT(*) as cnt FROM institution_assessments WHERE institution_id = ?');
    $stmt->execute([$instId]);
    $assessCount = $stmt->fetch()['cnt'];
    echo "  Institution ID $instId: $assessCount assessments\n";
    
    if ($assessCount > 0) {
        $stmt = $pdo->prepare('SELECT id, title FROM institution_assessments WHERE institution_id = ? LIMIT 3');
        $stmt->execute([$instId]);
        $assess = $stmt->fetchAll();
        foreach ($assess as $a) {
            echo "    - Assessment ID: {$a['id']}, Title: {$a['title']}\n";
        }
    }
}
echo "\n";

// 3. Check which popcorn applications have missing required fields
echo "3. POPCORN APPLICATIONS - MISSING REQUIRED FIELDS FOR INSERT\n";
echo "─".str_repeat("─", 100)."\n";
$stmt = $pdo->query('
    SELECT 
        id,
        popcorn_code,
        institution_id,
        user_id,
        attempt_id,
        CASE 
            WHEN institution_id IS NULL OR institution_id = 0 THEN "NO institution_id"
            WHEN user_id IS NULL OR user_id = 0 THEN "NO user_id"
            WHEN attempt_id IS NULL OR attempt_id = 0 THEN "NO attempt_id"
            ELSE "OK"
        END as status
    FROM popcorn_applications
    ORDER BY applied_at DESC
');
$missing = $stmt->fetchAll();

$okCount = 0;
$missingCount = 0;

foreach ($missing as $row) {
    if ($row['status'] !== 'OK') {
        echo "  ✗ Code: {$row['popcorn_code']} → {$row['status']}\n";
        $missingCount++;
    } else {
        $okCount++;
    }
}

echo "Summary: $okCount should work, $missingCount are missing required fields\n\n";

// 4. Compare: Do institution_candidates exist for these popcorn applications?
echo "4. INSTITUTION_CANDIDATES - DO THEY MATCH?\n";
echo "─".str_repeat("─", 100)."\n";
$stmt = $pdo->query('
    SELECT 
        pa.id as popcorn_id,
        pa.popcorn_code,
        pa.institution_id,
        pa.user_id,
        pa.attempt_id,
        ic.id as candidate_id,
        CASE 
            WHEN ic.id IS NULL THEN "NOT FOUND"
            ELSE "FOUND"
        END as in_candidates
    FROM popcorn_applications pa
    LEFT JOIN institution_candidates ic ON ic.exam_attempt_id = pa.attempt_id
    WHERE pa.institution_id IS NOT NULL AND pa.user_id IS NOT NULL AND pa.attempt_id IS NOT NULL
    ORDER BY pa.applied_at DESC
');
$matches = $stmt->fetchAll();

$foundCount = 0;
$notFoundCount = 0;

foreach ($matches as $row) {
    if ($row['in_candidates'] === 'NOT FOUND') {
        echo "  ✗ Popcorn {$row['popcorn_code']}: NOT in institution_candidates\n";
        echo "      Expected to find: exam_attempt_id = {$row['attempt_id']}, institution_id = {$row['institution_id']}\n";
        $notFoundCount++;
    } else {
        echo "  ✓ Popcorn {$row['popcorn_code']}: Found in institution_candidates (ID: {$row['candidate_id']})\n";
        $foundCount++;
    }
}

echo "\nSummary: $foundCount found in institution_candidates, $notFoundCount NOT found\n\n";

// 5. Direct institution_candidates count
echo "5. INSTITUTION_CANDIDATES TABLE - CURRENT STATE\n";
echo "─".str_repeat("─", 100)."\n";
$stmt = $pdo->query('SELECT COUNT(*) as cnt FROM institution_candidates');
$totalCands = $stmt->fetch()['cnt'];
echo "Total institution_candidates: $totalCands\n";

$stmt = $pdo->query('
    SELECT institution_id, COUNT(*) as cnt FROM institution_candidates GROUP BY institution_id
');
$byInst = $stmt->fetchAll();
foreach ($byInst as $row) {
    echo "  - Institution ID {$row['institution_id']}: {$row['cnt']} candidates\n";
}
echo "\n";

// 6. Check assessment_id issue
echo "6. CHECKING IF ASSESSMENTS ARE THE BLOCKER\n";
echo "─".str_repeat("─", 100)."\n";
$stmt = $pdo->query('
    SELECT DISTINCT institution_id FROM popcorn_applications 
    WHERE institution_id IS NOT NULL AND user_id IS NOT NULL AND attempt_id IS NOT NULL
');
$instIds = $stmt->fetchAll();

foreach ($instIds as $row) {
    $instId = $row['institution_id'];
    $stmt = $pdo->prepare('SELECT COUNT(*) as cnt FROM institution_assessments WHERE institution_id = ?');
    $stmt->execute([$instId]);
    $assessCount = $stmt->fetch()['cnt'];
    
    echo "Institution $instId: ";
    if ($assessCount > 0) {
        echo "✓ HAS $assessCount assessments\n";
    } else {
        echo "✗ NO assessments (fallback insert should work)\n";
    }
}
echo "\n";

// 7. Check for SQL errors in error logs
echo "7. DATABASE STRUCTURE CHECK\n";
echo "─".str_repeat("─", 100)."\n";
$stmt = $pdo->query('DESCRIBE institution_candidates');
$cols = $stmt->fetchAll();
echo "institution_candidates columns:\n";
foreach ($cols as $col) {
    echo "  - {$col['Field']} ({$col['Type']}) - Null: {$col['Null']}, Key: {$col['Key']}\n";
}

echo "\n";
echo "=".str_repeat("=", 100)."\n";
echo "| END DEBUG REPORT\n";
echo "=".str_repeat("=", 100)."\n";

// Try a manual test insert
echo "\n";
echo "8. MANUAL TEST INSERT\n";
echo "─".str_repeat("─", 100)."\n";

$testInstId = 1;
$testUserId = 1;
$testAttemptId = 999;

echo "Attempting manual INSERT into institution_candidates:\n";
echo "  institution_id: $testInstId\n";
echo "  user_id: $testUserId\n";
echo "  exam_attempt_id: $testAttemptId\n";
echo "  full_name: 'Test User'\n";

try {
    $stmt = $pdo->prepare('INSERT INTO institution_candidates (institution_id, user_id, full_name, email, exam_attempt_id, status, invited_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())');
    $stmt->execute([$testInstId, $testUserId, 'Test User', 'test@example.com', $testAttemptId, 'started']);
    echo "✓ Manual INSERT succeeded\n";
    
    // Now delete it
    $stmt = $pdo->prepare('DELETE FROM institution_candidates WHERE exam_attempt_id = ? AND full_name = ?');
    $stmt->execute([$testAttemptId, 'Test User']);
    echo "✓ Cleanup completed\n";
} catch (Exception $e) {
    echo "✗ Manual INSERT failed: " . $e->getMessage() . "\n";
}
?>
