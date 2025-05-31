<div style="display: flex; justify-content: between; align-items: center; margin-bottom: 30px;">
    <h2 style="color: #2d5016;">
        <i class="fas fa-comments"></i> Chat
        <span style="background: #ff4757; color: white; padding: 4px 8px; border-radius: 10px; font-size: 0.7rem; margin-left: 10px;">NIEUW</span>
    </h2>
    <button class="action-btn" onclick="startNewChat()">
        <i class="fas fa-plus"></i> Nieuwe Chat
    </button>
</div>

<!-- Chat Interface -->
<div style="display: grid; grid-template-columns: 300px 1fr; gap: 20px; height: 600px;">
    <!-- Chat List -->
    <div style="background: white; border-radius: 10px; padding: 20px; overflow-y: auto;">
        <h3 style="margin-bottom: 15px; color: #2d5016;">Gesprekken</h3>
        <div style="text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-comments" style="font-size: 2rem; margin-bottom: 10px; color: #ddd;"></i>
            <p>Nog geen gesprekken</p>
        </div>
    </div>
    
    <!-- Chat Area -->
    <div style="background: white; border-radius: 10px; padding: 20px; display: flex; flex-direction: column;">
        <div style="flex: 1; display: flex; align-items: center; justify-content: center; color: #666;">
            <div style="text-align: center;">
                <i class="fas fa-comments" style="font-size: 4rem; margin-bottom: 15px; color: #ddd;"></i>
                <h3>Selecteer een gesprek</h3>
                <p>Kies een gesprek uit de lijst of start een nieuwe chat</p>
            </div>
        </div>
        
        <!-- Message Input -->
        <div style="border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px;">
            <div style="display: flex; gap: 10px;">
                <input type="text" placeholder="Typ een bericht..." style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 20px;">
                <button style="padding: 10px 15px; background: #2d5016; color: white; border: none; border-radius: 20px;">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function startNewChat() {
    alert('Nieuwe chat starten! 💬');
}
</script>