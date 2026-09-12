<?php
// backend/exam-api.php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// === FATAL ERROR HANDLER ===
function handleFatalError() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $error['message'],
            'type' => 'fatal_error',
            'file' => $error['file'],
            'line' => $error['line']
        ]);
        error_log('[EXAM-API-FATAL] ' . $error['type'] . ': ' . $error['message'] . ' at ' . $error['file'] . ':' . $error['line']);
        exit;
    }
}
register_shutdown_function('handleFatalError');

// === EXCEPTION HANDLER ===
set_exception_handler(function($e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'type' => 'exception'
    ]);
    error_log('[EXAM-API-EXCEPTION] ' . get_class($e) . ': ' . $e->getMessage());
    exit;
});

require_once 'config.php';
require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

switch ($action) {
    case 'get_exam_readiness':
        getExamReadiness($pdo, $user_id);
        break;
    case 'start_exam_verification':
        startExamVerification($pdo, $user_id);
        break;
    case 'get_exam_attempt':
        getExamAttempt($pdo, $user_id);
        break;
    case 'save_answer':
        saveAnswer($pdo, $user_id);
        break;
    case 'submit_exam':
        submitExam($pdo, $user_id);
        break;
    case 'submit_lessonexam':
        submitLessonExam($pdo, $user_id);
        break;
    case 'get_results':
        getResults($pdo, $user_id);
        break;
    case 'get_certificates':
        getCertificates($pdo, $user_id);
        break;
    case 'get_available_exams':
        getAvailableExams($pdo, $user_id);
        break;
    case 'cancel_attempt':
        cancelAttempt($pdo, $user_id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function ensureCertificateColumns($pdo) {
    $columns = [
        'exam_id' => 'INT NULL',
        'session_id' => 'INT NULL',
        'overall_score' => 'DECIMAL(5,2) DEFAULT 0',
        'total_questions' => 'INT DEFAULT 0',
        'correct_answers' => 'INT DEFAULT 0',
        'percentage' => 'DECIMAL(5,2) DEFAULT 0',
        'passed' => 'TINYINT(1) DEFAULT 0',
        'passed_score' => 'INT DEFAULT 0',
        'strengths' => 'JSON NULL',
        'weaknesses' => 'JSON NULL',
        'recommendations' => 'JSON NULL',
        'graded_at' => 'TIMESTAMP NULL',
        'status' => 'VARCHAR(20) DEFAULT "pending"'
    ];

    foreach ($columns as $name => $definition) {
        $col = $pdo->query("SHOW COLUMNS FROM certificates LIKE '$name'")->fetch();
        if (!$col) {
            $pdo->exec("ALTER TABLE certificates ADD COLUMN $name $definition");
        }
    }

    $idx = $pdo->query("SHOW INDEX FROM certificates WHERE Key_name='ux_certificates_attempt'")->fetch();
    if (!$idx) {
        $pdo->exec("ALTER TABLE certificates ADD UNIQUE INDEX ux_certificates_attempt (exam_attempt_id)");
    }
}

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
    
    // First check if there's an institution_candidate for this exam_attempt (for popcorn applicants)
    if (!empty($attempt['id'])) {
        $stmt = $pdo->prepare('SELECT full_name FROM institution_candidates WHERE exam_attempt_id = ? LIMIT 1');
        $stmt->execute([$attempt['id']]);
        $row = $stmt->fetch();
        if ($row && !empty($row['full_name'])) {
            return $row['full_name'];
        }
    }
    
    // Fall back to user full_name
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

function saveExamResultsRecord($pdo, $attempt, $data) {
    try {
        ensureCertificateColumns($pdo);

        $certificateId = $attempt['certificate_id'] ?? null;
        // Auto-approve certificates if exam is passed, auto-decline if failed
        $status = $data['passed'] ? 'approved' : 'declined';
        $studentName = getStudentNameForAttempt($pdo, $attempt);
        $languagePair = buildCertificateLanguagePair($attempt);
        $issuedAt = date('Y-m-d H:i:s');

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

        $stmt->execute([
            $certificateId,
            $studentName,
            $attempt['user_id'],
            $attempt['id'],
            $attempt['exam_id'],
            $attempt['id'],
            $languagePair,
            $attempt['level_number'] ?? 0,
            (float)$data['score'],
            $attempt['passing_score'] ?? 0,
            (float)($data['percentage'] ?? $data['score']),
            (int)($data['total_questions'] ?? 0),
            (int)($data['correct_answers'] ?? 0),
            (float)($data['percentage'] ?? $data['score']),
            $data['passed'] ? 1 : 0,
            $attempt['passing_score'] ?? 0,
            $issuedAt,
            $status
        ]);
        return true;
    } catch (Exception $e) {
        error_log("exam-api.php: failed to save exam_results for attempt {$attempt['id']}: " . $e->getMessage());
        return false;
    }
}

/**
 * Get exam readiness status for user
 */
function getExamReadiness($pdo, $user_id) {
    $active_pair = $_GET['pair'] ?? $_SESSION['active_pair'] ?? 'en-rw';
    $active_level = isset($_GET['level']) ? (int)$_GET['level'] : ($_SESSION['active_level'] ?? 1);
    
    // Calculate user readiness based on lessons completed and XP
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT CONCAT(level_number, '/', topic_file)) as lessons_completed,
            COALESCE(SUM(xp_earned), 0) as total_xp,
            COALESCE(AVG(accuracy), 0) as avg_accuracy
        FROM user_progress
        WHERE user_id = ? AND completed = 1
    ");
    $stmt->execute([$user_id]);
    $progress = $stmt->fetch();
    
    // Get total lessons for this level
    $pair_folder_map = [
        'en-rw' => 'EN-TO-RW',
        'rw-en' => 'RW-TO-EN',
        'fr-rw' => 'FR-TO-RW',
        'rw-fr' => 'RW-TO-FR',
        'en-sw' => 'EN-TO-SW',
        'sw-en' => 'SW-TO-EN',
        'fr-sw' => 'FR-TO-SW',
        'sw-fr' => 'SW-TO-FR',
    ];
    
    $folder = $pair_folder_map[$active_pair] ?? (strpos($active_pair, 'en') === 0 ? 'EN-TO-RW' : 'RW-TO-EN');
    
    $content_dir = "../content/{$folder}/level{$active_level}/";
    $total_topics = 0;
    if (is_dir($content_dir)) {
        $total_topics = count(array_filter(scandir($content_dir), fn($f) => pathinfo($f, PATHINFO_EXTENSION) === 'yaml'));
    }
    
    // Calculate readiness percentage
    $lessons_progress = $total_topics > 0 ? ($progress['lessons_completed'] / $total_topics) * 100 : 0;
    $xp_progress = min(100, ($progress['total_xp'] / ($active_level * 500)) * 100); // ~500 XP per level
    $accuracy_progress = $progress['avg_accuracy'];
    
    $is_ready = $lessons_progress >= 80 && $xp_progress >= 60 && $accuracy_progress >= 50;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'lessons_completed' => $progress['lessons_completed'],
            'total_xp' => $progress['total_xp'],
            'avg_accuracy' => round($progress['avg_accuracy'], 1),
            'lessons_progress' => round($lessons_progress, 1),
            'xp_progress' => round($xp_progress, 1),
            'accuracy_progress' => round($accuracy_progress, 1),
            'is_ready' => $is_ready,
            'can_take_exam' => $lessons_progress >= 50
        ]
    ]);
}

