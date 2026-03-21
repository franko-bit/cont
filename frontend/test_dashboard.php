<?php
session_start();
require_once '../backend/config.php';

if (!isset($_SESSION['user_id'])) {
    die("Please login first");
}

$user_id = $_SESSION['user_id'];
$lang = $_GET['lang'] ?? 'rw';

echo "<h1>📊 Database Dashboard Test</h1>";

// 1. Check user_progress table
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM user_progress WHERE user_id = ?");
$stmt->execute([$user_id]);
$total = $stmt->fetchColumn();
echo "<h2>User Progress: $total records found</h2>";

if ($total > 0) {
    // Show recent progress
    $stmt = $pdo->prepare("
        SELECT * FROM user_progress 
        WHERE user_id = ? 
        ORDER BY completed_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $progress = $stmt->fetchAll();
    
    echo "<h3>Recent 10 exercises:</h3>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse'>";
    echo "<tr>
            <th>ID</th>
            <th>Language</th>
            <th>Level</th>
            <th>Topic</th>
            <th>Exercise</th>
            <th>XP</th>
            <th>Accuracy</th>
            <th>Completed At</th>
          </tr>";
    
    $total_xp = 0;
    foreach ($progress as $row) {
        $total_xp += $row['xp_earned'];
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['language_code']}</td>";
        echo "<td>{$row['level_number']}</td>";
        echo "<td>{$row['topic_file']}</td>";
        echo "<td>{$row['exercise_id']}</td>";
        echo "<td>{$row['xp_earned']}</td>";
        echo "<td>{$row['accuracy']}%</td>";
        echo "<td>{$row['completed_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Calculate today's XP
    $stmt = $pdo->prepare("
        SELECT SUM(xp_earned) as xp_today 
        FROM user_progress 
        WHERE user_id = ? AND DATE(completed_at) = CURDATE()
    ");
    $stmt->execute([$user_id]);
    $xp_today = $stmt->fetchColumn() ?: 0;
    
    echo "<p><strong>XP Today:</strong> $xp_today</p>";
    echo "<p><strong>Total XP All Time:</strong> $total_xp</p>";
    
} else {
    echo "<p style='color: red'>❌ No progress found in database for user $user_id</p>";
    echo "<p>Complete some exercises in lesson.php first!</p>";
}

// 2. Check user_streaks
$stmt = $pdo->prepare("SELECT * FROM user_streaks WHERE user_id = ?");
$stmt->execute([$user_id]);
$streak = $stmt->fetch();

echo "<h2>Streak Info:</h2>";
if ($streak) {
    echo "<pre>";
    print_r($streak);
    echo "</pre>";
} else {
    echo "<p>No streak record found</p>";
}

// 3. Check user_sessions
$stmt = $pdo->prepare("
    SELECT * FROM user_sessions 
    WHERE user_id = ? 
    ORDER BY session_date DESC 
    LIMIT 7
");
$stmt->execute([$user_id]);
$sessions = $stmt->fetchAll();

echo "<h2>Last 7 Days Sessions:</h2>";
if (count($sessions) > 0) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>Date</th><th>XP</th><th>Exercises</th></tr>";
    foreach ($sessions as $s) {
        echo "<tr>";
        echo "<td>{$s['session_date']}</td>";
        echo "<td>{$s['xp_earned']}</td>";
        echo "<td>{$s['exercises_completed']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No sessions found</p>";
}

// 4. Check user_languages
$stmt = $pdo->prepare("SELECT * FROM user_languages WHERE user_id = ?");
$stmt->execute([$user_id]);
$languages = $stmt->fetchAll();

echo "<h2>User Languages:</h2>";
if (count($languages) > 0) {
    echo "<pre>";
    print_r($languages);
    echo "</pre>";
} else {
    echo "<p>No languages found</p>";
}

// 5. Quick fix - Insert test data if needed
echo "<p><a href='?insert=1' style='background:#4CAF50; color:white; padding:10px 20px; text-decoration:none'>➕ Insert Test Data</a></p>";

if (isset($_GET['insert'])) {
    try {
        // Insert test progress
        $stmt = $pdo->prepare("
            INSERT INTO user_progress 
            (user_id, language_code, level_number, topic_file, exercise_id, completed, xp_earned, accuracy, time_spent, completed_at)
            VALUES 
            (?, 'rw', 1, 'animals.yaml', 1, 1, 10, 100, 5, NOW()),
            (?, 'rw', 1, 'animals.yaml', 2, 1, 15, 100, 8, NOW()),
            (?, 'rw', 1, 'animals.yaml', 3, 1, 10, 100, 6, NOW())
        ");
        $stmt->execute([$user_id, $user_id, $user_id]);
        
        // Update streak
        $stmt = $pdo->prepare("
            INSERT INTO user_streaks (user_id, current_streak, last_activity_date)
            VALUES (?, 1, CURDATE())
            ON DUPLICATE KEY UPDATE
            current_streak = current_streak + 1,
            last_activity_date = CURDATE()
        ");
        $stmt->execute([$user_id]);
        
        // Update session
        $stmt = $pdo->prepare("
            INSERT INTO user_sessions (user_id, session_date, xp_earned, exercises_completed, time_spent)
            VALUES (?, CURDATE(), 35, 3, 19)
            ON DUPLICATE KEY UPDATE
            xp_earned = xp_earned + 35,
            exercises_completed = exercises_completed + 3,
            time_spent = time_spent + 19
        ");
        $stmt->execute([$user_id]);
        
        echo "<p style='color:green'>✅ Test data inserted! <a href='?'>Refresh</a></p>";
        
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
    }
}
?>