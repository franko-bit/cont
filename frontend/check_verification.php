<?php
require_once '../backend/config.php';

// Define API key
$apiKey = "5llHSFsLPLkehuL671j-slRF6Hm5dqyvJkXapkve9bQ";

// Enable error logging for debugging
ini_set('display_errors', 0);
error_reporting(E_ALL);
$error_log = __DIR__ . '/../verification_debug.log';

// Log incoming callback data
error_log("[" . date('Y-m-d H:i:s') . "] check_verification GET: " . print_r($_GET, true), 3, $error_log);

// Check if this is a callback from Didit
$sessionId = $_GET['verificationSessionId'] ?? $_GET['session_id'] ?? $_GET['sessionId'] ?? null;
$callbackStatus = $_GET['status'] ?? null;
$attempt_id = (int)($_GET['attempt_id'] ?? 0);

if ($sessionId) {
    // Handle callback - find user by session ID
    // Find user by session id from didit_session_id or vendor_data if necessary
    $stmt = $pdo->prepare("SELECT id FROM users WHERE didit_session_id = ?");
    $stmt->execute([$sessionId]);
    $user = $stmt->fetch();
    
    if (!$user && !empty($_GET['vendor_data'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$_GET['vendor_data']]);
        $user = $stmt->fetch();
    }

    if (!$user) {
        $_SESSION['error'] = "Verification session not found.";
        header("Location: signin.php");
        exit;
    }
    
    $userId = $user['id'];
    $_SESSION['user_id'] = $userId; // Ensure user is logged in for session
    
    if ($attempt_id === 0) {
        $stmt = $pdo->prepare("SELECT id FROM exam_attempts WHERE didit_session_id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$sessionId, $userId]);
        $attemptRow = $stmt->fetch();
        if ($attemptRow) {
            $attempt_id = (int)$attemptRow['id'];
        }
    }
    
    // If status provided in callback, use it directly
    if ($callbackStatus) {
        $status = strtolower($callbackStatus);
    } else {
        // Check status from API
        $url = "https://verification.didit.me/v3/session/" . $sessionId . "/decision/";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "x-api-key: $apiKey"
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $_SESSION['error'] = "Error checking verification status.";
            header("Location: onboard.php");
            exit;
        }
        
        $result = json_decode($response, true);
        $status = strtolower($result["status"] ?? "unknown");
        
        // Log the response for debugging
        error_log("[" . date('Y-m-d H:i:s') . "] Didit API Response (callback): " . print_r($result, true), 3, $error_log);
    }
} else {
    // Manual check - user must be logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: signin.php");
        exit;
    }

    $userId = $_SESSION['user_id'];
    $attempt_id = (int)($_GET['attempt_id'] ?? 0);

    // Get session ID from database
    $stmt = $pdo->prepare("SELECT didit_session_id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || empty($user['didit_session_id'])) {
        $_SESSION['error'] = "No verification session found. Please start verification first.";
        header("Location: onboard.php");
        exit;
    }

    $sessionId = $user['didit_session_id'];

    // Check status from Didit API
    $url = "https://verification.didit.me/v3/session/" . $sessionId . "/decision/";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-api-key: $apiKey"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        $_SESSION['error'] = "Error checking verification status.";
        header("Location: onboard.php");
        exit;
    }

    $result = json_decode($response, true);
    $status = $result["status"] ?? "unknown";
    
    // Log the response for debugging
    error_log("[" . date('Y-m-d H:i:s') . "] Didit API Response (manual): " . print_r($result, true), 3, $error_log);
}

// Log verification status
error_log("[" . date('Y-m-d H:i:s') . "] Verification Status: " . $status . " | UserId: " . $userId . " | AttemptId: " . $attempt_id, 3, $error_log);

// Update database based on verification status
if ($status === "approved") {
    $stmt = $pdo->prepare("UPDATE users SET verified = 1, verification_status = 'approved' WHERE id = ?");
    $result = $stmt->execute([$userId]);
    
    // Verify the update worked
    $stmt = $pdo->prepare("SELECT verified, verification_status FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user_check = $stmt->fetch();
    
    error_log("[" . date('Y-m-d H:i:s') . "] Database Update Result: verified=" . $user_check['verified'] . " status=" . $user_check['verification_status'], 3, $error_log);
    
    // Check if there's an attempt_id in the URL (from take-exam.php flow)
    $attempt_id = (int)($_GET['attempt_id'] ?? 0);
    if ($attempt_id > 0) {
        // Update the exam attempt status to 'fully_verified' so user can proceed to onboard
        $stmt = $pdo->prepare("UPDATE exam_attempts SET verification_status = 'fully_verified' WHERE id = ? AND user_id = ?");
        $stmt->execute([$attempt_id, $userId]);
        
        // Redirect to onboard.php to complete the flow
        header("Location: onboard.php?attempt_id=" . $attempt_id);
        exit;
    } else {
        // No specific attempt, redirect to onboard.php for general verification completion
        $_SESSION['success'] = "  Identity verified successfully!";
        header("Location: onboard.php");
        exit;
    }
    
} elseif ($status === "declined") {
    $stmt = $pdo->prepare("UPDATE users SET verification_status = 'declined' WHERE id = ?");
    $stmt->execute([$userId]);
    
    if ($attempt_id > 0) {
        // Update attempt status to failed
        $stmt = $pdo->prepare("UPDATE exam_attempts SET verification_status = 'failed' WHERE id = ? AND user_id = ?");
        $stmt->execute([$attempt_id, $userId]);
        
        $_SESSION['error'] = "❌ Verification failed. Please try again.";
        header("Location: verify.php?attempt_id=" . $attempt_id);
        exit;
    } else {
        $_SESSION['error'] = "❌ Verification failed. Please try again.";
        header("Location: onboard.php");
        exit;
    }
    
} elseif ($status === "pending") {
    if ($attempt_id > 0) {
        $_SESSION['info'] = "⏳ Verification still pending. Please check again in a few moments.";
        header("Location: check_verification.php?attempt_id=" . $attempt_id);
        exit;
    } else {
        $_SESSION['info'] = "⏳ Verification still pending. Please check again in a few moments.";
        header("Location: onboard.php");
        exit;
    }
    
} else {
    if ($attempt_id > 0) {
        $_SESSION['error'] = "Unknown verification status: " . $status;
        header("Location: verify.php?attempt_id=" . $attempt_id);
        exit;
    } else {
        $_SESSION['error'] = "Unknown verification status: " . $status;
        header("Location: onboard.php");
        exit;
    }
}
?>