<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed');
}

$user_id = validateSession();
$institution_id = 1;

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    sendResponse(false, 'Invalid JSON input');
}

function ensureColumn($pdo, $table, $column, $definition) {
    $escapedTable = '`' . str_replace('`', '``', $table) . '`';
    $escapedColumn = $pdo->quote($column);
    $stmt = $pdo->query("SHOW COLUMNS FROM $escapedTable LIKE $escapedColumn");
    if (!$stmt || !$stmt->fetch()) {
        $pdo->exec("ALTER TABLE $escapedTable ADD COLUMN $definition");
    }
}

ensureColumn($pdo, 'institution_assessments', 'exam_language', "VARCHAR(20)");
ensureColumn($pdo, 'institution_assessments', 'exam_content_type', "VARCHAR(50) DEFAULT 'yaml'");
ensureColumn($pdo, 'institution_assessments', 'assessment_model', "VARCHAR(30) DEFAULT 'assessment'");
ensureColumn($pdo, 'certificates', 'institution_id', "INT NULL");
ensureColumn($pdo, 'certificates', 'exam_attempt_id', "INT NULL");

$title = trim($input['title'] ?? '');
$exam_language = trim($input['exam_language'] ?? 'English');
$passing_score = isset($input['passing_score']) ? intval($input['passing_score']) : 70;
$assessment_model = trim($input['assessment_model'] ?? 'exam');

if (!$title) {
    sendResponse(false, 'Title is required');
}

if (!in_array($exam_language, ['English', 'French', 'en', 'fr'])) {
    sendResponse(false, 'Invalid language');
}

// Normalize language
$lang = strtolower($exam_language);
$lang = ($lang === 'en' || $lang === 'english') ? 'English' : 'French';

// Verify YAML file exists
$yamlFile = '';
if ($lang === 'English') {
    $yamlFile = __DIR__ . '/../content/EXAMS/ENGLISH.yaml';
} else {
    $yamlFile = __DIR__ . '/../content/EXAMS/French.yaml';
}

if (!file_exists($yamlFile)) {
    sendResponse(false, 'Exam YAML file not found for ' . $lang);
}

// Load exam info from YAML
$content = file_get_contents($yamlFile);
$totalXP = 0;
$exerciseCount = 0;

preg_match_all('/^\s*xp_reward:\s*(\d+)\s*$/mi', $content, $matches);
foreach ($matches[1] as $v) $totalXP += intval($v);

$exerciseCount = substr_count($content, '- id:');

// Parse password if provided
$exam_password = trim($input['exam_password'] ?? '');
$exam_password = !empty($exam_password) ? password_hash($exam_password, PASSWORD_BCRYPT) : null;

// Generate invite code
$inviteCode = strtoupper(bin2hex(random_bytes(3)));
$topicFile = $lang === 'English' ? 'ENGLISH.yaml' : 'French.yaml';
$pair = $lang === 'English' ? 'en-rw' : 'fr-rw';
$direction = $lang === 'English' ? 'en' : 'fr';
$inviteLink = SITE_URL . '/frontend/lesson.php?invite=' . urlencode($inviteCode) . '&topic=' . urlencode('EXAMS/' . $topicFile) . '&level=1&pair=' . urlencode($pair) . '&direction=' . urlencode($direction);

try {
    $stmt = $pdo->prepare("
        INSERT INTO institution_assessments (
            institution_id, title, passing_score, exam_language, 
            exam_content_type, assessment_model, invite_code, invite_link,
            total_xp, total_questions, exam_password, is_active, view_count, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0, NOW())
    ");
    
    $stmt->execute([
        $institution_id,
        $title,
        $passing_score,
        $lang,
        'yaml',
        $assessment_model,
        $inviteCode,
        $inviteLink,
        $totalXP,
        $exerciseCount,
        $exam_password
    ]);
    
    $examId = $pdo->lastInsertId();
    
    sendResponse(true, 'Exam created successfully', [
        'exam_id' => (int)$examId,
        'invite_code' => $inviteCode,
        'invite_link' => $inviteLink,
        'title' => $title,
        'language' => $lang,
        'total_xp' => $totalXP,
        'exercises' => $exerciseCount,
        'has_password' => !empty($exam_password)
    ]);
    
} catch (Exception $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
