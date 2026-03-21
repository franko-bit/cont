<?php
// backend/completeLesson.php

require_once 'config.php';
require_once 'progressService.php';

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    sendResponse(false, 'Invalid request data');
}

// Validate session
$user_id = validateSession();

// Validate required fields
if (!isset($input['lesson_id']) || !isset($input['xp_earned'])) {
    sendResponse(false, 'Missing required fields');
}

try {
    $progressService = new ProgressService($pdo);
    
    $result = $progressService->completeLesson(
        $user_id,
        $input['lesson_id'],
        $input['xp_earned'],
        $input['time_spent'] ?? 0
    );
    
    if ($result['success']) {
        // Get next lesson
        require_once 'lessonService.php';
        $lessonService = new LessonService($pdo, $user_id);
        $next_lesson = $lessonService->getNextLesson($input['lesson_id']);
        
        $result['next_lesson'] = $next_lesson;
        
        // Check for new badges
        $gamification = new Gamification($pdo);
        $new_badges = $gamification->getUserBadges($user_id);
        
        // Get only recently earned badges (last 5 minutes)
        $recent_badges = array_filter($new_badges, function($badge) {
            $earned = strtotime($badge['earned_at']);
            $five_minutes_ago = time() - 300;
            return $earned > $five_minutes_ago;
        });
        
        if (!empty($recent_badges)) {
            $result['new_badges'] = array_values($recent_badges);
        }
        
        echo json_encode($result);
    } else {
        sendResponse(false, $result['message']);
    }
    
} catch (Exception $e) {
    error_log("Complete lesson error: " . $e->getMessage());
    sendResponse(false, 'Failed to complete lesson');
}
?>