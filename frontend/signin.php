<?php
// signin.php
require_once '../backend/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        // Get user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Update last login and streak
            $today = date('Y-m-d');
            $last_login = date('Y-m-d', strtotime($user['last_login']));
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            
            // Calculate streak
            if ($last_login == $yesterday) {
                // Consecutive day
                $new_streak = $user['current_streak'] + 1;
            } elseif ($last_login == $today) {
                // Already logged in today
                $new_streak = $user['current_streak'];
            } else {
                // Streak broken
                $new_streak = 1;
            }
            
            // Update longest streak if needed
            $longest_streak = max($user['longest_streak'], $new_streak);
            
            // Update user
            $stmt = $pdo->prepare("
                UPDATE users 
                SET last_login = NOW(), 
                    current_streak = ?,
                    longest_streak = ?,
                    last_activity = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$new_streak, $longest_streak, $user['id']]);

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['is_student'] ? 'student' : 'learner';

            // Set remember me cookie (30 days)
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                // Store token in database (you'd need a remember_tokens table)
                // For simplicity, we'll skip this part
                
                setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
            }

            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Invalid email or password';
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