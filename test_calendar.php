<?php
// Connexion à la base de données
$pdo = new PDO("mysql:host=127.0.0.1;dbname=centremedical;charset=utf8mb4", "root", "");

// Récupérer le lundi de la semaine actuelle
$startOfWeek = new DateTime();
$startOfWeek->setISODate((int) $startOfWeek->format("o"), (int) $startOfWeek->format("W"));
$startStr = $startOfWeek->format("Y-m-d");
$endOfWeek = clone $startOfWeek;
$endOfWeek->modify('+6 days');
$endStr = $endOfWeek->format("Y-m-d 23:59:59");

// Requête SQL : tous les rendez-vous de la semaine
$stmt = $pdo->prepare("SELECT date FROM rdv2 WHERE date BETWEEN :start AND :end");
$stmt->execute(['start' => $startStr, 'end' => $endStr]);
$rdvDates = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Préparation pour JavaScript
$rdvTimestamps = array_map(function ($dt) {
    return (new DateTime($dt))->format("Y-m-d H:i");
}, $rdvDates);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Calendrier hebdomadaire Flatpickr 15 min</title>
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        #calendar-container {
            max-width: 1100px;
            margin: 0 auto;
        }

        #week-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #ccc;
        }

        .day-column {
            background: #fff;
            display: flex;
            flex-direction: column;
        }

        .day-header {
            background: #007bff;
            color: white;
            text-align: center;
            padding: 6px 4px;
            font-weight: bold;
        }

        .time-slot {
            border-top: 1px solid #ddd;
            padding: 5px;
            font-size: 0.9em;
            cursor: pointer;
            user-select: none;
            text-align: center;
            color: blue;
            background-color: #e6f0ff;
        }

        .time-slot.unavailable {
            color: gray;
            background-color: #f0f0f0;
            cursor: default;
            pointer-events: none;
        }

        .time-slot:hover:not(.unavailable) {
            background-color: #c2dbff;
        }

        #selected-info {
            margin-top: 20px;
            font-size: 1.1em;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h2>Calendrier hebdomadaire (15 min) avec Flatpickr</h2>
    <div id="calendar-container">
        <div id="week-grid"></div>
        <div id="selected-info">Aucun créneau sélectionné</div>
    </div>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        // Variables PHP vers JS
        const startOfWeekStr = '<?php echo $startOfWeek->format("Y-m-d"); ?>';
        const startOfWeekDate = new Date(startOfWeekStr + 'T00:00:00');
        const unavailableSlots = <?php echo json_encode($rdvTimestamps); ?>;
        const daysOfWeek = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

        // Crée un tableau de dates pour la semaine
        function getWeekDates(startDate) {
            const dates = [];
            for (let i = 0; i < 7; i++) {
                const d = new Date(startDate);
                d.setDate(d.getDate() + i);
                dates.push(d);
            }
            return dates;
        }

        function formatDate(d) {
            return `${d.getDate()}/${d.getMonth() + 1}`;
        }

        function generateTimeSlots() {
            const slots = [];
            for (let hour = 8; hour < 18; hour++) {
                for (let min = 0; min < 60; min += 15) {
                    slots.push({ hour: hour, min: min });
                }
            }
            return slots;
        }

        // Vérifie si un créneau est déjà pris dans la base
        function isUnavailable(dateObj, hour, min) {
            const y = dateObj.getFullYear();
            const mo = (dateObj.getMonth() + 1).toString().padStart(2, '0');
            const d = dateObj.getDate().toString().padStart(2, '0');
            const h = hour.toString().padStart(2, '0');
            const mn = min.toString().padStart(2, '0');
            const fullStr = `${y}-${mo}-${d} ${h}:${mn}`;
            return unavailableSlots.includes(fullStr);
        }

        // Crée la grille du calendrier
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
                header.textContent = `${daysOfWeek[idx]} ${formatDate(date)}`;
                dayCol.appendChild(header);

                slots.forEach(slot => {
                    const slotDiv = document.createElement('div');
                    slotDiv.className = 'time-slot';

                    const h = slot.hour.toString().padStart(2, '0');
                    const m = slot.min.toString().padStart(2, '0');
                    const timeStr = `${h}:${m}`;
                    slotDiv.textContent = timeStr;

                    // Rendre tous les créneaux entre 12h00 et 12h59 non cliquables
                    const isLunchHour = slot.hour === 12;

                    if (isUnavailable(date, slot.hour, slot.min) || isLunchHour) {
                        slotDiv.classList.add('unavailable');
                    } else {
                        slotDiv.addEventListener('click', () => {
                            onSelectSlot(date, slot.hour, slot.min);
                        });
                    }

                    dayCol.appendChild(slotDiv);
                });

                container.appendChild(dayCol);
            });
        }

        function onSelectSlot(date, hour, min) {
            const selectedInfo = document.getElementById('selected-info');
            const y = date.getFullYear();
            const mo = (date.getMonth() + 1).toString().padStart(2, '0');
            const d = date.getDate().toString().padStart(2, '0');
            const h = hour.toString().padStart(2, '0');
            const mn = min.toString().padStart(2, '0');
            selectedInfo.textContent = `Créneau sélectionné : ${y}-${mo}-${d} ${h}:${mn}`;
        }

        // Initialisation
        createWeekGrid();
    </script>
</body>

</html>