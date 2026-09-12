<?php
/**
 * EXAM ACCESS ENDPOINT
 * POST /backend/access-exam.php
 * Auto-registers candidate for exam when accessing via invite code or direct link
 */

header('Content-Type: application/json');
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'POST method required');
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$invite_code = trim($input['invite_code'] ?? $input['code'] ?? '');
$password = $input['password'] ?? '';
$email = trim($input['email'] ?? '');
$name = trim($input['name'] ?? '');

if (!$invite_code) {
    sendResponse(false, 'Invite code is required', null, 400);
}

try {
    // Find exam by invite code
    $stmt = $pdo->prepare('SELECT * FROM institution_assessments WHERE invite_code = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$invite_code]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$exam) {
        sendResponse(false, 'Exam not found or is inactive', null, 404);
    }
    
    // Check password if exam is password-protected
    if (!empty($exam['exam_password'])) {
        if (!$password) {
            sendResponse(false, 'This exam requires a password', ['requires_password' => true], 403);
        }
        if (!password_verify($password, $exam['exam_password'])) {
            sendResponse(false, 'Incorrect password', null, 403);
        }
    }
    
    // Get user ID if authenticated, otherwise use email/name for guest access
    $user_id = $_SESSION['user_id'] ?? null;
    
    // Check if candidate already registered for this exam
    $stmt = $pdo->prepare('SELECT * FROM institution_candidates WHERE assessment_id = ? AND (user_id = ? OR email = ?) LIMIT 1');
    $stmt->execute([$exam['id'], $user_id, $email]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update attempt count
        $stmt = $pdo->prepare('UPDATE institution_candidates SET exam_attempt_count = exam_attempt_count + 1 WHERE id = ?');
        $stmt->execute([$existing['id']]);
        $candidate_id = $existing['id'];
    } else {
        // Register new candidate
        $stmt = $pdo->prepare('
            INSERT INTO institution_candidates (
                institution_id, assessment_id, user_id, email, full_name,
                exam_status, exam_attempt_count, invited_at
            ) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
        ');
        $stmt->execute([
            $exam['institution_id'],
            $exam['id'],
            $user_id,
            $email,
            $name
        ]);
        $candidate_id = $pdo->lastInsertId();
    }
    
    // Increment exam view count
    $stmt = $pdo->prepare('UPDATE institution_assessments SET view_count = view_count + 1 WHERE id = ?');
    $stmt->execute([$exam['id']]);
    
    // Load exam questions from YAML
    $language = strtolower($exam['exam_language'] ?? 'english');
    if (in_array($language, ['en', 'english'])) {
        $yamlFile = __DIR__ . '/../content/EXAMS/ENGLISH.yaml';
    } else {
        $yamlFile = __DIR__ . '/../content/EXAMS/French.yaml';
    }
    
    if (!file_exists($yamlFile)) {
        sendResponse(false, 'Exam content not found', null, 500);
    }
    
    // Parse YAML and extract exercises
    $yamlContent = file_get_contents($yamlFile);
    $exercises = parseYamlExercises($yamlContent);

    // Build a safe candidate access URL matching the homework-style lesson flow
    $pair = in_array($language, ['en', 'english']) ? 'en-rw' : 'fr-rw';
    $direction = in_array($language, ['en', 'english']) ? 'en' : 'fr';
    $topicFile = in_array($language, ['en', 'english']) ? 'ENGLISH.yaml' : 'French.yaml';
    $examUrl = SITE_URL . '/frontend/lesson.php?invite=' . urlencode($invite_code) . '&topic=' . urlencode('EXAMS/' . $topicFile) . '&level=1&pair=' . urlencode($pair) . '&direction=' . urlencode($direction);
    
    sendResponse(true, 'Exam access granted', [
        'candidate_id' => (int)$candidate_id,
        'exam_id' => (int)$exam['id'],
        'exam_title' => $exam['title'],
        'exam_language' => $exam['exam_language'],
        'passing_score' => (int)$exam['passing_score'],
        'total_xp' => (int)$exam['total_xp'],
        'total_questions' => (int)$exam['total_questions'],
        'questions' => $exercises,
        'exam_url' => $examUrl
    ]);
    
} catch (Exception $e) {
    sendResponse(false, 'Error: ' . $e->getMessage(), null, 500);
}

/**
 * Parse YAML exercises into structured array
 */
function parseYamlExercises($content) {
    $exercises = [];
    
    // Split by exercise marker (- id:)
    $parts = preg_split('/^(\s*- id:)/m', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
    
    for ($i = 2; $i < count($parts); $i += 2) {
        $exerciseBlock = $parts[$i];
        $exercise = parseExerciseBlock($exerciseBlock);
        if ($exercise) {
            $exercises[] = $exercise;
        }
    }
    
    return $exercises;
}

/**
 * Parse individual exercise block
 */
function parseExerciseBlock($block) {
    $exercise = [];
    
    // Extract id
    if (preg_match('/^(\d+)/', $block, $m)) {
        $exercise['id'] = (int)$m[1];
    } else {
        return null;
    }
    
    // Extract type
    if (preg_match('/^\s*type:\s*(\w+)/m', $block, $m)) {
        $exercise['type'] = $m[1];
    }
    
    // Extract question
    if (preg_match('/^\s*question:\s*["\']?(.*?)["\']?\s*$/m', $block, $m)) {
        $exercise['question'] = trim($m[1]);
    }
    
    // Extract options (if present)
    if (preg_match('/^\s*options:\s*\[(.*?)\]/ms', $block, $m)) {
        $optionsStr = $m[1];
        $options = [];
        if (preg_match_all('/["\']([^"\']*)["\']/', $optionsStr, $om)) {
            $options = $om[1];
        }
        $exercise['options'] = $options;
    }
    
    // Extract correct answer or answer
    if (preg_match('/^\s*correct_answer:\s*(?:true|false|["\']?(.*?)["\']?)\s*$/m', $block, $m)) {
        $fullMatch = $m[0];
        if (strpos(strtolower($fullMatch), 'true') !== false) {
            $exercise['correct_answer'] = true;
        } elseif (strpos(strtolower($fullMatch), 'false') !== false) {
            $exercise['correct_answer'] = false;
        } else {
            $exercise['correct_answer'] = isset($m[1]) ? trim($m[1]) : null;
        }
    }
    if (preg_match('/^\s*answer:\s*["\']?(.*?)["\']?\s*$/m', $block, $m)) {
        $exercise['correct_answer'] = trim($m[1]);
    }
    
    // Extract XP reward
    if (preg_match('/^\s*xp_reward:\s*(\d+)\s*$/m', $block, $m)) {
        $exercise['xp_reward'] = (int)$m[1];
    }
    
    // Extract difficulty
    if (preg_match('/^\s*difficulty:\s*(\w+)/m', $block, $m)) {
        $exercise['difficulty'] = $m[1];
    }
    
    // Extract time limit
    if (preg_match('/^\s*time_limit_seconds:\s*(\d+)\s*$/m', $block, $m)) {
        $exercise['time_limit_seconds'] = (int)$m[1];
    }
    
    // Extract topic
    if (preg_match('/^\s*topic:\s*["\']?(\w+)["\']?\s*$/m', $block, $m)) {
        $exercise['topic'] = $m[1];
    }
    
    return $exercise;
}
?>
