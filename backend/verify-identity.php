<?php
// backend/verify-identity.php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once '../db.php';
require_once __DIR__ . '/../vendor/autoload.php';

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

// Handle ID photo upload
$id_photo_url = null;
if (isset($_FILES['id_photo']) && $_FILES['id_photo']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/verification/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $ext = pathinfo($_FILES['id_photo']['name'], PATHINFO_EXTENSION);
    $filename = "id_{$attempt_id}_{$user_id}_" . time() . ".{$ext}";
    $id_photo_url = $upload_dir . $filename;
    move_uploaded_file($_FILES['id_photo']['tmp_name'], $id_photo_url);
}

if (!$id_photo_url) {
    echo json_encode(['success' => false, 'message' => 'ID photo is required']);
    exit;
}

// For now, auto-approve ID verification (in production, you'd use OCR and validation)
$stmt = $pdo->prepare("
    INSERT INTO exam_verifications (attempt_id, verification_type, id_photo_url, verification_status, verified_at)
    VALUES (?, 'id_photo', ?, 'approved', NOW())
    ON DUPLICATE KEY UPDATE
    id_photo_url = VALUES(id_photo_url),
    verification_status = VALUES(verification_status),
    verified_at = VALUES(verified_at)
");
$stmt->execute([$attempt_id, $id_photo_url]);

// Update attempt status
$stmt = $pdo->prepare("UPDATE exam_attempts SET verification_status = 'id_verified' WHERE id = ?");
$stmt->execute([$attempt_id]);

echo json_encode([
    'success' => true,
    'message' => 'ID verification completed successfully'
]);
?>