console.log('Valeur users au chargement:', document.getElementById('users')?.value);

function getMonday(d) {
    const date = new Date(d);
    const day = date.getDay();
    const diff = day === 0 ? -6 : 1 - day;
    date.setDate(date.getDate() + diff);
    date.setHours(0, 0, 0, 0);
    return date;
}

const selectedDateStrRaw = document.getElementById('hidden-date').value;
const selectedDate = selectedDateStrRaw ? new Date(selectedDateStrRaw) : new Date();
const today = new Date();

const initialMonday = getMonday(selectedDate);
const currentMonday = getMonday(today);

const diffMs = initialMonday.getTime() - currentMonday.getTime();
let weekOffset = Math.round(diffMs / (7 * 24 * 60 * 60 * 1000));

console.log("Date sélectionnée :", selectedDate.toISOString().slice(0, 16));
console.log("Lundi semaine sélectionnée :", initialMonday.toISOString().slice(0, 10));
console.log("Lundi semaine actuelle :", currentMonday.toISOString().slice(0, 10));
console.log("Décalage weekOffset calculé :", weekOffset);

let startOfWeekDate = new Date(initialMonday); // lundi à afficher au départ

const daysOfWeek = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
let unavailableSlots = [];

async function fetchUnavailableSlots(medecinId) {
    console.log('Fetching slots for medecinId:', medecinId);
    if (!medecinId) return;

    // Recalcul dynamique de la date de début de semaine avec le bon offset
    const startOfWeek = getMonday(new Date());
    startOfWeek.setDate(startOfWeek.getDate() + weekOffset * 7);

    const weekStartStr = startOfWeek.toISOString().split('T')[0];
    const weekEnd = new Date(startOfWeek);
    weekEnd.setDate(weekEnd.getDate() + 6);
    const weekEndStr = weekEnd.toISOString().split('T')[0];

    try {
        const res = await fetch(`?ajax_get_slots=1&medecin_id=${medecinId}&start_date=${weekStartStr}&end_date=${weekEndStr}`);
        unavailableSlots = await res.json();
        console.log("Slots reçus:", unavailableSlots);
        startOfWeekDate = startOfWeek; // <-- met à jour la variable globale pour le reste du script
        createWeekGrid();
        updateWeekLabel();
    } catch (e) {
        console.error(e);
    }
}

function getWeekDates(startDate) {
    const dates = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(startDate);
        d.setDate(d.getDate() + i);
        dates.push(d);
    }
    return dates;
}

function generateTimeSlots() {
    const slots = [];
    for (let hour = 8; hour < 18; hour++) {
        if (hour === 12) continue; // pause midi
        for (let min = 0; min < 60; min += 30) {
            slots.push({ hour, min });
        }
    }
    return slots;
}

function isUnavailable(dateObj, hour, min) {
    const y = dateObj.getFullYear();
    const mo = (dateObj.getMonth() + 1).toString().padStart(2, '0');
    const d = dateObj.getDate().toString().padStart(2, '0');
    const h = hour.toString().padStart(2, '0');
    const mn = min.toString().padStart(2, '0');
    const fullStr = `${y}-${mo}-${d} ${h}:${mn}`;
    return unavailableSlots.includes(fullStr);
}

function isInPast(dateObj, hour, min) {
    const now = new Date();
    const slotDate = new Date(
        dateObj.getFullYear(),
        dateObj.getMonth(),
        dateObj.getDate(),
        hour,
        min,
        0,
        0
    );
    return slotDate < now;
}



function createWeekGrid() {
    const container = document.getElementById('week-grid');
    container.innerHTML = '';
    const weekDates = getWeekDates(startOfWeekDate);
    const slots = generateTimeSlots();

    weekDates.forEach((date, idx) => {
        const dayCol = document.createElement('div');
        dayCol.className = 'day-column';

        const header = document.createElement('div');
        header.className = 'day-header';
        const daysOfWeekFR = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
        const monthsFR = ['janv.', 'févr.', 'mars', 'avril', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

        function formatDateFR(date) {
            const dayName = daysOfWeekFR[date.getDay()];
            const dayNum = date.getDate();
            const monthName = monthsFR[date.getMonth()];
            return `${dayName} ${dayNum} ${monthName}`;
        }
        header.textContent = formatDateFR(date);
        dayCol.appendChild(header);

        slots.forEach(slot => {
            if (slot.hour === 13 && slot.min === 0) {
                const pauseDiv = document.createElement('div');
                pauseDiv.className = 'pause-midi';
                dayCol.appendChild(pauseDiv);
            }

            const slotDiv = document.createElement('div');
            slotDiv.className = 'time-slot';
            const h = slot.hour.toString().padStart(2, '0');
            const m = slot.min.toString().padStart(2, '0');
            slotDiv.textContent = `${h}:${m}`;
            const y = date.getFullYear();
            const mo = (date.getMonth() + 1).toString().padStart(2, '0');
            const d = date.getDate().toString().padStart(2, '0');
            const fullStr = `${y}-${mo}-${d} ${h}:${m}`;

            if (fullStr === selectedDateStrRaw?.slice(0, 16)) {
                slotDiv.classList.add('initial');
            }

            if (isUnavailable(date, slot.hour, slot.min) || isInPast(date, slot.hour, slot.min)) {
                slotDiv.classList.add('unavailable');
            } else {
                slotDiv.addEventListener('click', () => {
                    document.querySelectorAll('.time-slot.selected').forEach(el => {
                        el.classList.remove('selected');
                    });
                    slotDiv.classList.add('selected');
                    document.getElementById('hidden-date').value = fullStr;
                });
            }
            dayCol.appendChild(slotDiv);
        });

        container.appendChild(dayCol);
    });
}

function updateWeekLabel() {
    const weekStart = new Date(startOfWeekDate);
    const weekEnd = new Date(startOfWeekDate);
    weekEnd.setDate(weekStart.getDate() + 6);
    const year = weekStart.getFullYear();

    const options = { day: '2-digit', month: '2-digit' };

    document.getElementById('week-year').textContent = year;
    document.getElementById('current-week-label').textContent = ``;
    document.getElementById('prev-week').disabled = (weekOffset === 0);
}

document.getElementById('next-week').addEventListener('click', () => {
    weekOffset++;
    startOfWeekDate.setDate(startOfWeekDate.getDate() + 7);
    fetchUnavailableSlots(document.getElementById('users').value);
});

document.getElementById('prev-week').addEventListener('click', () => {
    if (weekOffset > 0) {
        weekOffset--;
        startOfWeekDate.setDate(startOfWeekDate.getDate() - 7);
        fetchUnavailableSlots(document.getElementById('users').value);
    }
});

document.getElementById('users').addEventListener('change', function () {
    document.getElementById('hidden-date').value = '';
    fetchUnavailableSlots(this.value);
});

createWeekGrid();
updateWeekLabel();
fetchUnavailableSlots(document.getElementById('users').value);
