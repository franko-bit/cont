<?php
require_once 'backend/config.php';

$pdo = getPDO();

echo "=== Checking certificates for direct exam-takers ===\n\n";

// Check if any certificates exist for direct (non-institution) exam-takers
$stmt = $pdo->prepare("
    SELECT c.id, c.certificate_id, c.student_name, c.user_id, c.exam_attempt_id, c.score, c.status,
           ea.user_id as attempt_user_id, u.full_name
    FROM certificates c
    JOIN exam_attempts ea ON c.exam_attempt_id = ea.id
    JOIN users u ON ea.user_id = u.id
    LIMIT 10
");
$stmt->execute();
$certs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total certificates found: " . count($certs) . "\n\n";

foreach ($certs as $cert) {
    echo "Certificate: " . $cert['certificate_id'] . "\n";
    echo "  User: " . $cert['full_name'] . " (ID: " . $cert['user_id'] . ")\n";
    echo "  Score: " . $cert['score'] . "%\n";
    echo "  Status: " . $cert['status'] . "\n";
    echo "  Exam Attempt ID: " . $cert['exam_attempt_id'] . "\n";
    echo "---\n";
}

echo "\n=== Checking if User 3 has any certificates ===\n";

$stmt = $pdo->prepare("
    SELECT c.* FROM certificates c
    JOIN exam_attempts ea ON c.exam_attempt_id = ea.id
    WHERE ea.user_id = 3
");
$stmt->execute();
$user3Certs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($user3Certs)) {
    echo "❌ NO certificates found for User 3\n";
} else {
    echo "✓ Found " . count($user3Certs) . " certificates for User 3:\n";
    foreach ($user3Certs as $cert) {
        echo "  " . $cert['certificate_id'] . " - Status: " . $cert['status'] . "\n";
    }
}

echo "\n=== Check if User 3's exam_attempts have scores ===\n";

$stmt = $pdo->prepare("
    SELECT id, status, score, passed, certificate_id
    FROM exam_attempts
    WHERE user_id = 3
    ORDER BY id DESC
    LIMIT 5
");
$stmt->execute();
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($attempts as $att) {
    echo "Attempt {$att['id']}: status={$att['status']}, score={$att['score']}, passed={$att['passed']}, " .
         "cert_id={$att['certificate_id']}\n";
}

?>
