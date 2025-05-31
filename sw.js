// sw.js - NIEUWE CACHE VERSIE
const CACHE_NAME = 'yesscarp-v3.0.0'; // GEWIJZIGD NAAR v3.0.0

const urlsToCache = [
    '/',
    '/index.php',
    '/assets/css/main.css',
    '/assets/css/pwa.css',
    '/assets/js/main.js',
    '/assets/js/pwa.js',
    '/assets/js/modules.js',
    '/assets/images/logos/logo.png',
    '/assets/images/backgrounds/fishing-bg.jpg',
    '/manifest.json'
];

// Install event
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
    );
});

// Fetch event
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
    );
});

// Activate event - VERWIJDER OUDE CACHES
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Handle messages from main thread
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
