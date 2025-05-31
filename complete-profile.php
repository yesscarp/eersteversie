<?php
// complete-profile.php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Check if profile is already completed
if (isset($_SESSION['profile_completed']) && $_SESSION['profile_completed']) {
    header('Location: modules/dashboard/dashboard.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $date_of_birth = $_POST['date_of_birth'];
    $fishing_experience = $_POST['fishing_experience'];
    $favorite_fish_species = trim($_POST['favorite_fish_species']);
    
    // Validation
    if (empty($first_name)) $errors[] = "Voornaam is verplicht";
    if (empty($last_name)) $errors[] = "Achternaam is verplicht";
    if (empty($city)) $errors[] = "Woonplaats is verplicht";
    if (empty($date_of_birth)) $errors[] = "Geboortedatum is verplicht";
    if (empty($fishing_experience)) $errors[] = "Vis ervaring is verplicht";
    
    if (empty($errors)) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, address = ?, city = ?, postal_code = ?, date_of_birth = ?, fishing_experience = ?, favorite_fish_species = ?, profile_completed = 1 WHERE id = ?");
            
            if ($stmt->execute([$first_name, $last_name, $phone, $address, $city, $postal_code, $date_of_birth, $fishing_experience, $favorite_fish_species, $_SESSION['user_id']])) {
                $_SESSION['profile_completed'] = 1;
                header('Location: modules/dashboard/dashboard.php');
                exit();
            } else {
                $errors[] = "Er is een fout opgetreden bij het opslaan van je profiel";
            }
        } catch (Exception $e) {
            $errors[] = "Er is een technische fout opgetreden";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profiel Aanvullen - YessCarp</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body {
            background: linear-gradient(135deg, #2d5016, #4a7c59);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .profile-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2d5016;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4a7c59;
        }
        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #f4d03f, #f1c40f);
            color: #2d5016;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(244, 208, 63, 0.4);
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2d5016; margin-bottom: 10px;">🎣 Welkom bij YessCarp!</h1>
            <p style="color: #666;">Vul je profiel aan om je account te voltooien</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?php foreach ($errors as $error): ?>
                    <p style="margin: 5px 0;"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">Voornaam *</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Achternaam *</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Telefoonnummer</label>
                    <input type="tel" id="phone" name="phone" placeholder="+31 6 12345678">
                </div>
                <div class="form-group">
                    <label for="date_of_birth">Geboortedatum *</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Adres</label>
                <input type="text" id="address" name="address" placeholder="Straat en huisnummer">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="postal_code">Postcode</label>
                    <input type="text" id="postal_code" name="postal_code" placeholder="1234 AB">
                </div>
                <div class="form-group">
                    <label for="city">Woonplaats *</label>
                    <input type="text" id="city" name="city" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="fishing_experience">Vis ervaring *</label>
                <select id="fishing_experience" name="fishing_experience" required>
                    <option value="">Selecteer je ervaring</option>
                    <option value="beginner">Beginner (0-2 jaar)</option>
                    <option value="intermediate">Gevorderd (2-5 jaar)</option>
                    <option value="advanced">Expert (5-10 jaar)</option>
                    <option value="expert">Professional (10+ jaar)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="favorite_fish_species">Favoriete vissoorten</label>
                <textarea id="favorite_fish_species" name="favorite_fish_species" rows="3" placeholder="Bijv. Karper, Snoek, Baars..."></textarea>
            </div>
            
            <button type="submit" class="submit-btn">
                🎣 PROFIEL VOLTOOIEN
            </button>
        </form>
    </div>
</body>
</html>
