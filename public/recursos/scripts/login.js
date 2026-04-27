document.addEventListener('DOMContentLoaded', function () {
    // Inicializar funcionalidades esenciales
    initLoginForm();
    initValidation();
    initAccessibility();
    initBasicAnimations();
});

/**
 * Funcion que Configura el formulario de login con validación, efectos visuales yprevención de envíos múltiples
 * @returns 
 */
function initLoginForm() {
    const loginForm = document.querySelector('form[action*="login"]');
    const submitBtn = document.querySelector('.btn-rf-primary');
    const inputs = document.querySelectorAll('.form-floating input');

    if (!loginForm) return;

    // Prevenir envío múltiple del formulario
    let isSubmitting = false;

    loginForm.addEventListener('submit', handleSubmit);

    // Delegación de eventos para inputs
    inputs.forEach(input => {
        input.addEventListener('focus', () => input.parentElement.classList.add('focused'));
        input.addEventListener('blur', () => input.parentElement.classList.remove('focused'));
        input.addEventListener('input', () => validateField(input));
        input.addEventListener('keydown', handleKeyNavigation);
    });

    function handleSubmit(e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }

        isSubmitting = true;
        showLoadingState();
    }

    function showLoadingState() {
        if (!submitBtn) return;

        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>INGRESANDO...';
        submitBtn.disabled = true;

        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            isSubmitting = false;
        }, 5000);
    }
}

/**
 * Funcion que inicializa animaciones básicas esenciales
 */
function initBasicAnimations() {
    // Animación de entrada simple para elementos principales
    const elements = document.querySelectorAll('.login-card, .form-floating, .btn-rf-primary');
    
    elements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';

        setTimeout(() => {
            el.style.transition = 'all 0.6s ease';
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, 100 * index);
    });
}

/**
 * Funcion que implementa sistema completo de validación de formulario
 */
function initValidation() {
    const inputs = document.querySelectorAll('.form-floating input');
    const form = document.querySelector('form[action*="login"]');

    inputs.forEach(input => {
        input.addEventListener('blur', () => validateField(input));
        input.addEventListener('input', () => clearFieldError(input));
    });

    if (form) {
        form.addEventListener('submit', handleFormValidation);
    }

    function handleFormValidation(e) {
        let isValid = true;
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            showNotification('Por favor, completa todos los campos correctamente', 'error');
        }
    }
}

/**
 * Funcion que valida un campo específico según su tipo y contenido
 * @param {HTMLElement} field - Campo del formulario a validar
 * @returns {boolean} - Retorna true si el campo es válido, false si no
 */
function validateField(field) {
    const value = field.value.trim();
    const fieldName = field.name;
    let isValid = true;
    let errorMessage = '';

    // Validaciones específicas por campo
    switch (fieldName) {
        case 'usuario':
            if (value.length < 3) {
                isValid = false;
                errorMessage = 'El usuario debe tener al menos 3 caracteres';
            } else if (!/^[a-zA-Z0-9_]+$/.test(value)) {
                isValid = false;
                errorMessage = 'El usuario solo puede contener letras, números y guiones bajos';
            }
            break;

        case 'clave':
            if (value.length < 3) {
                isValid = false;
                errorMessage = 'La contraseña debe tener al menos 3 caracteres';
            }
            break;
    }

    if (!isValid && value) {
        showFieldError(field, errorMessage);
    } else if (isValid) {
        clearFieldError(field);
    }

    return isValid;
}

/**
 * Funcion que muestra mensaje de error visual bajo el campo inválido
 * @param {HTMLElement} field - Campo del formulario donde mostrar error
 * @param {string} message - Mensaje de error a mostrar
 */
function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('is-invalid');

    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
        color: #ff6b6b;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: block;
        animation: slideDown 0.3s ease;
    `;

    field.parentElement.appendChild(errorDiv);
}

/**
 * Funcion que elimina estado de error de un campo específico
 * @param {HTMLElement} field - Campo del formulario a limpiar
 */
function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const errorDiv = field.parentElement.querySelector('.field-error');
    if (errorDiv) errorDiv.remove();
}

/**
 * Funcion que configura funcionalidades básicas de accesibilidad
 */
function initAccessibility() {
    const inputs = document.querySelectorAll('.form-floating input');
    const submitBtn = document.querySelector('.btn-rf-primary');

    // Atributos ARIA para lectores de pantalla
    if (submitBtn) {
        submitBtn.setAttribute('aria-label', 'Acceder al sistema de gestión');
    }

    // Navegación por teclado básica
    inputs.forEach((input, index) => {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && index < inputs.length - 1) {
                e.preventDefault();
                inputs[index + 1].focus();
            }
            if (e.key === 'Escape') {
                this.value = '';
                clearFieldError(this);
            }
        });
    });
}

/**
 * Funcion que muestra notificaciones simples
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo de notificación ('error', 'success')
 */
function showNotification(message, type = 'info') {
    // Eliminar notificaciones existentes
    const existing = document.querySelector('.notification-toast');
    if (existing) existing.remove();

    const notification = document.createElement('div');
    notification.className = `notification-toast notification-${type}`;
    
    const bgColor = type === 'error' ? 'rgba(220, 53, 69, 0.9)' : 'rgba(40, 167, 69, 0.9)';
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${bgColor};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        max-width: 300px;
        opacity: 0;
        transform: translateX(100px);
        transition: all 0.3s ease;
    `;

    notification.innerHTML = `<span>${message}</span>`;
    document.body.appendChild(notification);

    // Animar entrada
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 10);

    // Auto-eliminar después de 3 segundos
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100px)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
