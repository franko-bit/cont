<?php
// signup.php
require_once '../backend/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $is_student = isset($_POST['is_student']) ? 1 : 0;
    $school_name = trim($_POST['school_name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $daily_goal = (int)($_POST['daily_goal'] ?? 20);

    // Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif ($is_student && (empty($school_name) || empty($student_id))) {
        $error = 'School name and student ID are required for students';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email already registered';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $pdo->prepare("
                INSERT INTO users (
                    full_name, email, password, created_at, last_login,
                    current_streak, longest_streak, total_xp, daily_goal,
                    is_student, school_name, student_id
                ) VALUES (
                    ?, ?, ?, NOW(), NOW(),
                    0, 0, 0, ?,
                    ?, ?, ?
                )
            ");
            
            try {
                $stmt->execute([
                    $full_name, $email, $hashed_password, $daily_goal,
                    $is_student, $school_name, $student_id
                ]);
                
                $user_id = $pdo->lastInsertId();
                
                // Auto login
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $full_name;
                $_SESSION['user_email'] = $email;
                
                $success = 'Account created successfully! Redirecting...';
                
                // Redirect after 2 seconds
                header("refresh:2;url=dashboard.php");
                
            } catch (PDOException $e) {
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up · PLAYMATES</title>
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
        .signup-card{
            background:var(--surface);
            border:3px solid var(--border-dark);
            border-radius:48px;
            padding:48px;
            max-width:560px;
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
        input, select{
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
            margin-bottom:16px;
        }
        .checkbox-group input[type="checkbox"]{
            width:24px;
            height:24px;
            border:3px solid var(--border);
            border-radius:8px;
            cursor:pointer;
        }
        .student-fields{
            background:var(--yellow-light);
            border:2px solid var(--yellow-dark);
            border-radius:30px;
            padding:20px;
            margin-top:16px;
            margin-bottom:16px;
            display:none;
        }
        .student-fields.show{
            display:block;
            animation:fadeIn .3s ease;
        }
        @keyframes fadeIn{
            from{opacity:0;transform:translateY(-10px)}
            to{opacity:1;transform:translateY(0)}
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
        .success{
            background:#e8f5e9;
            border:2px solid var(--green);
            border-radius:60px;
            padding:12px 20px;
            color:var(--green-dark);
            font-weight:600;
            margin-bottom:20px;
            font-size:.9rem;
        }
        .login-link{
            text-align:center;
            font-size:.95rem;
            color:#6b7b6b;
        }
        .login-link a{
            color:var(--green);
            font-weight:700;
            text-decoration:none;
        }
        .login-link a:hover{
            text-decoration:underline;
        }
        .goal-select{
            width:100%;
            padding:14px 18px;
            border:3px solid var(--border);
            border-radius:60px;
            font-family:var(--font-sans);
            font-size:0.95rem;
            background:#FAFEFA;
        }
    </style>
</head>
<body>
    <div class="signup-card">
        <a href="index.php" class="logo">PLAYMATES</a>
        
        <h1>Create <span>Account</span></h1>
        <p class="subtitle">Join the PLAYMATES community and start your language journey</p>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" id="signupForm">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <label>Daily Goal (XP)</label>
                <select name="daily_goal" class="goal-select">
                    <option value="10">10 XP - Casual</option>
                    <option value="20" selected>20 XP - Regular</option>
                    <option value="30">30 XP - Dedicated</option>
                    <option value="50">50 XP - Serious</option>
                    <option value="100">100 XP - Hardcore</option>
                </select>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" name="is_student" id="is_student" value="1" <?= isset($_POST['is_student']) ? 'checked' : '' ?>>
                <label for="is_student" style="margin-bottom:0;cursor:pointer">I am a student</label>
            </div>
            
            <div class="student-fields <?= isset($_POST['is_student']) ? 'show' : '' ?>" id="studentFields">
                <div class="form-group">
                    <label>School/Institution Name</label>
                    <input type="text" name="school_name" value="<?= htmlspecialchars($_POST['school_name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Sign Up →</button>
            
            <p class="login-link">
                Already have an account? <a href="signin.php">Sign In</a>
            </p>
        </form>
    </div>

    <script>
        // Toggle student fields
        const studentCheckbox = document.getElementById('is_student');
        const studentFields = document.getElementById('studentFields');
        
        studentCheckbox.addEventListener('change', function() {
            if (this.checked) {
                studentFields.classList.add('show');
                document.querySelector('[name="school_name"]').required = true;
                document.querySelector('[name="student_id"]').required = true;
            } else {
                studentFields.classList.remove('show');
                document.querySelector('[name="school_name"]').required = false;
                document.querySelector('[name="student_id"]').required = false;
            }
        });
        
        // Password match validation
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const password = document.querySelector('[name="password"]').value;
            const confirm = document.querySelector('[name="confirm_password"]').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>