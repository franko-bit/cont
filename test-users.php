<?php
require_once 'backend/config.php';

$result = $pdo->query('DESCRIBE users');
$columns = $result->fetchAll(PDO::FETCH_ASSOC);

echo "Users table columns:\n";
foreach ($columns as $col) {
    echo '- ' . $col['Field'] . "\n";
}
?>
