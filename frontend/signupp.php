<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - PLAYMATES</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* ── Student Toggle ── */
        .student-toggle-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border: 1px solid #e0ddd6;
            border-radius: 10px;
            background: #faf9f6;
            margin-bottom: 1px;
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .student-toggle-group.active {
            background: #edf7f2;
            border-color: #006f4a;
        }

        .student-toggle-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #333;
            user-select: none;
        }

        .student-toggle-label .toggle-icon {
            font-size: 1.15rem;
        }

        /* The switch track */
        .toggle-switch {
            position: relative;
            width: 56px;
            height: 28px;
            flex-shrink: 0;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
            position: absolute;
        }

        .toggle-track {
            position: absolute;
            inset: 0;
            background: #ccc;
            border-radius: 28px;
            cursor: pointer;
            transition: background 0.3s ease;
            display: flex;
            align-items: center;
            padding: 0 4px;
        }

        .toggle-track::after {
            content: 'OFF';
            position: absolute;
            left: 8px;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #fff;
            transition: opacity 0.2s;
        }

        .toggle-knob {
            position: absolute;
            right: 4px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 1px 4px rgba(0,0,0,0.25);
            transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1), background 0.3s;
        }

        .toggle-switch input:checked + .toggle-track {
            background: #006f4a;
        }

        .toggle-switch input:checked + .toggle-track::after {
            content: 'ON';
            left: auto;
            right: 28px;
        }

        .toggle-switch input:checked + .toggle-track .toggle-knob {
            right: calc(100% - 24px);
        }

        /* Student extra fields */
        .student-fields {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transform: translateY(-8px);
            transition:
                max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                opacity 0.35s ease,
                transform 0.35s ease;
        }

        .student-fields.visible {
            max-height: 300px;
            opacity: 1;
            transform: translateY(0);
        }

        .student-fields-inner {
            padding: 4px 0 8px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Reuse existing form-group style + badge for optional */
        .optional-badge {
            font-size: 0.72rem;
            font-weight: 500;
            color: #888;
            background: #f0ece6;
            padding: 2px 7px;
            border-radius: 20px;
            margin-left: 6px;
            vertical-align: middle;
        }

        .student-fields .form-group {
            margin-bottom: 0;
        }

        /* Subtle divider below the toggle block when expanded */
        .student-divider {
            height: 1px;
            background: #e0ddd6;
            margin: 4px 0 18px;
            display: none;
        }

        .student-divider.visible {
            display: block;
        }
    </style>
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
                <h1>Join PLAYMATES</h1>
                <p>Create your account to start learning through play</p>
            </div>

            <form id="signupForm" class="auth-form">
                <!-- Full Name -->
                <div class="form-group">
                    <label for="signupName">Full Name</label>
                    <input type="text" id="signupName" name="full_name" required placeholder="Enter your full name">
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="signupEmail">Email Address</label>
                    <input type="email" id="signupEmail" name="email" required placeholder="Enter your email">
                </div>

                <!-- ══════════ STUDENT TOGGLE ══════════ -->
                <div class="student-toggle-group" id="studentToggleGroup">
                    <span class="student-toggle-label">
                        Are you a student?
                    </span>
                    <label class="toggle-switch">
                        <input type="checkbox" id="isStudent" name="is_student">
                        <div class="toggle-track">
                            <div class="toggle-knob"></div>
                        </div>
                    </label>
                </div>

                <!-- Student extra fields (hidden until toggle ON) -->
                <div class="student-fields" id="studentFields">
                    <div class="student-fields-inner">
                        <div class="form-group">
                            <label for="schoolName">School Name</label>
                            <input type="text" id="schoolName" name="school_name"
                                   placeholder="Enter your school name">
                        </div>
                        <div class="form-group">
                            <label for="studentId">
                                Student ID
                                <span class="optional-badge">Optional</span>
                            </label>
                            <input type="text" id="studentId" name="student_id"
                                   placeholder="Enter your student ID">
                        </div>
                    </div>
                </div>

                <div class="student-divider" id="studentDivider"></div>
                <!-- ═════════════════════════════════════ -->

                <!-- Password -->
                <div class="form-group">
                    <label for="signupPassword">Password</label>
                    <input type="password" id="signupPassword" name="password" required
                           placeholder="Create a password (min. 8 characters)"
                           oninput="checkPasswordStrength(this.value)">
                    <button type="button" class="password-toggle" onclick="togglePassword('signupPassword')">Show</button>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <div class="password-strength-text" id="passwordStrengthText"></div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="signupConfirmPassword">Confirm Password</label>
                    <input type="password" id="signupConfirmPassword" name="confirm_password" required
                           placeholder="Confirm your password">
                    <button type="button" class="password-toggle" onclick="togglePassword('signupConfirmPassword')">Show</button>
                </div>

                <!-- Terms -->
                <div class="terms-checkbox">
                    <input type="checkbox" id="acceptTerms" name="accept_terms" required>
                    <label for="acceptTerms">
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="submit-btn" id="signupSubmit" disabled>Create Account</button>

                <div class="form-footer">
                    Already have an account? <a href="loginui.php">Login here</a>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        © 2025 PLAYMATES - Educational Games Platform
    </footer>

    <script src="javascript.js"></script>
    <script>
        // ══════════ STUDENT TOGGLE LOGIC ══════════
        const isStudentCheckbox = document.getElementById('isStudent');
        const studentFields     = document.getElementById('studentFields');
        const studentDivider    = document.getElementById('studentDivider');
        const toggleGroup       = document.getElementById('studentToggleGroup');
        const schoolNameInput   = document.getElementById('schoolName');

        isStudentCheckbox.addEventListener('change', function () {
            const on = this.checked;

            // Animate fields in/out
            studentFields.classList.toggle('visible', on);
            studentDivider.classList.toggle('visible', on);
            toggleGroup.classList.toggle('active', on);

            // School name required only when toggle is ON
            schoolNameInput.required = on;

            // Focus school name when revealed
            if (on) {
                setTimeout(() => schoolNameInput.focus(), 350);
            } else {
                // Clear student fields when toggled off
                schoolNameInput.value = '';
                document.getElementById('studentId').value = '';
            }
        });
        // ══════════════════════════════════════════
    </script>
</body>
</html>