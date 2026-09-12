<?php
require_once __DIR__ . '/backend/config.php';

// Development preview: allow quick access using ?dev=1 when working locally
if (isset($_GET['dev']) && $_GET['dev'] === '1') {
  try {
    $tmpPdo = getPDO();
    $stmt = $tmpPdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute(['sample.student@example.com']);
    $devUser = $stmt->fetchColumn();
    if ($devUser) {
      $_SESSION['user_id'] = (int)$devUser;
      $_SESSION['institution_id'] = 1;
      $_SESSION['user_name'] = 'Dev Tester';
    }
  } catch (Exception $e) {}
}

// Check session
if (empty($_SESSION['user_id'])) {
  header('Location: ' . SITE_URL . '/frontend/signin.php?redirect_to=' . urlencode('/language-platform/debug_try_data.php?dev=1'));
  exit;
}

$institutionId = isset($_SESSION['institution_id']) ? (int)$_SESSION['institution_id'] : 1;
$pdo = getPDO();

echo "=== TRY.PHP DATABASE DATA DEBUG ===\n";
echo "Institution ID: " . $institutionId . "\n";
echo "User ID: " . $_SESSION['user_id'] . "\n\n";

// 1. Check institution_assessments
echo "1. INSTITUTION_ASSESSMENTS\n";
echo str_repeat("─", 60) . "\n";
$stmt = $pdo->prepare('SELECT id, title, duration_minutes FROM institution_assessments WHERE institution_id = ? LIMIT 5');
$stmt->execute([$institutionId]);
$assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found: " . count($assessments) . " assessments\n";
foreach ($assessments as $a) {
    echo "  - ID: " . $a['id'] . " | " . $a['title'] . " (" . $a['duration_minutes'] . " min)\n";
}

// 2. Check institution_candidates
echo "\n2. INSTITUTION_CANDIDATES\n";
echo str_repeat("─", 60) . "\n";
$stmt = $pdo->prepare('SELECT id, full_name, email, status, score FROM institution_candidates WHERE institution_id = ? LIMIT 5');
$stmt->execute([$institutionId]);
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found: " . count($candidates) . " candidates\n";
foreach ($candidates as $c) {
    echo "  - ID: " . $c['id'] . " | " . $c['full_name'] . " | Score: " . $c['score'] . " | Status: " . $c['status'] . "\n";
}

// 3. Check certificates
echo "\n3. CERTIFICATES\n";
echo str_repeat("─", 60) . "\n";
$stmt = $pdo->query('SELECT COUNT(*) FROM certificates');
$totalCerts = $stmt->fetchColumn();
echo "Total certificates in DB: " . $totalCerts . "\n";

// 4. Check certificates for this institution (JOIN query)
echo "\n4. CERTIFICATES FOR THIS INSTITUTION\n";
echo str_repeat("─", 60) . "\n";
$stmt = $pdo->prepare(
  'SELECT c.certificate_id, c.student_name, c.score, c.issued_at,
          ic.id AS candidate_id, ic.full_name AS candidate_name, ia.title AS assessment_title
   FROM certificates c
   INNER JOIN institution_candidates ic ON ic.exam_attempt_id = c.exam_attempt_id
   LEFT JOIN institution_assessments ia ON ia.id = ic.assessment_id
   WHERE ic.institution_id = ?
   LIMIT 10'
);
$stmt->execute([$institutionId]);
$certs = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found: " . count($certs) . " certificates for institution " . $institutionId . "\n";
foreach ($certs as $c) {
    echo "  - " . $c['certificate_id'] . " | " . $c['student_name'] . " | Score: " . $c['score'] . "%\n";
}

// 5. Check what's passed to JavaScript
echo "\n5. WHAT GETS PASSED TO JAVASCRIPT (certificateRows)\n";
echo str_repeat("─", 60) . "\n";
echo json_encode($certs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

?>
