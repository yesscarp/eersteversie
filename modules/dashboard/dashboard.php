<?php
// modules/dashboard/dashboard.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in
requireLogin();

// Language detection - consistent met index.php
function detectLanguage() {
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['nl', 'en'])) {
        $_SESSION['language'] = $_GET['lang'];
        return $_GET['lang'];
    }
    
    if (isset($_SESSION['language']) && in_array($_SESSION['language'], ['nl', 'en'])) {
        return $_SESSION['language'];
    }
    
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, '.com') !== false) {
        return 'en';
    } elseif (strpos($host, '.nl') !== false) {
        return 'nl';
    }
    
    return 'nl'; // Default
}

$lang = detectLanguage();
$_SESSION['language'] = $lang;

// Load language file
$translations = [];
if (file_exists("../../languages/{$lang}.php")) {
    include "../../languages/{$lang}.php";
}

function t($key) {
    global $translations;
    return isset($translations[$key]) ? $translations[$key] : $key;
}

// Get user info
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        header('Location: ../../index.php');
        exit();
    }
    
    // Get user stats
    $stats = [
        'catches' => 0,
        'friends' => 0,
        'locations' => 0,
        'photos' => 0
    ];
    
    // Get catches count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM catches WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['catches'] = $stmt->fetch()['count'] ?? 0;
    
    // Get friends count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM user_relationships WHERE (follower_id = ? OR following_id = ?) AND status = 'accepted'");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $stats['friends'] = $stmt->fetch()['count'] ?? 0;
    
    // Get locations count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM locations WHERE added_by = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['locations'] = $stmt->fetch()['count'] ?? 0;
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $user = null;
    $stats = ['catches' => 0, 'friends' => 0, 'locations' => 0, 'photos' => 0];
}

// Get current module
$current_module = $_GET['module'] ?? 'home';
$allowed_modules = ['home', 'catches', 'locations', 'friends', 'chat', 'profile', 'settings', 'groups'];

