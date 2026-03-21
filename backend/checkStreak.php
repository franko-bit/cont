<?php
// backend/checkStreak.php

require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Not authenticated');
}

$user_id = $_SESSION['user_id'];

try {
    // Get current streak info
    $stmt = $pdo->prepare("
        SELECT current_streak, longest_streak, last_activity 
        FROM users WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    $streak_at_risk = false;
    $hours_remaining = null;
    
    if ($user['last_activity'] == $yesterday) {
        // Streak is active but needs activity today
        $streak_at_risk = true;
        $hours_remaining = 24 - date('H');
    }
    
    // Get streak achievements
    $stmt = $pdo->prepare("
        SELECT name FROM badges b
        JOIN user_badges ub ON b.id = ub.badge_id
        WHERE ub.user_id = ? AND b.name LIKE '%Streak%'
    ");
    $stmt->execute([$user_id]);
    $streak_badges = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Calculate next milestone
    $next_milestone = null;
    $milestones = [3, 7, 14, 30, 60, 100, 365];
    foreach ($milestones as $milestone) {
        if ($user['current_streak'] < $milestone) {
            $next_milestone = $milestone;
            break;
        }
    }
    
    $response = [
        'success' => true,
        'current_streak' => $user['current_streak'],
        'longest_streak' => $user['longest_streak'],
        'last_activity' => $user['last_activity'],
        'streak_at_risk' => $streak_at_risk,
        'hours_remaining' => $hours_remaining,
        'next_milestone' => $next_milestone,
        'days_to_milestone' => $next_milestone ? $next_milestone - $user['current_streak'] : null,
        'streak_badges' => $streak_badges
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Check streak error: " . $e->getMessage());
    sendResponse(false, 'Failed to check streak');
}
?>