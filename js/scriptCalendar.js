console.log('scriptCalendar chargé');
console.log('Valeur users au chargement:', document.getElementById('users')?.value);

let weekOffset = 0;
const today = new Date();
let startOfWeekDate = new Date();
startOfWeekDate.setDate(startOfWeekDate.getDate() - startOfWeekDate.getDay() + 1);

const daysOfWeek = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
let unavailableSlots = [];

async function fetchUnavailableSlots(medecinId) {
    console.log('Fetching slots for medecinId:', medecinId);
    if (!medecinId) return;

    const weekStartStr = startOfWeekDate.toISOString().split('T')[0];
    const weekEnd = new Date(startOfWeekDate);
    weekEnd.setDate(weekEnd.getDate() + 6);
    const weekEndStr = weekEnd.toISOString().split('T')[0];

    try {
        const res = await fetch(`?ajax_get_slots=1&medecin_id=${medecinId}&start_date=${weekStartStr}&end_date=${weekEndStr}`);
        unavailableSlots = await res.json();
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
        if (hour === 12) continue;
        for (let min = 0; min < 60; min += 15) {
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
        header.textContent = `${daysOfWeek[idx]} ${date.getDate()}/${date.getMonth() + 1}`;
        dayCol.appendChild(header);

        slots.forEach(slot => {
            const slotDiv = document.createElement('div');
            slotDiv.className = 'time-slot';
            const h = slot.hour.toString().padStart(2, '0');
            const m = slot.min.toString().padStart(2, '0');
            slotDiv.textContent = `${h}:${m}`;

            if (isUnavailable(date, slot.hour, slot.min)) {
                slotDiv.classList.add('unavailable');
            } else {
                slotDiv.addEventListener('click', () => {
                    const y = date.getFullYear();
                    const mo = (date.getMonth() + 1).toString().padStart(2, '0');
                    const d = date.getDate().toString().padStart(2, '0');
                    const fullStr = `${y}-${mo}-${d} ${h}:${m}`;
                    document.getElementById('hidden-date').value = fullStr;
                    document.getElementById('selected-info').textContent = `Créneau sélectionné : ${fullStr}`;
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
    const options = { day: '2-digit', month: '2-digit' };
    document.getElementById('current-week-label').textContent =
        `Semaine du ${weekStart.toLocaleDateString('fr-FR', options)} au ${weekEnd.toLocaleDateString('fr-FR', options)}`;
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
    document.getElementById('selected-info').textContent = 'Aucun créneau sélectionné';
    fetchUnavailableSlots(this.value);
});

createWeekGrid();
updateWeekLabel();
fetchUnavailableSlots(document.getElementById('users').value);