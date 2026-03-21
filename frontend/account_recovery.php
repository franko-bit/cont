<?php
// account_recovery.php - Complete password recovery system
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // ACTION 1: Send verification code
    if ($action === 'send_code') {
        $email = trim($_POST['email']);
        
        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
            exit;
        }
        
        // Check if email exists in database
        $stmt = $conn->prepare("SELECT id, reset_attempts, reset_expires FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Email address not found']);
            exit;
        }
        
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        $reset_attempts = $user['reset_attempts'];
        $reset_expires = $user['reset_expires'];
        $stmt->close();
        
        // Reset attempts counter if last attempt was more than 1 hour ago
        if ($reset_expires && strtotime($reset_expires) < strtotime('-1 hour')) {
            $stmt = $conn->prepare("UPDATE users SET reset_attempts = 0 WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            $reset_attempts = 0;
        }
        
        // Check reset attempts (max 5 per hour)
        if ($reset_attempts >= 5) {
            echo json_encode(['success' => false, 'message' => 'Too many reset attempts. Please try again in 1 hour.']);
            exit;
        }
        
        // Generate 6-digit verification code
        $code = str_pad(strval(rand(100000, 999999)), 6, '0', STR_PAD_LEFT);
        
        // Generate unique reset token
        $reset_token = bin2hex(random_bytes(32));
        
        // Calculate expiry time (10 minutes from now)
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Update user record with code, token, and expiry
        $stmt = $conn->prepare("UPDATE users SET reset_code = ?, reset_token = ?, reset_expires = ?, reset_attempts = reset_attempts + 1 WHERE id = ?");
        $stmt->bind_param("sssi", $code, $reset_token, $expiry, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Send email via Formspree using cURL
            $formspreeUrl = 'https://formspree.io/f/xjgkwyrg';
            $emailMessage = "Your PLAYMATES password reset verification code is: " . $code . "\n\nThis code will expire in 10 minutes.\n\nIf you did not request this code, please ignore this email.";
            
            $postData = [
                'email' => $email,
                'message' => $emailMessage,
                '_subject' => 'PLAYMATES - Password Reset Code'
            ];
            
            $ch = curl_init($formspreeUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Verification code sent to your email',
                'token' => $reset_token,
                'debug_code' => $code // TEMPORARY - Remove in production
            ]);
        } else {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Failed to generate verification code. Please try again.']);
        }
        exit;
    }
    
    // ACTION 2: Verify code
    elseif ($action === 'verify_code') {
        $email = trim($_POST['email']);
        $code = trim($_POST['code']);
        
        if (empty($email) || empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Email and code are required']);
            exit;
        }
        
        // Check if code is valid and not expired
        $stmt = $conn->prepare("SELECT id, reset_token, reset_code, reset_expires FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $stmt->close();
            echo json_encode([
                'success' => false, 
                'message' => 'Email not found',
                'debug' => 'No user with this email'
            ]);
            exit;
        }
        
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Check if reset_expires exists and is not expired
        if (!$user['reset_expires'] || strtotime($user['reset_expires']) < time()) {
            echo json_encode([
                'success' => false, 
                'message' => 'Verification code has expired. Please request a new one.',
                'debug' => 'Expired: ' . $user['reset_expires'] . ' vs now: ' . date('Y-m-d H:i:s')
            ]);
            exit;
        }
        
        // Check if reset_code exists
        if (!$user['reset_code']) {
            echo json_encode([
                'success' => false, 
                'message' => 'No verification code found. Please request a new one.',
                'debug' => 'No code in database'
            ]);
            exit;
        }
        
        // Compare codes as strings (trim both to be safe)
        if (trim($user['reset_code']) === trim($code)) {
            echo json_encode([
                'success' => true, 
                'message' => 'Code verified successfully',
                'token' => $user['reset_token']
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid verification code. Please check and try again.',
                'debug' => 'Code mismatch: DB=' . $user['reset_code'] . ' Input=' . $code
            ]);
        }
        exit;
    }
    
    // ACTION 3: Reset password
    elseif ($action === 'reset_password') {
        $email = trim($_POST['email']);
        $token = trim($_POST['token']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate inputs
        if (empty($email) || empty($token) || empty($new_password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }
        
        if (strlen($new_password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
            exit;
        }
        
        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            exit;
        }
        
        // Verify token is valid and not expired
        $stmt = $conn->prepare("SELECT id, reset_expires FROM users WHERE email = ? AND reset_token = ?");
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Invalid reset session. Please start over.']);
            exit;
        }
        
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        $reset_expires = $user['reset_expires'];
        $stmt->close();
        
        // Check if token is expired
        if (!$reset_expires || strtotime($reset_expires) < time()) {
            echo json_encode(['success' => false, 'message' => 'Reset session has expired. Please start over.']);
            exit;
        }
        
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password and clear reset fields
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL, reset_token = NULL, reset_expires = NULL, reset_attempts = 0 WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode([
                'success' => true, 
                'message' => 'Password has been reset successfully!'
            ]);
        } else {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Failed to update password. Please try again.']);
        }
        exit;
    }
    
    else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }
}

else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$conn->close();
?>