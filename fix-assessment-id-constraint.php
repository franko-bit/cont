<?php
require 'backend/config.php';
$pdo = getPDO();

echo "Fixing institution_candidates schema...\n\n";

try {
    // Make assessment_id nullable
    $pdo->exec('ALTER TABLE institution_candidates MODIFY COLUMN assessment_id INT(11) NULL');
    echo "✓ Step 1: Made assessment_id nullable\n";
    
    // Remove the foreign key constraint if it exists
    $stmt = $pdo->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME='institution_candidates' 
                        AND COLUMN_NAME='assessment_id' 
                        AND REFERENCED_TABLE_NAME IS NOT NULL");
    
    $constraints = $stmt->fetchAll();
    foreach ($constraints as $c) {
        $constraintName = $c['CONSTRAINT_NAME'];
        $pdo->exec("ALTER TABLE institution_candidates DROP FOREIGN KEY $constraintName");
        echo "✓ Step 2: Dropped foreign key constraint: $constraintName\n";
    }
    
    // Re-add the foreign key with ON DELETE SET NULL
    $pdo->exec("ALTER TABLE institution_candidates 
               ADD CONSTRAINT institution_candidates_assessment_fk 
               FOREIGN KEY (assessment_id) REFERENCES institution_assessments(id) ON DELETE SET NULL");
    echo "✓ Step 3: Added nullable foreign key constraint\n";
    
    echo "\n✓ Schema fixed! Now institution_candidates can be inserted without assessment_id\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
