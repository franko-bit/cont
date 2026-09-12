<?php
session_start();
error_log("=== SAVE_PROGRESS CALLED ===");
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("Raw input: " . file_get_contents('php://input'));
require_once '../backend/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No data received']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Default values
    $lang = $data['language_code'] ?? $_SESSION['active_language'] ?? 'rw';
    // Normalize — strip pair info, keep just the target language code
    $lang = strtolower(trim($lang));
    $level = $data['level'] ?? 1;
    $topic = $data['topic'] ?? 'animals.yaml';
    $exercise_id = $data['exercise_id'] ?? 1;
    $xp = $data['xp'] ?? 10;
    $accuracy = $data['accuracy'] ?? 100;
    $time_spent = $data['time_spent'] ?? 5;
    $invite_code = trim($data['invite_code'] ?? '');
    
    $assessment_id = null;
    $institution_id = null;
    
    error_log("SAVE: language_code=" . $lang . " topic=" . $topic . " invite_code=" . $invite_code);
    
    // If this progress belongs to an assigned homework, link that candidate record.
    if ($invite_code !== '') {
        $stmt = $pdo->prepare("SELECT id, institution_id FROM institution_assessments WHERE invite_code = ? LIMIT 1");
        $stmt->execute([$invite_code]);
        $assessment = $stmt->fetch();
        if ($assessment) {
            $assessment_id = $assessment['id'];
            $institution_id = $assessment['institution_id'];
            $stmt = $pdo->prepare("SELECT email, full_name FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            $user_email = $user['email'] ?? null;
            $user_name = $user['full_name'] ?? null;
            if ($user_email) {
                // First, try to find candidate by email (from CSV upload)
                $stmt = $pdo->prepare("SELECT id, status, user_id FROM institution_candidates WHERE assessment_id = ? AND email = ? LIMIT 1");
                $stmt->execute([$assessment_id, $user_email]);
                $candidate = $stmt->fetch();
                
                if ($candidate) {
                    // Update existing candidate record - link user_id if not already linked, and update status
                    if (in_array($candidate['status'], ['pending', 'started'])) {
                        $stmt = $pdo->prepare("UPDATE institution_candidates SET user_id = ?, status = 'started', attempts = GREATEST(attempts, 1), updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$user_id, $candidate['id']]);
                    } elseif ($candidate['user_id'] === null) {
                        // Link user_id even if status changed
                        $stmt = $pdo->prepare("UPDATE institution_candidates SET user_id = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$user_id, $candidate['id']]);
                    }
                } else {
                    // No candidate record found by email - create new one
                    $stmt = $pdo->prepare("INSERT INTO institution_candidates (institution_id, assessment_id, user_id, full_name, email, status, attempts, invited_at, updated_at) VALUES (?, ?, ?, ?, ?, 'started', 1, NOW(), NOW())");
                    $stmt->execute([$institution_id, $assessment_id, $user_id, $user_name ?: 'Student', $user_email]);
                }
            }
        }
    }

    // Insert progress
    $stmt = $pdo->prepare("
        INSERT INTO user_progress
        (user_id, language_code, level_number, topic_file, exercise_id, completed, xp_earned, accuracy, time_spent, completed_at)
        VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
        completed = 1,
        xp_earned = VALUES(xp_earned),
        accuracy = VALUES(accuracy),
        time_spent = time_spent + VALUES(time_spent),
        attempts = attempts + 1,
        completed_at = NOW()
    ");
    
    $stmt->execute([$user_id, $lang, $level, $topic, $exercise_id, $xp, $accuracy, $time_spent]);
    
    // Update streak
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM user_streaks WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $streak = $stmt->fetch();
    
    if (!$streak) {
        $stmt = $pdo->prepare("INSERT INTO user_streaks (user_id, current_streak, last_activity_date) VALUES (?, 1, ?)");
        $stmt->execute([$user_id, $today]);
    } else {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        if ($streak['last_activity_date'] == $yesterday) {
            $new_streak = $streak['current_streak'] + 1;
            $stmt = $pdo->prepare("UPDATE user_streaks SET current_streak = ?, longest_streak = GREATEST(longest_streak, ?), last_activity_date = ? WHERE user_id = ?");
            $stmt->execute([$new_streak, $new_streak, $today, $user_id]);
        } elseif ($streak['last_activity_date'] != $today) {
            $stmt = $pdo->prepare("UPDATE user_streaks SET current_streak = 1, last_activity_date = ? WHERE user_id = ?");
            $stmt->execute([$today, $user_id]);
        }
    }
    
    // Update daily session
    $stmt = $pdo->prepare("
        INSERT INTO user_sessions (user_id, session_date, xp_earned, exercises_completed, time_spent)
        VALUES (?, ?, ?, 1, ?)
        ON DUPLICATE KEY UPDATE
        xp_earned = xp_earned + VALUES(xp_earned),
        exercises_completed = exercises_completed + 1,
        time_spent = time_spent + VALUES(time_spent)
    ");
    $stmt->execute([$user_id, $today, $xp, $time_spent]);

    // Update total XP for the user.
    $stmt = $pdo->prepare("UPDATE users SET total_xp = total_xp + ? WHERE id = ?");
    $stmt->execute([$xp, $user_id]);

    // If this homework has an invite_code, check if all exercises are now completed
    if ($invite_code !== '' && isset($assessment_id) && $assessment_id > 0) {
        // Count total exercises in this topic
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_exercises
            FROM user_progress
            WHERE user_id = ? AND language_code = ? AND level_number = ? AND topic_file = ?
        ");
        $stmt->execute([$user_id, $lang, $level, $topic]);
        $result = $stmt->fetch();
        $total_exercises = $result['total_exercises'] ?? 0;

        // Count completed exercises
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as completed_exercises
            FROM user_progress
            WHERE user_id = ? AND language_code = ? AND level_number = ? AND topic_file = ? AND completed = 1
        ");
        $stmt->execute([$user_id, $lang, $level, $topic]);
        $result = $stmt->fetch();
        $completed_exercises = $result['completed_exercises'] ?? 0;

        // If all exercises are completed, mark homework as completed
        if ($total_exercises > 0 && $completed_exercises > 0 && $completed_exercises == $total_exercises) {
            // Calculate total XP earned for this homework
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(xp_earned), 0) as total_xp
                FROM user_progress
                WHERE user_id = ? AND language_code = ? AND level_number = ? AND topic_file = ?
            ");
            $stmt->execute([$user_id, $lang, $level, $topic]);
            $xp_result = $stmt->fetch();
            $homework_xp = $xp_result['total_xp'] ?? 0;

            // Calculate average accuracy
            $stmt = $pdo->prepare("
                SELECT COALESCE(AVG(accuracy), 0) as avg_accuracy
                FROM user_progress
                WHERE user_id = ? AND language_code = ? AND level_number = ? AND topic_file = ?
            ");
            $stmt->execute([$user_id, $lang, $level, $topic]);
            $acc_result = $stmt->fetch();
            $avg_accuracy = round($acc_result['avg_accuracy'] ?? 0, 2);

            // Update candidate status to 'completed' and record the score
            $stmt = $pdo->prepare("
                UPDATE institution_candidates 
                SET status = 'completed', 
                    score = ?,
                    time_taken_seconds = TIMESTAMPDIFF(SECOND, invited_at, NOW()),
                    updated_at = NOW()
                WHERE assessment_id = ? AND user_id = ?
            ");
            $stmt->execute([$avg_accuracy, $assessment_id, $user_id]);
            
            error_log("HOMEWORK COMPLETED: user_id=$user_id, assessment_id=$assessment_id, score=$avg_accuracy, total_xp=$homework_xp");
        }
    }

    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "Progress saved! +$xp XP",
        'data' => $data
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>