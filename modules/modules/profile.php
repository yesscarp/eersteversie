<h2 style="color: #2d5016; margin-bottom: 30px;">
    <i class="fas fa-user"></i> Mijn Profiel
</h2>

<!-- Profile Header -->
<div style="background: linear-gradient(135deg, #2d5016, #4a7c59); color: white; border-radius: 15px; padding: 30px; margin-bottom: 20px;">
    <div style="display: flex; align-items: center; gap: 20px;">
        <div style="width: 100px; height: 100px; border-radius: 50%; background: #f4d03f; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #2d5016; font-weight: bold;">
            <?php echo strtoupper(substr($user['first_name'] ?? $user['full_name'] ?? 'U', 0, 1)); ?>
        </div>
        <div>
            <h1><?php echo htmlspecialchars($user['first_name'] ? $user['first_name'] . ' ' . $user['last_name'] : $user['full_name']); ?></h1>
            <p style="opacity: 0.9; margin: 5px 0;"><?php echo htmlspecialchars($user['email']); ?></p>
            <p style="opacity: 0.8;"><?php echo ucfirst($user['fishing_experience'] ?? 'Beginner'); ?> Visser</p>
            <button class="action-btn" style="margin-top: 15px;" onclick="editProfile()">
                <i class="fas fa-edit"></i> Profiel Bewerken
            </button>
        </div>
    </div>
</div>

<!-- Profile Stats -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
    <div style="background: white; padding: 20px; border-radius: 10px; text-align: center;">
        <div style="font-size: 2rem; color: #2d5016; font-weight: bold;"><?php echo $stats['catches']; ?></div>
        <div style="color: #666;">Vangsten</div>
    </div>
    <div style="background: white; padding: 20px; border-radius: 10px; text-align: center;">
        <div style="font-size: 2rem; color: #2d5016; font-weight: bold;"><?php echo $stats['friends']; ?></div>
        <div style="color: #666;">Vrienden</div>
    </div>
    <div style="background: white; padding: 20px; border-radius: 10px; text-align: center;">
        <div style="font-size: 2rem; color: #2d5016; font-weight: bold;">0</div>
        <div style="color: #666;">Foto's</div>
    </div>
</div>

<!-- Profile Details -->
<div style="background: white; border-radius: 10px; padding: 20px;">
    <h3 style="color: #2d5016; margin-bottom: 20px;">Profiel Details</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <div>
            <strong style="color: #2d5016;">Locatie:</strong>
            <p style="margin: 5px 0; color: #666;"><?php echo htmlspecialchars($user['city'] ?? 'Niet ingevuld'); ?></p>
        </div>
        <div>
            <strong style="color: #2d5016;">Vis Ervaring:</strong>
            <p style="margin: 5px 0; color: #666;"><?php echo ucfirst($user['fishing_experience'] ?? 'Niet ingevuld'); ?></p>
        </div>
        <div>
            <strong style="color: #2d5016;">Favoriete Vissoorten:</strong>
            <p style="margin: 5px 0; color: #666;"><?php echo htmlspecialchars($user['favorite_fish_species'] ?? 'Niet ingevuld'); ?></p>
        </div>
        <div>
            <strong style="color: #2d5016;">Lid sinds:</strong>
            <p style="margin: 5px 0; color: #666;"><?php echo date('d-m-Y', strtotime($user['created_at'])); ?></p>
        </div>
    </div>
</div>

<script>
function editProfile() {
    alert('Profiel bewerken modal komt hier! ✏️');
}
</script>