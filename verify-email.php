<?php
// verify-email.php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$message = '';
$success = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("SELECT id, email, full_name, token_expires_at FROM users WHERE verification_token = ? AND email_verified = 0");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Check if token is expired
            if (strtotime($user['token_expires_at']) > time()) {
                // Token is valid, verify email
                $updateStmt = $db->prepare("UPDATE users SET email_verified = 1, verification_token = NULL, token_expires_at = NULL WHERE id = ?");
                if ($updateStmt->execute([$user['id']])) {
                    $success = true;
                    $message = "Je email adres is succesvol geverifieerd! Je kunt nu inloggen.";
                } else {
                    $message = "Er is een fout opgetreden bij het verifiëren van je email.";
                }
            } else {
                $message = "Deze verificatie link is verlopen. Vraag een nieuwe aan.";
            }
        } else {
            $message = "Ongeldige of reeds gebruikte verificatie link.";
        }
    } catch (Exception $e) {
        $message = "Er is een technische fout opgetreden.";
    }
} else {
    $message = "Geen verificatie token gevonden.";
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verificatie - YessCarp</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body {
            background: linear-gradient(135deg, #2d5016, #4a7c59);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .verification-container {
            max-width: 500px;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
        }
        .verification-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        .success { color: #4caf50; }
        .error { color: #f44336; }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-icon <?php echo $success ? 'success' : 'error'; ?>">
            <?php echo $success ? '✅' : '❌'; ?>
        </div>
        <h1 style="color: #2d5016; margin-bottom: 20px;">
            <?php echo $success ? 'Verificatie Succesvol!' : 'Verificatie Mislukt'; ?>
        </h1>
        <p style="color: #666; margin-bottom: 30px; line-height: 1.6;">
            <?php echo htmlspecialchars($message); ?>
        </p>
        <a href="index.php" style="background: linear-gradient(45deg, #f4d03f, #f1c40f); color: #2d5016; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold;">
            🎣 Naar Login
        </a>
    </div>
</body>
</html>
