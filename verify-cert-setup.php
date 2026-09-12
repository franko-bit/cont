<?php
/**
 * Certificate Fix Verification Script
 * Checks that all certificate-related changes are in place
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'backend/config.php';

echo "<h2>Certificate System Verification</h2>\n";
echo "<hr>\n";

// 1. Check if verify-certificate.php exists
echo "<h3>1. Certificate Verification File</h3>\n";
if (file_exists('frontend/verify-certificate.php')) {
    echo "✓ <code>frontend/verify-certificate.php</code> exists<br>\n";
    $lines = count(file('frontend/verify-certificate.php'));
    echo "  → File size: $lines lines<br>\n";
} else {
    echo "✗ <code>frontend/verify-certificate.php</code> is MISSING<br>\n";
}

// 2. Check if assets/images/cert.svg exists
echo "<h3>2. Certificate Image</h3>\n";
if (file_exists('assets/images/cert.svg')) {
    echo "✓ <code>assets/images/cert.svg</code> exists<br>\n";
    $size = filesize('assets/images/cert.svg');
    echo "  → File size: " . number_format($size) . " bytes<br>\n";
} else {
    echo "✗ <code>assets/images/cert.svg</code> is MISSING<br>\n";
}

// 3. Check certificates table structure
echo "<h3>3. Certificates Table Structure</h3>\n";
try {
    $stmt = $pdo->query("DESCRIBE certificates");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $required = ['id', 'certificate_id', 'student_name', 'user_id', 'exam_id', 'score', 'status', 'created_at'];
    $found = [];
    
    foreach ($columns as $col) {
        $found[] = $col['Field'];
    }
    
    echo "Table has " . count($columns) . " columns:\n<br>";
    foreach ($required as $req) {
        if (in_array($req, $found)) {
            echo "✓ <code>$req</code> column exists<br>\n";
        } else {
            echo "✗ <code>$req</code> column is MISSING<br>\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking table: " . $e->getMessage() . "<br>\n";
}

// 4. Check for test certificates
echo "<h3>4. Test Certificate Data</h3>\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM certificates WHERE status IN ('approved', 'issued', 'pending')");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['cnt'] ?? 0;
    
    if ($count > 0) {
        echo "✓ Found $count verified/pending certificates in database<br>\n";
        
        // Show sample certificates
        $stmt = $pdo->query("
            SELECT id, certificate_id, student_name, score, status, exam_id 
            FROM certificates 
            LIMIT 3
        ");
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5'>\n";
        echo "<tr><th>ID</th><th>Certificate ID</th><th>Student</th><th>Score</th><th>Status</th></tr>\n";
        foreach ($samples as $cert) {
            echo "<tr>";
            echo "<td>" . $cert['id'] . "</td>";
            echo "<td><code>" . substr($cert['certificate_id'] ?? '', 0, 16) . "...</code></td>";
            echo "<td>" . htmlspecialchars($cert['student_name'] ?? 'N/A') . "</td>";
            echo "<td>" . $cert['score'] . "%</td>";
            echo "<td>" . strtoupper($cert['status']) . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "⚠ No certificates found in database<br>\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking certificates: " . $e->getMessage() . "<br>\n";
}

// 5. Check dashboard.php for required changes
echo "<h3>5. Dashboard Changes</h3>\n";
$dashboard = file_get_contents('frontend/dashboard.php');

$checks = [
    'student_name field in query' => strpos($dashboard, "student_name") !== false,
    'viewCertificate function' => strpos($dashboard, "function viewCertificate") !== false,
    'downloadCertificate function' => strpos($dashboard, "function downloadCertificate") !== false,
    'Holder label' => strpos($dashboard, "<strong>Holder:</strong>") !== false,
    'cert-card-wrapper' => strpos($dashboard, "cert-card-wrapper") !== false,
    'cert-card-watermark' => strpos($dashboard, "cert-card-watermark") !== false,
    'onclick viewCertificate' => strpos($dashboard, "onclick=\"viewCertificate") !== false,
];

foreach ($checks as $check => $result) {
    echo ($result ? '✓' : '✗') . " " . ucfirst($check) . "<br>\n";
}

// 6. Test certificate URL building
echo "<h3>6. Certificate URL Test</h3>\n";
echo "Sample certificate URL would be: <br>\n";
echo "<code>frontend/verify-certificate.php?cert_id=CERT-SAMPLE-123</code><br>\n";

echo "<hr>\n";
echo "<p><strong>Verification Complete!</strong></p>\n";
echo "<p>If all checks pass, the certificate system is ready to use.</p>\n";
?>
