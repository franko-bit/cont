<?php
// signin.php
require_once '../backend/config.php';

// Validate and sanitize redirect URL
function sanitizeRedirect($url) {
    // Default to dashboard if invalid
    $default = 'dashboard.php';
    // Block open redirects (full URLs with ://)
    if (filter_var($url, FILTER_VALIDATE_URL) || strpos($url, '://') !== false) {
        return $default;
    }
    // Allow relative paths with /, ./, or ../
    if (strpos($url, '/') !== 0 && strpos($url, './') !== 0 && strpos($url, '../') !== 0) {
        $url = './' . $url;
    }
    return $url;
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirect_to = sanitizeRedirect($_GET['redirect_to'] ?? $_POST['redirect_to'] ?? 'dashboard.php');
    if (empty($_SESSION['institution_id']) && strpos($redirect_to, 'exam-dashboard.php') !== false) {
        $redirect_to = 'dashboard.php';
    }
    header("Location: " . $redirect_to);
    exit;
}

$error = '';

// Get redirect URL from query or form
$redirect_to = sanitizeRedirect($_GET['redirect_to'] ?? $_POST['redirect_to'] ?? 'dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        try {
            // Get user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Success - set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Get institution_id for this user
                $inst_stmt = $pdo->prepare("SELECT institution_id FROM institution_users WHERE user_id = ? LIMIT 1");
                $inst_stmt->execute([$user['id']]);
                $inst_row = $inst_stmt->fetch();
                if ($inst_row) {
                    $_SESSION['institution_id'] = $inst_row['institution_id'];
                }
                
                // Debug: Log session was set
                error_log("Login success for user_id: " . $user['id'] . ", session_id: " . session_id() . ", institution_id: " . ($_SESSION['institution_id'] ?? 'none'));
                
                if (empty($_SESSION['institution_id']) && strpos($redirect_to, 'exam-dashboard.php') !== false) {
                    $redirect_to = 'dashboard.php';
                }
                
                header("Location: " . $redirect_to);
                exit;
            } else {
                $error = 'Invalid email or password';
                error_log("Login failed for email: $email - user found: " . ($user ? 'yes' : 'no'));
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
            error_log("Login PDO error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In · PLAYMATES</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{
            --bg:#f0f2e6;--surface:#fff;--ink:#1e2b1e;--green:#58cc02;--green-dark:#3f9a00;
            --yellow:#ffdc6a;--yellow-dark:#e5b800;--border:#d4d8c8;--border-dark:#bcc0b0;
            --font-serif:'Instrument Serif',serif;--font-sans:'DM Sans',sans-serif;
        }
        body{
            font-family:var(--font-sans);
            background:linear-gradient(135deg,var(--bg) 0%,#e0e4d5 100%);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:20px;
        }
        .signin-card{
            background:var(--surface);
            border:3px solid var(--border-dark);
            border-radius:48px;
            padding:48px;
            max-width:480px;
            width:100%;
            box-shadow:16px 16px 0 var(--border);
            animation:slideUp .5s ease;
        }
        @keyframes slideUp{
            from{opacity:0;transform:translateY(30px)}
            to{opacity:1;transform:translateY(0)}
        }
        .logo{
            font-family:var(--font-serif);
            font-size:2.2rem;
            font-style:italic;
            color:var(--green-dark);
            text-decoration:none;
            background:var(--yellow);
            padding:6px 20px 4px;
            border-radius:40px 12px 40px 12px;
            display:inline-block;
            margin-bottom:24px;
            border:2px solid var(--border-dark);
            box-shadow:0 6px 0 var(--yellow-dark);
        }
        h1{
            font-family:var(--font-serif);
            font-size:2.8rem;
            font-style:italic;
            margin-bottom:8px;
        }
        h1 span{color:var(--green)}
        .subtitle{
            color:#6b7b6b;
            margin-bottom:32px;
            font-size:1rem;
        }
        .form-group{
            margin-bottom:20px;
        }
        label{
            display:block;
            font-weight:700;
            font-size:0.8rem;
            text-transform:uppercase;
            letter-spacing:1px;
            margin-bottom:8px;
            color:var(--ink);
        }
        input{
            width:100%;
            padding:14px 18px;
            border:3px solid var(--border);
            border-radius:60px;
            font-family:var(--font-sans);
            font-size:0.95rem;
            outline:none;
            transition:.2s;
            background:#FAFEFA;
        }
        input:focus{
            border-color:var(--green);
            box-shadow:0 0 0 4px rgba(88,204,2,.15);
        }
        .checkbox-group{
            display:flex;
            align-items:center;
            gap:12px;
            margin-bottom:24px;
        }
        .checkbox-group input[type="checkbox"]{
            width:20px;
            height:20px;
            border:3px solid var(--border);
            border-radius:6px;
            cursor:pointer;
        }
        .checkbox-group label{
            margin-bottom:0;
            text-transform:none;
            font-size:.9rem;
            cursor:pointer;
        }
        .btn{
            width:100%;
            padding:16px;
            border:none;
            border-radius:60px;
            font-family:var(--font-serif);
            font-size:1.3rem;
            font-style:italic;
            cursor:pointer;
            transition:.1s;
            margin-bottom:16px;
        }
        .btn-primary{
            background:var(--green);
            color:white;
            border:2px solid var(--green-dark);
            box-shadow:0 6px 0 var(--green-dark);
        }
        .btn-primary:active{
            transform:translateY(4px);
            box-shadow:0 2px 0 var(--green-dark);
        }
        .error{
            background:#ffebee;
            border:2px solid #ef5350;
            border-radius:60px;
            padding:12px 20px;
            color:#c62828;
            font-weight:600;
            margin-bottom:20px;
            font-size:.9rem;
        }
        .links{
            display:flex;
            justify-content:space-between;
            align-items:center;
            font-size:.9rem;
        }
        .links a{
            color:var(--green);
            text-decoration:none;
            font-weight:600;
        }
        .links a:hover{
            text-decoration:underline;
        }
        .signup-link{
            text-align:center;
            margin-top:24px;
            padding-top:24px;
            border-top:2px solid var(--border);
            font-size:.95rem;
        }
        .signup-link a{
            color:var(--green);
            font-weight:700;
            text-decoration:none;
        }
        .signup-link a:hover{
            text-decoration:underline;
        }
    </style>
</head>
<body>
    <div class="signin-card">
        <a href="index.php" class="logo">PLAYMATES</a>
        
        <h1>Welcome <span>Back!</span></h1>
        <p class="subtitle">Sign in to continue your language journey</p>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirect_to) ?>">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Remember me for 30 days</label>
            </div>
            
            <button type="submit" class="btn btn-primary">Sign In →</button>
            
            <div class="links">
                <a href="forgot-password.php">Forgot password?</a>
                <a href="resend-verification.php">Resend verification</a>
            </div>
            
            <div class="signup-link">
                New to PLAYMATES? <a href="signup.php">Create an account</a>
            </div>
        </form>
    </div>
</body>
</html>