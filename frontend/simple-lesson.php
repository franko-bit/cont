<?php
session_start();
require_once '../backend/config.php';

if (!isset($_SESSION['user_id'])) {
    die("Please login first");
}

$user_id = $_SESSION['user_id'];

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exercise_id = $_POST['exercise_id'] ?? 1;
    $answer = $_POST['answer'] ?? '';
    $correct_answer = $_POST['correct_answer'] ?? '';
    
    $is_correct = (strtolower(trim($answer)) == strtolower(trim($correct_answer)));
    
    if ($is_correct) {
        try {
            // Simple insert - no ON DUPLICATE KEY for now
            $stmt = $pdo->prepare("
                INSERT INTO user_progress 
                (user_id, language_code, level_number, topic_file, exercise_id, completed, xp_earned, accuracy, time_spent, completed_at)
                VALUES (?, 'rw', 1, 'animals.yaml', ?, 1, 10, 100, 5, NOW())
            ");
            $result = $stmt->execute([$user_id, $exercise_id]);
            
            if ($result) {
                $message = "✅ SUCCESS! Data saved to database!";
                
                // Also update streak
                $today = date('Y-m-d');
                $stmt = $pdo->prepare("
                    INSERT INTO user_streaks (user_id, current_streak, last_activity_date)
                    VALUES (?, 1, ?)
                    ON DUPLICATE KEY UPDATE
                    current_streak = current_streak + 1,
                    last_activity_date = ?
                ");
                $stmt->execute([$user_id, $today, $today]);
                
            } else {
                $message = "❌ Database insert failed";
            }
            
        } catch (PDOException $e) {
            $message = "❌ Database error: " . $e->getMessage();
        }
    } else {
        $message = "❌ Wrong answer. Try again!";
    }
}

// Get current progress count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_progress WHERE user_id = ?");
$stmt->execute([$user_id]);
$progress_count = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Test Lesson</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f0f7f0; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .exercise { background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; }
        input, button { padding: 10px 20px; font-size: 16px; margin: 5px; border-radius: 5px; }
        button { background: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background: #45a049; }
        .message { padding: 15px; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .stats { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Simple Test Lesson</h1>
        
        <div class="stats">
            <strong>User ID:</strong> <?= $user_id ?><br>
            <strong>Current Progress Records:</strong> <?= $progress_count ?><br>
            <strong>Today's Date:</strong> <?= date('Y-m-d H:i:s') ?>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <div class="exercise">
            <h3>Exercise 1: Translation</h3>
            <p><strong>Question:</strong> How do you say "Cow" in Kinyarwanda?</p>
            <p><em>Correct answer is "Inka"</em></p>
            
            <form method="POST">
                <input type="hidden" name="exercise_id" value="1">
                <input type="hidden" name="correct_answer" value="Inka">
                <input type="text" name="answer" placeholder="Type your answer" required style="width: 200px;">
                <button type="submit">Check Answer</button>
            </form>
        </div>
        
        <div class="exercise">
            <h3>Exercise 2: Translation</h3>
            <p><strong>Question:</strong> How do you say "Dog" in Kinyarwanda?</p>
            <p><em>Correct answer is "Imbwa"</em></p>
            
            <form method="POST">
                <input type="hidden" name="exercise_id" value="2">
                <input type="hidden" name="correct_answer" value="Imbwa">
                <input type="text" name="answer" placeholder="Type your answer" required style="width: 200px;">
                <button type="submit">Check Answer</button>
            </form>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="test_dashboard.php" style="background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">📊 Check Dashboard</a>
            <a href="simple-lesson.php" style="background: #FF9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;">🔄 Reset</a>
        </div>
    </div>
</body>
</html>