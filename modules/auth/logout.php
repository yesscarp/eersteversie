<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Verwijder remember token als die bestaat
if (isset($_COOKIE['yesscarp_remember'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $token = $_COOKIE['yesscarp_remember'];
        removeRememberToken($token, $db);
        clearRememberCookie();
    } catch (Exception $e) {
        // Log error maar ga verder met uitloggen
        error_log("Error removing remember token: " . $e->getMessage());
    }
}

session_destroy();
header('Location: ../../index.php');
exit();
?>