/**
 * Start exam verification process
 */
function startExamVerification($pdo, $user_id) {
    $exam_id = (int)($_GET['exam_id'] ?? $_POST['exam_id'] ?? 0);
    
    if ($exam_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid exam ID']);
        return;
    }
    
    // Check if exam exists
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ? AND is_active = 1");
    $stmt->execute([$exam_id]);
    $exam = $stmt->fetch();
    
    if (!$exam) {
        echo json_encode(['success' => false, 'message' => 'Exam not found']);
        return;
    }
    
    // Check for existing pending/in-progress attempt
    $stmt = $pdo->prepare("
        SELECT * FROM exam_attempts 
        WHERE user_id = ? AND exam_id = ? AND status IN ('pending', 'verification', 'in_progress')
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$user_id, $exam_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Resume existing attempt
        echo json_encode([
            'success' => true,
            'data' => [
                'attempt_id' => $existing['id'],
                'status' => $existing['status'],
                'verification_status' => $existing['verification_status'],
                'resume' => true
            ]
        ]);
        return;
    }
    
    // Create new attempt
    $stmt = $pdo->prepare("
        INSERT INTO exam_attempts (user_id, exam_id, status, verification_status, ip_address, user_agent)
        VALUES (?, ?, 'verification', 'pending', ?, ?)
    ");
    $stmt->execute([
        $user_id, 
        $exam_id, 
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    $attempt_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'attempt_id' => $attempt_id,
            'status' => 'verification',
            'exam' => $exam
        ]
    ]);
}

