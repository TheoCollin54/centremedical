<?php
    session_start();

    // Vérifie que l'utilisateur est connecté
    if (!isset($_SESSION['users_id'])) {
        header("Location: index.php"); // redirection vers la page de connexion si non connecté
        exit();
    }

    $user_id = $_SESSION['users_id'];

    $host = 'localhost';
    $dbname = 'centre-medical';
    $username_db = 'root';
    $password_db = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username_db, $password_db);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }

    $sql_user = "SELECT username FROM users WHERE users_id = :user_id";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->execute(['user_id' => $user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['username'] === 'admin') {
        header("Location: dashboard_admin.php");
        exit();
    }

    $sql_doctor = "SELECT doctor FROM users WHERE users_id = :user_id";
    $stmt_doctor = $pdo->prepare($sql_doctor);
    $stmt_doctor->execute(['user_id' => $user_id]);
    $doctor = $stmt_doctor->fetch(PDO::FETCH_ASSOC);

    if ($doctor && $doctor['doctor'] === 1) {
        header("Location: dashboard_doctor.php");
        exit();
    }

    $sql = "SELECT u.username AS doctor_name, r.title, r.date, r.place
        FROM rdv2 r
        JOIN users u ON r.doctor_id = u.users_id
        WHERE r.patient_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes rendez-vous</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <aside> <!-- Sidebar -->
        <nav>
            <ul>
                <li>Mes rendez-vous</a></li>
                <li><a href="infos_sante.php">Informations de santé</a></li>
                <li><a href="logout.php">Se déconnecter</a></li>
            </ul>
        </nav>
    </aside>
    <main>
        <?php if (empty($rendezvous)): ?>
        <p>Vous n'avez aucun rendez-vous.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($rendezvous as $rdv): ?>
                    <li>
                        Docteur : <?= htmlspecialchars($rdv['doctor_name']) ?> <br> Intitulé : <?= htmlspecialchars($rdv['title']) ?> <br> Date : <?= htmlspecialchars($rdv['date']) ?> <br> Lieu : <?= htmlspecialchars($rdv['place']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>