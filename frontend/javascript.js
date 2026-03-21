// ============================================
// PLAYMATES - Shared JavaScript Functions
// ============================================

// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = 'Hide';
    } else {
        input.type = 'password';
        button.textContent = 'Show';
    }
}

// Check password strength
function checkPasswordStrength(password, strengthBarId = 'passwordStrengthBar', strengthTextId = 'passwordStrengthText') {
    const bar = document.getElementById(strengthBarId);
    const text = document.getElementById(strengthTextId);
    
    if (!bar || !text) return 0;

    let strength = 0;
    let suggestions = [];

    if (password.length >= 8) strength++;
    else suggestions.push('At least 8 characters');

    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    else suggestions.push('Mix of uppercase and lowercase letters');

    if (/\d/.test(password)) strength++;
    else suggestions.push('At least one number');

    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    else suggestions.push('At least one special character');

    switch (strength) {
        case 1:
            bar.className = 'password-strength-bar strength-weak';
            text.textContent = 'Weak password';
            break;
        case 2:
            bar.className = 'password-strength-bar strength-fair';
            text.textContent = 'Fair password - ' + suggestions.join(', ');
            break;
        case 3:
            bar.className = 'password-strength-bar strength-good';
            text.textContent = 'Good password';
            break;
        case 4:
            bar.className = 'password-strength-bar strength-strong';
            text.textContent = 'Strong password';
            break;
        default:
            bar.className = 'password-strength-bar';
            text.textContent = 'Enter a password';
    }
    
    return strength;
}

// Show message in form
function showMessage(text, type = 'info', containerId = null) {
    let container;
    if (containerId) {
        container = document.getElementById(containerId);
    } else {
        container = document.querySelector('.auth-container') || document.body;
    }
    
    const existingMessage = container.querySelector('.form-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `form-message ${type}`;
    messageDiv.textContent = text;
    
    if (container.firstChild) {
        container.insertBefore(messageDiv, container.firstChild);
    } else {
        container.appendChild(messageDiv);
    }
    
    setTimeout(() => {
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 100);
    
    if (type === 'info' || type === 'warning') {
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.style.opacity = '0';
                setTimeout(() => {
                    if (messageDiv.parentNode) {
                        messageDiv.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
    
    return messageDiv;
}

// Validate email format
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Setup verification code inputs
function setupVerificationInputs(inputsContainerId = 'verificationInputs', onComplete = null) {
    const inputs = document.querySelectorAll(`#${inputsContainerId} .verification-input`);
    
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            const value = e.target.value;
            
            if (!/^\d?$/.test(value)) {
                input.value = '';
                return;
            }
            
            if (value.length === 1) {
                input.classList.add('filled');
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                } else {
                    input.blur();
                    if (onComplete) {
                        const code = Array.from(inputs).map(i => i.value).join('');
                        onComplete(code);
                    }
                }
            } else if (value.length === 0) {
                input.classList.remove('filled');
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && input.value.length === 0 && index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].classList.remove('filled');
            }
        });

        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text');
            if (pastedData.length === 6 && /^\d+$/.test(pastedData)) {
                for (let i = 0; i < 6; i++) {
                    inputs[i].value = pastedData[i];
                    inputs[i].classList.add('filled');
                }
                inputs[5].focus();
                if (onComplete) {
                    onComplete(pastedData);
                }
            }
        });
    });
}

// Clear verification inputs
function clearVerificationInputs(containerId = 'verificationInputs') {
    const inputs = document.querySelectorAll(`#${containerId} .verification-input`);
    inputs.forEach(input => {
        input.value = '';
        input.classList.remove('filled');
    });
    if (inputs[0]) {
        inputs[0].focus();
    }
}

// Set button loading state
function setButtonLoading(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        button.classList.add('loading');
        button.dataset.originalText = button.textContent;
        button.textContent = 'Processing...';
    } else {
        button.disabled = false;
        button.classList.remove('loading');
        if (button.dataset.originalText) {
            button.textContent = button.dataset.originalText;
        }
    }
}

// Validate form
function validateForm(formId, rules = {}) {
    const form = document.getElementById(formId);
    if (!form) return { isValid: false, errors: ['Form not found'] };
    
    const formData = new FormData(form);
    const errors = [];
    
    for (const [field, rule] of Object.entries(rules)) {
        const input = form.querySelector(`[name="${field}"]`);
        if (!input) continue;
        
        const value = formData.get(field) || input.value;
        
        if (rule.required && !value) {
            errors.push(`${field} is required`);
            continue;
        }
        
        if (rule.email && !isValidEmail(value)) {
            errors.push(`Please enter a valid email address`);
        }
        
        if (rule.minLength && value.length < rule.minLength) {
            errors.push(`${field} must be at least ${rule.minLength} characters`);
        }
        
        if (rule.match) {
            const matchInput = form.querySelector(`[name="${rule.match}"]`);
            if (matchInput && value !== matchInput.value) {
                errors.push(`${field} does not match`);
            }
        }
    }
    
    return {
        isValid: errors.length === 0,
        errors: errors,
        formData: formData
    };
}

