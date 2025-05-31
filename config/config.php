<?php
// config/config.php
define('APP_NAME', 'YessCarp');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'https://yesscarp.nl');

// Environment
define('ENVIRONMENT', 'development'); // development, staging, production

// Email settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@yesscarp.nl');
define('SMTP_PASSWORD', 'your-email-password');
define('FROM_EMAIL', 'noreply@yesscarp.nl');
define('FROM_NAME', 'YessCarp');

// Contact email
define('CONTACT_EMAIL', 'info@yesscarp.nl');

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Session settings
define('SESSION_LIFETIME', 3600 * 24 * 30); // 30 days

// Security settings
define('HASH_ALGORITHM', PASSWORD_DEFAULT);
define('TOKEN_LENGTH', 32);

// API Keys (move sensitive data to api_keys.php)
define('WEATHER_API_KEY', 'your-weather-api-key');
define('MAPS_API_KEY', 'your-maps-api-key');

// PWA settings
define('PWA_THEME_COLOR', '#2d5016');
define('PWA_BACKGROUND_COLOR', '#ffffff');

// Feature flags
define('ENABLE_EMAIL_VERIFICATION', true);
define('ENABLE_PROFILE_COMPLETION', true);
define('ENABLE_CONTACT_FORM', true);
define('ENABLE_PWA_FEATURES', true);

// Paths
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('LOG_PATH', __DIR__ . '/../logs/');

// Create directories if they don't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

if (!file_exists(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

// Error reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOG_PATH . 'php_errors.log');
}

// Set timezone
date_default_timezone_set('Europe/Amsterdam');

// Session configuration
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_httponly', true);
ini_set('session.cookie_samesite', 'Strict');
?>
