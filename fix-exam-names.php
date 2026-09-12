<?php
/**
 * FIX: Rename exams to Academic English and Business English
 * and update try.php filter logic
 */
require 'backend/config.php';
$pdo = getPDO();

echo "Updating exam names to Academic English and Business English...\n\n";

try {
    // Update exams to alternate between Academic and Business
    $stmt = $pdo->query('SELECT id, title FROM exams ORDER BY id');
    $exams = $stmt->fetchAll();
    
    $acadCount = 0;
    $bizCount = 0;
    
    foreach ($exams as $exam) {
        $id = $exam['id'];
        
        // Alternate between Academic and Business
        if ($acadCount <= $bizCount) {
            $newTitle = 'Academic English Exam - Level ' . ($acadCount + 1);
            $acadCount++;
            $type = "Academic";
        } else {
            $newTitle = 'Business English Exam - Level ' . ($bizCount + 1);
            $bizCount++;
            $type = "Business";
        }
        
        $updateStmt = $pdo->prepare('UPDATE exams SET title = ? WHERE id = ?');
        $updateStmt->execute([$newTitle, $id]);
        
        echo "✓ Exam $id: '$exam[title]' → '$newTitle' ($type)\n";
    }
    
    echo "\n✓ All exams updated!\n";
    
    // Verify the changes
    echo "\nVerifying updated exams:\n";
    $stmt = $pdo->query('SELECT id, title FROM exams ORDER BY id LIMIT 10');
    $updated = $stmt->fetchAll();
    foreach ($updated as $e) {
        echo "  - {$e['id']}: {$e['title']}\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
