<?php
/**
 * Display the new simplified exam_results table schema
 */
require_once './backend/config.php';

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  EXAM_RESULTS TABLE - SIMPLIFIED SCHEMA                    ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$columns = $pdo->query("DESCRIBE exam_results")->fetchAll(PDO::FETCH_ASSOC);

echo "TABLE STRUCTURE (9 columns)\n";
echo str_repeat("─", 60) . "\n";

foreach ($columns as $col) {
    $required = $col['Null'] === 'NO' ? '[REQUIRED]' : '[OPTIONAL]';
    echo sprintf("%-18s %-20s %s\n", $col['Field'], $col['Type'], $required);
}

echo "\n" . str_repeat("─", 60) . "\n";
echo "INDEXES\n";
echo str_repeat("─", 60) . "\n";

$indexes = $pdo->query("SHOW INDEXES FROM exam_results")->fetchAll(PDO::FETCH_ASSOC);
$uniqueIndexes = [];
foreach ($indexes as $idx) {
    if ($idx['Key_name'] !== 'PRIMARY') {
        $uniqueIndexes[$idx['Key_name']][] = $idx['Column_name'];
    }
}

foreach ($uniqueIndexes as $name => $cols) {
    echo "  $name: " . implode(', ', $cols) . "\n";
}

echo "\n" . str_repeat("─", 60) . "\n";
echo "FOREIGN KEYS\n";
echo str_repeat("─", 60) . "\n";
echo "  user_id → users(id)\n";
echo "  exam_id → exams(id)\n";

echo "\n" . str_repeat("─", 60) . "\n";
echo "SUMMARY\n";
echo str_repeat("─", 60) . "\n";

$count = $pdo->query("SELECT COUNT(*) FROM exam_results")->fetchColumn();
echo "Current records: $count\n";
echo "Purpose: Store essential exam results for certificate determination\n";
echo "Key columns: user_id, exam_id, overall_score, correct_answers, percentage, passed\n";
echo "Backup table: exam_results_backup (can be dropped)\n";

?>
