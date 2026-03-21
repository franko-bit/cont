<?php
// backend/progressService.php

require_once 'config.php';

class ProgressService {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Save exercise progress
    public function saveProgress($user_id, $lesson_id, $exercise_id, $is_correct, $time_spent) {
        try {
            $this->pdo->beginTransaction();
            
            // Check if progress exists
            $stmt = $this->pdo->prepare("
                SELECT id, attempts, correct_attempts 
                FROM user_progress 
                WHERE user_id = ? AND exercise_id = ?
            ");
            $stmt->execute([$user_id, $exercise_id]);
            $existing = $stmt->fetch();
            
            $now = date('Y-m-d H:i:s');
            
            if ($existing) {
                // Update existing progress
                $new_attempts = $existing['attempts'] + 1;
                $new_correct = $existing['correct_attempts'] + ($is_correct ? 1 : 0);
                $completed = $is_correct ? 1 : 0;
                
                $stmt = $this->pdo->prepare("
                    UPDATE user_progress 
                    SET attempts = ?, 
                        correct_attempts = ?, 
                        completed = completed OR ?,
                        last_attempt = ?,
                        time_spent = time_spent + ?,
                        completed_at = IF(? = 1 AND completed = 0, ?, completed_at)
                    WHERE user_id = ? AND exercise_id = ?
                ");
                $stmt->execute([
                    $new_attempts, 
                    $new_correct, 
                    $completed,
                    $now,
                    $time_spent,
                    $completed,
                    $now,
                    $user_id, 
                    $exercise_id
                ]);
            } else {
                // Insert new progress
                $stmt = $this->pdo->prepare("
                    INSERT INTO user_progress 
                    (user_id, lesson_id, exercise_id, attempts, correct_attempts, 
                     completed, first_attempt, last_attempt, time_spent, completed_at)
                    VALUES (?, ?, ?, 1, ?, ?, ?, ?, ?, ?)
                ");
                
                $first_attempt = $now;
                $last_attempt = $now;
                $completed_at = $is_correct ? $now : null;
                
                $stmt->execute([
                    $user_id, 
                    $lesson_id, 
                    $exercise_id, 
                    ($is_correct ? 1 : 0),
                    ($is_correct ? 1 : 0),
                    $first_attempt,
                    $last_attempt,
                    $time_spent,
                    $completed_at
                ]);
            }
            
            // Log the response
            $stmt = $this->pdo->prepare("
                INSERT INTO activity_responses 
                (user_id, exercise_id, response, is_correct, time_taken)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $exercise_id,
                $_POST['response'] ?? null,
                $is_correct,
                $time_spent
            ]);
            
            // If correct, award XP
            if ($is_correct) {
                $this->awardXP($user_id, $this->getExerciseXP($exercise_id));
            }
            
            $this->pdo->commit();
            
            // Check for achievements
            $this->checkAchievements($user_id);
            
            return ['success' => true, 'message' => 'Progress saved'];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Save progress error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to save progress'];
        }
    }
    
    // Get exercise XP
    private function getExerciseXP($exercise_id) {
        $stmt = $this->pdo->prepare("SELECT xp_reward FROM exercises WHERE id = ?");
        $stmt->execute([$exercise_id]);
        return $stmt->fetchColumn() ?: 10;
    }
    
    // Award XP to user
    private function awardXP($user_id, $xp) {
        // Update user total XP
        $stmt = $this->pdo->prepare("
            UPDATE users SET total_xp = total_xp + ? WHERE id = ?
        ");
        $stmt->execute([$xp, $user_id]);
        
        // Update leaderboard
        $stmt = $this->pdo->prepare("
            INSERT INTO leaderboard (user_id, xp) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE xp = xp + ?
        ");
        $stmt->execute([$user_id, $xp, $xp]);
    }
    
    // Complete entire lesson
    public function completeLesson($user_id, $lesson_id, $xp_earned, $time_spent) {
        try {
            $this->pdo->beginTransaction();
            
            // Award bonus XP for completing lesson
            $bonus_xp = 50;
            $total_xp = $xp_earned + $bonus_xp;
            
            $this->awardXP($user_id, $total_xp);
            
            // Update streak
            $this->updateStreak($user_id);
            
            // Check if all exercises in lesson are completed
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total,
                       SUM(CASE WHEN up.completed = 1 THEN 1 ELSE 0 END) as completed
                FROM exercises e
                LEFT JOIN user_progress up ON up.exercise_id = e.id AND up.user_id = ?
                WHERE e.lesson_id = ?
            ");
            $stmt->execute([$user_id, $lesson_id]);
            $progress = $stmt->fetch();
            
            $all_completed = ($progress['total'] == $progress['completed']);
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Lesson completed',
                'xp_earned' => $total_xp,
                'bonus_xp' => $bonus_xp,
                'all_completed' => $all_completed
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Complete lesson error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to complete lesson'];
        }
    }
    
    // Update user streak
    private function updateStreak($user_id) {
        try {
            $today = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                SELECT last_activity, current_streak, longest_streak 
                FROM users WHERE id = ?
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            $last_activity = $user['last_activity'] ?? null;
            $current_streak = $user['current_streak'] ?? 0;
            $longest_streak = $user['longest_streak'] ?? 0;
            
            if ($last_activity == $today) {
                return $current_streak;
            }
            
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            
            if ($last_activity == $yesterday) {
                $current_streak++;
            } else {
                $current_streak = 1;
            }
            
            if ($current_streak > $longest_streak) {
                $longest_streak = $current_streak;
            }
            
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET last_activity = ?, current_streak = ?, longest_streak = ? 
                WHERE id = ?
            ");
            $stmt->execute([$today, $current_streak, $longest_streak, $user_id]);
            
            $stmt = $this->pdo->prepare("
                UPDATE leaderboard SET streak = ? WHERE user_id = ?
            ");
            $stmt->execute([$current_streak, $user_id]);
            
            return $current_streak;
            
        } catch (Exception $e) {
            error_log("Streak update error: " . $e->getMessage());
            return 0;
        }
    }
    
    // Check and award achievements
    private function checkAchievements($user_id) {
        try {
            // Get user stats
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(DISTINCT lesson_id) as lessons_completed,
                    SUM(correct_attempts) as total_correct,
                    MAX(completed_at) as last_activity,
                    (SELECT current_streak FROM users WHERE id = ?) as streak
                FROM user_progress 
                WHERE user_id = ? AND completed = 1
            ");
            $stmt->execute([$user_id, $user_id]);
            $stats = $stmt->fetch();
            
            // Get all badges
            $stmt = $this->pdo->query("SELECT * FROM badges");
            $badges = $stmt->fetchAll();
            
            foreach ($badges as $badge) {
                $should_award = false;
                
                switch ($badge['name']) {
                    case 'First Steps':
                        $should_award = ($stats['lessons_completed'] >= 1);
                        break;
                        
                    case 'Quick Learner':
                        $should_award = ($stats['lessons_completed'] >= 10);
                        break;
                        
                    case 'Streak Master':
                        $should_award = ($stats['streak'] >= 7);
                        break;
                        
                    case 'Vocabulary Builder':
                        $should_award = ($stats['total_correct'] >= 100);
                        break;
                        
                    case 'Perfect Score':
                        // Check if any lesson has 100% completion
                        $stmt = $this->pdo->prepare("
                            SELECT l.id
                            FROM lessons l
                            JOIN exercises e ON e.lesson_id = l.id
                            LEFT JOIN user_progress up ON up.exercise_id = e.id AND up.user_id = ?
                            GROUP BY l.id
                            HAVING COUNT(e.id) = SUM(CASE WHEN up.completed = 1 THEN 1 ELSE 0 END)
                            LIMIT 1
                        ");
                        $stmt->execute([$user_id]);
                        $should_award = ($stmt->rowCount() > 0);
                        break;
                }
                
                if ($should_award) {
                    // Award badge if not already owned
                    $stmt = $this->pdo->prepare("
                        INSERT IGNORE INTO user_badges (user_id, badge_id) 
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$user_id, $badge['id']]);
                }
            }
            
        } catch (Exception $e) {
            error_log("Check achievements error: " . $e->getMessage());
        }
    }
    
    // Get user progress summary
    public function getUserProgress($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.total_xp,
                    u.current_streak,
                    u.longest_streak,
                    COUNT(DISTINCT up.lesson_id) as lessons_completed,
                    COUNT(DISTINCT CASE WHEN up.completed = 1 THEN up.exercise_id END) as exercises_completed,
                    COUNT(DISTINCT ub.badge_id) as badges_earned
                FROM users u
                LEFT JOIN user_progress up ON u.id = up.user_id
                LEFT JOIN user_badges ub ON u.id = ub.user_id
                WHERE u.id = ?
                GROUP BY u.id
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Get user progress error: " . $e->getMessage());
            return null;
        }
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = validateSession();
    $service = new ProgressService($pdo);
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'save':
            $result = $service->saveProgress(
                $user_id,
                $_POST['lesson_id'] ?? 0,
                $_POST['exercise_id'] ?? 0,
                $_POST['is_correct'] ?? false,
                $_POST['time_spent'] ?? 0
            );
            sendResponse($result['success'], $result['message']);
            break;
            
        case 'complete_lesson':
            $result = $service->completeLesson(
                $user_id,
                $_POST['lesson_id'] ?? 0,
                $_POST['xp_earned'] ?? 0,
                $_POST['time_spent'] ?? 0
            );
            sendResponse($result['success'], $result['message'], $result);
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'summary') {
    $user_id = validateSession();
    $service = new ProgressService($pdo);
    $progress = $service->getUserProgress($user_id);
    sendResponse(true, '', $progress);
}
?>