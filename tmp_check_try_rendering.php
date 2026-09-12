<?php
session_start();
require_once 'backend/config.php';

// Simulate being logged in
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

$pdo = getPDO();

// Set up the same data fetching as try.php
$institutionId = $_SESSION['institution_id'] ?? 1;

$certificateRows = [];

// Get certificates (same query as try.php)
$cr = $pdo->prepare(
    'SELECT c.id, c.certificate_id, c.student_name, c.score, c.passing_score, c.language_pair, c.level_number,
            c.issued_at, c.exam_attempt_id, c.status, c.user_id,
            u.full_name, u.email, u.school_name, e.title AS exam_title
     FROM certificates c
     LEFT JOIN users u ON u.id = c.user_id
     LEFT JOIN exams e ON e.id = c.exam_id
     ORDER BY c.issued_at DESC'
);
$cr->execute();
$certificateRows = $cr->fetchAll(PDO::FETCH_ASSOC);

echo "=== Simulated try.php JavaScript Context ===\n\n";
echo "Total certificates: " . count($certificateRows) . "\n\n";

// Output the JSON like try.php does
?>
<script>
const certificateRows = <?= json_encode($certificateRows ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

console.log("Certificate rows loaded:", certificateRows.length);

const pending = certificateRows.filter(c => c.status === 'pending');
const approved = certificateRows.filter(c => c.status === 'approved');

console.log("Pending certificates:", pending.length);
console.log("Approved certificates:", approved.length);

console.log("\nFirst pending certificate:");
if (pending.length > 0) {
    const cert = pending[0];
    console.log("  ID:", cert.certificate_id);
    console.log("  Student:", cert.student_name || 'Certificate');
    console.log("  Exam:", cert.exam_title || '—');
    console.log("  Language:", cert.language_pair || '—');
    console.log("  Score:", cert.score !== null ? cert.score+'%' : '—');
}
</script>
<?php
echo "\n\nCheck the JavaScript console output above:\n";
echo "If you see the certificate data displayed correctly, then the page is working.\n";
?>
