// shared.js - Shared JavaScript functions

/**
 * Show message to user
 * @param {string} message - The message to display
 * @param {string} type - 'success' or 'error'
 * @param {string} containerId - ID of container to show message in
 */
function showMessage(message, type, containerId) {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error('Container not found:', containerId);
        alert(message); // Fallback to alert
        return;
    }
    
    // Remove any existing messages
    const existingMessages = container.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${type}`;
    messageDiv.textContent = message;
    
    // Style the message
    messageDiv.style.padding = '12px 15px';
    messageDiv.style.marginBottom = '15px';
    messageDiv.style.borderRadius = '5px';
    messageDiv.style.textAlign = 'center';
    messageDiv.style.fontSize = '14px';
    messageDiv.style.fontWeight = '500';
    messageDiv.style.animation = 'slideDown 0.3s ease';
    
    if (type === 'success') {
        messageDiv.style.backgroundColor = '#d4edda';
        messageDiv.style.color = '#155724';
        messageDiv.style.border = '1px solid #c3e6cb';
    } else if (type === 'error') {
        messageDiv.style.backgroundColor = '#f8d7da';
        messageDiv.style.color = '#721c24';
        messageDiv.style.border = '1px solid #f5c6cb';
    }
    
    // Insert at the top of the container
    container.insertBefore(messageDiv, container.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.style.animation = 'slideUp 0.3s ease';
            setTimeout(() => messageDiv.remove(), 300);
        }
    }, 5000);
}

/**
 * Setup verification code inputs with auto-focus and paste support
 * @param {string} containerId - ID of container with inputs
 * @param {function} onComplete - Callback when all 6 digits are entered
 */
function setupVerificationInputs(containerId, onComplete) {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error('Verification container not found:', containerId);
        return;
    }
    
    const inputs = container.querySelectorAll('.verification-input');
    
    if (inputs.length === 0) {
        console.error('No verification inputs found');
        return;
    }
    
    inputs.forEach((input, index) => {
        // Clear any previous event listeners by cloning
        const newInput = input.cloneNode(true);
        input.parentNode.replaceChild(newInput, input);
    });
    
    // Re-query after cloning
    const freshInputs = container.querySelectorAll('.verification-input');
    
    freshInputs.forEach((input, index) => {
        // Handle input event
        input.addEventListener('input', (e) => {
            const value = e.target.value;
            
            // Only allow single digit
            if (value.length > 1) {
                e.target.value = value.charAt(0);
            }
            
            // Only allow numbers
            if (value && !/^\d$/.test(value)) {
                e.target.value = '';
                return;
            }
            
            // Move to next input if digit entered
            if (value && index < freshInputs.length - 1) {
                freshInputs[index + 1].focus();
            }
            
            // Check if all inputs are filled
            const code = Array.from(freshInputs).map(inp => inp.value).join('');
            if (code.length === 6 && /^\d{6}$/.test(code)) {
                onComplete(code);
            }
        });
        
        // Handle backspace
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace') {
                if (!e.target.value && index > 0) {
                    freshInputs[index - 1].focus();
                    freshInputs[index - 1].value = '';
                }
            } else if (e.key === 'ArrowLeft' && index > 0) {
                freshInputs[index - 1].focus();
            } else if (e.key === 'ArrowRight' && index < freshInputs.length - 1) {
                freshInputs[index + 1].focus();
            }
        });
        
        // Handle paste
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text');
            const digits = pastedData.replace(/\D/g, '').slice(0, 6);
            
            digits.split('').forEach((digit, i) => {
                if (freshInputs[i]) {
                    freshInputs[i].value = digit;
                }
            });
            
            // Focus last filled input or next empty
            const lastIndex = Math.min(digits.length - 1, 5);
            if (freshInputs[lastIndex]) {
                freshInputs[lastIndex].focus();
            }
            
            // Check if complete
            if (digits.length === 6) {
                onComplete(digits);
            }
        });
        
        // Handle focus - select all
        input.addEventListener('focus', (e) => {
            e.target.select();
        });
    });
    
    // Focus first input
    if (freshInputs[0]) {
        freshInputs[0].focus();
    }
}

/**
 * Validate email format
 * @param {string} email 
 * @returns {boolean}
 */
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideUp {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(-10px);
        }
    }
`;
document.head.appendChild(style);