<?php
// backend/config.php

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = "localhost";
$dbname = "playmates";
$username = "root";
$password = ""; // Change this to your MySQL password

// Site configuration
define('SITE_NAME', 'KinyaEnglish');
define('SITE_URL', 'http://localhost/language-platform');
define('API_URL', SITE_URL . '/backend');
define('VOICE_ROOMS_SECRET', getenv('VOICE_ROOMS_SECRET') ?: 'playmates-voice-rooms-local-secret');

define('ELEVENLABS_API_KEY', trim((string)(getenv('ELEVENLABS_API_KEY') ?: 'd342b11181769d1e33f9553d4e9314b570f7007750c43b6a9e41b68e5af3df56')));
define('ELEVENLABS_VOICE_ID', trim((string)(getenv('ELEVENLABS_VOICE_ID') ?: 'FwReqHRwmKpMSQaHBOCn')));
define('ELEVENLABS_MODEL_ID', trim((string)(getenv('ELEVENLABS_MODEL_ID') ?: 'eleven_multilingual_v2')));

// Image CDN links
define('CDN_URL', 'https://images.unsplash.com');
define('DEFAULT_LEVEL_IMAGE', CDN_URL . '/photo-1503676260728-5177c2b399b2?w=400');
define('DEFAULT_CATEGORY_ICON', 'https://img.icons8.com/color/96/000000/category.png');
define('DEFAULT_BADGE_ICON', 'https://img.icons8.com/color/96/000000/medal.png');
define('DEFAULT_AVATAR', 'https://img.icons8.com/color/96/000000/user.png');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Ensure verification columns exist in users table
    try {
        $columns = $pdo->query("DESCRIBE users")->fetchAll();
        $column_names = array_column($columns, 'Field');
        
        if (!in_array('verified', $column_names)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN verified TINYINT(1) DEFAULT 0 AFTER id");
        }
        if (!in_array('verification_status', $column_names)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN verification_status VARCHAR(50) DEFAULT 'pending' AFTER verified");
        }
        if (!in_array('didit_session_id', $column_names)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN didit_session_id VARCHAR(255) NULL AFTER verification_status");
        }
    } catch (Exception $e) {
        // Users table may not exist yet
    }

    try {
        $attemptColumns = $pdo->query("DESCRIBE exam_attempts")->fetchAll();
        $attemptColumnNames = array_column($attemptColumns, 'Field');
        if (!in_array('didit_session_id', $attemptColumnNames)) {
            $pdo->exec("ALTER TABLE exam_attempts ADD COLUMN didit_session_id VARCHAR(255) NULL AFTER verification_status");
        }
    } catch (Exception $e) {
        // exam_attempts table may not exist yet
    }
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}

function getPDO() {
    global $pdo;
    return $pdo;
}

// Helper function to send JSON response
function sendResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Helper function to validate user session
function validateSession() {
    if (!isset($_SESSION['user_id'])) {
        sendResponse(false, 'Not authenticated');
    }
    return $_SESSION['user_id'];
}
?>