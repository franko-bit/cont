<?php
header('Content-Type: application/json');
try {
    require_once __DIR__ . '/config.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Config error']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? intval($input['id']) : 0;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid assessment id']);
    exit;
}

$institution_id = 1;
try {
    $stmt = $pdo->prepare('DELETE FROM institution_candidates WHERE assessment_id = ? AND institution_id = ?');
    $stmt->execute([$id, $institution_id]);

    $stmt = $pdo->prepare('DELETE FROM institution_assessments WHERE id = ? AND institution_id = ?');
    $stmt->execute([$id, $institution_id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
