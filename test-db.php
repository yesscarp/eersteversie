<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "✅ Database verbinding succesvol!<br>";
    
    // Test of users tabel bestaat
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users tabel bestaat!<br>";
        
        // Tel aantal gebruikers
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch()['count'];
        echo "📊 Aantal gebruikers in database: " . $count . "<br>";
        
        // FORCEER TESTGEBRUIKER AANMAKEN
        echo "<br>🔧 Testgebruiker aanmaken...<br>";
        
        // Verwijder eerst eventuele bestaande testgebruiker
        $stmt = $db->prepare("DELETE FROM users WHERE email = ?");
        $stmt->execute(['test@yesscarp.nl']);
        
        $testEmail = 'test@yesscarp.nl';
        $testPassword = 'test123';
        $testName = 'Test Gebruiker';
        $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
        $username = 'test_user_' . uniqid();
        
        echo "🔑 Hash van wachtwoord: " . $hashedPassword . "<br>";
        
        $stmt = $db->prepare("INSERT INTO users (email, password, full_name, username, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
        
        if ($stmt->execute([$testEmail, $hashedPassword, $testName, $username])) {
            $userId = $db->lastInsertId();
            echo "✅ Testgebruiker aangemaakt met ID: " . $userId . "!<br>";
            echo "📧 Email: <strong>test@yesscarp.nl</strong><br>";
            echo "🔑 Wachtwoord: <strong>test123</strong><br>";
            
            // Test direct de wachtwoord verificatie
            if (password_verify('test123', $hashedPassword)) {
                echo "✅ Wachtwoord verificatie werkt!<br>";
            } else {
                echo "❌ Wachtwoord verificatie mislukt!<br>";
            }
            
        } else {
            echo "❌ Fout bij aanmaken testgebruiker<br>";
            print_r($stmt->errorInfo());
        }
        
        // Toon alle gebruikers
        $stmt = $db->query("SELECT id, email, full_name, username, status, created_at FROM users");
        $users = $stmt->fetchAll();
        
        if (!empty($users)) {
            echo "<h3>Gebruikers in database:</h3>";
            foreach ($users as $user) {
                echo "ID: {$user['id']}, Email: {$user['email']}, Naam: {$user['full_name']}, Status: {$user['status']}<br>";
            }
        }
        
    } else {
        echo "❌ Users tabel bestaat niet!<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database fout: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString();
}
?>

<hr>
<h3>Test Inloggen</h3>
<p>Ga naar <a href="index.php">index.php</a> en probeer in te loggen met:</p>
<ul>
    <li><strong>Email:</strong> test@yesscarp.nl</li>
    <li><strong>Wachtwoord:</strong> test123</li>
</ul>

<h3>Test met foute gegevens</h3>
<p>Probeer ook in te loggen met:</p>
<ul>
    <li><strong>Email:</strong> fout@email.com</li>
    <li><strong>Wachtwoord:</strong> foutwachtwoord</li>
</ul>
<p>Dit zou moeten falen!</p>

