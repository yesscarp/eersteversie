<div style="display: flex; justify-content: between; align-items: center; margin-bottom: 30px;">
    <h2 style="color: #2d5016;">
        <i class="fas fa-users"></i> Vrienden
    </h2>
    <button class="action-btn" onclick="showFindFriendsModal()">
        <i class="fas fa-user-plus"></i> Zoek Vrienden
    </button>
</div>

<!-- Live Status -->
<div style="background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #4caf50;">
    <h3 style="color: #2d5016; margin-bottom: 15px;">Live Status</h3>
    <div style="display: flex; align-items: center; gap: 10px; color: #666;">
        <span style="width: 10px; height: 10px; background: #4caf50; border-radius: 50%; display: inline-block;"></span>
        0 van 0 leden online
    </div>
    <div style="display: flex; align-items: center; gap: 10px; color: #666; margin-top: 10px;">
        <span style="width: 10px; height: 10px; background: #ff9800; border-radius: 50%; display: inline-block;"></span>
        0 van 5 vrienden online
    </div>
</div>

<!-- Active Friends -->
<div style="background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px;">
    <h3 style="color: #2d5016; margin-bottom: 15px;">Actieve Vrienden</h3>
    <div style="text-align: center; padding: 40px; color: #666;">
        <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 15px; color: #ddd;"></i>
        <p>Geen actieve vrienden gevonden</p>
        <button class="action-btn" style="margin-top: 15px;" onclick="showFindFriendsModal()">
            <i class="fas fa-search"></i> Vrienden Zoeken
        </button>
    </div>
</div>

<script>
function showFindFriendsModal() {
    alert('Vrienden zoeken modal komt hier! 👥');
}
</script>