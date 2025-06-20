document.addEventListener('DOMContentLoaded', () => {
    // La variable medecins doit être injectée dans la page PHP
    // Exemple dans la page PHP :
    // <script>const medecins = <?= json_encode($medecins, JSON_UNESCAPED_UNICODE) ?>;</script>

    const select = document.getElementById('users');
    const addressDiv = document.getElementById('doctor-address');

    if (!select || !addressDiv) {
        console.error('Element #users or #doctor-address not found in the DOM.');
        return;
    }

    select.addEventListener('change', () => {
        const selectedId = select.value;
        if (!selectedId) {
            addressDiv.textContent = '';
            return;
        }
        const medecin = medecins.find(m => m.users_id == selectedId);
        if (medecin && medecin.adress) {
            addressDiv.innerHTML = `Votre rendez-vous aura lieu au <span class="adresse-medecin">${medecin.adress}</span>`;
        } else {
            addressDiv.textContent = 'Adresse non disponible.';
        }
    });
});