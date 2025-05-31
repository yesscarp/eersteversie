// assets/js/main.js - COMPLETE WERKENDE VERSIE
document.addEventListener('DOMContentLoaded', function() {
    console.log('YessCarp Main JS loaded');
    
    // Initialize app
    initializeApp();
    
    // Form enhancements
    enhanceForms();
    
    // PWA features
    initializePWA();
    
    // Initialize tab handling
    initializeTabHandling();
});

function initializeApp() {
    // Check for saved theme
    const savedTheme = localStorage.getItem('yesscarp-theme');
    if (savedTheme) {
        document.body.classList.add(savedTheme);
    }
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize animations
    initializeAnimations();
}

function initializeTabHandling() {
    // Initialize tab state from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentTab = urlParams.get('tab') || 'login';
    const currentLang = urlParams.get('lang') || 'nl';
    
    // Show correct tab without triggering URL change
    showTabSilent(currentTab);
    
    // Handle browser back/forward
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'login';
        showTabSilent(tab);
    });
    
    // Fix language switcher to preserve tab
    const langForms = document.querySelectorAll('form');
    langForms.forEach(form => {
        const langButton = form.querySelector('button[name="switch_language"]');
        if (langButton) {
            form.addEventListener('submit', function(e) {
                // Add current tab to the language switch
                const currentTab = document.querySelector('.tab-content.active')?.id.replace('-tab', '') || 'login';
                
                // Create hidden input for tab
                const tabInput = document.createElement('input');
                tabInput.type = 'hidden';
                tabInput.name = 'current_tab';
                tabInput.value = currentTab;
                form.appendChild(tabInput);
            });
        }
    });
}

// Global function for tab switching (called from onclick events)
function showTab(tabName) {
    // Remove active class from all tabs and contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    document.querySelectorAll('.auth-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to clicked tab and corresponding content
    const targetTab = document.getElementById(tabName + '-tab');
    if (targetTab) {
        targetTab.classList.add('active');
    }
    
    // Find and activate the correct button
    const buttons = document.querySelectorAll('.auth-btn');
    const tabOrder = ['login', 'register', 'contact', 'info'];
    const index = tabOrder.indexOf(tabName);
    if (index !== -1 && buttons[index]) {
        buttons[index].classList.add('active');
    }
    
    // Update URL with BOTH tab and lang parameters
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    
    // Preserve language parameter
    const currentLang = url.searchParams.get('lang') || 'nl';
    url.searchParams.set('lang', currentLang);
    
    // Use replaceState to avoid page reload
    window.history.replaceState({}, '', url);
}

// Silent tab switching (without URL update)
function showTabSilent(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    document.querySelectorAll('.auth-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    const targetTab = document.getElementById(tabName + '-tab');
    if (targetTab) {
        targetTab.classList.add('active');
    }
    
    const buttons = document.querySelectorAll('.auth-btn');
    const tabOrder = ['login', 'register', 'contact', 'info'];
    const index = tabOrder.indexOf(tabName);
    if (index !== -1 && buttons[index]) {
        buttons[index].classList.add('active');
    }
}

function enhanceForms() {
    // Add real-time validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required], textarea[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', clearValidation);
        });
    });
    
    // Password strength indicator
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        if (input.name === 'password') {
            input.addEventListener('input', showPasswordStrength);
        }
    });
}

function validateField(event) {
    const field = event.target;
    const value = field.value.trim();
    
    // Remove existing validation classes
    field.classList.remove('valid', 'invalid');
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailRegex.test(value)) {
            field.classList.add('valid');
        } else {
            field.classList.add('invalid');
        }
    }
    
    // Password validation
    if (field.type === 'password' && field.name === 'password' && value) {
        if (value.length >= 6) {
            field.classList.add('valid');
        } else {
            field.classList.add('invalid');
        }
    }
    
    // Required field validation
    if (field.hasAttribute('required')) {
        if (value) {
            field.classList.add('valid');
        } else {
            field.classList.add('invalid');
        }
    }
}

function clearValidation(event) {
    const field = event.target;
    field.classList.remove('valid', 'invalid');
}

function showPasswordStrength(event) {
    const password = event.target.value;
    const strength = calculatePasswordStrength(password);
    
    // Remove existing strength indicator
    let indicator = document.querySelector('.password-strength');
    if (indicator) {
        indicator.remove();
    }
    
    if (password.length > 0) {
        // Create strength indicator
        indicator = document.createElement('div');
        indicator.className = 'password-strength';
        indicator.innerHTML = `
            <div class="strength-bar">
                <div class="strength-fill strength-${strength.level}"></div>
            </div>
            <span class="strength-text">${strength.text}</span>
        `;
        
        event.target.parentNode.appendChild(indicator);
    }
}

function calculatePasswordStrength(password) {
    let score = 0;
    
    if (password.length >= 6) score++;
    if (password.length >= 8) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;
    
    if (score < 3) return { level: 'weak', text: 'Zwak' };
    if (score < 5) return { level: 'medium', text: 'Gemiddeld' };
    return { level: 'strong', text: 'Sterk' };
}

function initializeTooltips() {
    // Simple tooltip implementation
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(event) {
    const element = event.target;
    const text = element.getAttribute('data-tooltip');
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
}

function hideTooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

function initializeAnimations() {
    // Fade in animations for elements
    const animatedElements = document.querySelectorAll('.fade-in');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    });
    
    animatedElements.forEach(el => observer.observe(el));
}

function initializePWA() {
    // Check if running as PWA
    if (window.matchMedia('(display-mode: standalone)').matches) {
        document.body.classList.add('pwa-mode');
    }
    
    // Handle online/offline status
    window.addEventListener('online', () => {
        document.body.classList.remove('offline');
        showNotification('Verbinding hersteld', 'success');
    });
    
    window.addEventListener('offline', () => {
        document.body.classList.add('offline');
        showNotification('Geen internetverbinding', 'warning');
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

// Global error handler
window.addEventListener('error', function(event) {
    console.error('YessCarp Error:', event.error);
    // Could send to error tracking service
});

// Make showTab available globally for onclick events
window.showTab = showTab;
