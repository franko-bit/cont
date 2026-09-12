<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$pdo = getPDO();
$action = $input['action'];
$certificate_id = $input['certificate_id'] ?? null;

if ($action === 'approve') {
    if (!$certificate_id) {
        echo json_encode(['success' => false, 'error' => 'Certificate ID required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE certificates SET status = 'approved', updated_at = NOW() WHERE certificate_id = ?");
        $stmt->execute([$certificate_id]);

        if ($stmt->rowCount() > 0) {
            // Fetch the updated certificate
            $stmt = $pdo->prepare("SELECT certificate_id, student_name FROM certificates WHERE certificate_id = ? LIMIT 1");
            $stmt->execute([$certificate_id]);
            $cert = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'message' => 'Certificate approved',
                'data' => $cert
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Certificate not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'reject') {
    if (!$certificate_id) {
        echo json_encode(['success' => false, 'error' => 'Certificate ID required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE certificates SET status = 'rejected', updated_at = NOW() WHERE certificate_id = ?");
        $stmt->execute([$certificate_id]);

        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("SELECT certificate_id, student_name FROM certificates WHERE certificate_id = ? LIMIT 1");
            $stmt->execute([$certificate_id]);
            $cert = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'message' => 'Certificate rejected',
                'data' => $cert
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Certificate not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>
