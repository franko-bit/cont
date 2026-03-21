<?php
// backend/lessonService.php

require_once 'config.php';

class LessonService {
    private $pdo;
    private $user_id;
    
    public function __construct($pdo, $user_id = null) {
        $this->pdo = $pdo;
        $this->user_id = $user_id;
    }
    
    // Get all lessons for a level
    public function getLessonsByLevel($level_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT l.*, c.name as category_name, c.icon_url as category_icon,
                       COUNT(e.id) as exercise_count
                FROM lessons l
                JOIN categories c ON l.category_id = c.id
                LEFT JOIN exercises e ON e.lesson_id = l.id
                WHERE c.level_id = ?
                GROUP BY l.id
                ORDER BY l.order_index
            ");
            $stmt->execute([$level_id]);
            $lessons = $stmt->fetchAll();
            
            // Get user progress if logged in
            if ($this->user_id) {
                foreach ($lessons as &$lesson) {
                    $stmt = $this->pdo->prepare("
                        SELECT COUNT(DISTINCT exercise_id) as completed
                        FROM user_progress
                        WHERE user_id = ? AND lesson_id = ? AND completed = 1
                    ");
                    $stmt->execute([$this->user_id, $lesson['id']]);
                    $completed = $stmt->fetchColumn();
                    $lesson['completed_exercises'] = $completed;
                    $lesson['progress'] = $lesson['exercise_count'] > 0 
                        ? round(($completed / $lesson['exercise_count']) * 100) 
                        : 0;
                }
            }
            
            return ['success' => true, 'data' => $lessons];
            
        } catch (Exception $e) {
            error_log("Get lessons error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to load lessons'];
        }
    }
    
    // Load single lesson with exercises
    public function loadLesson($lesson_id) {
        try {
            // Get lesson details
            $stmt = $this->pdo->prepare("
                SELECT l.*, c.name as category_name, c.icon_url, 
                       lvl.level_number, lvl.name as level_name
                FROM lessons l
                JOIN categories c ON l.category_id = c.id
                JOIN levels lvl ON c.level_id = lvl.id
                WHERE l.id = ?
            ");
            $stmt->execute([$lesson_id]);
            $lesson = $stmt->fetch();
            
            if (!$lesson) {
                return ['success' => false, 'message' => 'Lesson not found'];
            }
            
            // Get exercises
            $stmt = $this->pdo->prepare("
                SELECT * FROM exercises 
                WHERE lesson_id = ? 
                ORDER BY order_index ASC
            ");
            $stmt->execute([$lesson_id]);
            $exercises = $stmt->fetchAll();
            
            // Get user progress if logged in
            if ($this->user_id) {
                $stmt = $this->pdo->prepare("
                    SELECT exercise_id, completed, attempts, correct_attempts 
                    FROM user_progress 
                    WHERE user_id = ? AND lesson_id = ?
                ");
                $stmt->execute([$this->user_id, $lesson_id]);
                $progress = $stmt->fetchAll();
                
                $progressMap = [];
                foreach ($progress as $p) {
                    $progressMap[$p['exercise_id']] = $p;
                }
                
                // Add progress to exercises
                foreach ($exercises as &$exercise) {
                    if (isset($progressMap[$exercise['id']])) {
                        $exercise['completed'] = (bool)$progressMap[$exercise['id']]['completed'];
                        $exercise['attempts'] = $progressMap[$exercise['id']]['attempts'];
                        $exercise['correct_attempts'] = $progressMap[$exercise['id']]['correct_attempts'];
                    } else {
                        $exercise['completed'] = false;
                        $exercise['attempts'] = 0;
                        $exercise['correct_attempts'] = 0;
                    }
                }
            }
            
            // Format exercises for frontend
            $formattedExercises = [];
            foreach ($exercises as $exercise) {
                $formatted = [
                    'id' => $exercise['id'],
                    'type' => $exercise['type'],
                    'question' => $exercise['question'],
                    'xp_reward' => $exercise['xp_reward'],
                    'image' => $exercise['image_url'] ?: null,
                    'hints' => $exercise['hints'] ? explode('|', $exercise['hints']) : []
                ];
                
                // Add type-specific fields
                switch ($exercise['type']) {
                    case 'multiple_choice':
                        $formatted['options'] = json_decode($exercise['options'], true);
                        break;
                        
                    case 'matching':
                        $pairs = json_decode($exercise['options'], true);
                        $formatted['pairs'] = $pairs;
                        break;
                        
                    case 'listening':
                        $formatted['tts_text'] = $exercise['tts_text'] ?: $exercise['question'];
                        break;
                        
                    case 'sentence_building':
                        $formatted['words'] = json_decode($exercise['options'], true);
                        break;
                        
                    case 'translation':
                        $formatted['source_lang'] = 'English';
                        $formatted['target_lang'] = 'Kinyarwanda';
                        break;
                }
                
                // Add progress
                if (isset($exercise['completed'])) {
                    $formatted['completed'] = $exercise['completed'];
                    $formatted['attempts'] = $exercise['attempts'];
                }
                
                $formattedExercises[] = $formatted;
            }
            
            // Calculate total XP
            $total_xp = array_sum(array_column($exercises, 'xp_reward'));
            
            return [
                'success' => true,
                'data' => [
                    'id' => $lesson['id'],
                    'name' => $lesson['name'],
                    'description' => $lesson['description'],
                    'image' => $lesson['image_url'],
                    'category' => $lesson['category_name'],
                    'category_icon' => $lesson['icon_url'],
                    'level' => $lesson['level_number'],
                    'level_name' => $lesson['level_name'],
                    'exercises' => $formattedExercises,
                    'total_xp' => $total_xp,
                    'exercise_count' => count($formattedExercises)
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Load lesson error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to load lesson'];
        }
    }
    
    // Get next lesson in sequence
    public function getNextLesson($current_lesson_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT l.id, l.name, c.level_id
                FROM lessons l
                JOIN categories c ON l.category_id = c.id
                WHERE l.id = ?
            ");
            $stmt->execute([$current_lesson_id]);
            $current = $stmt->fetch();
            
            if (!$current) {
                return null;
            }
            
            // Get next lesson in same category
            $stmt = $this->pdo->prepare("
                SELECT id, name
                FROM lessons
                WHERE category_id = ? AND id > ?
                ORDER BY id ASC
                LIMIT 1
            ");
            $stmt->execute([$current['category_id'], $current_lesson_id]);
            $next = $stmt->fetch();
            
            if ($next) {
                return $next;
            }
            
            // Get first lesson of next category in same level
            $stmt = $this->pdo->prepare("
                SELECT l.id, l.name
                FROM lessons l
                JOIN categories c ON l.category_id = c.id
                WHERE c.level_id = ? AND c.id > ?
                ORDER BY c.id, l.id ASC
                LIMIT 1
            ");
            $stmt->execute([$current['level_id'], $current['category_id']]);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Get next lesson error: " . $e->getMessage());
            return null;
        }
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['user_id'] ?? null;
    $service = new LessonService($pdo, $user_id);
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_by_level':
            $level_id = $_GET['level_id'] ?? 0;
            if (!$level_id) {
                sendResponse(false, 'Level ID required');
            }
            $result = $service->getLessonsByLevel($level_id);
            sendResponse($result['success'], $result['message'] ?? '', $result['data'] ?? null);
            break;
            
        case 'load':
            $lesson_id = $_GET['lesson_id'] ?? 0;
            if (!$lesson_id) {
                sendResponse(false, 'Lesson ID required');
            }
            $result = $service->loadLesson($lesson_id);
            sendResponse($result['success'], $result['message'] ?? '', $result['data'] ?? null);
            break;
            
        case 'next':
            $lesson_id = $_GET['lesson_id'] ?? 0;
            if (!$lesson_id) {
                sendResponse(false, 'Lesson ID required');
            }
            $next = $service->getNextLesson($lesson_id);
            sendResponse(true, '', $next);
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
}
?>