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

    if ($doctor && $doctor['doctor'] != 1) {
        header("Location: dashboard.php");
        exit();
    }

    $stmt = $pdo->query("SELECT users_id, username FROM users");
    $utilisateurs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter rendez-vous</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <aside> <!-- Sidebar -->
        <nav>
            <ul>
                <li>Ajouter rendez-vous</a></li>
                <li><a href="ajout_rdv.php">Ajouter un rendez-vous</a></li>
                <li><a href="logout.php">Se déconnecter</a></li>
            </ul>
        </nav>
    </aside>
    <main>
        <div class="container">
            <form  action="add_rdv.php" method="POST">
                <input type="hidden" name="doctor_id" value="<?php echo $_SESSION['users_id']; ?>">


                <label for="users_id">Patient:</label>
                <select name="users_id" id="users" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($utilisateurs as $user): ?>
                        <option value="<?= htmlspecialchars($user['users_id']) ?>">
                            <?= htmlspecialchars($user['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="title"><strong>Intitulé:</strong></label>
                <input type="text" id="title" name="title" required>

                <label for="place"><strong>Lieu:</strong></label>
                <input type="text" id="place" name="place" required>

                <label for="date"><strong>Date:</strong></label>
                <input type="date" id="date" name="date" required>

                <button class="btn" type="submit" class="login-btn">Ajouter</button>
            </form>
        </div>  
    </main>
</body>
</html>