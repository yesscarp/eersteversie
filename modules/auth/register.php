<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    if (empty($full_name)) $errors[] = "Naam is verplicht";
    if (empty($email)) $errors[] = "Email is verplicht";
    if (!isValidEmail($email)) $errors[] = "Ongeldig emailadres";
    if (empty($password)) $errors[] = "Wachtwoord is verplicht";
    if (strlen($password) < 6) $errors[] = "Wachtwoord moet minimaal 6 tekens zijn";
    if ($password !== $confirm_password) $errors[] = "Wachtwoorden komen niet overeen";
    
    if (empty($errors)) {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $errors[] = "Email is al in gebruik";
        } else {
            // Create user
            $hashedPassword = hashPassword($password);
            $username = generateSlug($full_name) . '_' . uniqid();
            
            $stmt = $db->prepare("INSERT INTO users (email, password, full_name, username, created_at) VALUES (?, ?, ?, ?, NOW())");
            
            if ($stmt->execute([$email, $hashedPassword, $full_name, $username])) {
                $userId = $db->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $full_name;
                
                header('Location: ../dashboard/dashboard.php');
                exit();
            } else {
                $errors[] = "Fout bij aanmaken account";
            }
        }
    }
}
?>
