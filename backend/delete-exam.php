<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$examId = (int)($input['examId'] ?? 0);
$institutionId = 1;

if (!$examId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Exam ID required']);
    exit;
}

try {
    // Delete associated candidates first
    $stmt = $pdo->prepare("DELETE FROM institution_candidates WHERE assessment_id = ? AND institution_id = ?");
    $stmt->execute([$examId, $institutionId]);
    
    // Delete exam
    $stmt = $pdo->prepare("DELETE FROM institution_assessments WHERE id = ? AND institution_id = ? AND assessment_model = 'exam'");
    $stmt->execute([$examId, $institutionId]);
    
    echo json_encode(['success' => true, 'message' => 'Exam deleted']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
