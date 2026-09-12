<?php
// remove-language.php
session_start();
require_once '../backend/config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || !is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$code = trim($data['language_code'] ?? '');

try {
    // Delete user's progress for this language first
    $stmt = $pdo->prepare("DELETE FROM user_progress WHERE user_id = ? AND language_code = ?");
    $stmt->execute([$user_id, $code]);
    
    // Delete the language from user_languages
    $stmt = $pdo->prepare("DELETE FROM user_languages WHERE user_id = ? AND language_code = ?");
    $stmt->execute([$user_id, $code]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Language removed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Language not found']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>