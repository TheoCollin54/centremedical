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

// Récupération des créneaux déjà réservés pour la semaine en cours
$startOfWeek = new DateTime();
$startOfWeek->setISODate((int)$startOfWeek->format("o"), (int)$startOfWeek->format("W"));
$startStr = $startOfWeek->format("Y-m-d");
$endStr = (clone $startOfWeek)->modify('+6 days')->format("Y-m-d 23:59:59");

$stmt = $pdo->prepare("SELECT date FROM rdv2 WHERE date BETWEEN :start AND :end");
$stmt->execute(['start' => $startStr, 'end' => $endStr]);
$rdvDates = $stmt->fetchAll(PDO::FETCH_COLUMN);

$rdvTimestamps = array_map(fn($dt) => (new DateTime($dt))->format("Y-m-d H:i"), $rdvDates);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter rendez-vous</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

    <aside>
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Retour à l'accueil</a></li>
            </ul>
        </nav>
    </aside>

    <main>
        <div class="container">
            <form action="add_rdv.php" method="POST" class="index">

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
                    <div id="week-grid"></div>
                    <div id="selected-info">Aucun créneau sélectionné</div>
                </div>
                <input type="hidden" name="date" id="hidden-date" required>

                <button class="btn" type="submit">Ajouter</button>
            </form>
        </div>
    </main>

    <script>
        const startOfWeekStr = '<?= $startOfWeek->format("Y-m-d") ?>';
        const startOfWeekDate = new Date(startOfWeekStr + 'T00:00:00');
        const unavailableSlots = <?= json_encode($rdvTimestamps) ?>;
        const daysOfWeek = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

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
                if (hour === 12) continue; // Skip lunch hour
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
                header.textContent = `${daysOfWeek[idx]} ${formatDate(date)}`;
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

        createWeekGrid();
    </script>

    <script src="./js/scriptMsg.js"></script>
</body>

</html>