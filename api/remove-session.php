<?php
// api/remove-session.php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['token'] ?? '';
    
    if ($token) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Remove specific token for this user
            $stmt = $db->prepare("DELETE FROM remember_tokens WHERE token = ? AND user_id = ?");
            $stmt->execute([$token, $_SESSION['user_id']]);
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Token required']);
    }
}
?>