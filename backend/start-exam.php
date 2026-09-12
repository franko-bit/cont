<?php
// backend/start-exam.php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once 'config.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$attempt_id = (int)($data['attempt_id'] ?? 0);

if ($attempt_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid attempt ID']);
    exit;
}

// Verify attempt belongs to user and user is verified
$stmt = $pdo->prepare("SELECT ea.*, u.verified FROM exam_attempts ea JOIN users u ON ea.user_id = u.id WHERE ea.id = ? AND ea.user_id = ?");
$stmt->execute([$attempt_id, $user_id]);
$attempt = $stmt->fetch();

if (!$attempt) {
    echo json_encode(['success' => false, 'message' => 'Attempt not found']);
    exit;
}

if ($attempt['verified'] != 1) {
    echo json_encode(['success' => false, 'message' => 'User must be verified before starting the exam']);
    exit;
}

// Start the exam
$stmt = $pdo->prepare("
    UPDATE exam_attempts
    SET status = 'in_progress',
        verification_status = 'fully_verified',
        started_at = NOW()
    WHERE id = ?
");
$stmt->execute([$attempt_id]);

echo json_encode([
    'success' => true,
    'message' => 'Exam started successfully'
]);
?>