if (!in_array($current_module, $allowed_modules)) {
    $current_module = 'home';
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('dashboard_title'); ?> - YessCarp</title>
    
    <!-- PWA Meta Tags - VERBETERD -->
    <meta name="theme-color" content="#2d5016">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="YessCarp">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="YessCarp">
    
    <link rel="manifest" href="../../manifest.json">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/images/icons/icon-32x32.png">
    <link rel="apple-touch-icon" href="../../assets/images/icons/icon-192x192.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/pwa.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Language Switcher - Rechtsboven zoals in je main.css -->
    <div class="language-switcher">
        <button class="lang-btn <?php echo $lang === 'nl' ? 'active' : ''; ?>" onclick="switchLanguage('nl')">
            🇳🇱 NL
        </button>
        <button class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>" onclick="switchLanguage('en')">
            🇬🇧 EN
        </button>
    </div>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">🎣 YessCarp</div>
                <p><?php echo t('social_network_subtitle'); ?></p>
            </div>

            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['first_name'] ?? $user['full_name'] ?? 'U', 0, 1)); ?>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($user['first_name'] ? $user['first_name'] . ' ' . $user['last_name'] : $user['full_name']); ?></h3>
                    <p><?php echo ucfirst($user['fishing_experience'] ?? t('beginner')); ?></p>
                </div>
            </div>

            <div class="nav-menu">
                <div class="nav-section">
                    <h4><?php echo t('dashboard'); ?></h4>
                    <a href="?module=home&lang=<?php echo $lang; ?>" class="nav-item <?php echo $current_module === 'home' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> <?php echo t('home_feed'); ?>
                    </a>
                    <a href="?module=catches&lang=<?php echo $lang; ?>" class="nav-item <?php echo $current_module === 'catches' ? 'active' : ''; ?>">
                        <i class="fas fa-fish"></i> <?php echo t('my_catches'); ?>
                    </a>
                    <a href="?module=albums&lang=<?php echo $lang; ?>" class="nav-item">
                        <i class="fas fa-images"></i> <?php echo t('photo_albums'); ?>
                    </a>
                </div>

                <div class="nav-section">
                    <h4><?php echo t('social'); ?></h4>
                    <a href="?module=friends&lang=<?php echo $lang; ?>" class="nav-item <?php echo $current_module === 'friends' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> <?php echo t('friends'); ?>
                        <?php if ($stats['friends'] > 0): ?>
                            <span class="badge"><?php echo $stats['friends']; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="?module=groups&lang=<?php echo $lang; ?>" class="nav-item <?php echo $current_module === 'groups' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> <?php echo t('groups'); ?>
                    </a>
                    <a href="?module=chat&lang=<?php echo $lang; ?>" class="nav-item <?php echo $current_module === 'chat' ? 'active' : ''; ?>">
                        <i class="fas fa-comments"></i> <?php echo t('chat'); ?>
                        <span class="badge">NEW</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-search"></i> <?php echo t('discover'); ?>
                    </a>
                </div>

                <div class="nav-section">
                    <h4><?php echo t('fishing_tools'); ?></h4>
                    <a href="?module=logbook&lang=<?php echo $lang; ?>" class="nav-item">
                        <i class="fas fa-book"></i> <?php echo t('fishing_logbook'); ?>
                    </a>
                    <a href="?module=weather&lang=<?php echo $lang; ?>" class="nav-item">
                        <i class="fas fa-cloud-sun"></i> <?php echo t('weather_info'); ?>
                    </a>
                    <a href="?module=locations&lang=<?php echo $lang; ?>" class="nav-item <?php echo $current_module === 'locations' ? 'active' : ''; ?>">
                        <i class="fas fa-map-marker-alt"></i> <?php echo t('fishing_locations'); ?>
                    </a>
                    <a href="?module=map&lang=<?php echo $lang; ?>" class="nav-item">
                        <i class="fas fa-map"></i> <?php echo t('fishing_map'); ?>
                        <span class="badge">1</span>
                    </a>
                </div>

                <div class="nav-section">
                    <h4><?php echo t('account'); ?></h4>
                    <a href="?module=profile&lang=<?php echo $lang; ?>" class="nav-item <?php echo $current_module === 'profile' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> <?php echo t('my_profile'); ?>
                    </a>
                    <a href="?module=settings&lang=<?php echo $lang; ?>" class="nav-item <?php echo $current_module === 'settings' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i> <?php echo t('settings'); ?>
                    </a>
                    <a href="../auth/logout.php" class="nav-item">
                        <i class="fas fa-sign-out-alt"></i> <?php echo t('logout'); ?>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="topbar">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="mobile-menu-btn" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">
                        <?php
                        $page_titles = [
                            'home' => t('home_feed'),
                            'catches' => t('my_catches'),
                            'locations' => t('fishing_locations'),
                            'friends' => t('friends'),
                            'groups' => t('groups'),
                            'chat' => t('chat'),
                            'profile' => t('my_profile'),
                            'settings' => t('settings')
                        ];
                        echo $page_titles[$current_module] ?? t('dashboard');
                        ?>
                    </h1>
                </div>

                <div class="topbar-actions">
                    <button class="notification-btn" title="<?php echo t('notifications'); ?>">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">5</span>
                    </button>
                    <button class="notification-btn" title="<?php echo t('search'); ?>">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="notification-btn" title="<?php echo t('add_new'); ?>">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-area">
                <?php if ($current_module === 'home'): ?>
                    <!-- Stats Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-header">
                                <div>
                                    <div class="stat-number"><?php echo $stats['catches']; ?></div>
                                    <div class="stat-label"><?php echo t('catches'); ?></div>
                                </div>
                                <div class="stat-icon catches">
                                    <i class="fas fa-fish"></i>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-header">
                                <div>
                                    <div class="stat-number"><?php echo $stats['friends']; ?></div>
                                    <div class="stat-label"><?php echo t('friends'); ?></div>
                                </div>
                                <div class="stat-icon friends">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-header">
                                <div>
                                    <div class="stat-number"><?php echo $stats['locations']; ?></div>
                                    <div class="stat-label"><?php echo t('locations'); ?></div>
                                </div>
                                <div class="stat-icon locations">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-header">
                                <div>
                                    <div class="stat-number"><?php echo $stats['photos']; ?></div>
                                    <div class="stat-label"><?php echo t('photos'); ?></div>
                                </div>
                                <div class="stat-icon photos">
                                    <i class="fas fa-camera"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <a href="?module=catches&action=add&lang=<?php echo $lang; ?>" class="action-btn">
                            <i class="fas fa-plus"></i> <?php echo t('new_catch'); ?>
                        </a>
                        <a href="?module=locations&action=discover&lang=<?php echo $lang; ?>" class="action-btn">
                            <i class="fas fa-map"></i> <?php echo t('discover_locations'); ?>
                        </a>
                        <a href="?module=friends&action=find&lang=<?php echo $lang; ?>" class="action-btn">
                            <i class="fas fa-user-plus"></i> <?php echo t('find_friends'); ?>
                        </a>
                        <a href="?module=chat&lang=<?php echo $lang; ?>" class="action-btn">
                            <i class="fas fa-comments"></i> <?php echo t('start_chat'); ?>
                        </a>
                    </div>

                    <!-- Recent Activity -->
                    <div class="module-content">
                        <h2 style="margin-bottom: 20px; color: #2d5016;">
                            <i class="fas fa-clock"></i> <?php echo t('recent_activity'); ?>
                        </h2>
                        <div style="text-align: center; padding: 40px; color: #666;">
                            <i class="fas fa-fish" style="font-size: 3rem; margin-bottom: 15px; color: #ddd;"></i>
                            <p><?php echo t('no_activity_yet'); ?></p>
                            <a href="?module=catches&action=add&lang=<?php echo $lang; ?>" class="action-btn" style="margin-top: 20px; display: inline-flex;">
                                <i class="fas fa-plus"></i> <?php echo t('add_first_catch'); ?>
                            </a>
                        </div>
                    </div>
                
                <?php elseif ($current_module === 'groups'): ?>
                    <!-- Groups/Location Chat Page -->
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                            <div style="background: var(--accent-color); color: var(--primary-color); padding: 15px; border-radius: 12px; font-size: 1.5rem;">💬</div>
                            <div>
                                <h1 style="font-size: 2rem; margin: 0; color: white;"><?php echo t('location_chat'); ?></h1>
                                <p style="color: rgba(255,255,255,0.7); margin: 5px 0 0 0;">
                                    <?php echo t('chat_live_with_anglers'); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- GPS Status -->
                    <div style="background: rgba(0,0,0,0.2); border-radius: 10px; padding: 15px; margin-bottom: 20px; border: 1px solid var(--border-color);">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                            <i class="fas fa-map-marker-alt" style="color: var(--accent-color);"></i>
                            <strong><?php echo t('gps_active'); ?>: 52.1326, 5.2913 • <?php echo t('chat_access_within'); ?> 3km</strong>
                        </div>
                        
                        <div style="display: flex; gap: 15px; font-size: 0.9rem; flex-wrap: wrap;">
                            <span>
                                <i class="fas fa-map"></i> <?php echo t('all_locations'); ?> (164)
                            </span>
                            <span>
                                <i class="fas fa-ban"></i> <?php echo t('inaccessible'); ?> (0)
                            </span>
                            <span>
                                <i class="fas fa-map-marker"></i> <?php echo t('steenwijk'); ?> (15)
                            </span>
                            <span>
                                <i class="fas fa-circle" style="color: #ff4757;"></i> <?php echo t('active'); ?> (0)
                            </span>
                        </div>
                    </div>

                    <!-- Location Cards -->
                    <div style="display: grid; gap: 20px;">
                        <!-- Weerribben -->
                        <div class="location-card">
                            <div class="location-header">
                                <div style="background: #3498db; color: white; padding: 8px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">
                                    📍 85.1km
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; color: rgba(255,255,255,0.7); font-size: 0.9rem;">
                                    <i class="fas fa-lock"></i>
                                    <span><?php echo t('chat_locked_too_far'); ?></span>
                                </div>
                            </div>
                            
                            <h2 class="location-title"><?php echo t('weerribben'); ?></h2>
                            <div class="location-subtitle">📍 <?php echo t('steenwijk'); ?></div>
                            
                            <div class="location-stats">
                                <span>
                                    <i class="fas fa-users"></i> 0 <?php echo t('members'); ?>
                                </span>
                                <span>
                                    <i class="fas fa-comments"></i> 1 <?php echo t('messages'); ?>
                                </span>
                            </div>
                            
                            <div style="margin-top: 15px;">
                                <button class="chat-btn chat-locked">
                                    <i class="fas fa-lock"></i> <?php echo t('chat_locked'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Kalenbergergracht -->
                        <div class="location-card">
                            <div class="location-header">
                                <div style="background: #3498db; color: white; padding: 8px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">
                                    📍 87.9km
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; color: rgba(255,255,255,0.7); font-size: 0.9rem;">
                                    <i class="fas fa-lock"></i>
                                    <span><?php echo t('chat_locked_too_far'); ?></span>
                                </div>
                            </div>
                            
                            <h2 class="location-title"><?php echo t('kalenbergergracht'); ?></h2>
                            <div class="location-subtitle">📍 <?php echo t('steenwijk'); ?></div>
                            
                            <div class="location-stats">
                                <span>
                                    <i class="fas fa-users"></i> 0 <?php echo t('members'); ?>
                                </span>
                                <span>
                                    <i class="fas fa-comments"></i> 1 <?php echo t('messages'); ?>
                                </span>
                            </div>
                            
                            <div style="margin-top: 15px;">
                                <button class="chat-btn chat-locked">
                                    <i class="fas fa-lock"></i> <?php echo t('chat_locked'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Steenwijk Kanaal Zuid -->
                        <div class="location-card">
                            <div class="location-header">
                                <div style="background: #3498db; color: white; padding: 8px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">
                                    📍 90.8km
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; color: rgba(255,255,255,0.7); font-size: 0.9rem;">
                                    <i class="fas fa-lock"></i>
                                    <span><?php echo t('chat_locked_too_far'); ?></span>
                                </div>
                            </div>
                            
                            <h2 class="location-title"><?php echo t('steenwijk_kanaal_zuid'); ?></h2>
                            <div class="location-subtitle">📍 <?php echo t('steenwijk'); ?></div>
                            
                            <div class="location-stats">
                                <span>
                                    <i class="fas fa-users"></i> 0 <?php echo t('members'); ?>
                                </span>
                                <span>
                                    <i class="fas fa-comments"></i> 1 <?php echo t('messages'); ?>
                                </span>
                            </div>
                            
                            <div style="margin-top: 15px;">
                                <button class="chat-btn chat-locked">
                                    <i class="fas fa-lock"></i> <?php echo t('chat_locked'); ?>
                                </button>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Dynamic Module Content -->
                    <div class="module-content">
                        <?php 
                        $module_file = "modules/{$current_module}.php";
                        if (file_exists($module_file)) {
                            include $module_file; 
                        } else {
                            echo '<div style="text-align: center; padding: 40px; color: #666;">';
                            echo '<h2>' . t('module_not_found') . '</h2>';
                            echo '<p>' . sprintf(t('module_coming_soon'), ucfirst($current_module)) . '</p>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- PWA Install Button -->
    <button class="pwa-install" id="pwaInstall" onclick="installPWA()">
        <i class="fas fa-download"></i> <?php echo t('install_app'); ?>
    </button>

    <script>
        // Language Switch
        function switchLanguage(lang) {
            const currentModule = new URLSearchParams(window.location.search).get('module') || 'home';
            window.location.href = `?module=${currentModule}&lang=${lang}`;
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('mobile-visible');
            overlay.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !menuBtn.contains(e.target) && 
                sidebar.classList.contains('mobile-visible')) {
                toggleSidebar();
            }
        });

        // PWA Install
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            document.getElementById('pwaInstall').classList.remove('hidden');
        });

        function installPWA() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    }
                    deferredPrompt = null;
                    document.getElementById('pwaInstall').classList.add('hidden');
                });
            }
        }

        // Check if already installed
        if (window.matchMedia('(display-mode: standalone)').matches) {
            document.getElementById('pwaInstall').classList.add('hidden');
        }

        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('../../sw.js')
                .then(registration => console.log('SW registered'))
                .catch(error => console.log('SW registration failed'));
        }
    </script>
</body>
</html>