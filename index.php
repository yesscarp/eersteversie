<?php
// index.php
session_start();
require_once 'config/database.php';
require_once 'config/api_keys.php';
require_once 'includes/functions.php';

// Auto-login check VOOR alles anders
if (!isset($_SESSION['user_id'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check remember token
        if (checkRememberToken($db)) {
            // Succesvol ingelogd via remember token
            if (!$_SESSION['profile_completed']) {
                header('Location: complete-profile.php');
            } else {
                header('Location: modules/dashboard/dashboard.php');
            }
            exit();
        }
        
        // Cleanup verlopen tokens (1% kans)
        if (rand(1, 100) === 1) {
            cleanupExpiredTokens($db);
        }
    } catch (Exception $e) {
        // Log error maar ga verder
        error_log("Remember token check failed: " . $e->getMessage());
    }
}

// Language detection and handling - VERBETERD
function detectLanguage() {
    // 1. Check URL parameter EERST
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['nl', 'en'])) {
        $_SESSION['language'] = $_GET['lang']; // Direct in session opslaan
        return $_GET['lang'];
    }
    
    // 2. Check session
    if (isset($_SESSION['language']) && in_array($_SESSION['language'], ['nl', 'en'])) {
        return $_SESSION['language'];
    }
    
    // 3. Check domain
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, '.com') !== false) {
        $defaultLang = 'en';
    } elseif (strpos($host, '.nl') !== false) {
        $defaultLang = 'nl';
    } else {
        $defaultLang = 'nl';
    }
    
    // 4. Check browser language als laatste
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (in_array($browserLang, ['nl', 'en'])) {
            return $browserLang;
        }
    }
    
    return $defaultLang;
}

// Set language
$lang = detectLanguage();
$_SESSION['language'] = $lang;

// Load language file
$translations = [];
if (file_exists("languages/{$lang}.php")) {
    include "languages/{$lang}.php";
}

function t($key) {
    global $translations;
    return isset($translations[$key]) ? $translations[$key] : $key;
}

// Initialize variables
$login_error = '';
$register_errors = [];
$contact_success = '';
$contact_error = '';

// Determine which tab to show - VERBETERD
$show_tab = 'login'; // default

// URL parameter heeft voorrang
if (isset($_GET['tab']) && in_array($_GET['tab'], ['login', 'register', 'contact', 'info'])) {
    $show_tab = $_GET['tab'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST bepaalt tab
    if (isset($_POST['register'])) {
        $show_tab = 'register';
    } elseif (isset($_POST['contact'])) {
        $show_tab = 'contact';
    } elseif (isset($_POST['login'])) {
        $show_tab = 'login';
    }
}

// Handle contact form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $contact_error = t('fill_all_fields');
    } elseif (!isValidEmail($email)) {
        $contact_error = t('invalid_email');
    } else {
        // Send contact email
        $to = "info@yesscarp.nl";
        $email_subject = "Contact form: " . $subject;
        $email_message = "Name: " . $name . "\n";
        $email_message .= "Email: " . $email . "\n";
        $email_message .= "Subject: " . $subject . "\n\n";
        $email_message .= "Message:\n" . $message;
        
        $headers = "From: " . $email . "\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        
        if (mail($to, $email_subject, $email_message, $headers)) {
            $contact_success = t('contact_success');
        } else {
            $contact_error = t('contact_error');
        }
    }
}

