<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed');
}

$user_id = validateSession();
$institution_id = isset($_SESSION['institution_id']) ? intval($_SESSION['institution_id']) : 1;

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

// Ensure the institution assessment and candidate schema supports the new homework fields.
ensureColumn($pdo, 'institution_assessments', 'topic_file', "topic_file VARCHAR(255) NULL");
ensureColumn($pdo, 'institution_assessments', 'direction', "direction VARCHAR(20) NULL");
ensureColumn($pdo, 'institution_assessments', 'language_code', "language_code VARCHAR(50) NULL");
ensureColumn($pdo, 'institution_assessments', 'due_date', "due_date DATE NULL");
ensureColumn($pdo, 'institution_assessments', 'cert_min_xp', "cert_min_xp INT DEFAULT 0");
ensureColumn($pdo, 'institution_assessments', 'cert_min_pct', "cert_min_pct INT DEFAULT 0");
ensureColumn($pdo, 'institution_assessments', 'total_xp', "total_xp INT DEFAULT 0");
ensureColumn($pdo, 'institution_candidates', 'cert_status', "cert_status VARCHAR(30) NULL");
ensureColumn($pdo, 'institution_candidates', 'cert_issued_at', "cert_issued_at TIMESTAMP NULL");

$id = isset($input['id']) && $input['id'] ? intval($input['id']) : null;
$name = trim($input['name'] ?? '');
$language_pair = trim($input['language_pair'] ?? 'en-rw');
$direction = trim($input['direction'] ?? 'en');
if (!$direction && preg_match('/^[a-z]{2}/i', $language_pair, $matches)) {
    $direction = strtolower($matches[0]);
}
$exam_level = isset($input['exam_level']) && $input['exam_level'] !== null ? intval($input['exam_level']) : 1;
$topic_file = trim($input['topic_file'] ?? '');
$due_date = trim($input['due_date'] ?? '');
$total_xp = isset($input['total_xp']) ? intval($input['total_xp']) : 0;
$cert_min_xp = isset($input['cert_min_xp']) ? intval($input['cert_min_xp']) : 0;
$cert_min_pct = isset($input['cert_min_pct']) ? intval($input['cert_min_pct']) : 0;

if (!$name) {
    sendResponse(false, 'Homework title is required');
}
if (!$topic_file) {
    sendResponse(false, 'Lesson topic is required');
}

$direction = $direction ?: (explode('-', $language_pair)[0] ?? 'en');
$lesson_level = $exam_level ?: 1;
$language_code_map = [
    'en-rw:en' => 'en-to-rw',
    'en-rw:rw' => 'rw-to-en',
    'fr-rw:fr' => 'fr-to-rw',
    'fr-rw:rw' => 'rw-to-fr',
    'rw-en:rw' => 'rw-to-en',
    'rw-en:en' => 'en-to-rw',
    'rw-fr:rw' => 'rw-to-fr',
    'rw-fr:fr' => 'fr-to-rw',
    'en-sw:en' => 'en-to-sw',
    'en-sw:sw' => 'sw-to-en',
    'fr-sw:fr' => 'fr-to-sw',
    'fr-sw:sw' => 'sw-to-fr',
    'sw-en:sw' => 'sw-to-en',
    'sw-en:en' => 'en-to-sw',
    'sw-fr:sw' => 'sw-to-fr',
    'sw-fr:fr' => 'fr-to-sw',
];
$language_code = $language_code_map[$language_pair . ':' . $direction] ?? strtolower($direction) . '-to-rw';

$duration_minutes = 60;
$passing_score = 70;
$skills = '';
if ($language_pair && $exam_level) {
    try {
        $stmt = $pdo->prepare('SELECT duration_minutes, passing_score FROM exams WHERE language_pair = ? AND level_number = ? LIMIT 1');
        $stmt->execute([$language_pair, $exam_level]);
        $examRow = $stmt->fetch();
        if ($examRow) {
            $duration_minutes = (int)($examRow['duration_minutes'] ?? 60);
            $passing_score = (int)($examRow['passing_score'] ?? 70);
        }
    } catch (Exception $e) {
        // Leave defaults in place if exams table is unavailable.
    }
}

$invite_code = null;
$invite_link = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT invite_code FROM institution_assessments WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        sendResponse(false, 'Homework not found');
    }
    $invite_code = $existing['invite_code'];
} else {
    $invite_code = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '-', $name), 0, 12)) . '-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
}

$invite_link = SITE_URL . '/frontend/lesson.php?pair=' . urlencode($language_pair) . '&direction=' . urlencode($direction) . '&level=' . intval($lesson_level) . '&topic=' . urlencode($topic_file) . '&invite=' . urlencode($invite_code);

try {
    $pdo->beginTransaction();
    if ($id) {
        $stmt = $pdo->prepare("UPDATE institution_assessments SET
                title = ?,
                language_pair = ?,
                direction = ?,
                language_code = ?,
                exam_level = ?,
                topic_file = ?,
                invite_link = ?,
                total_xp = ?,
                due_date = ?,
                cert_min_xp = ?,
                cert_min_pct = ?,
                updated_at = NOW()
            WHERE id = ?");
        $stmt->execute([
            $name,
            $language_pair,
            $direction,
            $language_code,
            $exam_level,
            $topic_file,
            $invite_link,
            $total_xp,
            $due_date ?: null,
            $cert_min_xp,
            $cert_min_pct,
            $id,
        ]);
        $assessment_id = $id;
    } else {
        $stmt = $pdo->prepare("INSERT INTO institution_assessments (
                institution_id, title, invite_code, invite_link, language_pair,
                direction, language_code, exam_level, topic_file, duration_minutes, skills,
                passing_score, assessment_model, total_questions, total_xp, due_date,
                cert_min_xp, cert_min_pct, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $institution_id,
            $name,
            $invite_code,
            $invite_link,
            $language_pair,
            $direction,
            $language_code,
            $exam_level,
            $topic_file,
            $duration_minutes,
            $skills,
            $passing_score,
            '',
            0,
            $total_xp,
            $due_date ?: null,
            $cert_min_xp,
            $cert_min_pct,
        ]);
        $assessment_id = $pdo->lastInsertId();
    }
    $pdo->commit();

    sendResponse(true, $id ? 'Homework updated successfully' : 'Homework created successfully', [
        'assessment_id' => $assessment_id,
        'invite_code' => $invite_code,
        'invite_link' => $invite_link,
        'topic_file' => $topic_file,
        'due_date' => $due_date,
        'total_xp' => $total_xp,
        'cert_min_xp' => $cert_min_xp,
        'cert_min_pct' => $cert_min_pct,
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    sendResponse(false, 'Failed to save homework: ' . $e->getMessage());
}
?>