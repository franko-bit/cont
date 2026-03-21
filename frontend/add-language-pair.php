<?php
// Catch ALL PHP errors/warnings and return them as JSON instead of crashing
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'message'=>"PHP Error [$errno]: $errstr in $errfile on line $errline"]);
    exit;
});
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'],[E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR])) {
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>false,'message'=>"Fatal: {$err['message']} in {$err['file']} on line {$err['line']}"]);
    }
});
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

$config_path = '../backend/config.php';
if (!file_exists($config_path)) {
    echo json_encode(['success'=>false,'message'=>'config.php not found at expected path: '.$config_path.' (script is at '.realpath(__FILE__).')']);
    exit;
}

session_start();
require_once $config_path;

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Not logged in — session may have expired']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !is_array($data)) {
    echo json_encode(['success'=>false,'message'=>'Could not parse JSON body. Received: '.substr($raw,0,200)]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$code    = trim($data['language_code'] ?? '');
$name    = trim($data['language_name']  ?? '');

$allowed = ['en','rw','fr','sw','es','de'];
if (!in_array($code, $allowed, true)) {
    echo json_encode(['success'=>false,'message'=>'Invalid language code: "'.$code.'"']);
    exit;
}
if (empty($name)) {
    echo json_encode(['success'=>false,'message'=>'Language name is empty']);
    exit;
}
if (!isset($pdo)) {
    echo json_encode(['success'=>false,'message'=>'$pdo not set after config.php loaded — check config.php']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO user_languages (user_id, language_code, language_name) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $code, $name]);
    echo json_encode(['success'=>true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Database error: '.$e->getMessage()]);
}