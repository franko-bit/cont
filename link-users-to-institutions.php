<?php
/**
 * Link existing users to institutions based on their institution_candidates records
 */
require 'backend/config.php';
$pdo = getPDO();

echo "Linking users to their institutions via institution_candidates...\n\n";

try {
    // Get all institution_candidates records with user_id and institution_id
    $stmt = $pdo->query(
        'SELECT DISTINCT user_id, institution_id 
         FROM institution_candidates 
         WHERE user_id IS NOT NULL AND institution_id IS NOT NULL AND institution_id > 0'
    );
    $records = $stmt->fetchAll();
    
    $linked = 0;
    $skipped = 0;
    
    foreach ($records as $record) {
        $userId = $record['user_id'];
        $instId = $record['institution_id'];
        
        // Check if already linked
        $checkStmt = $pdo->prepare('SELECT id FROM institution_users WHERE user_id = ? AND institution_id = ?');
        $checkStmt->execute([$userId, $instId]);
        
        if ($checkStmt->fetch()) {
            $skipped++;
            continue;
        }
        
        // Link the user
        try {
            $linkStmt = $pdo->prepare('INSERT INTO institution_users (institution_id, user_id) VALUES (?, ?)');
            $linkStmt->execute([$instId, $userId]);
            echo "✓ Linked user $userId to institution $instId\n";
            $linked++;
        } catch (Exception $e) {
            echo "✗ Failed to link user $userId to institution $instId: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✓ Completed:\n";
    echo "  - New links created: $linked\n";
    echo "  - Already linked: $skipped\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
    exit(1);
}
?>
