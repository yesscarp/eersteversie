<div style="display: flex; justify-content: between; align-items: center; margin-bottom: 30px;">
    <h2 style="color: #2d5016;">
        <i class="fas fa-map-marker-alt"></i> Vis Locaties
    </h2>
    <button class="action-btn" onclick="addNewLocation()">
        <i class="fas fa-plus"></i> Locatie Toevoegen
    </button>
</div>

<!-- Map Placeholder -->
<div style="background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; height: 400px; display: flex; align-items: center; justify-content: center; border: 2px dashed #ddd;">
    <div style="text-align: center; color: #666;">
        <i class="fas fa-map" style="font-size: 4rem; margin-bottom: 15px; color: #ddd;"></i>
        <h3>Interactieve Kaart</h3>
        <p>Hier komt de kaart met vis locaties</p>
    </div>
</div>

<!-- Locations List -->
<div style="background: white; border-radius: 10px; padding: 20px;">
    <h3 style="color: #2d5016; margin-bottom: 15px;">Mijn Locaties</h3>
    <div style="text-align: center; padding: 40px; color: #666;">
        <i class="fas fa-map-marker-alt" style="font-size: 3rem; margin-bottom: 15px; color: #ddd;"></i>
        <p>Nog geen locaties toegevoegd</p>
        <button class="action-btn" style="margin-top: 15px;" onclick="addNewLocation()">
            <i class="fas fa-plus"></i> Eerste Locatie
        </button>
    </div>
</div>

<script>
function addNewLocation() {
    alert('Nieuwe locatie toevoegen! 📍');
}
</script>