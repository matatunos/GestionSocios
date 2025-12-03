/**
 * Password Strength Meter
 * Evaluates password strength and provides visual feedback
 */

function checkPasswordStrength(password) {
    let strength = 0;
    let feedback = [];

    // Length checks
    if (password.length >= 8) {
        strength += 25;
    } else {
        feedback.push('Mínimo 8 caracteres');
    }

    if (password.length >= 12) {
        strength += 10;
    }

    // Complexity checks
    if (/[a-z]/.test(password)) {
        strength += 15;
    } else {
        feedback.push('Añade minúsculas');
    }

    if (/[A-Z]/.test(password)) {
        strength += 15;
    } else {
        feedback.push('Añade mayúsculas');
    }

    if (/[0-9]/.test(password)) {
        strength += 15;
    } else {
        feedback.push('Añade números');
    }

    if (/[^a-zA-Z0-9]/.test(password)) {
        strength += 20;
    } else {
        feedback.push('Añade caracteres especiales');
    }

    // Determine strength level
    let level = 'weak';
    let levelText = 'Débil';

    if (strength >= 80) {
        level = 'strong';
        levelText = 'Fuerte';
        feedback = ['¡Excelente contraseña!'];
    } else if (strength >= 50) {
        level = 'medium';
        levelText = 'Media';
    }

    return {
        strength,
        level,
        levelText,
        feedback
    };
}

function updatePasswordStrengthMeter(inputId, meterId, feedbackId) {
    const input = document.getElementById(inputId);
    const meter = document.getElementById(meterId);
    const feedbackEl = document.getElementById(feedbackId);

    if (!input || !meter) return;

    input.addEventListener('input', function () {
        const result = checkPasswordStrength(this.value);

        // Update meter bar
        meter.style.width = result.strength + '%';
        meter.className = 'password-strength-bar ' + result.level;
        meter.setAttribute('data-strength', result.levelText);

        // Update feedback text
        if (feedbackEl && this.value.length > 0) {
            feedbackEl.innerHTML = '<strong>' + result.levelText + ':</strong> ' + result.feedback.join(', ');
            feedbackEl.className = 'password-feedback ' + result.level;
            feedbackEl.style.display = 'block';
        } else if (feedbackEl) {
            feedbackEl.style.display = 'none';
        }
    });
}

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    // Initialize for any password inputs with strength meter
    const passwordInputs = document.querySelectorAll('input[type="password"][data-strength-meter]');
    passwordInputs.forEach(input => {
        const meterId = input.getAttribute('data-strength-meter');
        const feedbackId = input.getAttribute('data-strength-feedback');
        updatePasswordStrengthMeter(input.id, meterId, feedbackId);
    });
});
