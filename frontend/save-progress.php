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
    $level = $data['level'] ?? 1;
    $topic = $data['topic'] ?? 'animals.yaml';
    $exercise_id = $data['exercise_id'] ?? 1;
    $xp = $data['xp'] ?? 10;
    $accuracy = $data['accuracy'] ?? 100;
    $time_spent = $data['time_spent'] ?? 5;
    
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