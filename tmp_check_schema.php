<?php
require_once 'backend/config.php';

$pdo = getPDO();

// Check certificates schema
echo "=== Certificates Table Structure ===\n";
$stmt = $pdo->query('DESCRIBE certificates');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo $col['Field'] . " | ";
}
echo "\n\n";

// Check exam_attempts schema
echo "=== Exam_Attempts Table Structure ===\n";
$stmt = $pdo->query('DESCRIBE exam_attempts');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo $col['Field'] . " | ";
}
echo "\n\n";

// Check a sample certificate
echo "=== Sample Certificate ===\n";
$stmt = $pdo->prepare("SELECT * FROM certificates LIMIT 1");
$stmt->execute();
$cert = $stmt->fetch(PDO::FETCH_ASSOC);
if ($cert) {
    foreach ($cert as $k => $v) {
        echo "$k: " . (is_string($v) ? substr($v, 0, 50) : $v) . "\n";
    }
} else {
    echo "No certificates found\n";
}

echo "\n=== Check if institution_id in certificates ===\n";
$stmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE institution_id IS NOT NULL");
$stmt->execute();
echo "Certificates with institution_id: " . $stmt->fetchColumn() . "\n";

echo "\n=== Sample Exam Attempt with User ===\n";
$stmt = $pdo->prepare("
    SELECT ea.id, ea.user_id, ea.exam_id, ea.score, u.full_name, u.id as user_id_check
    FROM exam_attempts ea
    LEFT JOIN users u ON u.id = ea.user_id
    WHERE ea.status = 'submitted'
    LIMIT 1
");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    foreach ($row as $k => $v) {
        echo "$k: $v\n";
    }
} else {
    echo "No submitted attempts found\n";
}

echo "\n=== Check institution_users table ===\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'institution_users'");
    if ($stmt->rowCount() > 0) {
        $desc = $pdo->query('DESCRIBE institution_users');
        $cols = $desc->fetchAll(PDO::FETCH_COLUMN, 0);
        echo "Columns: " . implode(", ", $cols) . "\n";
    } else {
        echo "Table does not exist\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