// Add focus animations to inputs
function addInputAnimations() {
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });
}

// Initialize common functionality
function initCommon() {
    addInputAnimations();
    
    document.querySelectorAll('.submit-btn, .back-home').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (this.disabled) return;
            
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.7);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                width: ${size}px;
                height: ${size}px;
                top: ${y}px;
                left: ${x}px;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => {
                if (ripple.parentNode) {
                    ripple.remove();
                }
            }, 600);
        });
    });
    
    if (!document.querySelector('#ripple-style')) {
        const style = document.createElement('style');
        style.id = 'ripple-style';
        style.textContent = `
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCommon);
} else {
    initCommon();
}

// ============================================
// LOGIN HANDLER
// ============================================
document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    if (!loginForm) return;

    loginForm.addEventListener("submit", async function(e) {
        e.preventDefault();

        const email = loginForm.email.value.trim();
        const password = loginForm.password.value.trim();
        const loginBtn = loginForm.querySelector(".submit-btn");

        if (!email || !password) {
            showMessage("Please enter your email and password", "warning");
            return;
        }

        if (!isValidEmail(email)) {
            showMessage("Please enter a valid email address", "error");
            return;
        }

        setButtonLoading(loginBtn, true);

        try {
            const response = await fetch("login.php", {
                method: "POST",
                body: new FormData(loginForm)
            });

            const result = await response.text();

            if (result.trim() === "Success") {
                showMessage("Login successful! Redirecting...", "success");
                setTimeout(() => {
                    window.location.href = "index.php";
                }, 800);
            } else if (result.includes("Invalid password")) {
                showMessage("Incorrect password. Please try again.", "error");
            } else if (result.includes("User not found")) {
                showMessage("No account found with this email.", "error");
            } else {
                showMessage("Something went wrong. Try again.", "error");
            }
        } catch (err) {
            showMessage("Network error. Please try again.", "error");
        }

        setButtonLoading(loginBtn, false);
    });
});

// ============================================
// SIGNUP HANDLER
// ============================================
document.addEventListener("DOMContentLoaded", () => {
    const signupForm = document.getElementById("signupForm");
    if (!signupForm) return;

    const signupSubmit = document.getElementById("signupSubmit");
    const acceptTerms = document.getElementById("acceptTerms");
    const passwordInput = document.getElementById("signupPassword");

    function updateSubmitButton() {
        const strength = checkPasswordStrength(passwordInput.value);
        signupSubmit.disabled = !(acceptTerms.checked && strength >= 2);
    }

    if (acceptTerms) {
        acceptTerms.addEventListener("change", updateSubmitButton);
    }

    if (passwordInput) {
        passwordInput.addEventListener("input", updateSubmitButton);
    }

    signupForm.addEventListener("submit", async function(e) {
        e.preventDefault();

        const fullName = document.getElementById("signupName").value.trim();
        const email = document.getElementById("signupEmail").value.trim();
        const password = document.getElementById("signupPassword").value.trim();
        const confirmPassword = document.getElementById("signupConfirmPassword").value.trim();
        const signupBtn = signupForm.querySelector(".submit-btn");

        if (!fullName || !email || !password || !confirmPassword) {
            showMessage("Please fill in all fields", "error");
            return;
        }

        if (!isValidEmail(email)) {
            showMessage("Please enter a valid email address", "error");
            return;
        }

        if (password.length < 8) {
            showMessage("Password must be at least 8 characters long", "error");
            return;
        }

        if (password !== confirmPassword) {
            showMessage("Passwords do not match", "error");
            return;
        }

        if (checkPasswordStrength(password) < 2) {
            showMessage("Please use a stronger password", "error");
            return;
        }

        if (!acceptTerms.checked) {
            showMessage("Please accept the Terms of Service and Privacy Policy", "error");
            return;
        }

        setButtonLoading(signupBtn, true);

        try {
            const response = await fetch("signup.php", {
                method: "POST",
                body: new FormData(signupForm)
            });

            const result = await response.text();

            if (result.trim() === "Success") {
                showMessage("Account created successfully! Redirecting to login...", "success");
                signupForm.reset();
                setTimeout(() => {
                    window.location.href = "loginui.php";
                }, 1500);
            } else if (result.includes("Email already exists")) {
                showMessage("This email is already registered. Please login instead.", "error");
            } else if (result.includes("Passwords do not match")) {
                showMessage("Passwords do not match", "error");
            } else {
                showMessage("Error creating account: " + result, "error");
            }
        } catch (err) {
            showMessage("Network error. Please try again.", "error");
            console.error("Signup error:", err);
        }

        setButtonLoading(signupBtn, false);
    });
});