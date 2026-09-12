<?php
require_once 'backend/config.php';

$pdo = getPDO();

// Test the saveExamResultsRecord function directly
function deriveExamTypeLabel($attempt) {
    $examType = '';
    if (!empty($attempt['exam_type'])) {
        $examType = trim(strtolower($attempt['exam_type']));
    }
    if (!$examType && !empty($attempt['title'])) {
        $title = strtolower($attempt['title']);
        if (strpos($title, 'academic') !== false) {
            $examType = 'academic';
        } elseif (strpos($title, 'business') !== false) {
            $examType = 'business';
        }
    }

    if ($examType === 'academic') {
        return 'Academic English';
    }
    if ($examType === 'business') {
        return 'Business English';
    }

    return '';
}

function buildCertificateLanguagePair($attempt) {
    $base = trim($attempt['language_pair'] ?? '');
    $typeLabel = deriveExamTypeLabel($attempt);
    if ($base && $typeLabel) {
        return "$base - $typeLabel";
    }
    if ($typeLabel) {
        return $typeLabel;
    }
    return $base;
}

function getStudentNameForAttempt($pdo, $attempt) {
    if (!empty($attempt['student_name'])) {
        return $attempt['student_name'];
    }
    if (!empty($attempt['user_id'])) {
        $stmt = $pdo->prepare('SELECT full_name FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$attempt['user_id']]);
        $row = $stmt->fetch();
        if ($row && !empty($row['full_name'])) {
            return $row['full_name'];
        }
    }
    return '';
}

// Get attempt 33
$stmt = $pdo->prepare("SELECT ea.*, e.language_pair, e.level_number, e.passing_score, e.title, e.exam_language FROM exam_attempts ea JOIN exams e ON ea.exam_id = e.id WHERE ea.id = 33");
$stmt->execute();
$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
    echo "Attempt 33 not found\n";
    exit;
}

echo "=== Attempt 33 ===\n";
echo "Status: " . $attempt['status'] . "\n";
echo "Score: " . $attempt['score'] . "\n";
echo "Passed: " . $attempt['passed'] . "\n\n";

// Try to insert a certificate manually using the same logic
$certificateId = 'CERT-ENRW-2026-00033';
$status = 0 ? 'pending' : 'failed'; // passed = 0
$studentName = getStudentNameForAttempt($pdo, $attempt);
$languagePair = buildCertificateLanguagePair($attempt);
$issuedAt = date('Y-m-d H:i:s');

echo "Values to insert:\n";
echo "  certificate_id: $certificateId\n";
echo "  student_name: $studentName\n";
echo "  user_id: " . $attempt['user_id'] . "\n";
echo "  exam_attempt_id: " . $attempt['id'] . "\n";
echo "  exam_id: " . $attempt['exam_id'] . "\n";
echo "  language_pair: $languagePair\n";
echo "  status: $status\n\n";

try {
    // This is the exact query from saveExamResultsRecord
    $stmt = $pdo->prepare(
        "INSERT INTO certificates (
            certificate_id, student_name, user_id, exam_attempt_id, exam_id, session_id,
            language_pair, level_number, score, passing_score, overall_score,
            total_questions, correct_answers, percentage, passed, passed_score,
            strengths, weaknesses, recommendations, graded_at, status,
            issued_at, created_at, updated_at, expires_at, certificate_url, institution_id, assessment_id
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL, ?, ?, NOW(), NOW(), NOW(), NULL, NULL, NULL, NULL
        )
        ON DUPLICATE KEY UPDATE
            student_name = VALUES(student_name),
            user_id = VALUES(user_id),
            exam_id = VALUES(exam_id),
            session_id = VALUES(session_id),
            language_pair = VALUES(language_pair),
            level_number = VALUES(level_number),
            score = VALUES(score),
            passing_score = VALUES(passing_score),
            overall_score = VALUES(overall_score),
            total_questions = VALUES(total_questions),
            correct_answers = VALUES(correct_answers),
            percentage = VALUES(percentage),
            passed = VALUES(passed),
            passed_score = VALUES(passed_score),
            graded_at = VALUES(graded_at),
            status = VALUES(status),
            certificate_id = VALUES(certificate_id),
            issued_at = COALESCE(issued_at, VALUES(issued_at)),
            updated_at = NOW()"
    );
    
    $params = [
        $certificateId,
        $studentName,
        $attempt['user_id'],
        $attempt['id'],
        $attempt['exam_id'],
        $attempt['id'],
        $languagePair,
        $attempt['level_number'] ?? 0,
        50.00, // score
        $attempt['passing_score'] ?? 0,
        50.00, // overall_score
        0, // total_questions
        0, // correct_answers
        50.00, // percentage
        0, // passed
        $attempt['passing_score'] ?? 0, // passed_score
        $issuedAt, // graded_at
        $status
    ];
    
    echo "Executing insert with " . count($params) . " parameters...\n";
    $result = $stmt->execute($params);
    
    if ($result) {
        echo "SUCCESS: Insert executed\n";
        echo "Rows affected: " . $stmt->rowCount() . "\n\n";
        
        // Check if it was actually inserted
        $check = $pdo->prepare("SELECT * FROM certificates WHERE exam_attempt_id = 33");
        $check->execute();
        $cert = $check->fetch(PDO::FETCH_ASSOC);
        if ($cert) {
            echo "Certificate found in database:\n";
            echo "  ID: " . $cert['id'] . "\n";
            echo "  Certificate ID: " . $cert['certificate_id'] . "\n";
            echo "  Student: " . $cert['student_name'] . "\n";
            echo "  Score: " . $cert['score'] . "\n";
        } else {
            echo "ERROR: Certificate not found after insert\n";
        }
    } else {
        echo "ERROR: Execute returned false\n";
        echo "Error info: " . print_r($stmt->errorInfo(), true);
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
