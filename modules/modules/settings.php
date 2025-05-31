<h2 style="color: #2d5016; margin-bottom: 30px;">
    <i class="fas fa-cog"></i> Instellingen
</h2>

<!-- Security Settings -->
<div style="background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px;">
    <h3 style="color: #2d5016; margin-bottom: 20px;">🔐 Beveiliging</h3>
    
    <?php
    // Get remember tokens for this user
    $tokens = getUserRememberTokens($_SESSION['user_id'], $db);
    ?>
    
    <div style="margin-bottom: 30px;">
        <h4 style="margin-bottom: 15px;">Actieve Sessies</h4>
        <?php if ($tokens): ?>
            <div style="background: #f8f9fa; border-radius: 8px; padding: 15px;">
                <?php foreach ($tokens as $token): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #eee;">
                        <div>
                            <strong>Apparaat sessie</strong><br>
                            <small style="color: #666;">
                                Aangemaakt: <?php echo date('d-m-Y H:i', strtotime($token['created_at'])); ?>
                                <?php if ($token['last_used']): ?>
                                    | Laatst gebruikt: <?php echo timeAgo($token['last_used']); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <button onclick="removeSession('<?php echo $token['token']; ?>')" style="padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;">
                            Verwijderen
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button onclick="removeAllSessions()" style="margin-top: 15px; padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;">
                🚪 Uitloggen van alle apparaten
            </button>
        <?php else: ?>
            <p style="color: #666;">Geen actieve onthoud-sessies gevonden.</p>
        <?php endif; ?>
    </div>
</div>

<!-- App Settings -->
<div style="background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px;">
    <h3 style="color: #2d5016; margin-bottom: 20px;">⚙️ App Instellingen</h3>
    
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #eee;">
        <div>
            <strong>Notificaties</strong><br>
            <small style="color: #666;">Ontvang meldingen van nieuwe berichten en vangsten</small>
        </div>
        <label style="position: relative; display: inline-block; width: 60px; height: 34px;">
            <input type="checkbox" checked style="opacity: 0; width: 0; height: 0;">
            <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #2d5016; transition: .4s; border-radius: 34px;"></span>
        </label>
    </div>
    
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0;">
        <div>
            <strong>Donkere Modus</strong><br>
            <small style="color: #666;">Schakel over naar donker thema</small>
        </div>
        <label style="position: relative; display: inline-block; width: 60px; height: 34px;">
            <input type="checkbox" style="opacity: 0; width: 0; height: 0;">
            <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px;"></span>
        </label>
    </div>
</div>

<!-- Account Actions -->
<div style="background: white; border-radius: 10px; padding: 20px;">
    <h3 style="color: #2d5016; margin-bottom: 20px;">👤 Account</h3>
    
    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
        <button onclick="changePassword()" style="padding: 10px 20px; background: #2d5016; color: white; border: none; border-radius: 5px; cursor: pointer;">
            🔑 Wachtwoord Wijzigen
        </button>
        <button onclick="exportData()" style="padding: 10px 20px; background: #17a2b8; color: white; border: none; border-radius: 5px; cursor: pointer;">
            📥 Data Exporteren
        </button>
        <button onclick="deleteAccount()" style="padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;">
            🗑️ Account Verwijderen
        </button>
    </div>
</div>

<script>
function removeSession(token) {
    if (confirm('Weet je zeker dat je deze sessie wilt verwijderen?')) {
        fetch('../../api/remove-session.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token: token })
        }).then(() => location.reload());
    }
}

function removeAllSessions() {
    if (confirm('Weet je zeker dat je van alle apparaten wilt uitloggen?')) {
        fetch('../../api/remove-all-sessions.php', {
            method: 'POST'
        }).then(() => location.reload());
    }
}

function changePassword() {
    alert('Wachtwoord wijzigen modal komt hier! 🔑');
}

function exportData() {
    alert('Data exporteren functie komt hier! 📥');
}

function deleteAccount() {
    if (confirm('Weet je ZEKER dat je je account wilt verwijderen? Dit kan niet ongedaan worden gemaakt!')) {
        alert('Account verwijderen functie komt hier! 🗑️');
    }
}
</script>