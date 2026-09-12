<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$attemptId = isset($_POST['attempt_id']) ? (int)$_POST['attempt_id'] : 0;
if ($attemptId <= 0 || empty($_FILES['exam_video'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing attempt ID or video file']);
    exit;
}

$videoFile = $_FILES['exam_video'];
if ($videoFile['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Video upload failed with error ' . $videoFile['error']]);
    exit;
}

try {
    $recordKey = 'exam_' . $attemptId . '_' . bin2hex(random_bytes(6));
    $baseDir = __DIR__ . '/../uploads/exam_records';
    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0755, true);
    }
    $recordDir = $baseDir . '/' . $recordKey;
    if (!is_dir($recordDir)) {
        mkdir($recordDir, 0755, true);
    }

    $targetPath = $recordDir . '/video.webm';
    if (!move_uploaded_file($videoFile['tmp_name'], $targetPath)) {
        throw new Exception('Unable to save uploaded video file');
    }

    $pdo = getPDO();
    $stmt = $pdo->prepare('INSERT INTO exam_records (user_id, exam_id, record_path, created_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE record_path = VALUES(record_path), created_at = NOW()');
    $stmt->execute([$_SESSION['user_id'], $recordKey, $recordDir]);

    echo json_encode(['success' => true, 'record_key' => $recordKey, 'record_path' => $recordDir]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
