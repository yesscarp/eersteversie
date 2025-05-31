<div style="display: flex; justify-content: between; align-items: center; margin-bottom: 30px;">
    <h2 style="color: #2d5016;">
        <i class="fas fa-fish"></i> Mijn Vangsten
    </h2>
    <button class="action-btn" onclick="showAddCatchModal()">
        <i class="fas fa-plus"></i> Nieuwe Vangst
    </button>
</div>

<!-- Filter Options -->
<div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
        <select style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
            <option>Alle vissoorten</option>
            <option>Karper</option>
            <option>Snoek</option>
            <option>Baars</option>
        </select>
        <select style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
            <option>Laatste maand</option>
            <option>Laatste 3 maanden</option>
            <option>Dit jaar</option>
        </select>
        <button style="padding: 8px 15px; background: #2d5016; color: white; border: none; border-radius: 5px;">
            <i class="fas fa-filter"></i> Filter
        </button>
    </div>
</div>

<!-- Catches Grid -->
<div id="catches-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
    <!-- Placeholder for now -->
    <div style="background: white; border: 2px dashed #ddd; border-radius: 10px; padding: 40px; text-align: center; color: #666;">
        <i class="fas fa-fish" style="font-size: 3rem; margin-bottom: 15px; color: #ddd;"></i>
        <h3>Nog geen vangsten</h3>
        <p>Begin met het toevoegen van je eerste vangst!</p>
        <button class="action-btn" style="margin-top: 15px;" onclick="showAddCatchModal()">
            <i class="fas fa-plus"></i> Eerste Vangst
        </button>
    </div>
</div>

<script>
function showAddCatchModal() {
    alert('Nieuwe vangst modal komt hier! 🎣');
}
</script>
