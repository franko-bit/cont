<?php
require_once 'backend/config.php';

$pdo = getPDO();

echo "=== Understanding User 3's Exam Context ===\n\n";

// Check user 3
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = 3");
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "User 3:\n";
foreach ($user as $key => $val) {
    if (strlen($val) < 50) {
        echo "  $key: $val\n";
    } else {
        echo "  $key: " . substr($val, 0, 50) . "...\n";
    }
}

echo "\n=== Checking exam_attempts for User 3 ===\n";

$stmt = $pdo->prepare("
    SELECT ea.id, ea.exam_id, ea.status, e.title, e.language_pair, e.level_number
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id = e.id
    WHERE ea.user_id = 3
    ORDER BY ea.created_at DESC
    LIMIT 5
");
$stmt->execute();
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Exam attempts for user 3:\n";
foreach ($attempts as $att) {
    echo "  Attempt {$att['id']}: exam_id={$att['exam_id']}, status={$att['status']}, title={$att['title']}\n";
}

echo "\n=== How are exams accessed? ===\n";

// Check if there's a way users access exams directly (not through institution_candidates)
echo "Total exams in system: ";
$stmt = $pdo->query("SELECT COUNT(*) FROM exams");
echo $stmt->fetchColumn() . "\n";

echo "Total institution_candidates in system: ";
$stmt = $pdo->query("SELECT COUNT(*) FROM institution_candidates");
echo $stmt->fetchColumn() . "\n";

echo "Total exam_attempts in system: ";
$stmt = $pdo->query("SELECT COUNT(*) FROM exam_attempts");
echo $stmt->fetchColumn() . "\n";

echo "\n=== Institution Context for User 3 ===\n";

$stmt = $pdo->prepare("
    SELECT institution_id, user_role, school_name
    FROM users
    WHERE id = 3
");
$stmt->execute();
$userInst = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($userInst, JSON_PRETTY_PRINT) . "\n";

?>
