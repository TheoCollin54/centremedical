<?php
session_start();
require_once('./db/connection.php');

$host = $dbConn['host'];
$username_db = $dbConn['user'];
$password_db = $dbConn['pass'];
$dbname = $dbConn['name'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Requête AJAX dynamique avec dates de semaine
if (isset($_GET['ajax_get_slots']) && isset($_GET['medecin_id'])) {
    $medecinId = intval($_GET['medecin_id']);
    $start = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
    $end = isset($_GET['end_date']) ? $_GET['end_date'] . ' 23:59:59' : date('Y-m-d 23:59:59');

    $stmt = $pdo->prepare("SELECT date FROM rdv2 WHERE doctor_id = :medecinId AND date BETWEEN :start AND :end");
    $stmt->execute(['medecinId' => $medecinId, 'start' => $start, 'end' => $end]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $slots = array_map(fn($d) => (new DateTime($d))->format("Y-m-d H:i"), $dates);

    header('Content-Type: application/json');
    echo json_encode($slots);
    exit;
}

$stmt = $pdo->query("SELECT users_id, username, speciality FROM users WHERE username != 'admin'");
$medecins = $stmt->fetchAll();

$message = "";
if (isset($_GET['fail'])) {
    if ($_GET['fail'] == 1) {
        $message = "Le numéro de téléphone n'est pas valide ❌";
    } elseif ($_GET['fail'] == 2) {
        $message = "Le numéro de sécurité sociale n'est pas valide ❌";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter rendez-vous</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        #calendar-container {
            margin-top: 1em;
        }
        #week-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            margin-bottom: 1em;
        }
        .day-column {
            border: 1px solid #ccc;
            padding: 5px;
        }
        .day-header {
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
            background: #eee;
        }
        .time-slot {
            padding: 4px;
            margin: 2px 0;
            text-align: center;
            background: #d0f0d0;
            cursor: pointer;
            border-radius: 4px;
        }
        .time-slot:hover {
            background: #b0e0b0;
        }
        .time-slot.unavailable {
            background: #f8d7da;
            color: #aaa;
            pointer-events: none;
            cursor: not-allowed;
        }
        #selected-info {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body data-message="<?= htmlspecialchars($message) ?>">
<main>
    <div class="container">
        <form action="add_rdv.php" method="POST" class="index" style="scale:0.7">
            <label for="users_id">Choisissez votre médecin :</label>
            <select name="users_id" id="users" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($medecins as $user): ?>
                    <option value="<?= htmlspecialchars($user['users_id']) ?>">
                        <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['speciality']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="name"><strong>NOM :</strong></label>
            <input type="text" id="name" name="name" required>
            <label for="firstname"><strong>PRÉNOM :</strong></label>
            <input type="text" id="firstname" name="firstname" required>
            <label for="tel"><strong>TÉLÉPHONE :</strong></label>
            <input type="number" id="tel" name="tel" maxlength="10" minlength="10" required>
            <label for="numsecu"><strong>NUMÉRO DE SÉCURITÉ SOCIALE :</strong></label>
            <input type="number" id="numsecu" name="numsecu" maxlength="15" minlength="15" required>
            <label for="date"><strong>DATE ET HEURE DU RENDEZ-VOUS :</strong></label>
            <div id="calendar-container">
                <div>
                    <button type="button" id="prev-week">Semaine précédente</button>
                    <span id="current-week-label"></span>
                    <button type="button" id="next-week">Semaine suivante</button>
                </div>
                <div id="week-grid"></div>
                <div id="selected-info">Aucun créneau sélectionné</div>
            </div>
            <input type="hidden" name="date" id="hidden-date" required>
            <button class="btn" type="submit">Ajouter</button>
        </form>
    </div>
</main>

<script>
    let weekOffset = 0;
    const today = new Date();
    let startOfWeekDate = new Date();
    startOfWeekDate.setDate(startOfWeekDate.getDate() - startOfWeekDate.getDay() + 1);

    const daysOfWeek = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    let unavailableSlots = [];

    async function fetchUnavailableSlots(medecinId) {
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
</script>
</body>
</html>
