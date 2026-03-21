<?php
// backend/submitAnswer.php

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
$required = ['exercise_id', 'answer', 'time_taken'];
foreach ($required as $field) {
    if (!isset($input[$field])) {
        sendResponse(false, "Missing field: $field");
    }
}

try {
    // Get exercise details
    $stmt = $pdo->prepare("
        SELECT e.*, l.id as lesson_id, l.name as lesson_name
        FROM exercises e
        JOIN lessons l ON e.lesson_id = l.id
        WHERE e.id = ?
    ");
    $stmt->execute([$input['exercise_id']]);
    $exercise = $stmt->fetch();
    
    if (!$exercise) {
        sendResponse(false, 'Exercise not found');
    }
    
    // Validate answer based on exercise type
    $is_correct = false;
    $correct_answer = $exercise['answer'];
    
    switch ($exercise['type']) {
        case 'multiple_choice':
            $options = json_decode($exercise['options'], true);
            $selected_index = (int)$input['answer'];
            $is_correct = ($options[$selected_index] == $correct_answer);
            break;
            
        case 'translation':
        case 'typing':
        case 'listening':
            // Case-insensitive comparison, trim whitespace
            $user_answer = trim(strtolower($input['answer']));
            $correct = trim(strtolower($correct_answer));
            
            // Remove extra spaces and punctuation
            $user_answer = preg_replace('/[^\w\s]/u', '', $user_answer);
            $correct = preg_replace('/[^\w\s]/u', '', $correct);
            $user_answer = preg_replace('/\s+/', ' ', $user_answer);
            $correct = preg_replace('/\s+/', ' ', $correct);
            
            $is_correct = ($user_answer == $correct);
            break;
            
        case 'matching':
            $user_pairs = json_decode($input['answer'], true);
            $correct_pairs = json_decode($exercise['options'], true);
            
            if (is_array($user_pairs) && is_array($correct_pairs)) {
                $is_correct = true;
                foreach ($user_pairs as $pair) {
                    $found = false;
                    foreach ($correct_pairs as $correct) {
                        if ($pair['left'] == $correct['left'] && $pair['right'] == $correct['right']) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $is_correct = false;
                        break;
                    }
                }
            }
            break;
            
        case 'sentence_building':
            $user_words = explode(' ', trim($input['answer']));
            $correct_words = json_decode($exercise['options'], true);
            
            if (is_array($correct_words)) {
                $is_correct = ($user_words == $correct_words);
            }
            break;
    }
    
    // Save progress
    $progressService = new ProgressService($pdo);
    $result = $progressService->saveProgress(
        $user_id,
        $exercise['lesson_id'],
        $exercise['id'],
        $is_correct,
        $input['time_taken']
    );
    
    if (!$result['success']) {
        sendResponse(false, 'Failed to save progress');
    }
    
    // Get feedback messages
    $feedback = [
        'correct' => $exercise['feedback_correct'] ?? 'Correct! Well done! 🎉',
        'incorrect' => $exercise['feedback_incorrect'] ?? 'Not quite right. Keep trying! 💪'
    ];
    
    // Prepare response
    $response = [
        'success' => true,
        'is_correct' => $is_correct,
        'xp_earned' => $is_correct ? $exercise['xp_reward'] : 0,
        'feedback' => $is_correct ? $feedback['correct'] : $feedback['incorrect'],
        'correct_answer' => $is_correct ? null : $correct_answer,
        'exercise_completed' => $is_correct
    ];
    
    // If correct, check if lesson is completed
    if ($is_correct) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN up.completed = 1 THEN 1 ELSE 0 END) as completed
            FROM exercises e
            LEFT JOIN user_progress up ON up.exercise_id = e.id AND up.user_id = ?
            WHERE e.lesson_id = ?
        ");
        $stmt->execute([$user_id, $exercise['lesson_id']]);
        $progress = $stmt->fetch();
        
        if ($progress['total'] == $progress['completed']) {
            $response['lesson_completed'] = true;
            $response['lesson_id'] = $exercise['lesson_id'];
            $response['lesson_name'] = $exercise['lesson_name'];
        }
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Submit answer error: " . $e->getMessage());
    sendResponse(false, 'Server error occurred');
}
?>