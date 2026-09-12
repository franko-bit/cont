<?php
require 'backend/config.php';
$pdo = getPDO();

echo "=".str_repeat("=", 120)."\n";
echo "| DEBUGGING: Why records aren't displaying in try.php\n";
echo "=".str_repeat("=", 120)."\n\n";

// 1. Check all institution_candidates records
echo "1. ALL INSTITUTION_CANDIDATES RECORDS\n";
echo "─".str_repeat("─", 120)."\n";
$stmt = $pdo->query('
    SELECT ic.id, ic.institution_id, ic.user_id, ic.full_name, ic.email, ic.status, ic.score, 
           ic.exam_attempt_id, ic.assessment_id, ea.exam_id, ea.score as ea_score, e.title as exam_title
    FROM institution_candidates ic
    LEFT JOIN exam_attempts ea ON ea.id = ic.exam_attempt_id
    LEFT JOIN exams e ON e.id = ea.exam_id
    ORDER BY ic.created_at DESC
');
$allCandidates = $stmt->fetchAll();

foreach ($allCandidates as $row) {
    $hasScore = ($row['score'] !== null || $row['ea_score'] !== null);
    $scoreVal = $row['score'] ?? $row['ea_score'] ?? 'NULL';
    $id = str_pad($row['id'], 3);
    $name = str_pad($row['full_name'], 25);
    $status = str_pad($row['status'], 10);
    echo "$id | $name | Status: $status | Score: $scoreVal | Exam: {$row['exam_title']}\n";
}
echo "\nTotal records: " . count($allCandidates) . "\n\n";

// 2. Look specifically for "Mukiza princ"
echo "2. SEARCHING FOR 'Mukiza princ'\n";
echo "─".str_repeat("─", 120)."\n";
$stmt = $pdo->prepare('
    SELECT ic.*, ea.exam_id, ea.score as ea_score, e.title as exam_title
    FROM institution_candidates ic
    LEFT JOIN exam_attempts ea ON ea.id = ic.exam_attempt_id
    LEFT JOIN exams e ON e.id = ea.exam_id
    WHERE ic.full_name LIKE ?
');
$stmt->execute(['%Mukiza%']);
$mukiza = $stmt->fetchAll();

if (empty($mukiza)) {
    echo "✗ No records found matching 'Mukiza%'\n";
} else {
    foreach ($mukiza as $m) {
        echo "Found: {$m['full_name']}\n";
        echo "  - ID: {$m['id']}, Institution: {$m['institution_id']}, Status: {$m['status']}\n";
        echo "  - Score: {$m['score']}, EA Score: {$m['ea_score']}\n";
        echo "  - Exam ID: {$m['exam_id']}, Exam: {$m['exam_title']}\n";
        echo "  - Exam Attempt ID: {$m['exam_attempt_id']}\n";
    }
}
echo "\n";

// 3. Check which institution_id is being used in try.php
echo "3. INSTITUTIONS IN SYSTEM\n";
echo "─".str_repeat("─", 120)."\n";
$stmt = $pdo->query('SELECT id, name FROM institutions');
$insts = $stmt->fetchAll();
foreach ($insts as $i) {
    $stmt = $pdo->prepare('SELECT COUNT(*) as cnt FROM institution_candidates WHERE institution_id = ?');
    $stmt->execute([$i['id']]);
    $count = $stmt->fetch()['cnt'];
    echo "Institution {$i['id']}: {$i['name']} - {$count} candidates\n";
}
echo "\n";

// 4. Run the EXACT query from try.php to see what it returns
echo "4. TRY.PHP MAIN QUERY (Institution ID 1)\n";
echo "─".str_repeat("─", 120)."\n";

$institutionId = 1;  // This is what try.php uses

$stmt = $pdo->prepare(
    'SELECT c.*, ia.title AS assessment_title, COALESCE(u.school_name, i.name) AS school_name, ea.score as exam_score, ea.status as exam_status
     FROM institution_candidates c
     LEFT JOIN institution_assessments ia ON ia.id = c.assessment_id
     LEFT JOIN institutions i ON i.id = c.institution_id
     LEFT JOIN users u ON u.id = c.user_id
     LEFT JOIN exam_attempts ea ON ea.id = c.exam_attempt_id
     WHERE c.institution_id = :institution_id
     ORDER BY c.updated_at DESC, c.created_at DESC
     LIMIT 500'
);
$stmt->execute(['institution_id' => $institutionId]);
$candidates = $stmt->fetchAll();

echo "Query returned " . count($candidates) . " records for institution_id = $institutionId\n";
foreach ($candidates as $c) {
    echo "  - {$c['full_name']} (Score: {$c['score']}, EA Score: {$c['exam_score']})\n";
}
echo "\n";

// 5. Check the filter applied in try.php (score !== null)
echo "5. FILTERED FOR DISPLAY (score !== null)\n";
echo "─".str_repeat("─", 120)."\n";
$filtered = array_filter($candidates, function($c) {
    $score = $c['score'] !== null ? (float)$c['score'] : null;
    if ($score === null && $c['exam_score'] !== null) {
        $score = (float)$c['exam_score'];
    }
    return $score !== null;
});

echo "After filtering for score !== null: " . count($filtered) . " records\n";
foreach ($filtered as $c) {
    $score = $c['score'] !== null ? (float)$c['score'] : (float)$c['exam_score'];
    echo "  - {$c['full_name']}: {$score}%\n";
}
echo "\n";

// 6. Check if Mukiza's institution_id matches
echo "6. WHY MUKIZA ISN'T SHOWING\n";
echo "─".str_repeat("─", 120)."\n";
if (!empty($mukiza)) {
    foreach ($mukiza as $m) {
        echo "Mukiza's institution_id: {$m['institution_id']}\n";
        echo "try.php is querying institution_id: 1\n";
        if ($m['institution_id'] != 1) {
            echo "✗ MISMATCH! Mukiza is in institution {$m['institution_id']}, but try.php queries institution 1\n";
        }
        
        $score = $m['score'] ?? $m['ea_score'];
        if ($score === null) {
            echo "✗ NO SCORE: Mukiza has no score, filtered out by 'completed work' filter\n";
        }
    }
}
echo "\n";

// 7. Check exams and exam types
echo "7. ALL EXAMS IN SYSTEM\n";
echo "─".str_repeat("─", 120)."\n";
$stmt = $pdo->query('SELECT id, title, language_pair, level_number FROM exams ORDER BY level_number, title');
$exams = $stmt->fetchAll();
foreach ($exams as $e) {
    $stmt = $pdo->prepare('SELECT COUNT(*) as cnt FROM exam_attempts WHERE exam_id = ? AND status = "submitted"');
    $stmt->execute([$e['id']]);
    $count = $stmt->fetch()['cnt'];
    $isAcad = stripos($e['title'], 'academic') !== false;
    $isBiz = stripos($e['title'], 'business') !== false;
    $keep = ($isAcad || $isBiz) ? "KEEP" : "REMOVE";
    $id = str_pad($e['id'], 2);
    $title = str_pad($e['title'], 40);
    echo "Exam $id: $title - $count attempts - $keep\n";
}
echo "\n";

// 8. Check exam type filtering logic from try.php
echo "8. EXAM TYPE FILTER FROM TRY.PHP\n";
echo "─".str_repeat("─", 120)."\n";
echo "try.php filters exams with this logic:\n";
echo "  array_filter(\$testsData, function(\$t) {\n";
echo "    \$name = strtolower(\$t['name'] ?? '');\n";
echo "    return (strpos(\$name, 'business') !== false || strpos(\$name, 'academic') !== false);\n";
echo "  });\n\n";

$testNames = array_map(function($e) { return $e['title']; }, $exams);
foreach ($testNames as $name) {
    $nameLower = strtolower($name);
    $hasBusiness = strpos($nameLower, 'business') !== false;
    $hasAcademic = strpos($nameLower, 'academic') !== false;
    $keep = ($hasBusiness || $hasAcademic) ? "✓ KEEP" : "✗ REMOVE";
    echo "  '$name' → $keep\n";
}
?>
