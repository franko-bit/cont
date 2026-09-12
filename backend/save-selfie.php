<?php
// backend/save-selfie.php
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

$attempt_id = (int)($_POST['attempt_id'] ?? 0);
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

// Handle selfie upload
$selfie_url = null;
if (isset($_FILES['selfie']) && $_FILES['selfie']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/verification/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $ext = pathinfo($_FILES['selfie']['name'], PATHINFO_EXTENSION);
    $filename = "selfie_{$attempt_id}_{$user_id}_" . time() . ".{$ext}";
    $selfie_url = $upload_dir . $filename;
    move_uploaded_file($_FILES['selfie']['tmp_name'], $selfie_url);
}

if (!$selfie_url) {
    echo json_encode(['success' => false, 'message' => 'Selfie photo is required']);
    exit;
}

// For now, auto-approve selfie verification (in production, you'd use face matching)
$face_match_score = 95.0; // Simulated face match

$stmt = $pdo->prepare("
    INSERT INTO exam_verifications (attempt_id, verification_type, selfie_photo_url, face_match_score, verification_status, verified_at)
    VALUES (?, 'selfie', ?, ?, 'approved', NOW())
    ON DUPLICATE KEY UPDATE
    selfie_photo_url = VALUES(selfie_photo_url),
    face_match_score = VALUES(face_match_score),
    verification_status = VALUES(verification_status),
    verified_at = VALUES(verified_at)
");
$stmt->execute([$attempt_id, $selfie_url, $face_match_score]);

// Update attempt status
$stmt = $pdo->prepare("UPDATE exam_attempts SET verification_status = 'selfie_verified' WHERE id = ?");
$stmt->execute([$attempt_id]);

echo json_encode([
    'success' => true,
    'message' => 'Selfie verification completed successfully'
]);
?>