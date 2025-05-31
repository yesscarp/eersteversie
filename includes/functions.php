<?php
// includes/functions.php - Verbeterde versie met Remember Me

/**
 * Convert timestamp to human readable time ago format
 */
function timeAgo($datetime, $full = false) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Bereken weken handmatig
    $weeks = floor($diff->d / 7);
    $days = $diff->d % 7;

    $string = array(
        'y' => 'jaar',
        'm' => 'maand', 
        'w' => 'week',
        'd' => 'dag',
        'h' => 'uur',
        'i' => 'minuut',
        's' => 'seconde',
    );
    
    $periods = array();
    
    if ($diff->y) {
        $periods[] = $diff->y . ' ' . $string['y'] . ($diff->y > 1 ? 'en' : '');
    }
    if ($diff->m) {
        $periods[] = $diff->m . ' ' . $string['m'] . ($diff->m > 1 ? 'en' : '');
    }
    if ($weeks) {
        $periods[] = $weeks . ' ' . $string['w'] . ($weeks > 1 ? 'en' : '');
    }
    if ($days) {
        $periods[] = $days . ' ' . $string['d'] . ($days > 1 ? 'en' : '');
    }
    if ($diff->h) {
        $periods[] = $diff->h . ' ' . $string['h'] . ($diff->h > 1 ? 'en' : '');
    }
    if ($diff->i) {
        $periods[] = $diff->i . ' ' . $string['i'] . ($diff->i > 1 ? 'en' : '');
    }
    if ($diff->s) {
        $periods[] = $diff->s . ' ' . $string['s'] . ($diff->s > 1 ? 'en' : '');
    }

    if (empty($periods)) {
        return 'zojuist';
    }

    if (!$full) {
        $periods = array_slice($periods, 0, 1);
    }
    
    return implode(', ', $periods) . ' geleden';
}

/**
 * Alternatieve eenvoudige timeAgo functie
 */
function simpleTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'zojuist';
    if ($time < 3600) return floor($time/60) . ' minuten geleden';
    if ($time < 86400) return floor($time/3600) . ' uur geleden';
    if ($time < 2592000) return floor($time/86400) . ' dagen geleden';
    if ($time < 31104000) return floor($time/2592000) . ' maanden geleden';
    
    return floor($time/31104000) . ' jaar geleden';
}

/**
 * Sanitize output for HTML display
 */
function sanitizeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../../index.php');
        exit();
    }
}

/**
 * Generate secure random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password securely
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Upload image file with validation
 */
function uploadImage($file, $uploadDir = '../../uploads/') {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Alleen JPEG, PNG, GIF en WebP bestanden zijn toegestaan');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('Bestand is te groot (max 5MB)');
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Fout bij uploaden van bestand');
    }
    
    return $filename;
}

/**
 * Get user's IP address
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Get weather icon class based on condition
 */
function getWeatherIcon($condition) {
    $condition = strtolower($condition);
    
    if (strpos($condition, 'rain') !== false || strpos($condition, 'regen') !== false) {
        return 'fas fa-cloud-rain';
    } elseif (strpos($condition, 'cloud') !== false || strpos($condition, 'bewolkt') !== false) {
        return 'fas fa-cloud';
    } elseif (strpos($condition, 'sun') !== false || strpos($condition, 'zon') !== false) {
        return 'fas fa-sun';
    } elseif (strpos($condition, 'snow') !== false || strpos($condition, 'sneeuw') !== false) {
        return 'fas fa-snowflake';
    } else {
        return 'fas fa-cloud-sun';
    }
}

/**
 * Calculate distance between two coordinates
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // km
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

/**
 * Generate slug from string
 */
function generateSlug($string) {
    // Convert to lowercase
    $slug = strtolower($string);
    
    // Handle Dutch characters
    $slug = str_replace(
        ['á', 'à', 'ä', 'â', 'ã', 'å', 'æ', 'ç', 'é', 'è', 'ë', 'ê', 'í', 'ì', 'ï', 'î', 'ñ', 'ó', 'ò', 'ö', 'ô', 'õ', 'ø', 'ú', 'ù', 'ü', 'û', 'ý', 'ÿ'],
        ['a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y'],
        $slug
    );
    
    // Remove special characters (keep only letters, numbers, spaces, and hyphens)
    $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
    
    // Replace multiple spaces/hyphens with single hyphen
    $slug = preg_replace('/[\s\-]+/', '-', $slug);
    
    // Trim hyphens from beginning and end
    $slug = trim($slug, '-');
    
    return $slug;
}

/**
 * Send verification email
 */
