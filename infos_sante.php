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

    $sql = "SELECT title, description
        FROM infos
        WHERE patient_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $infos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations de santé</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <aside> <!-- Sidebar -->
        <nav>
            <ul>
            <li><a href="dashboard.php">Mes rendez-vous</a></li>
            <li>Informations de santé</li>
            </ul>
        </nav>
    </aside>
    <main>
        <?php if (empty($infos)): ?>
        <p>Vous n'avez aucune information disponnible pour le moment.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($infos as $infos): ?>
                    <li>
                        Intitulé : <?= htmlspecialchars($infos['title']) ?> <br> Description : <?= htmlspecialchars($infos['description']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
    </main>
</body>
</html>