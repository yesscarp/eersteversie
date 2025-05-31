// assets/js/pwa.js - Aangepaste versie zonder dubbele installatieprompts

let deferredPrompt;
let isInstalled = false;

document.addEventListener('DOMContentLoaded', function() {
    initializePWA();
});

function initializePWA() {
    // Check if already installed
    if (window.matchMedia('(display-mode: standalone)').matches) {
        isInstalled = true;
        document.body.classList.add('pwa-installed');
        hideAllInstallPrompts();
    }

    // Register service worker
    if ('serviceWorker' in navigator) {
        registerServiceWorker();
    }

    // Handle install prompt
    handleInstallPrompt();

    // Handle app updates
    handleAppUpdates();

    // Initialize offline functionality
    initializeOfflineMode();

    // Bind existing install buttons to work with PWA
    bindInstallButtons();
}

async function registerServiceWorker() {
    try {
        const registration = await navigator.serviceWorker.register('/sw.js');
        console.log('Service Worker registered:', registration);

        // Handle updates
        registration.addEventListener('updatefound', () => {
            const newWorker = registration.installing;
            newWorker.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                    showUpdateNotification();
                }
            });
        });
    } catch (error) {
        console.error('Service Worker registration failed:', error);
    }
}

function handleInstallPrompt() {
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        console.log('Install prompt captured and prevented');
        // BELANGRIJK: Geen automatische showInstallPrompt() meer!
    });

    window.addEventListener('appinstalled', () => {
        isInstalled = true;
        hideAllInstallPrompts();
        showNotification('YessCarp is geïnstalleerd!', 'success');
        deferredPrompt = null;
    });
}

// Bind alle bestaande installatie buttons aan de PWA functionaliteit
function bindInstallButtons() {
    // Zoek naar alle buttons met "Installeren" tekst of data-install attribute
    const installButtons = document.querySelectorAll('button[data-install], button:contains("Installeren")');
    
    // Ook zoeken naar buttons die "Installeren" bevatten
    const allButtons = document.querySelectorAll('button');
    allButtons.forEach(button => {
        if (button.textContent.includes('Installeren')) {
            button.addEventListener('click', handleInstallClick);
        }
    });

    // Bind buttons met data-install attribute
    installButtons.forEach(button => {
        button.addEventListener('click', handleInstallClick);
    });
}

async function handleInstallClick(event) {
    event.preventDefault();
    
    if (!deferredPrompt) {
        console.log('Geen install prompt beschikbaar');
        showNotification('Installatie niet beschikbaar op dit apparaat', 'info');
        return;
    }

    try {
        // Toon de browser installatieprompt
        const result = await deferredPrompt.prompt();
        
        // Wacht op gebruikerskeuze
        const choiceResult = await deferredPrompt.userChoice;
        
        if (choiceResult.outcome === 'accepted') {
            console.log('Gebruiker heeft installatie geaccepteerd');
            showNotification('YessCarp wordt geïnstalleerd...', 'success');
        } else {
            console.log('Gebruiker heeft installatie afgewezen');
        }
        
        // Reset de prompt
        deferredPrompt = null;
        
    } catch (error) {
        console.error('Fout bij installatie:', error);
        showNotification('Er ging iets mis bij de installatie', 'error');
    }
}

// Verberg alle installatieprompts wanneer app al geïnstalleerd is
function hideAllInstallPrompts() {
    // Verberg custom prompts
    const prompts = document.querySelectorAll('.pwa-install-prompt, .install-prompt');
    prompts.forEach(prompt => prompt.style.display = 'none');
    
    // Disable install buttons
    const installButtons = document.querySelectorAll('button[data-install]');
    installButtons.forEach(button => {
        button.style.display = 'none';
    });
}

// Toon notificatie aan gebruiker
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        z-index: 10000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animatie in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Verwijder na 3 seconden
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Update notificatie
function showUpdateNotification() {
    const updateDiv = document.createElement('div');
    updateDiv.innerHTML = `
        <div style="position: fixed; top: 0; left: 0; right: 0; background: #2196F3; color: white; padding: 10px; text-align: center; z-index: 10001;">
            Er is een nieuwe versie van YessCarp beschikbaar.
            <button onclick="window.location.reload()" style="margin-left: 10px; background: white; color: #2196F3; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                Vernieuwen
            </button>
        </div>
    `;
    document.body.appendChild(updateDiv);
}

// Placeholder functies
function handleAppUpdates() {
    // Implementatie voor app updates
    console.log('App updates handler initialized');
}

function initializeOfflineMode() {
    // Implementatie voor offline modus
    console.log('Offline mode initialized');
    
    // Luister naar online/offline events
    window.addEventListener('online', () => {
        showNotification('Verbinding hersteld', 'success');
    });
    
    window.addEventListener('offline', () => {
        showNotification('Offline modus geactiveerd', 'info');
    });
}

// Check of PWA al geïnstalleerd is
function isPWAInstalled() {
    return window.matchMedia('(display-mode: standalone)').matches || 
           window.navigator.standalone === true;
}

// Exporteer functies voor gebruik in andere bestanden
window.PWA = {
    install: handleInstallClick,
    isInstalled: isPWAInstalled,
    showNotification: showNotification
};