function findTesseractBinary() {
    $candidates = ['tesseract', 'tesseract.exe'];
    foreach ($candidates as $cmd) {
        $check = stripos(PHP_OS_FAMILY, 'Windows') === 0 ? "where $cmd" : "command -v $cmd";
        $output = null;
        $status = null;
        exec($check . ' 2>&1', $output, $status);
        if ($status === 0 && !empty($output[0])) {
            return trim($output[0]);
        }
    }
    return null;
}

function runTesseractOcrFile($filePath) {
    $bin = findTesseractBinary();
    if (!$bin || !file_exists($filePath)) {
        return ['success' => false, 'text' => ''];
    }

    $outputBase = tempnam(sys_get_temp_dir(), 'tess_');
    if ($outputBase === false) {
        return ['success' => false, 'text' => ''];
    }
    unlink($outputBase);

    $cmd = sprintf('%s %s %s -l eng --psm 6', escapeshellcmd($bin), escapeshellarg($filePath), escapeshellarg($outputBase));
    exec($cmd . ' 2>&1', $output, $status);

    $textFile = $outputBase . '.txt';
    $text = '';
    if ($status === 0 && file_exists($textFile)) {
        $text = file_get_contents($textFile);
        unlink($textFile);
    }

    return ['success' => $status === 0 && $text !== false, 'text' => $text ?: ''];
}

function parseIdFields($text) {
    $normalized = preg_replace([
        '/\r/',
        '/[\x{2018}\x{2019}\x{201C}\x{201D}]/u',
        '/[^\x00-\x7F\n]+/u',
        '/_/'
    ], ["\n", "'", ' ', ' '], $text);
    $normalized = trim($normalized);
    $lines = preg_split('/[\r\n]+/', $normalized);

    $idNumber = '';
    $fullName = '';

    foreach ($lines as $rawLine) {
        $line = trim(preg_replace('/[^A-Za-z0-9]/', ' ', $rawLine));
        $line = preg_replace('/\s+/', ' ', $line);
        if (!$idNumber) {
            if (preg_match('/(?:id\s*(?:no|number)?\s*[:\-]?\s*)([A-Z0-9]{5,})/i', $rawLine, $m)
                || preg_match('/\b([A-Z0-9]{6,})\b/', $line, $m)) {
                $idNumber = $m[1];
            }
        }
        if (!$fullName && preg_match('/name/i', $rawLine) && preg_match('/[A-Za-z]+\s+[A-Za-z]+/', $line)) {
            $fullName = $line;
        }
    }

    if (!$fullName) {
        foreach ($lines as $rawLine) {
            $line = trim(preg_replace('/[^A-Za-z ]/', ' ', $rawLine));
            $line = preg_replace('/\s+/', ' ', $line);
            if (preg_match('/[A-Za-z]{3,}/', $line) && str_word_count($line) >= 2
                && !preg_match('/\b(national|passport|republic|ministry|application|issue|birth|date|sex|male|female)\b/i', $line)) {
                $fullName = $line;
                break;
            }
        }
    }

    return [
        'full_name' => $fullName ?: 'Unknown',
        'id_number' => $idNumber ?: 'Not found',
        'raw_text' => $normalized
    ];
}

