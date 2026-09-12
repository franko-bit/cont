<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'backend/config.php';

// Simulate a user session
$_SESSION['user_id'] = 1;

echo "<h3>Dashboard Certificate Query Test</h3>\n";
echo "<pre>\n";

$user_id = $_SESSION['user_id'];

try {
    // Original query from dashboard
    $stmt = $pdo->prepare("
        SELECT c.*, a.title as exam_title, a.exam_language,
               CASE 
                   WHEN a.title LIKE '%Academic%' THEN 'Academic English Exam'
                   WHEN a.title LIKE '%Business%' THEN 'Business English Exam'
                   ELSE a.title
               END as exam_type,
               u.full_name
        FROM certificates c
        LEFT JOIN institution_assessments a ON c.assessment_id = a.id
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $allCerts = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    echo "Query executed successfully!\n";
    echo "Found " . count($allCerts) . " certificates\n\n";
    
    // Group by exam_type
    $certificates = [];
    $seenTypes = [];
    foreach ($allCerts as $cert) {
        $examType = $cert['exam_type'] ?? $cert['exam_title'];
        if (!in_array($examType, $seenTypes)) {
            $certificates[] = $cert;
            $seenTypes[] = $examType;
        }
    }
    
    echo "After deduplication: " . count($certificates) . " certificates\n\n";
    
    // Show first certificate
    if (!empty($certificates)) {
        echo "First certificate:\n";
        echo json_encode($certificates[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo "\n\n";
        
        // Test display values
        $cert = $certificates[0];
        echo "Display Test:\n";
        echo "Holder: " . ($cert['student_name'] ?? $cert['full_name'] ?? 'Not Specified') . "\n";
        echo "Exam Title: " . ($cert['exam_title'] ?? 'Verified Certificate') . "\n";
        echo "Score: " . ($cert['score'] ?? '—') . "%\n";
        echo "Certificate ID: " . ($cert['certificate_id'] ?? 'N/A') . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>\n";
?>
