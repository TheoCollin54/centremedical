// Archivo JavaScript principal para el Centro Médico
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todos los componentes
    initializeFormValidation();
    initializeAppointmentSystem();
    initializeLoadingStates();
    initializeConfirmations();
});

// Sistema de validación de formularios
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showError(field, 'Este campo es obligatorio');
            isValid = false;
        } else {
            clearError(field);
        }
        
        // Validación de email
        if (field.type === 'email' && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                showError(field, 'Por favor ingrese un email válido');
                isValid = false;
            }
        }
        
        // Validación de teléfono
        if (field.name === 'telephone' && field.value) {
            const phoneRegex = /^[0-9]{10}$/;
            if (!phoneRegex.test(field.value.replace(/\s/g, ''))) {
                showError(field, 'Por favor ingrese un número de teléfono válido (10 dígitos)');
                isValid = false;
            }
        }
    });
    
    return isValid;
}

function showError(field, message) {
    const errorDiv = field.parentElement.querySelector('.error-message') || 
                    document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    field.classList.add('error');
    
    if (!field.parentElement.querySelector('.error-message')) {
        field.parentElement.appendChild(errorDiv);
    }
}

function clearError(field) {
    const errorDiv = field.parentElement.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
    field.classList.remove('error');
}

// Mejoras del sistema de citas
function initializeAppointmentSystem() {
    const dateInputs = document.querySelectorAll('input[type="datetime-local"]');
    const doctorSelect = document.querySelector('select[name="doctor_id"]');
    
    if (dateInputs.length && doctorSelect) {
        dateInputs.forEach(input => {
            input.addEventListener('change', function() {
                updateAvailableTimeSlots(this.value, doctorSelect.value);
            });
        });
        
        doctorSelect.addEventListener('change', function() {
            const dateInput = document.querySelector('input[type="datetime-local"]');
            if (dateInput && dateInput.value) {
                updateAvailableTimeSlots(dateInput.value, this.value);
            }
        });
    }
}

async function updateAvailableTimeSlots(date, doctorId) {
    if (!date || !doctorId) return;
    
    const timeSlotSelect = document.querySelector('select[name="time_slot"]');
    if (!timeSlotSelect) return;
    
    showLoading(timeSlotSelect);
    
    try {
        const response = await fetch(`get_available_slots.php?date=${date}&doctor_id=${doctorId}`);
        const slots = await response.json();
        
        timeSlotSelect.innerHTML = '';
        slots.forEach(slot => {
            const option = document.createElement('option');
            option.value = slot;
            option.textContent = slot;
            timeSlotSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error al obtener los horarios disponibles:', error);
        showNotification('Error al obtener los horarios disponibles', 'error');
    } finally {
        hideLoading(timeSlotSelect);
    }
}

// Estados de carga
function initializeLoadingStates() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                showLoading(submitButton);
            }
        });
    });
}

function showLoading(element) {
    element.disabled = true;
    element.dataset.originalText = element.textContent;
    element.innerHTML = '<span class="spinner"></span> Cargando...';
}

function hideLoading(element) {
    element.disabled = false;
    element.textContent = element.dataset.originalText;
}

// Diálogos de confirmación
function initializeConfirmations() {
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || '¿Está seguro de que desea realizar esta acción?')) {
                e.preventDefault();
            }
        });
    });
}

// Sistema de notificaciones
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Activar animación
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Eliminar después de 5 segundos
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Agregar CSS para las nuevas características
const style = document.createElement('style');
style.textContent = `
    .error-message {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    .error {
        border-color: #dc3545 !important;
    }
    
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 4px;
        color: white;
        opacity: 0;
        transform: translateY(-20px);
        transition: all 0.3s ease;
        z-index: 1000;
    }
    
    .notification.show {
        opacity: 1;
        transform: translateY(0);
    }
    
    .notification.info {
        background-color: #17a2b8;
    }
    
    .notification.error {
        background-color: #dc3545;
    }
    
    .notification.success {
        background-color: #28a745;
    }
    
    .spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid #fff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s linear infinite;
        margin-right: 0.5rem;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
`;

document.head.appendChild(style);
