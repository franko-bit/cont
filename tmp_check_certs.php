<?php
require_once 'backend/config.php';

try {
    $stmt = $pdo->query('DESCRIBE certificates');
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    echo "Certificates table columns:\n";
    foreach ($columns as $col) {
        echo "  - " . $col . "\n";
    }
    
    echo "\n\nAttempt 33 status:\n";
    $stmt = $pdo->prepare("SELECT id, user_id, exam_id, status, score, passed, certificate_id FROM exam_attempts WHERE id = 33");
    $stmt->execute();
    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($attempt, JSON_PRETTY_PRINT) . "\n";
    
    echo "\n\nCertificates for attempt 33:\n";
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE exam_attempt_id = 33");
    $stmt->execute();
    $certs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($certs, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    echo "\nTrace:\n" . $e->getTraceAsString();
}
?>
