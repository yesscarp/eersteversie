<?php
// api/remove-all-sessions.php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Remove all tokens for this user
        removeAllRememberTokens($_SESSION['user_id'], $db);
        
        // Clear current remember cookie if exists
        if (isset($_COOKIE['yesscarp_remember'])) {
            clearRememberCookie();
        }
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
}
?>