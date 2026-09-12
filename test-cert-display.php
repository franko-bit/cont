<?php
// Include data first
require_once 'backend/config.php';
$pdo = getPDO();
$cr = $pdo->prepare(
    'SELECT c.id, c.certificate_id, c.student_name, c.score, c.status, e.title AS exam_title
     FROM certificates c
     LEFT JOIN exams e ON e.id = c.exam_id
     WHERE c.status IN ("pending", "approved")
     ORDER BY c.issued_at DESC
     LIMIT 20'
);
$cr->execute();
$certificateRows = $cr->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Certificate Display Test</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .pending { border: 1px solid red; padding: 10px; margin: 10px 0; }
        .approved { border: 1px solid green; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Certificate Display Test</h1>
    <div id="output"></div>
    
    <script>
        // Test data from PHP query
        const certificateRows = <?= json_encode($certificateRows ?? []) ?>;
        const pending = certificateRows.filter(c => c.status === 'pending');
        const approved = certificateRows.filter(c => c.status === 'approved');
        
        let html = '<h2>Certificate Data Test</h2>';
        html += '<p>Total: ' + certificateRows.length + '</p>';
        html += '<p>Pending: ' + pending.length + '</p>';
        html += '<p>Approved: ' + approved.length + '</p>';
        
        if (pending.length > 0) {
            html += '<h3>Pending Certificates</h3>';
            pending.forEach(c => {
                html += '<div class="pending">';
                html += '<strong>' + (c.student_name || 'N/A') + '</strong><br>';
                html += 'Exam: ' + (c.exam_title || 'N/A') + '<br>';
                html += 'Score: ' + c.score + '%<br>';
                html += 'ID: ' + c.certificate_id;
                html += '</div>';
            });
        }
        
        if (approved.length > 0) {
            html += '<h3>Approved Certificates</h3>';
            approved.forEach(c => {
                html += '<div class="approved">';
                html += '<strong>' + (c.student_name || 'N/A') + '</strong><br>';
                html += 'Score: ' + c.score + '%';
                html += '</div>';
            });
        }
        
        document.getElementById('output').innerHTML = html;
    </script>
</body>
</html>