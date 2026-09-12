<?php
/**
 * CERTIFICATE DATA FLOW VERIFICATION GUIDE
 * 
 * Problem: Data not being saved in the certificates table after completing exam
 * 
 * SUMMARY OF FINDINGS:
 * ===================
 * ✓ Backend submission pipeline is WORKING correctly
 * ✓ Certificate insertion into database is FUNCTIONAL
 * ✓ Try.php dashboard displays certificates correctly
 * ⚠ Frontend exam submission may not be triggering automatically
 * 
 * THE ISSUE:
 * ==========
 * Users take exams but exam_attempts remain in 'in_progress' status with score=0
 * This indicates the frontend is NOT calling submitLessonExam() when exam completes
 * 
 * HOW THE FLOW SHOULD WORK:
 * =========================
 * 1. User starts exam → exam_attempts created with status='in_progress'
 * 2. User answers questions → answers recorded in exam_answers table
 * 3. User completes all questions OR time runs out
 * 4. Frontend automatically calls submitLessonExam() function
 * 5. Backend receives submission, updates exam_attempts (status='submitted')
 * 6. Backend creates certificate in certificates table
 * 7. Try.php displays the new certificate in "Awaiting Approval" section
 * 
 * VERIFICATION CHECKLIST:
 * ======================
 */

require_once 'backend/config.php';
$pdo = getPDO();

echo "<html><head><title>Certificate Data Flow Verification</title><style>
body { font-family: Courier, monospace; background: #f5f5f5; padding: 20px; }
.section { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; }
.pass { color: #28a745; font-weight: bold; }
.fail { color: #dc3545; font-weight: bold; }
.warn { color: #ff9800; font-weight: bold; }
code { background: #eee; padding: 2px 5px; border-radius: 3px; }
button { padding: 10px 15px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
button:hover { background: #0056b3; }
</style></head><body>";

echo "<h1>🔍 Certificate Data Flow Verification</h1>";

// 1. Check backend is accessible
echo "<div class='section'>";
echo "<h2>1. Backend Accessibility</h2>";
$testFile = __DIR__ . '/backend/exam-api.php';
if (file_exists($testFile)) {
    echo "<span class='pass'>✓ Backend file exists</span><br>";
} else {
    echo "<span class='fail'>✗ Backend file NOT FOUND</span><br>";
}

// 2. Check database tables exist
echo "</div><div class='section'>";
echo "<h2>2. Database Tables</h2>";
$tables = ['exam_attempts', 'certificates', 'exam_answers', 'exams'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table LIMIT 1");
        $count = $stmt->fetchColumn();
        echo "<span class='pass'>✓ $table</span> ({$count} records)<br>";
    } catch (Exception $e) {
        echo "<span class='fail'>✗ $table - " . $e->getMessage() . "</span><br>";
    }
}

// 3. Check active exams
echo "</div><div class='section'>";
echo "<h2>3. Active Exam Attempts</h2>";
$stmt = $pdo->query("
    SELECT status, COUNT(*) as count
    FROM exam_attempts
    GROUP BY status
    ORDER BY status
");
while ($row = $stmt->fetch()) {
    $color = ($row['status'] === 'in_progress') ? 'warn' : ($row['status'] === 'submitted' ? 'pass' : 'fail');
    echo "<span class='$color'>{$row['status']}</span>: {$row['count']} attempts<br>";
}

// 4. Get sample incomplete attempts
echo "</div><div class='section'>";
echo "<h2>4. In-Progress Exams (Not Submitted)</h2>";
$stmt = $pdo->prepare("
    SELECT ea.id, ea.user_id, ea.status, ea.score, ea.passed, 
           e.title, e.language_pair,
           COUNT(ean.id) as answers_recorded
    FROM exam_attempts ea
    LEFT JOIN exam_answers ean ON ea.id = ean.session_id
    LEFT JOIN exams e ON ea.exam_id = e.id
    WHERE ea.status = 'in_progress'
    GROUP BY ea.id
    LIMIT 10
");
$stmt->execute();

if ($stmt->rowCount() === 0) {
    echo "<span class='pass'>✓ No incomplete attempts</span> - All exams submitted!<br>";
} else {
    echo "<table border='1' style='width:100%; border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Attempt ID</th><th>User ID</th><th>Exam</th><th>Status</th><th>Score</th><th>Answers</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['title']} ({$row['language_pair']})</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['score']}</td>";
        echo "<td>{$row['answers_recorded']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 5. Check error log
echo "</div><div class='section'>";
echo "<h2>5. Backend Error Log</h2>";
$logFile = __DIR__ . '/error.log';
if (file_exists($logFile)) {
    $lines = array_slice(file($logFile), -50);
    $submissions = array_filter($lines, function($l) { return strpos($l, 'EXAM-SUBMIT') !== false; });
    if ($submissions) {
        echo "<span class='pass'>✓ Found submission logs:</span><br>";
        foreach (array_slice($submissions, -10) as $line) {
            echo "<code>" . htmlspecialchars($line) . "</code><br>";
        }
    } else {
        echo "<span class='warn'>⚠ No submission logs found</span><br>";
        echo "This means submitLessonExam() has not been called yet<br>";
    }
} else {
    echo "No error.log file. Enable error logging to diagnose issues.<br>";
}

// 6. Test submission endpoint
echo "</div><div class='section'>";
echo "<h2>6. Test Submission Endpoint</h2>";
echo "Click button below to simulate exam submission:<br>";
echo "<button onclick=\"testSubmission()\">Test POST to /backend/exam-api.php</button>";

// 7. Instructions
echo "</div><div class='section'>";
echo "<h2>7. What You Should Do Now</h2>";
echo "<ol>";
echo "<li><strong>Take an exam normally:</strong> Go to an exam page and complete it end-to-end</li>";
echo "<li><strong>Answer all questions:</strong> Don't skip any - reach the final question</li>";
echo "<li><strong>Complete:</strong> Finish the exam (time out or click submit)</li>";
echo "<li><strong>Check results:</strong> You should see a completion message</li>";
echo "<li><strong>Verify in database:</strong> Run this script again to see updated status</li>";
echo "<li><strong>Check try.php:</strong> Go to try.php and open Certificates tab</li>";
echo "</ol>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>8. If Submission Still Fails</h2>";
echo "<ol>";
echo "<li>Open browser Developer Tools (F12)</li>";
echo "<li>Go to Console tab</li>";
echo "<li>Take an exam and complete it</li>";
echo "<li>Look for error messages containing 'submit' or 'POST'</li>";
echo "<li>Share those errors for debugging</li>";
echo "</ol>";
echo "</div>";

// Add JavaScript for test
echo "<script>
function testSubmission() {
    const payload = {
        attempt_id: 1,
        score: 75,
        total_questions: 20,
        correct_answers: 15,
        time_taken_seconds: 1800
    };
    
    fetch('/backend/exam-api.php?action=submit_lessonexam', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(d => {
        alert('Response: ' + (d.success ? 'SUCCESS' : 'ERROR: ' + d.message));
        console.log(d);
    })
    .catch(e => alert('Network error: ' + e.message));
}
</script>";

echo "</body></html>";
?>
