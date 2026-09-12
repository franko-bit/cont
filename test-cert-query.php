<?php
require_once 'backend/config.php';

echo "Certificates table columns:\n";
$result = $pdo->query('DESCRIBE certificates');
$columns = $result->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
}

echo "\n\nInstitution Assessments table columns:\n";
$result = $pdo->query('DESCRIBE institution_assessments');
$columns = $result->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
}

echo "\n\nTest Query:\n";
try {
    $stmt = $pdo->prepare("
        SELECT c.id, c.certificate_id, c.student_name, a.title as exam_title
        FROM certificates c
        LEFT JOIN institution_assessments a ON c.assessment_id = a.id
        LIMIT 1
    ");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "Query result: " . json_encode($result) . "\n";
} catch (Exception $e) {
    echo "Query error: " . $e->getMessage() . "\n";
}
?>
