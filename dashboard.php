<?php
    session_start();

    require_once ('./db/connection.php'); 

    
    $host = $dbConn['host'];
    $username_db = $dbConn['user'];
    $password_db = $dbConn['pass'];
    $dbname = $dbConn['name'];


    // Vérifie que l'utilisateur est connecté
    if (!isset($_SESSION['users_id'])) {
        header("Location: index.php"); // redirection vers la page de connexion si non connecté
        exit();
    }

    $user_id = $_SESSION['users_id'];

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

    $sql = "SELECT r.patient_nom, r.patient_prenom, r.patient_tel, r.num_secu, r.date
        FROM rdv2 r
        WHERE r.doctor_id  = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $sql_name = "SELECT username 
                FROM users
                WHERE users_id = :user_id";
    $stmt_name = $pdo->prepare($sql_name);
    $stmt_name->execute(['user_id' => $user_id]);
    $name = $stmt_name->fetch(PDO::FETCH_ASSOC);
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
    <!-- Message de succès après redirection vers l'index si le rendez-vous a bien été ajouté-->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <script>
        alert("Le rendez-vous a bien été ajouté ✅");
    </script>
    <?php endif; ?>
    <aside> <!-- Sidebar -->
        <nav>
            <ul>
                <li><a href="#" class = "inactive">Mes rendez-vous</a></li>
                <li><a href="demande_rdv_doc.php">Ajouter un rendez-vous</a></li>
                <li><a href="logout.php">Se déconnecter</a></li>
            </ul>
        </nav>
        <br>
        <p class = "doctor_name">Connecté en tant que :
            <?= htmlspecialchars($name['username']) ?>
        </p>
    </aside>
    <main>
        <?php if (empty($rendezvous)): ?>
        <p>Vous n'avez aucun rendez-vous.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($rendezvous as $rdv): ?>
                    <li>
                        Nom : <?= htmlspecialchars($rdv['patient_nom']) ?> <br> Prénom : <?= htmlspecialchars($rdv['patient_prenom']) ?> <br> Téléphone : <?= htmlspecialchars($rdv['patient_tel']) ?> <br> Numéro de sécurité sociale : <?= htmlspecialchars($rdv['num_secu']) ?><br> Date : <?= htmlspecialchars($rdv['date']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>