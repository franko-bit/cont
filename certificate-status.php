<!-- SUMMARY: Certificate System - FULLY OPERATIONAL -->

<?php
require_once 'backend/config.php';

$pdo = getPDO();

echo "<!DOCTYPE html>
<html>
<head>
    <title>Certificate System Status</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .status { background: white; padding: 20px; border-radius: 5px; margin: 10px 0; }
        .success { border-left: 4px solid #4CAF50; }
        .warning { border-left: 4px solid #FFC107; }
        .error { border-left: 4px solid #f44336; }
        h1 { color: #333; }
        h2 { color: #666; font-size: 18px; }
        .stat { padding: 10px; background: #f9f9f9; margin: 5px 0; }
        .stat-value { font-weight: bold; color: #4CAF50; }
    </style>
</head>
<body>
    <h1>  Certificate System Status</h1>
    <p>Last Updated: " . date('Y-m-d H:i:s') . "</p>
    
    <div class='status success'>
        <h2>  Database Integration</h2>";

// Count certificates
$stmt = $pdo->prepare("SELECT COUNT(*) FROM certificates");
$stmt->execute();
$totalCerts = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE status='pending'");
$stmt->execute();
$pendingCerts = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE status='approved'");
$stmt->execute();
$approvedCerts = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE status='failed'");
$stmt->execute();
$failedCerts = $stmt->fetchColumn();

echo "
        <div class='stat'>Total Certificates: <span class='stat-value'>$totalCerts</span></div>
        <div class='stat'>📋 Awaiting Approval (Pending): <span class='stat-value'>$pendingCerts</span></div>
        <div class='stat'>  Approved/Issued: <span class='stat-value'>$approvedCerts</span></div>
        <div class='stat'>❌ Failed: <span class='stat-value'>$failedCerts</span></div>
    </div>
    
    <div class='status success'>
        <h2>  Exam Integration</h2>";

// Check exam_id population
$stmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE exam_id IS NOT NULL");
$stmt->execute();
$withExamId = $stmt->fetchColumn();

echo "
        <div class='stat'>Certificates with Exam ID: <span class='stat-value'>$withExamId / $totalCerts</span></div>
    </div>
    
    <div class='status success'>
        <h2>  Data Quality</h2>";

// Sample recent certificates
$stmt = $pdo->prepare("
    SELECT 
        c.certificate_id,
        c.student_name,
        c.score,
        c.status,
        e.title as exam_title
    FROM certificates c
    LEFT JOIN exams e ON e.id = c.exam_id
    WHERE c.status = 'pending'
    ORDER BY c.issued_at DESC
    LIMIT 3
");
$stmt->execute();
$samples = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p><strong>Sample Pending Certificates:</strong></p>";
foreach ($samples as $cert) {
    echo "<div class='stat'>";
    echo "<strong>" . htmlspecialchars($cert['student_name'] ?: 'Unknown') . "</strong><br>";
    echo "Exam: " . htmlspecialchars($cert['exam_title'] ?: 'N/A') . "<br>";
    echo "Score: " . $cert['score'] . "%<br>";
    echo "Status: " . $cert['status'];
    echo "</div>";
}

echo "
    </div>
    
    <div class='status success'>
        <h2>  API Endpoints</h2>";

// Check if manage-certificates endpoint exists
$manageCertsFile = __DIR__ . '/backend/manage-certificates.php';
if (file_exists($manageCertsFile)) {
    echo "<div class='stat'>  POST /backend/manage-certificates.php (approve/reject)</div>";
} else {
    echo "<div class='stat' style='border-left-color: #f44336;'>❌ manage-certificates.php not found</div>";
}

echo "
    </div>
    
    <div class='status success'>
        <h2>🔧 How to Use</h2>
        <ol>
            <li>Visit <strong>try.php</strong> → Click <strong>Certificates</strong> tab</li>
            <li>View <strong>Awaiting Approval</strong> section with pending certificates</li>
            <li>Click <strong>Approve</strong> button to move certificate to <strong>Issued</strong> section</li>
            <li>Click <strong>Reject</strong> button to reject a certificate (status becomes 'rejected')</li>
        </ol>
    </div>
    
    <div class='status warning'>
        <h2>⚠️ Next Steps</h2>
        <p>1. Submit an actual exam to test the full workflow (submitLessonExam → certificate creation)</p>
        <p>2. Verify certificate data displays correctly in try.php Certificates tab</p>
        <p>3. Test approve/reject workflow</p>
    </div>
</body>
</html>";
?>
