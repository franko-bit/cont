<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['csv']) || !isset($_POST['examId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing CSV file or exam ID']);
    exit;
}

$examId = (int)$_POST['examId'];
$institutionId = 1;
$file = $_FILES['csv']['tmp_name'];

if ($_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File upload error']);
    exit;
}

try {
    // Verify exam exists
    $stmt = $pdo->prepare("SELECT id FROM institution_assessments WHERE id = ? AND institution_id = ? AND assessment_model = 'exam'");
    $stmt->execute([$examId, $institutionId]);
    if (!$stmt->fetch()) {
        throw new Exception('Exam not found');
    }
    
    $handle = fopen($file, 'r');
    $imported = 0;
    $skipped = 0;
    
    // Skip header
    fgetcsv($handle);
    
    while ($row = fgetcsv($handle)) {
        $name = trim($row[0] ?? '');
        $email = trim($row[1] ?? '');
        
        if (!$name || !$email) {
            $skipped++;
            continue;
        }
        
        // Check if candidate already exists
        $stmt = $pdo->prepare("
            SELECT id FROM institution_candidates 
            WHERE institution_id = ? AND assessment_id = ? AND email = ?
        ");
        $stmt->execute([$institutionId, $examId, $email]);
        
        if ($stmt->fetch()) {
            $skipped++;
            continue;
        }
        
        // Insert candidate
        $stmt = $pdo->prepare("
            INSERT INTO institution_candidates (
                institution_id, assessment_id, full_name, email, exam_status, invited_at
            ) VALUES (?, ?, ?, ?, 'invited', NOW())
        ");
        $stmt->execute([$institutionId, $examId, $name, $email]);
        $imported++;
    }
    
    fclose($handle);
    unlink($file);
    
    echo json_encode([
        'success' => true,
        'message' => "Imported $imported candidates",
        'imported' => $imported,
        'skipped' => $skipped
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
