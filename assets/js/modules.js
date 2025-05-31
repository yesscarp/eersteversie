// assets/js/modules.js
const YessCarp = {
    modules: {},
    
    // Register a module
    register(name, module) {
        this.modules[name] = module;
        console.log(`Module ${name} registered`);
    },
    
    // Get a module
    get(name) {
        return this.modules[name];
    },
    
    // Initialize all modules
    init() {
        Object.keys(this.modules).forEach(name => {
            const module = this.modules[name];
            if (typeof module.init === 'function') {
                module.init();
                console.log(`Module ${name} initialized`);
            }
        });
    }
};

// Auth Module
YessCarp.register('auth', {
    init() {
        this.bindEvents();
    },
    
    bindEvents() {
        // Handle form submissions
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', this.handleFormSubmit.bind(this));
        });
    },
    
    handleFormSubmit(event) {
        const form = event.target;
        const formData = new FormData(form);
        
        // Add loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Bezig...';
        }
        
        // Form will submit normally, this is just for UX
    },
    
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    validatePassword(password) {
        return password.length >= 6;
    }
});

// UI Module
YessCarp.register('ui', {
    init() {
        this.initTabSwitching();
        this.initAnimations();
    },
    
    initTabSwitching() {
        // Tab switching is handled in the main index.php
        // This could be extended for more complex UI interactions
    },
    
    initAnimations() {
        // Add entrance animations
        const elements = document.querySelectorAll('.login-container, .auth-buttons, .tab-content');
        elements.forEach((el, index) => {
            el.style.animationDelay = `${index * 0.1}s`;
            el.classList.add('animate-in');
        });
    },
    
    showLoading(element) {
        element.classList.add('loading');
    },
    
    hideLoading(element) {
        element.classList.remove('loading');
    },
    
    showMessage(message, type = 'info') {
        const messageEl = document.createElement('div');
        messageEl.className = `message message-${type}`;
        messageEl.textContent = message;
        
        document.body.appendChild(messageEl);
        
        setTimeout(() => {
            messageEl.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            messageEl.classList.remove('show');
            setTimeout(() => messageEl.remove(), 300);
        }, 3000);
    }
});

// Contact Module
YessCarp.register('contact', {
    init() {
        this.bindContactForm();
    },
    
    bindContactForm() {
        const contactForm = document.querySelector('#contact-tab form');
        if (contactForm) {
            contactForm.addEventListener('submit', this.handleContactSubmit.bind(this));
        }
    },
    
    handleContactSubmit(event) {
        // Add any client-side validation or enhancements here
        const form = event.target;
        const formData = new FormData(form);
        
        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('error');
                isValid = false;
            } else {
                field.classList.remove('error');
            }
        });
        
        if (!isValid) {
            event.preventDefault();
            YessCarp.get('ui').showMessage('Vul alle verplichte velden in', 'error');
        }
    }
});

// Analytics Module
YessCarp.register('analytics', {
    init() {
        this.trackPageView();
        this.bindEvents();
    },
    
    trackPageView() {
        // Track page views (could integrate with Google Analytics, etc.)
        console.log('Page view tracked:', window.location.pathname);
    },
    
    bindEvents() {
        // Track button clicks
        document.addEventListener('click', (event) => {
            if (event.target.matches('button, .btn')) {
                this.trackEvent('button_click', {
                    button_text: event.target.textContent.trim(),
                    button_class: event.target.className
                });
            }
        });
        
        // Track form submissions
        document.addEventListener('submit', (event) => {
            const form = event.target;
            const formType = form.querySelector('input[type="hidden"]')?.value || 'unknown';
            this.trackEvent('form_submit', {
                form_type: formType
            });
        });
    },
    
    trackEvent(eventName, properties = {}) {
        console.log('Event tracked:', eventName, properties);
        // Here you could send to analytics service
    }
});

// Initialize all modules when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    YessCarp.init();
});

// Export for global use
window.YessCarp = YessCarp;
