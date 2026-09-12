<?php
/**
 * add-language-pair.php
 * Handles adding new language pairs for users
 * Location: /frontend/add-language-pair.php
 */

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/php-error.log');
error_reporting(E_ALL);
ob_start();
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    while (ob_get_level()) ob_end_clean();
    
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
    }
      
    echo json_encode([
        'success' => false,
        'message' => "PHP Error [$errno]: $errstr in " . basename($errfile) . " on line $errline"
    ]);
    exit;
});

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Clear any output buffers
        while (ob_get_level()) ob_end_clean();
        
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        
        echo json_encode([
            'success' => false,
            'message' => "Fatal Error: {$error['message']} in " . basename($error['file']) . " on line {$error['line']}"
        ]);
    }
});

// Disable error display for production
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// ==================== DATABASE CONNECTION ====================
// Include database configuration
require_once '../backend/config.php';

// ==================== SESSION CHECK ====================
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in. Please login first.',
        'redirect' => '../loginui.php'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// ==================== REQUEST METHOD CHECK ====================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Please use POST request.'
    ]);
    exit;
}

// ==================== PARSE INPUT DATA ====================
$pair_code = trim($_POST['pair_code'] ?? '');

$allowed_pairs = [
    'en-rw' => ['English', 'Kinyarwanda'],
    'fr-rw' => ['French', 'Kinyarwanda'],
    'en-fr' => ['English', 'French'],
    'rw-en' => ['Kinyarwanda', 'English'],
    'rw-fr' => ['Kinyarwanda', 'French'],
    'fr-en' => ['French', 'English'],
    'en-sw' => ['English', 'Kiswahili'],
    'sw-en' => ['Kiswahili', 'English'],
    'fr-sw' => ['French', 'Kiswahili'],
    'sw-fr' => ['Kiswahili', 'French'],
];

$language_names = [
    'en' => 'English',
    'rw' => 'Kinyarwanda', 
    'fr' => 'French',
    'sw' => 'Swahili',
    'es' => 'Spanish',
    'de' => 'German'
];

if (empty($pair_code) || !array_key_exists($pair_code, $allowed_pairs)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid pair code. Allowed: ' . implode(', ', array_keys($allowed_pairs))
    ]);
    exit;
}

// ==================== DATABASE CONNECTION CHECK ====================
// PDO connection is now established above

// ==================== CHECK IF TABLE EXISTS ====================
try {
    // Check if user_language_pairs table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_language_pairs'");
    if ($stmt->rowCount() == 0) {
        // Create the table if it doesn't exist
        $create_table_sql = "
            CREATE TABLE IF NOT EXISTS user_language_pairs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                pair_code VARCHAR(10) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_pair (user_id, pair_code),
                INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $pdo->exec($create_table_sql);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to verify database table: ' . $e->getMessage()
    ]);
    exit;
}

// ==================== ADD PAIR ====================
try {
    // Check if pair already exists for this user
    $stmt = $pdo->prepare("SELECT id FROM user_language_pairs WHERE user_id = ? AND pair_code = ?");
    $stmt->execute([$user_id, $pair_code]);
    
    if ($stmt->fetch()) {
        // Pair already exists
        $pair_info = $allowed_pairs[$pair_code];
        $pair_display = $pair_info[0] . ' → ' . $pair_info[1];
        
        echo json_encode([
            'success' => false,
            'message' => 'Language pair "' . $pair_display . '" is already in your list',
            'already_exists' => true
        ]);
        exit;
    }
    
    // Insert new language pair
    $stmt = $pdo->prepare("INSERT INTO user_language_pairs (user_id, pair_code) VALUES (?, ?)");
    $result = $stmt->execute([$user_id, $pair_code]);
    
    if ($result) {
        // Clear any output buffers
        while (ob_get_level()) ob_end_clean();
        
        $pair_info = $allowed_pairs[$pair_code];
        $pair_display = $pair_info[0] . ' → ' . $pair_info[1];
        
        // Success response
        echo json_encode([
            'success' => true,
            'message' => 'Language pair added successfully!',
            'pair_code' => $pair_code,
            'source_language' => $pair_info[0],
            'target_language' => $pair_info[1],
            'display_name' => $pair_display
        ]);
    } else {
        throw new Exception('Failed to insert language pair');
    }
    
} catch (PDOException $e) {
    // Handle database errors
    $error_message = $e->getMessage();
    
    // Check for duplicate entry error
    if (strpos($error_message, 'Duplicate entry') !== false) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'This language pair is already in your list'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $error_message
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>