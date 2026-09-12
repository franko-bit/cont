<?php
require_once 'backend/config.php';

// Copy helper functions here to avoid session checks
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

$stmt = $pdo->prepare("SELECT ea.*, e.language_pair, e.level_number, e.passing_score, e.title, e.exam_language FROM exam_attempts ea JOIN exams e ON ea.exam_id = e.id WHERE ea.id = 33");
$stmt->execute();
$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Attempt data:\n";
echo json_encode($attempt, JSON_PRETTY_PRINT) . "\n\n";

$studentName = getStudentNameForAttempt($pdo, $attempt);
$languagePair = buildCertificateLanguagePair($attempt);

echo "Student Name: '$studentName'\n";
echo "Language Pair: '$languagePair'\n";

$examType = deriveExamTypeLabel($attempt);
echo "Exam Type: '$examType'\n";

// Check user
$stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE id = ?");
$stmt->execute([$attempt['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\nUser data:\n";
echo json_encode($user, JSON_PRETTY_PRINT) . "\n";

// Try the insert manually
echo "\n\nAttempting manual insert...\n";

$certificateId = $attempt['certificate_id'] ?? null;
$status = 0 ? 'pending' : 'failed'; // passed = 0
$strengths = json_encode([]);
$weaknesses = json_encode([]);
$recommendations = json_encode([]);
$studentName = getStudentNameForAttempt($pdo, $attempt);
$languagePair = buildCertificateLanguagePair($attempt);
$issuedAt = date('Y-m-d H:i:s');

echo "Values to insert:\n";
echo "  certificateId: $certificateId\n";
echo "  studentName: $studentName\n";
echo "  user_id: " . $attempt['user_id'] . "\n";
echo "  exam_attempt_id: " . $attempt['id'] . "\n";
echo "  exam_id: " . $attempt['exam_id'] . "\n";
echo "  session_id: " . $attempt['id'] . "\n";
echo "  language_pair: $languagePair\n";
echo "  level_number: " . ($attempt['level_number'] ?? 0) . "\n";
echo "  score: 50.00\n";
echo "  passing_score: " . ($attempt['passing_score'] ?? 0) . "\n";
echo "  percentage: 50.00\n";
echo "  passed: 0\n";
echo "  issuedAt: $issuedAt\n";
echo "  status: $status\n";

try {
    $stmt = $pdo->prepare("
        INSERT INTO certificates (
            certificate_id, student_name, user_id, exam_attempt_id, exam_id, session_id,
            language_pair, level_number, score, passing_score, overall_score,
            total_questions, correct_answers, percentage, passed, passed_score,
            strengths, weaknesses, recommendations, graded_at, status,
            issued_at, created_at, updated_at, expires_at, certificate_url, institution_id, assessment_id
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NULL, NULL, NULL, NULL, NULL
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
            strengths = VALUES(strengths),
            weaknesses = VALUES(weaknesses),
            recommendations = VALUES(recommendations),
            graded_at = VALUES(graded_at),
            status = VALUES(status),
            certificate_id = VALUES(certificate_id),
            issued_at = COALESCE(issued_at, VALUES(issued_at)),
            updated_at = NOW()
    ");
    
    $result = $stmt->execute([
        $certificateId,
        $studentName,
        $attempt['user_id'],
        $attempt['id'],
        $attempt['exam_id'],
        $attempt['id'],
        $languagePair,
        $attempt['level_number'] ?? 0,
        50.00,
        $attempt['passing_score'] ?? 0,
        50.00,
        0,
        0,
        50.00,
        0,
        $attempt['passing_score'] ?? 0,
        $strengths,
        $weaknesses,
        $recommendations,
        $issuedAt,
        $status
    ]);
    
    echo "\nInsert result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
    
    // Check if it was inserted
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE exam_attempt_id = 33");
    $stmt->execute();
    $cert = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nCertificate after insert:\n";
    echo json_encode($cert, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
?>
