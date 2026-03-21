<?php
// backend/getLeaderboard.php

require_once 'config.php';
require_once 'gamification.php';

header('Content-Type: application/json');

$gamification = new Gamification($pdo);

$limit = $_GET['limit'] ?? 10;
$timeframe = $_GET['timeframe'] ?? 'all';

$result = $gamification->getLeaderboard($limit, $timeframe);

if ($result['success']) {
    // Get current user rank if logged in
    if (isset($_SESSION['user_id'])) {
        $user_rank = $gamification->getUserRank($_SESSION['user_id']);
        $result['user_rank'] = $user_rank;
    }
    
    echo json_encode($result);
} else {
    sendResponse(false, $result['message']);
}
?>