// scriptCalendarDoc.js

// Variables globales
let unavailableSlots = [];
let currentWeekStart = null; // Date objet du lundi courant sélectionné
let selectedDate = null;     // Date-heure du créneau choisi

// Fonction utilitaire pour obtenir le lundi de la semaine d'une date donnée
function getMonday(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = (day === 0 ? -6 : 1) - day; // Si dimanche (0), revient à lundi précédent
    d.setDate(d.getDate() + diff);
    d.setHours(0, 0, 0, 0);
    return d;
}

// Formatage date en 'YYYY-MM-DD HH:mm'
function formatDate(date) {
    return date.toISOString().slice(0, 16).replace('T', ' ');
}

// Met à jour le label semaine affichée (ex: du 12/06 au 18/06)
function updateWeekLabel() {
    const options = { day: '2-digit', month: '2-digit' };
    const startStr = currentWeekStart.toLocaleDateString('fr-FR', options);
    const endDate = new Date(currentWeekStart);
    endDate.setDate(endDate.getDate() + 6);
    const endStr = endDate.toLocaleDateString('fr-FR', options);

    const labelElem = document.getElementById('current-week-label');
    if (labelElem) {
        labelElem.textContent = `Semaine du ${startStr} au ${endStr}`;
    }
}

// Vérifie si le créneau est dans la liste des indisponibles
function isUnavailable(dateStr) {
    return unavailableSlots.includes(dateStr);
}

// Crée la grille des jours et créneaux horaires (8h-18h, 15 min, pause 12-13h)
function createWeekGrid() {
    const grid = document.getElementById('week-grid');
    if (!grid) return;

    grid.innerHTML = ''; // Vide la grille avant de recréer

    // Pour chaque jour de la semaine (lundi à dimanche)
    for (let dayOffset = 0; dayOffset < 7; dayOffset++) {
        const dayCol = document.createElement('div');
        dayCol.style.display = 'flex';
        dayCol.style.flexDirection = 'column';
        dayCol.style.gap = '5px';

        // Date du jour
        const dayDate = new Date(currentWeekStart);
        dayDate.setDate(dayDate.getDate() + dayOffset);

        // Label jour (ex: Lun 12/06)
        const dayLabel = document.createElement('div');
        dayLabel.textContent = dayDate.toLocaleDateString('fr-FR', { weekday: 'short', day: '2-digit', month: '2-digit' });
        dayLabel.style.fontWeight = 'bold';
        dayLabel.style.textAlign = 'center';
        dayCol.appendChild(dayLabel);

        // Créneaux horaires 8h-18h, 15min, hors 12h-13h
        for (let hour = 8; hour < 18; hour++) {
            if (hour === 12) continue; // pause midi

            for (let min of [0, 15, 30, 45]) {
                const slotDate = new Date(dayDate);
                slotDate.setHours(hour, min, 0, 0);

                const slotStr = formatDate(slotDate);

                const slotBtn = document.createElement('button');
                slotBtn.type = 'button';
                slotBtn.textContent = `${hour.toString().padStart(2, '0')}:${min.toString().padStart(2, '0')}`;
                slotBtn.style.marginBottom = '2px';

                if (isUnavailable(slotStr)) {
                    slotBtn.disabled = true;
                    slotBtn.style.backgroundColor = '#ddd';
                    slotBtn.title = 'Créneau réservé';
                } else {
                    slotBtn.onclick = () => {
                        selectedDate = slotStr;
                        updateSelectedInfo();
                    };
                }

                dayCol.appendChild(slotBtn);
            }
        }
        grid.appendChild(dayCol);
    }
}

// Met à jour l'affichage du créneau sélectionné et la valeur du champ caché
function updateSelectedInfo() {
    const info = document.getElementById('selected-info');
    const hiddenDateInput = document.getElementById('hidden-date');

    if (!info || !hiddenDateInput) return;

    if (selectedDate) {
        info.textContent = `Créneau sélectionné : ${selectedDate}`;
        hiddenDateInput.value = selectedDate;
    } else {
        info.textContent = 'Aucun créneau sélectionné';
        hiddenDateInput.value = '';
    }
}

// Charge les créneaux réservés pour la semaine affichée depuis le serveur via fetch
function loadReservedSlots() {
    const startStr = currentWeekStart.toISOString().slice(0, 10);
    const endDate = new Date(currentWeekStart);
    endDate.setDate(endDate.getDate() + 6);
    const endStr = endDate.toISOString().slice(0, 10);

    // Ajoute un paramètre supplémentaire doctor_id si besoin (exemple user_id = doctor_id)
    // Si doctor_id est dans une variable globale côté JS, sinon adapter ici
    const url = `demande_rdv_doc.php?action=get_slots&start_date=${startStr}&end_date=${endStr}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            unavailableSlots = Array.isArray(data) ? data : [];
            createWeekGrid();
            updateWeekLabel();
            updateSelectedInfo();
        })
        .catch(err => {
            console.error('Erreur chargement créneaux:', err);
            unavailableSlots = [];
            createWeekGrid();
            updateWeekLabel();
            updateSelectedInfo();
        });
}

// Initialisation au chargement de la page
function initCalendar() {
    currentWeekStart = getMonday(new Date());
    selectedDate = null;
    loadReservedSlots();

    // Boutons semaine précédente / suivante
    const btnPrev = document.getElementById('prev-week');
    const btnNext = document.getElementById('next-week');

    if (btnPrev) {
        btnPrev.onclick = () => {
            currentWeekStart.setDate(currentWeekStart.getDate() - 7);
            selectedDate = null;
            loadReservedSlots();
        };
    }

    if (btnNext) {
        btnNext.onclick = () => {
            currentWeekStart.setDate(currentWeekStart.getDate() + 7);
            selectedDate = null;
            loadReservedSlots();
        };
    }
}

// Exporte l'init pour pouvoir l'appeler dans la page HTML
window.initCalendar = initCalendar;
