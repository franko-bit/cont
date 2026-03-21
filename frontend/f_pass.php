<?php
// forgot-password.php
require_once '../backend/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Email is required';
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset code
            $reset_code = sprintf("%06d", random_int(0, 999999));
            $reset_token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Save reset data
            $stmt = $pdo->prepare("
                UPDATE users 
                SET reset_code = ?, reset_token = ?, reset_expires = ?, reset_attempts = 0
                WHERE id = ?
            ");
            $stmt->execute([$reset_code, $reset_token, $expires, $user['id']]);
            
            // In production, send email here
            // For demo, show the code
            $success = "Reset code: $reset_code (In production, this would be emailed)";
        } else {
            // Don't reveal if email exists
            $success = "If your email exists in our system, you'll receive a reset code.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password · PLAYMATES</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{
            --bg:#f0f2e6;--surface:#fff;--ink:#1e2b1e;--green:#58cc02;--green-dark:#3f9a00;
            --yellow:#ffdc6a;--border:#d4d8c8;--border-dark:#bcc0b0;
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
        .card{
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
        }
        h1{
            font-family:var(--font-serif);
            font-size:2.5rem;
            font-style:italic;
            margin-bottom:8px;
        }
        h1 span{color:var(--green)}
        .form-group{
            margin-bottom:20px;
        }
        input{
            width:100%;
            padding:14px 18px;
            border:3px solid var(--border);
            border-radius:60px;
            font-family:var(--font-sans);
            outline:none;
        }
        .btn{
            width:100%;
            padding:16px;
            border:none;
            border-radius:60px;
            font-family:var(--font-serif);
            font-size:1.2rem;
            font-style:italic;
            cursor:pointer;
            background:var(--green);
            color:white;
            border:2px solid var(--green-dark);
            box-shadow:0 6px 0 var(--green-dark);
            margin-bottom:16px;
        }
        .btn:active{
            transform:translateY(4px);
            box-shadow:0 2px 0 var(--green-dark);
        }
        .error{
            background:#ffebee;
            border:2px solid #ef5350;
            border-radius:60px;
            padding:12px;
            margin-bottom:20px;
        }
        .success{
            background:#e8f5e9;
            border:2px solid var(--green);
            border-radius:60px;
            padding:12px;
            margin-bottom:20px;
        }
        .back-link{
            text-align:center;
        }
        .back-link a{
            color:var(--green);
            text-decoration:none;
            font-weight:600;
        }
    </style>
</head>
<body>
    <div class="card">
        <a href="index.php" class="logo">PLAYMATES</a>
        
        <h1>Reset <span>Password</span></h1>
        <p style="margin-bottom:24px">Enter your email to receive a reset code</p>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <input type="email" name="email" placeholder="Your email address" required>
            </div>
            
            <button type="submit" class="btn">Send Reset Code →</button>
            
            <div class="back-link">
                <a href="signin.php">← Back to Sign In</a>
            </div>
        </form>
    </div>
</body>
</html>