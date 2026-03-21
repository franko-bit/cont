<?php
// backend/gamification.php

require_once 'config.php';

class Gamification {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Get leaderboard
    public function getLeaderboard($limit = 10, $timeframe = 'all') {
        try {
            $where = "";
            switch ($timeframe) {
                case 'daily':
                    $where = "WHERE DATE(ub.earned_at) = CURDATE()";
                    break;
                case 'weekly':
                    $where = "WHERE YEARWEEK(ub.earned_at) = YEARWEEK(CURDATE())";
                    break;
                case 'monthly':
                    $where = "WHERE MONTH(ub.earned_at) = MONTH(CURDATE()) AND YEAR(ub.earned_at) = YEAR(CURDATE())";
                    break;
            }
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.id,
                    u.full_name,
                    COALESCE(lb.xp, 0) as xp,
                    COALESCE(lb.streak, 0) as streak,
                    COUNT(DISTINCT ub.badge_id) as badges,
                    (
                        SELECT COUNT(DISTINCT lesson_id) 
                        FROM user_progress 
                        WHERE user_id = u.id AND completed = 1
                    ) as lessons_completed
                FROM users u
                LEFT JOIN leaderboard lb ON u.id = lb.user_id
                LEFT JOIN user_badges ub ON u.id = ub.user_id $where
                GROUP BY u.id
                ORDER BY xp DESC, lessons_completed DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $leaders = $stmt->fetchAll();
            
            // Add rank
            foreach ($leaders as $index => &$leader) {
                $leader['rank'] = $index + 1;
            }
            
            return ['success' => true, 'data' => $leaders];
            
        } catch (Exception $e) {
            error_log("Get leaderboard error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to load leaderboard'];
        }
    }
    
    // Get user badges
    public function getUserBadges($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.*, ub.earned_at
                FROM badges b
                JOIN user_badges ub ON b.id = ub.badge_id
                WHERE ub.user_id = ?
                ORDER BY ub.earned_at DESC
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Get user badges error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get all available badges
    public function getAllBadges() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM badges ORDER BY id");
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Get all badges error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get user rank
    public function getUserRank($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT user_id, xp,
                       @rownum := @rownum + 1 as rank
                FROM leaderboard, (SELECT @rownum := 0) r
                ORDER BY xp DESC
            ");
            $stmt->execute();
            $ranks = $stmt->fetchAll();
            
            foreach ($ranks as $rank) {
                if ($rank['user_id'] == $user_id) {
                    return $rank['rank'];
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Get user rank error: " . $e->getMessage());
            return null;
        }
    }
    
    // Get achievements progress
    public function getAchievementsProgress($user_id) {
        try {
            // Get user stats
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.total_xp,
                    u.current_streak,
                    u.longest_streak,
                    COUNT(DISTINCT up.lesson_id) as lessons_completed,
                    COUNT(DISTINCT up.exercise_id) as exercises_attempted,
                    SUM(up.correct_attempts) as correct_answers,
                    COUNT(DISTINCT ub.badge_id) as badges_earned
                FROM users u
                LEFT JOIN user_progress up ON u.id = up.user_id
                LEFT JOIN user_badges ub ON u.id = ub.user_id
                WHERE u.id = ?
                GROUP BY u.id
            ");
            $stmt->execute([$user_id]);
            $stats = $stmt->fetch();
            
            // Get all badges with progress
            $badges = $this->getAllBadges();
            
            foreach ($badges as &$badge) {
                $badge['progress'] = 0;
                $badge['max'] = 100;
                $badge['earned'] = false;
                
                // Check if earned
                $stmt = $this->pdo->prepare("
                    SELECT 1 FROM user_badges 
                    WHERE user_id = ? AND badge_id = ?
                ");
                $stmt->execute([$user_id, $badge['id']]);
                $badge['earned'] = ($stmt->rowCount() > 0);
                
                // Calculate progress
                switch ($badge['name']) {
                    case 'First Steps':
                        $badge['progress'] = min($stats['lessons_completed'] ?? 0, 1) * 100;
                        $badge['max'] = 1;
                        break;
                        
                    case 'Quick Learner':
                        $badge['progress'] = min($stats['lessons_completed'] ?? 0, 10) * 10;
                        $badge['max'] = 10;
                        break;
                        
                    case 'Streak Master':
                        $badge['progress'] = min($stats['current_streak'] ?? 0, 7) * 14.28;
                        $badge['max'] = 7;
                        break;
                        
                    case 'Vocabulary Builder':
                        $badge['progress'] = min($stats['correct_answers'] ?? 0, 100);
                        $badge['max'] = 100;
                        break;
                }
            }
            
            return [
                'stats' => $stats,
                'badges' => $badges
            ];
            
        } catch (Exception $e) {
            error_log("Get achievements progress error: " . $e->getMessage());
            return null;
        }
    }
    
    // Award streak bonus
    public function awardStreakBonus($user_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT current_streak FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $streak = $stmt->fetchColumn();
            
            $bonus_xp = 0;
            
            if ($streak >= 7) {
                $bonus_xp = 50;
            } elseif ($streak >= 30) {
                $bonus_xp = 200;
            } elseif ($streak >= 100) {
                $bonus_xp = 1000;
            }
            
            if ($bonus_xp > 0) {
                $stmt = $this->pdo->prepare("
                    UPDATE users SET total_xp = total_xp + ? WHERE id = ?
                ");
                $stmt->execute([$bonus_xp, $user_id]);
                
                $stmt = $this->pdo->prepare("
                    UPDATE leaderboard SET xp = xp + ? WHERE user_id = ?
                ");
                $stmt->execute([$bonus_xp, $user_id]);
            }
            
            return $bonus_xp;
            
        } catch (Exception $e) {
            error_log("Award streak bonus error: " . $e->getMessage());
            return 0;
        }
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $gamification = new Gamification($pdo);
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'leaderboard':
            $limit = $_GET['limit'] ?? 10;
            $timeframe = $_GET['timeframe'] ?? 'all';
            $result = $gamification->getLeaderboard($limit, $timeframe);
            sendResponse($result['success'], $result['message'] ?? '', $result['data'] ?? null);
            break;
            
        case 'badges':
            if (isset($_GET['user_id'])) {
                $badges = $gamification->getUserBadges($_GET['user_id']);
            } else {
                $badges = $gamification->getAllBadges();
            }
            sendResponse(true, '', $badges);
            break;
            
        case 'rank':
            $user_id = $_GET['user_id'] ?? null;
            if (!$user_id) {
                sendResponse(false, 'User ID required');
            }
            $rank = $gamification->getUserRank($user_id);
            sendResponse(true, '', ['rank' => $rank]);
            break;
            
        case 'achievements':
            $user_id = $_GET['user_id'] ?? null;
            if (!$user_id) {
                sendResponse(false, 'User ID required');
            }
            $progress = $gamification->getAchievementsProgress($user_id);
            sendResponse(true, '', $progress);
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'streak_bonus') {
    $user_id = validateSession();
    $gamification = new Gamification($pdo);
    $bonus = $gamification->awardStreakBonus($user_id);
    sendResponse(true, '', ['bonus_xp' => $bonus]);
}
?>