// Handle login - MET REMEMBER ME
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']); // Nieuwe checkbox
    
    if (empty($email) || empty($password)) {
        $login_error = t('fill_all_fields');
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->prepare("SELECT id, email, password, full_name, status, email_verified, profile_completed FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password']) && $user['status'] === 'active') {
                if (!$user['email_verified']) {
                    $login_error = t('email_not_verified');
                } else {
                    // Succesvol ingelogd
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['profile_completed'] = $user['profile_completed'];
                    
                    // Update last login
                    $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $updateStmt->execute([$user['id']]);
                    
                    // Remember me functionaliteit
                    if ($remember_me) {
                        $rememberToken = createRememberToken($user['id'], $db);
                        setRememberCookie($rememberToken);
                    }
                    
                    // Redirect naar juiste pagina
                    if (!$user['profile_completed']) {
                        header('Location: complete-profile.php');
                    } else {
                        header('Location: modules/dashboard/dashboard.php');
                    }
                    exit();
                }
            } else {
                if (!$user) {
                    $login_error = t('no_account_found');
                } elseif ($user['status'] !== 'active') {
                    $login_error = t('account_deactivated');
                } else {
                    $login_error = t('wrong_password');
                }
            }
        } catch (Exception $e) {
            $login_error = t('technical_error');
        }
    }
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($full_name)) $register_errors[] = t('name_required');
    if (empty($email)) $register_errors[] = t('email_required');
    if (!isValidEmail($email)) $register_errors[] = t('invalid_email');
    if (empty($password)) $register_errors[] = t('password_required');
    if (strlen($password) < 6) $register_errors[] = t('password_min_length');
    if ($password !== $confirm_password) $register_errors[] = t('passwords_not_match');
    
    if (empty($register_errors)) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $register_errors[] = t('email_exists');
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $username = strtolower(str_replace([' ', '.', ',', '!', '?', '@', '#', '$', '%', '&', '*'], '_', $full_name)) . '_' . uniqid();
                $verificationToken = generateVerificationToken();
                $tokenExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                $stmt = $db->prepare("INSERT INTO users (email, password, full_name, username, status, email_verified, verification_token, token_expires_at, profile_completed, created_at) VALUES (?, ?, ?, ?, 'active', 0, ?, ?, 0, NOW())");
                
                if ($stmt->execute([$email, $hashedPassword, $full_name, $username, $verificationToken, $tokenExpires])) {
                    // Send verification email
                    if (sendVerificationEmail($email, $verificationToken, $full_name)) {
                        $_SESSION['registration_success'] = true;
                        $_SESSION['registration_email'] = $email;
                        header('Location: index.php?registered=1&lang=' . $lang . '&tab=login');
                        exit();
                    } else {
                        $register_errors[] = t('email_send_error');
                    }
                } else {
                    $register_errors[] = t('account_creation_error');
                }
            }
        } catch (Exception $e) {
            $register_errors[] = t('technical_error');
        }
    }
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    if (!$_SESSION['profile_completed']) {
        header('Location: complete-profile.php');
    } else {
        header('Location: modules/dashboard/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('site_title'); ?></title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#2d5016">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="YessCarp">
    <link rel="manifest" href="manifest.json">
    
    <!-- Hreflang tags for SEO -->
    <link rel="alternate" hreflang="nl" href="https://yesscarp.nl/?lang=nl" />
    <link rel="alternate" hreflang="en" href="https://yesscarp.com/?lang=en" />
    <link rel="alternate" hreflang="x-default" href="https://yesscarp.com/" />
    
    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/images/icons/icon-32x32.png">
    <link rel="apple-touch-icon" href="assets/images/icons/icon-192x192.png">
    
    <!-- CSS BESTANDEN -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pwa.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Remember Me Styling -->
    <style>
        /* Remember Me Checkbox Styling */
        .remember-me-group {
            margin: 15px 0;
        }

        .remember-checkbox {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 14px;
            color: #666;
        }

        .remember-checkbox input[type="checkbox"] {
            display: none;
        }

        .checkmark {
            width: 20px;
            height: 20px;
            border: 2px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
            position: relative;
            transition: all 0.3s ease;
        }

        .remember-checkbox input[type="checkbox"]:checked + .checkmark {
            background: linear-gradient(45deg, #4a7c59, #2d5016);
            border-color: #4a7c59;
        }

        .remember-checkbox input[type="checkbox"]:checked + .checkmark::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: bold;
            font-size: 12px;
        }

        .remember-text {
            font-size: 13px;
            color: #555;
        }

        .info-message {
            background: #e3f2fd;
            color: #1976d2;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #2196f3;
        }

        .info-message i {
            margin-right: 8px;
        }
    </style>
</head>
<body class="login-page">
    <!-- Language Switcher - VERBETERD -->
    <div class="language-switcher">
        <button onclick="switchLanguage('nl')" class="lang-btn <?php echo $lang === 'nl' ? 'active' : ''; ?>">
            🇳🇱 NL
        </button>
        <button onclick="switchLanguage('en')" class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>">
            🇬🇧 EN
        </button>
    </div>

    <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
    <!-- REGISTRATIE SUCCES BERICHT -->
    <div class="registration-success-overlay">
        <div class="registration-success">
            <div class="success-container">
                <div class="success-icon">📧</div>
                <h2><?php echo t('registration_success_title'); ?></h2>
                <p><?php echo t('verification_email_sent'); ?>:</p>
                <strong><?php echo htmlspecialchars($_SESSION['registration_email'] ?? ''); ?></strong>
                <p><?php echo t('click_verification_link'); ?></p>
                <div class="success-actions">
                    <a href="index.php?lang=<?php echo $lang; ?>&tab=login" class="btn btn-primary"><?php echo t('back_to_login'); ?></a>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- NORMALE LOGIN/REGISTER/CONTACT/INFO INTERFACE -->
    <div class="login-container">
        <div class="logo">
            <img src="assets/images/logos/logo.png" alt="YessCarp Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="logo-fallback" style="display: none;">🎣</div>
        </div>
        <h1 class="site-title">YessCarp</h1>
        <p class="site-subtitle"><?php echo t('welcome_subtitle'); ?></p>
        
        <div class="auth-buttons">
            <button class="auth-btn <?php echo $show_tab === 'login' ? 'active' : ''; ?>" onclick="showTab('login')">
                <i class="fas fa-sign-in-alt"></i> <?php echo t('login'); ?>
            </button>
            <button class="auth-btn <?php echo $show_tab === 'register' ? 'active' : ''; ?>" onclick="showTab('register')">
                <i class="fas fa-user-plus"></i> <?php echo t('register'); ?>
            </button>
            <button class="auth-btn <?php echo $show_tab === 'contact' ? 'active' : ''; ?>" onclick="showTab('contact')">
                <i class="fas fa-envelope"></i> <?php echo t('contact'); ?>
            </button>
            <button class="auth-btn <?php echo $show_tab === 'info' ? 'active' : ''; ?>" onclick="showTab('info')">
                <i class="fas fa-info-circle"></i> <?php echo t('info'); ?>
            </button>
        </div>
        
        <!-- Login Form MET REMEMBER ME -->
        <div id="login-tab" class="tab-content <?php echo $show_tab === 'login' ? 'active' : ''; ?>">
            <?php if (isset($_SESSION['auto_logged_in']) && $_SESSION['auto_logged_in']): ?>
                <div class="info-message">
                    <i class="fas fa-info-circle"></i> Je bent automatisch ingelogd
                </div>
                <?php unset($_SESSION['auto_logged_in']); ?>
            <?php endif; ?>
            
            <?php if (!empty($login_error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($login_error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="login" value="1">
                
                <div class="form-group">
                    <input type="email" name="email" placeholder="📧 <?php echo t('email_placeholder'); ?>" 
                           value="<?php echo isset($_POST['email']) && isset($_POST['login']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="🔒 <?php echo t('password_placeholder'); ?>" required>
                </div>
                
                <!-- Remember Me Checkbox -->
                <div class="form-group remember-me-group">
                    <label class="remember-checkbox">
                        <input type="checkbox" name="remember_me" value="1" 
                               <?php echo (isset($_POST['remember_me']) && isset($_POST['login'])) ? 'checked' : ''; ?>>
                        <span class="checkmark"></span>
                        <span class="remember-text">🔒 Onthoud mij (30 dagen)</span>
                    </label>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-sign-in-alt"></i> <?php echo t('login'); ?>
                </button>
            </form>
        </div>
        
        <!-- Register Form -->
        <div id="register-tab" class="tab-content <?php echo $show_tab === 'register' ? 'active' : ''; ?>">
            <?php if (!empty($register_errors)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php foreach ($register_errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="register" value="1">
                
                <div class="form-group">
                    <input type="text" name="full_name" placeholder="👤 <?php echo t('name_placeholder'); ?>" 
                           value="<?php echo isset($_POST['full_name']) && isset($_POST['register']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <input type="email" name="email" placeholder="📧 <?php echo t('email_placeholder'); ?>" 
                           value="<?php echo isset($_POST['email']) && isset($_POST['register']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="🔒 <?php echo t('password_min_hint'); ?>" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="🔒 <?php echo t('repeat_password'); ?>" required>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-user-plus"></i> <?php echo t('create_account'); ?>
                </button>
            </form>
        </div>
        
        <!-- Contact Form -->
        <div id="contact-tab" class="tab-content <?php echo $show_tab === 'contact' ? 'active' : ''; ?>">
            <?php if (!empty($contact_success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($contact_success); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($contact_error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($contact_error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="contact" value="1">
                
                <div class="form-group">
                    <input type="text" name="name" placeholder="👤 <?php echo t('your_name'); ?>" 
                           value="<?php echo isset($_POST['name']) && isset($_POST['contact']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <input type="email" name="email" placeholder="📧 <?php echo t('your_email'); ?>" 
                           value="<?php echo isset($_POST['email']) && isset($_POST['contact']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <input type="text" name="subject" placeholder="📝 <?php echo t('subject'); ?>" 
                           value="<?php echo isset($_POST['subject']) && isset($_POST['contact']) ? htmlspecialchars($_POST['subject']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <textarea name="message" rows="4" placeholder="💬 <?php echo t('your_message'); ?>..." required><?php echo isset($_POST['message']) && isset($_POST['contact']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> <?php echo t('send_message'); ?>
                </button>
            </form>
        </div>
        
        <!-- Info Tab -->
        <div id="info-tab" class="tab-content <?php echo $show_tab === 'info' ? 'active' : ''; ?>">
            <div class="info-content">
                <h3><i class="fas fa-info-circle"></i> <?php echo t('about_yesscarp'); ?></h3>
                <p><?php echo t('about_description'); ?></p>
                
                <ul>
                    <li><i class="fas fa-fish"></i> <?php echo t('share_catches'); ?></li>
                    <li><i class="fas fa-map-marker-alt"></i> <?php echo t('discover_locations'); ?></li>
                    <li><i class="fas fa-users"></i> <?php echo t('connect_anglers'); ?></li>
                    <li><i class="fas fa-cloud-sun"></i> <?php echo t('weather_info'); ?></li>
                    <li><i class="fas fa-calendar-alt"></i> <?php echo t('events_competitions'); ?></li>
                </ul>
                
                <h3><i class="fas fa-mobile-alt"></i> <?php echo t('pwa_app'); ?></h3>
                <p><?php echo t('pwa_description'); ?></p>
                
                <h3><i class="fas fa-envelope"></i> <?php echo t('contact'); ?></h3>
                <p><?php echo t('contact_description'); ?> <strong>info@yesscarp.nl</strong></p>
                
                <h3><i class="fas fa-shield-alt"></i> <?php echo t('privacy'); ?></h3>
                <p><?php echo t('privacy_description'); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/pwa.js"></script>
    <script src="assets/js/modules.js"></script>
    
    <script>
    // VERBETERDE Language Switch functie
    function switchLanguage(lang) {
        const currentTab = document.querySelector('.tab-content.active')?.id.replace('-tab', '') || 'login';
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('lang', lang);
        currentUrl.searchParams.set('tab', currentTab);
        window.location.href = currentUrl.toString();
    }
    
    // Tab switching functie (als deze nog niet bestaat in main.js)
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Remove active from all buttons
        document.querySelectorAll('.auth-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Show selected tab
        document.getElementById(tabName + '-tab').classList.add('active');
        
        // Add active to selected button
        event.target.classList.add('active');
        
        // Update URL without reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);
    }
    </script>
</body>
</html>



