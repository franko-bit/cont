<?php
// backend/auth.php

require_once 'config.php';

class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Signup function
    public function signup($full_name, $email, $password, $is_student = true, $school_name = null, $student_id = null) {
        try {
            // Validate input
            if (empty($full_name) || empty($email) || empty($password)) {
                return ["success" => false, "message" => "All fields are required"];
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ["success" => false, "message" => "Invalid email format"];
            }
            
            if (strlen($password) < 6) {
                return ["success" => false, "message" => "Password must be at least 6 characters"];
            }
            
            // Check if email exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                return ["success" => false, "message" => "Email already registered"];
            }
            
            // Hash password
            $hash = password_hash($password, PASSWORD_BCRYPT);
            
            // Generate reset token (for future use)
            $reset_token = bin2hex(random_bytes(32));
            
            // Insert user
            $stmt = $this->pdo->prepare("
                INSERT INTO users (full_name, email, password, is_student, school_name, student_id, reset_token) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$full_name, $email, $hash, $is_student, $school_name, $student_id, $reset_token]);
            
            $user_id = $this->pdo->lastInsertId();
            
            // Initialize leaderboard entry
            $stmt = $this->pdo->prepare("INSERT INTO leaderboard (user_id, xp, streak) VALUES (?, 0, 0)");
            $stmt->execute([$user_id]);
            
            return [
                "success" => true, 
                "message" => "Signup successful",
                "user_id" => $user_id
            ];
            
        } catch (Exception $e) {
            error_log("Signup error: " . $e->getMessage());
            return ["success" => false, "message" => "Signup failed. Please try again."];
        }
    }
    
    // Login function
    public function login($email, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ["success" => false, "message" => "Email not found"];
            }
            
            if (!password_verify($password, $user['password'])) {
                return ["success" => false, "message" => "Wrong password"];
            }
            
            // Update last login
            $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_student'] = $user['is_student'];
            
            // Check and update streak
            $this->updateStreak($user['id']);
            
            return [
                "success" => true, 
                "user" => [
                    "id" => $user['id'],
                    "full_name" => $user['full_name'],
                    "email" => $user['email'],
                    "is_student" => $user['is_student'],
                    "school_name" => $user['school_name']
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ["success" => false, "message" => "Login failed. Please try again."];
        }
    }
    
    // Update user streak
    private function updateStreak($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT last_activity, current_streak, longest_streak 
                FROM users WHERE id = ?
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            $today = date('Y-m-d');
            $last_activity = $user['last_activity'] ?? null;
            $current_streak = $user['current_streak'] ?? 0;
            $longest_streak = $user['longest_streak'] ?? 0;
            
            if ($last_activity == $today) {
                // Already logged in today, streak unchanged
                return;
            }
            
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            
            if ($last_activity == $yesterday) {
                // Consecutive day
                $current_streak++;
            } else {
                // Streak broken
                $current_streak = 1;
            }
            
            // Update longest streak if needed
            if ($current_streak > $longest_streak) {
                $longest_streak = $current_streak;
            }
            
            // Update user record
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET last_activity = ?, current_streak = ?, longest_streak = ? 
                WHERE id = ?
            ");
            $stmt->execute([$today, $current_streak, $longest_streak, $user_id]);
            
            // Update leaderboard streak
            $stmt = $this->pdo->prepare("
                UPDATE leaderboard SET streak = ? WHERE user_id = ?
            ");
            $stmt->execute([$current_streak, $user_id]);
            
        } catch (Exception $e) {
            error_log("Streak update error: " . $e->getMessage());
        }
    }
    
    // Logout function
    public function logout() {
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-3600, '/');
        }
        session_destroy();
        return ["success" => true, "message" => "Logged out successfully"];
    }
    
    // Password reset request
    public function requestPasswordReset($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ["success" => false, "message" => "Email not found"];
            }
            
            // Generate reset code
            $reset_code = sprintf("%06d", mt_rand(1, 999999));
            $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $this->pdo->prepare("
                UPDATE users SET reset_code = ?, reset_expires = ?, reset_attempts = 0 
                WHERE id = ?
            ");
            $stmt->execute([$reset_code, $reset_expires, $user['id']]);
            
            // In production, send email here
            // For now, return the code for testing
            return [
                "success" => true, 
                "message" => "Reset code generated",
                "reset_code" => $reset_code // Remove this in production
            ];
            
        } catch (Exception $e) {
            error_log("Password reset request error: " . $e->getMessage());
            return ["success" => false, "message" => "Failed to process request"];
        }
    }
    
    // Verify reset code and reset password
    public function resetPassword($email, $code, $new_password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, reset_attempts 
                FROM users 
                WHERE email = ? AND reset_code = ? AND reset_expires > NOW()
            ");
            $stmt->execute([$email, $code]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ["success" => false, "message" => "Invalid or expired reset code"];
            }
            
            // Update password
            $hash = password_hash($new_password, PASSWORD_BCRYPT);
            
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET password = ?, reset_code = NULL, reset_expires = NULL 
                WHERE id = ?
            ");
            $stmt->execute([$hash, $user['id']]);
            
            return ["success" => true, "message" => "Password reset successful"];
            
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ["success" => false, "message" => "Failed to reset password"];
        }
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth($pdo);
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'signup':
            $result = $auth->signup(
                $_POST['full_name'] ?? '',
                $_POST['email'] ?? '',
                $_POST['password'] ?? '',
                $_POST['is_student'] ?? true,
                $_POST['school_name'] ?? null,
                $_POST['student_id'] ?? null
            );
            sendResponse($result['success'], $result['message'], $result);
            break;
            
        case 'login':
            $result = $auth->login(
                $_POST['email'] ?? '',
                $_POST['password'] ?? ''
            );
            sendResponse($result['success'], $result['message'], $result);
            break;
            
        case 'logout':
            $result = $auth->logout();
            sendResponse($result['success'], $result['message']);
            break;
            
        case 'reset_request':
            $result = $auth->requestPasswordReset($_POST['email'] ?? '');
            sendResponse($result['success'], $result['message'], $result);
            break;
            
        case 'reset_password':
            $result = $auth->resetPassword(
                $_POST['email'] ?? '',
                $_POST['code'] ?? '',
                $_POST['new_password'] ?? ''
            );
            sendResponse($result['success'], $result['message']);
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
}
?>