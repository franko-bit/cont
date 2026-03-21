<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PLAYMATES</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="nav-logo">
            <a href="index.php" class="logo-link">
                <img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="PLAYMATES Logo" class="logo-image">
            </a>
        </div>
        <a href="index.php" class="back-home">← BACK TO HOME</a>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Login to your PLAYMATES account</p>
            </div>

            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label for="loginEmail">Email Address</label>
                    <input type="email" id="loginEmail" name="email" required placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" required placeholder="Enter your password">
                    <button type="button" class="password-toggle" onclick="togglePassword('loginPassword')">Show</button>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" id="rememberMe" name="rememberMe">
                        Remember me
                    </label>
                    <a href="f_pass.php" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="submit-btn">Login</button>

                <div class="form-footer">
                    Don't have an account? <a href="signupp.php">Sign up here</a>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        © 2025 PLAYMATES - Educational Games Platform
    </footer>

    <!-- Only include javascript.js - it has the proper login handler -->
    <script src="javascript.js"></script>
</body>
</html>