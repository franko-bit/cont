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
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
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