function sendVerificationEmail($email, $token, $fullName) {
    $verificationLink = "https://yesscarp.nl/verify-email.php?token=" . urlencode($token);
    
    $subject = "Bevestig je email adres - YessCarp";
    $message = "
    <html>
    <head>
        <title>Email Verificatie - YessCarp</title>
    </head>
    <body style='font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;'>
        <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(135deg, #2d5016, #4a7c59); padding: 30px; text-align: center;'>
                <h1 style='color: #f4d03f; margin: 0; font-size: 2rem;'>YessCarp</h1>
                <p style='color: white; margin: 10px 0 0 0;'>Het sociale netwerk voor vissers</p>
            </div>
            <div style='padding: 30px;'>
                <h2 style='color: #2d5016; margin-bottom: 20px;'>Hallo " . htmlspecialchars($fullName) . "!</h2>
                <p style='color: #333; line-height: 1.6; margin-bottom: 20px;'>
                    Welkom bij YessCarp! Om je account te activeren, klik op de onderstaande knop om je email adres te bevestigen.
                </p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . $verificationLink . "' style='background: linear-gradient(45deg, #f4d03f, #f1c40f); color: #2d5016; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; display: inline-block;'>
                        🎣 BEVESTIG EMAIL
                    </a>
                </div>
                <p style='color: #666; font-size: 14px; margin-top: 30px;'>
                    Deze link is 24 uur geldig. Als je deze email niet hebt aangevraagd, kun je deze negeren.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: YessCarp <noreply@yesscarp.nl>" . "\r\n";
    
    return mail($email, $subject, $message, $headers);
}

/**
 * Generate verification token
 */
function generateVerificationToken() {
    return bin2hex(random_bytes(32));
}

// =============================================================================
// REMEMBER ME FUNCTIES - NIEUW
// =============================================================================

/**
 * Generate remember token
 */
function generateRememberToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Create remember token for user
 */
function createRememberToken($userId, $db) {
    $token = generateRememberToken();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days')); // 30 dagen geldig
    
    $stmt = $db->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $token, $expiresAt]);
    
    return $token;
}

/**
 * Set remember cookie
 */
function setRememberCookie($token) {
    $cookieName = 'yesscarp_remember';
    $cookieValue = $token;
    $expiry = time() + (30 * 24 * 60 * 60); // 30 dagen
    $path = '/';
    $domain = '';
    $secure = isset($_SERVER['HTTPS']); // Alleen HTTPS als beschikbaar
    $httpOnly = true; // Bescherming tegen XSS
    
    setcookie($cookieName, $cookieValue, $expiry, $path, $domain, $secure, $httpOnly);
}

/**
 * Verify remember token and auto-login
 */
function checkRememberToken($db) {
    if (!isset($_COOKIE['yesscarp_remember'])) {
        return false;
    }
    
    $token = $_COOKIE['yesscarp_remember'];
    
    // Haal token info op uit database
    $stmt = $db->prepare("
        SELECT rt.user_id, rt.expires_at, u.email, u.full_name, u.profile_completed 
        FROM remember_tokens rt 
        JOIN users u ON rt.user_id = u.id 
        WHERE rt.token = ? AND rt.expires_at > NOW() AND u.status = 'active'
    ");
    $stmt->execute([$token]);
    $result = $stmt->fetch();
    
    if (!$result) {
        // Token niet gevonden of verlopen, verwijder cookie
        clearRememberCookie();
        return false;
    }
    
    // Update last_used timestamp
    $updateStmt = $db->prepare("UPDATE remember_tokens SET last_used = NOW() WHERE token = ?");
    $updateStmt->execute([$token]);
    
    // Login de gebruiker
    $_SESSION['user_id'] = $result['user_id'];
    $_SESSION['user_email'] = $result['email'];
    $_SESSION['user_name'] = $result['full_name'];
    $_SESSION['profile_completed'] = $result['profile_completed'];
    $_SESSION['auto_logged_in'] = true; // Flag voor UI feedback
    
    // Update last login in users tabel
    $loginStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $loginStmt->execute([$result['user_id']]);
    
    return true;
}

/**
 * Clear remember cookie
 */
function clearRememberCookie() {
    setcookie('yesscarp_remember', '', time() - 3600, '/');
}

/**
 * Remove remember token from database
 */
function removeRememberToken($token, $db) {
    $stmt = $db->prepare("DELETE FROM remember_tokens WHERE token = ?");
    $stmt->execute([$token]);
}

/**
 * Remove all remember tokens for user (logout from all devices)
 */
function removeAllRememberTokens($userId, $db) {
    $stmt = $db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
    $stmt->execute([$userId]);
}

/**
 * Cleanup expired tokens (run this periodically)
 */
function cleanupExpiredTokens($db) {
    $stmt = $db->prepare("DELETE FROM remember_tokens WHERE expires_at < NOW()");
    $stmt->execute();
}

/**
 * Get user remember tokens for management
 */
function getUserRememberTokens($userId, $db) {
    $stmt = $db->prepare("
        SELECT token, created_at, last_used, expires_at 
        FROM remember_tokens 
        WHERE user_id = ? AND expires_at > NOW() 
        ORDER BY last_used DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}
?>