/**
 * Get exam attempt details
 */
function getExamAttempt($pdo, $user_id) {
    $attempt_id = (int)($_GET['attempt_id'] ?? 0);
    
    $stmt = $pdo->prepare("
        SELECT ea.*, e.title, e.description, e.exam_language, e.duration_minutes, e.passing_score, e.total_questions
        FROM exam_attempts ea
        JOIN exams e ON ea.exam_id = e.id
        WHERE ea.id = ? AND ea.user_id = ?
    ");
    $stmt->execute([$attempt_id, $user_id]);
    $attempt = $stmt->fetch();
    
    if (!$attempt) {
        echo json_encode(['success' => false, 'message' => 'Attempt not found']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $attempt
    ]);
}

/**
 * Save answer during exam
 */
function saveAnswer($pdo, $user_id) {
    $attempt_id = (int)($_POST['attempt_id'] ?? 0);
    $question_id = (int)($_POST['question_id'] ?? 0);
    $answer_text = $_POST['answer_text'] ?? '';
    $answer_audio = $_POST['answer_audio'] ?? null;
    $time_spent = (int)($_POST['time_spent'] ?? 0);
    
    if ($attempt_id <= 0 || $question_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    // Verify attempt is pending, verification, or in progress
    $stmt = $pdo->prepare("
        SELECT * FROM exam_attempts 
        WHERE id = ? AND user_id = ? AND status IN ('pending', 'verification', 'in_progress')
    ");
    $stmt->execute([$attempt_id, $user_id]);
    $attempt = $stmt->fetch();
    
    if (!$attempt) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired attempt']);
        return;
    }
    
    // Check if answer already exists
    $stmt = $pdo->prepare("SELECT id FROM exam_answers WHERE session_id = ? AND question_id = ?");
    $stmt->execute([$attempt_id, $question_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing answer
        $stmt = $pdo->prepare(" 
            UPDATE exam_answers 
            SET user_answer = ?
            WHERE id = ?
        ");
        $stmt->execute([$answer_text, $existing['id']]);
    } else {
        // Insert new answer
        $stmt = $pdo->prepare(" 
            INSERT INTO exam_answers (session_id, question_id, user_answer)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$attempt_id, $question_id, $answer_text]);
    }
    
    // Update time remaining
    $stmt = $pdo->prepare("
        UPDATE exam_attempts 
        SET time_remaining_seconds = time_remaining_seconds - ?
        WHERE id = ?
    ");
    $stmt->execute([$time_spent, $attempt_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Answer saved',
        'data' => [
            'question_id' => $question_id,
            'saved' => true
        ]
    ]);
}

/**
 * Submit exam
 */
function submitExam($pdo, $user_id) {
    $attempt_id = (int)($_POST['attempt_id'] ?? 0);
    
    if ($attempt_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid attempt ID']);
        return;
    }
    
    // Verify attempt
    $stmt = $pdo->prepare("
        SELECT ea.*, e.language_pair, e.level_number, e.passing_score, e.title, e.exam_language
        FROM exam_attempts ea
        JOIN exams e ON ea.exam_id = e.id
        WHERE ea.id = ? AND ea.user_id = ? AND ea.status = 'in_progress'
    ");
    $stmt->execute([$attempt_id, $user_id]);
    $attempt = $stmt->fetch();
    
    if (!$attempt) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired attempt']);
        return;
    }
    
    // Grade the exam
    gradeExam($pdo, $attempt);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'attempt_id' => $attempt_id,
            'status' => 'submitted',
            'redirect' => 'results'
        ]
    ]);
}

function submitLessonExam($pdo, $user_id) {
    error_log("[EXAM-SUBMIT] User $user_id submitting lessonexam");
    
    $data = json_decode(file_get_contents('php://input'), true);
    $attempt_id = (int)($data['attempt_id'] ?? $_POST['attempt_id'] ?? 0);
    $score = isset($data['score']) ? floatval($data['score']) : 0;
    $correct_answers = isset($data['correct_answers']) ? (int)$data['correct_answers'] : 0;
    $total_questions = isset($data['total_questions']) ? (int)$data['total_questions'] : 0;
    $time_taken_seconds = isset($data['time_taken_seconds']) ? (int)$data['time_taken_seconds'] : 0;

    error_log("[EXAM-SUBMIT] attempt_id=$attempt_id, score=$score, correct=$correct_answers/$total_questions");

    if ($attempt_id <= 0) {
        error_log("[EXAM-SUBMIT] ERROR: Invalid attempt ID $attempt_id");
        echo json_encode(['success' => false, 'message' => 'Invalid attempt ID']);
        return;
    }

    $stmt = $pdo->prepare("SELECT ea.*, e.language_pair, e.level_number, e.passing_score, e.title, e.exam_language FROM exam_attempts ea JOIN exams e ON ea.exam_id = e.id WHERE ea.id = ? AND ea.user_id = ? AND ea.status IN ('pending', 'verification', 'in_progress')");
    $stmt->execute([$attempt_id, $user_id]);
    $attempt = $stmt->fetch();

    if (!$attempt) {
        error_log("[EXAM-SUBMIT] ERROR: Attempt $attempt_id not found for user $user_id");
        $stmt = $pdo->prepare("SELECT ea.*, e.language_pair, e.level_number, e.passing_score, e.title, e.exam_language FROM exam_attempts ea JOIN exams e ON ea.exam_id = e.id WHERE ea.id = ? AND ea.user_id = ? AND ea.status = 'submitted'");
        $stmt->execute([$attempt_id, $user_id]);
        $existing = $stmt->fetch();
        if ($existing) {
            error_log("[EXAM-SUBMIT] Attempt already submitted with score " . $existing['score']);
            echo json_encode(['success' => true, 'data' => [
                'attempt_id' => $attempt_id,
                'score' => $existing['score'],
                'passed' => $existing['passed'],
                'certificate_id' => $existing['certificate_id'] ?? null
            ]]);
            return;
        }

        error_log("[EXAM-SUBMIT] Invalid or expired attempt");
        echo json_encode(['success' => false, 'message' => 'Invalid or expired attempt']);
        return;
    }

    $score = max(0, min(100, $score));
    $passed = $score >= $attempt['passing_score'];

    error_log("[EXAM-SUBMIT] Final score=$score, passed=$passed (passing_score=" . $attempt['passing_score'] . ")");

    $certificate_id = null;
    if ($passed) {
        $certificate_id = 'CERT-' . strtoupper(str_replace('-', '', $attempt['language_pair'])) . '-' . date('Y') . '-' . str_pad($attempt_id, 5, '0', STR_PAD_LEFT);
    }
    
    error_log("[EXAM-SUBMIT] Updating exam_attempts status to 'submitted', certificate_id=$certificate_id");
    $stmt = $pdo->prepare("UPDATE exam_attempts SET status = 'submitted', submitted_at = NOW(), graded_at = NOW(), score = ?, passed = ?, certificate_id = ? WHERE id = ?");
    $stmt->execute([$score, $passed ? 1 : 0, $certificate_id, $attempt_id]);
    error_log("[EXAM-SUBMIT] exam_attempts updated, rows affected: " . $stmt->rowCount());
    
    $attempt['certificate_id'] = $certificate_id;

    linkInstitutionCandidateToAttempt($pdo, $attempt, $score, $passed, $time_taken_seconds);

    $saved = saveExamResultsRecord($pdo, $attempt, [
        'score' => $score,
        'total_questions' => $total_questions,
        'correct_answers' => $correct_answers,
        'percentage' => $score,
        'passed' => $passed,
        'passed_score' => $attempt['passing_score'],
        'strengths' => [],
        'weaknesses' => [],
        'recommendations' => []
    ]);
    error_log("[EXAM-SUBMIT] Certificate saved: " . ($saved ? "YES" : "NO"));
    
    if (!$saved) {
        error_log("[EXAM-SUBMIT] ERROR: exam_results save failed for lesson attempt {$attempt_id}");
    }

    $stmt = $pdo->prepare('SELECT id FROM popcorn_applications WHERE attempt_id = ? LIMIT 1');
    $stmt->execute([$attempt_id]);
    $application = $stmt->fetch();
    if ($application) {
        $updateApp = $pdo->prepare('UPDATE popcorn_applications SET status = ?, updated_at = NOW() WHERE id = ?');
        $updateApp->execute(['completed', $application['id']]);
    }

    error_log("[EXAM-SUBMIT] SUCCESS: Certificate $certificate_id created, response sent");
    echo json_encode([
        'success' => true,
        'data' => [
            'attempt_id' => $attempt_id,
            'score' => $score,
            'passed' => $passed,
            'certificate_id' => $certificate_id
        ]
    ]);
}

/**
 * Grade exam and calculate results
 */
function gradeExam($pdo, $attempt) {
    $attempt_id = $attempt['id'];
    
    // Get all questions and answers
    $stmt = $pdo->prepare("
        SELECT q.id, q.question_type, q.correct_answer, q.points, a.user_answer, a.is_correct
        FROM exam_questions q
        LEFT JOIN exam_answers a ON q.id = a.question_id AND a.session_id = ?
        WHERE q.exam_id = ?
    ");
    $stmt->execute([$attempt_id, $attempt['exam_id']]);
    $questions = $stmt->fetchAll();
    
    $scores = [
        'reading' => ['earned' => 0, 'max' => 0],
        'listening' => ['earned' => 0, 'max' => 0],
        'translation' => ['earned' => 0, 'max' => 0],
        'speaking' => ['earned' => 0, 'max' => 0],
        'writing' => ['earned' => 0, 'max' => 0]
    ];
    
    $total_earned = 0;
    $total_max = 0;
    $correctCount = 0;
    
    foreach ($questions as $q) {
        $type = $q['question_type'];
        $max_points = $q['points'];
        $total_max += $max_points;
        $scores[$type]['max'] += $max_points;
        
        if (!empty($q['user_answer'])) {
            // For multiple choice and reading, do exact match
            // For translation, listening, speaking, writing - require manual grading or partial match
            $is_correct = false;
            
            if (in_array($type, ['reading', 'listening', 'translation'])) {
                // Case-insensitive comparison
                $correct = strtolower(trim($q['correct_answer']));
                $answer = strtolower(trim($q['user_answer'] ?? ''));
                $is_correct = $correct === $answer;
                
                // For translation, allow partial match (contains key words)
                if ($type === 'translation' && !$is_correct) {
                    $correct_words = explode(' ', $correct);
                    $answer_words = explode(' ', $answer);
                    $match_count = count(array_intersect($correct_words, $answer_words));
                    $is_correct = $match_count >= count($correct_words) * 0.6; // 60% match
                }
            }
            // Speaking and writing would require manual grading or AI evaluation
            
            if ($is_correct) {
                $earned = $max_points;
                $correctCount++;
            } else {
                // Partial credit for showing effort
                $earned = in_array($type, ['speaking', 'writing']) ? ($max_points * 0.3) : 0;
            }
            
            $scores[$type]['earned'] += $earned;
            $total_earned += $earned;
            
            // Update answer record
            $stmt = $pdo->prepare("
                UPDATE exam_answers 
                SET is_correct = ?
                WHERE session_id = ? AND question_id = ?
            ");
            $stmt->execute([$is_correct, $attempt_id, $q['id']]);
        }
    }
    
    // Calculate overall score percentage
    $overall_score = $total_max > 0 ? ($total_earned / $total_max) * 100 : 0;
    $passed = $overall_score >= $attempt['passing_score'];
    
    // Determine strengths and weaknesses
    $strengths = [];
    $weaknesses = [];
    foreach ($scores as $type => $data) {
        if ($data['max'] > 0) {
            $pct = ($data['earned'] / $data['max']) * 100;
            if ($pct >= 70) {
                $strengths[] = ucfirst($type);
            } elseif ($pct < 50) {
                $weaknesses[] = ucfirst($type);
            }
        }
    }
    
    // Generate certificate ID if passed
    $certificate_id = null;
    if ($passed) {
        $cert_code = 'CERT-' . strtoupper(str_replace('-', '', $attempt['language_pair'])) . '-' . date('Y') . '-' . str_pad($attempt_id, 5, '0', STR_PAD_LEFT);
        $certificate_id = $cert_code;
    }
    
    // Update attempt
    $stmt = $pdo->prepare("
        UPDATE exam_attempts 
        SET status = 'submitted',
            submitted_at = NOW(),
            score = ?,
            passed = ?,
            certificate_id = ?
        WHERE id = ?
    ");
    $stmt->execute([$overall_score, $passed, $certificate_id, $attempt_id]);
    $attempt['certificate_id'] = $certificate_id;
    
    saveExamResultsRecord($pdo, $attempt, [
        'score' => round($overall_score, 2),
        'total_questions' => count($questions),
        'correct_answers' => $correctCount,
        'percentage' => round($overall_score, 2),
        'passed' => $passed,
        'passed_score' => $attempt['passing_score'],
        'strengths' => $strengths,
        'weaknesses' => $weaknesses,
        'recommendations' => ['Continue practicing ' . implode(' and ', $weaknesses)]
    ]);

    linkInstitutionCandidateToAttempt($pdo, $attempt, $overall_score, $passed);
}

function linkInstitutionCandidateToAttempt($pdo, $attempt, $score, $passed, $timeTakenSeconds = null) {
    if (empty($attempt['id'])) {
        return false;
    }

    // First check if there's already an institution_candidate linked to this exam_attempt (popcorn applicants)
    $stmt = $pdo->prepare('SELECT id FROM institution_candidates WHERE exam_attempt_id = ? LIMIT 1');
    $stmt->execute([$attempt['id']]);
    $candidate = $stmt->fetch();
    
    // If not found and we have user_id and exam_id, try to find by matching criteria
    if (!$candidate && !empty($attempt['user_id']) && !empty($attempt['exam_id'])) {
        $stmt = $pdo->prepare(
            'SELECT ic.id
             FROM institution_candidates ic
             JOIN institution_assessments ia ON ic.assessment_id = ia.id
             JOIN exams e ON e.language_pair = ia.language_pair AND e.level_number = ia.exam_level
             WHERE ic.user_id = ? AND (ic.exam_attempt_id IS NULL OR ic.exam_attempt_id = 0) AND e.id = ?
             ORDER BY ic.created_at DESC
             LIMIT 1'
        );
        $stmt->execute([$attempt['user_id'], $attempt['exam_id']]);
        $candidate = $stmt->fetch();
    }
    
    if (!$candidate) {
        error_log("[LINK-IC] No institution_candidate found for attempt " . $attempt['id']);
        return false;
    }

    $status = $passed ? 'completed' : 'failed';
    $fields = ['exam_attempt_id = ?', 'status = ?', 'score = ?', 'exam_status = ?', 'exam_score = ?', 'exam_completed_at = NOW()', 'updated_at = NOW()'];
    $params = [$attempt['id'], $status, $score, 'submitted', (int)round($score)];
    if ($timeTakenSeconds !== null) {
        $fields[] = 'time_taken_seconds = ?';
        $params[] = $timeTakenSeconds;
    }
    $params[] = $candidate['id'];

    error_log("[LINK-IC] Updating institution_candidates id " . $candidate['id'] . " with score=$score, status=$status");
    $stmt = $pdo->prepare('UPDATE institution_candidates SET ' . implode(', ', $fields) . ' WHERE id = ?');
    $stmt->execute($params);
    error_log("[LINK-IC] Updated, rows affected: " . $stmt->rowCount());
    return true;
}

/**
 * Get exam results
 */
function getResults($pdo, $user_id) {
    $attempt_id = (int)($_GET['attempt_id'] ?? 0);
    
    $stmt = $pdo->prepare("
        SELECT ea.*, e.title, e.language_pair, e.level_number, e.exam_language, e.passing_score
        FROM exam_attempts ea
        JOIN exams e ON ea.exam_id = e.id
        WHERE ea.id = ? AND ea.user_id = ? AND ea.status = 'submitted'
    ");
    $stmt->execute([$attempt_id, $user_id]);
    $attempt = $stmt->fetch();
    
    if (!$attempt) {
        echo json_encode(['success' => false, 'message' => 'Results not found']);
        return;
    }
    
    // Get detailed results from certificates
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE exam_attempt_id = ? LIMIT 1");
    $stmt->execute([$attempt_id]);
    $results = $stmt->fetch();
    
    // Get certificate if passed and approved/pending
    $certificate = null;
    if (!empty($results['certificate_id'])) {
        $certificate = $results;
    } elseif ($attempt['passed'] && $attempt['certificate_id']) {
        $stmt = $pdo->prepare("SELECT * FROM certificates WHERE certificate_id = ?");
        $stmt->execute([$attempt['certificate_id']]);
        $certificate = $stmt->fetch();
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'attempt' => $attempt,
            'results' => $results,
            'certificate' => $certificate
        ]
    ]);
}

/**
 * Get user certificates
 */
function getCertificates($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE user_id = ? AND status IN ('pending', 'approved') ORDER BY issued_at DESC");
    $stmt->execute([$user_id]);
    $certificates = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $certificates
    ]);
}

/**
 * Get available exams for user
 */
function getAvailableExams($pdo, $user_id) {
    $active_pair = $_GET['pair'] ?? $_SESSION['active_pair'] ?? 'en-rw';
    $active_level = isset($_GET['level']) ? (int)$_GET['level'] : ($_SESSION['active_level'] ?? 1);
    
    // Get user's exam attempts
    $stmt = $pdo->prepare("
        SELECT exam_id, MAX(score) as best_score, COUNT(*) as attempts, MAX(passed) as passed
        FROM exam_attempts
        WHERE user_id = ?
        GROUP BY exam_id
    ");
    $stmt->execute([$user_id]);
    $attempt_history = [];
    foreach ($stmt->fetchAll() as $row) {
        $attempt_history[$row['exam_id']] = $row;
    }
    
    // Get exams for current pair and level
    $stmt = $pdo->prepare("
        SELECT * FROM exams 
        WHERE language_pair = ? AND level_number = ? AND is_active = 1
    ");
    $stmt->execute([$active_pair, $active_level]);
    $exams = $stmt->fetchAll();
    
    // Add attempt info to each exam
    foreach ($exams as &$exam) {
        if (isset($attempt_history[$exam['id']])) {
            $exam['best_score'] = $attempt_history[$exam['id']]['best_score'];
            $exam['attempts'] = $attempt_history[$exam['id']]['attempts'];
            $exam['passed'] = (bool)$attempt_history[$exam['id']]['passed'];
        } else {
            $exam['best_score'] = null;
            $exam['attempts'] = 0;
            $exam['passed'] = false;
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $exams
    ]);
}

/**
 * Cancel exam attempt
 */
function cancelAttempt($pdo, $user_id) {
    $attempt_id = (int)($_POST['attempt_id'] ?? 0);
    
    $stmt = $pdo->prepare("
        UPDATE exam_attempts 
        SET status = 'cancelled'
        WHERE id = ? AND user_id = ? AND status IN ('pending', 'verification', 'in_progress')
    ");
    $stmt->execute([$attempt_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Attempt cancelled']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cannot cancel this attempt']);
    }
}
