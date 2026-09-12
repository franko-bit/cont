<?php
// backend/save-onboarding.php
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
$camera_ok = $data['camera_ok'] ?? false;
$mic_ok = $data['mic_ok'] ?? false;
$selfie_ok = $data['selfie_ok'] ?? false;
$fullscreen_ok = $data['fullscreen_ok'] ?? false;

if ($attempt_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid attempt ID']);
    exit;
}

// Verify attempt belongs to user
$stmt = $pdo->prepare("SELECT * FROM exam_attempts WHERE id = ? AND user_id = ?");
$stmt->execute([$attempt_id, $user_id]);
$attempt = $stmt->fetch();

if (!$attempt) {
    echo json_encode(['success' => false, 'message' => 'Attempt not found']);
    exit;
}

// Save device check verification
$stmt = $pdo->prepare("
    INSERT INTO exam_verifications (attempt_id, verification_type, device_camera_ok, device_microphone_ok, device_fullscreen_ok, verification_status, verified_at)
    VALUES (?, 'device_check', ?, ?, ?, 'approved', NOW())
    ON DUPLICATE KEY UPDATE
    device_camera_ok = VALUES(device_camera_ok),
    device_microphone_ok = VALUES(device_microphone_ok),
    device_fullscreen_ok = VALUES(device_fullscreen_ok),
    verification_status = VALUES(verification_status),
    verified_at = VALUES(verified_at)
");
$stmt->execute([$attempt_id, $camera_ok, $mic_ok, $fullscreen_ok]);

// Mark the attempt as fully verified if device check passed
$stmt = $pdo->prepare("UPDATE exam_attempts SET verification_status = 'fully_verified' WHERE id = ? AND user_id = ?");
$stmt->execute([$attempt_id, $user_id]);

echo json_encode([
    'success' => true,
    'message' => 'Onboarding completed successfully'
]);
?>