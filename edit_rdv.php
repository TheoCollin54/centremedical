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

    // Rediriger si l'utilisateur n'est pas connecté
    if (!isset($_SESSION['users_id'])) {
        header("Location: index_doc.php");
        exit();
    }

    // Si on a reçu un rdv_id depuis le bouton "Modifier"
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rdv_id'])) {
        $rdv_id = $_POST['rdv_id'];

        // Récupérer les infos du rendez-vous
        $stmt = $pdo->prepare("SELECT * FROM rdv2 WHERE rdv_id = :rdv_id");
        $stmt->execute(['rdv_id' => $rdv_id]);
        $rdv = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rdv) {
            echo "Rendez-vous introuvable.";
            exit();
        }

    } else {
        // Sinon on redirige vers le tableau de bord
        header("Location: dashboard.php");
        exit();
}

    

    $message = "";

    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rdv_id'], $_POST['date'])) {
        $rdv_id = intval($_POST['rdv_id']);
        $newDate = $_POST['date'];

        $stmt = $pdo->prepare("UPDATE rdv2 SET date = :date WHERE rdv_id = :rdv_id");
        $stmt->execute(['date' => $newDate, 'rdv_id' => $rdv_id]);

        $message = "✅ Le rendez-vous a été mis à jour.";
    }
?>

<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Modifier le rendez-vous</title>
        <link rel="stylesheet" href="styles.css" />
    </head>
    <body>
    <?php if ($message): ?>
    <script>
        alert("<?= htmlspecialchars($message) ?>");
        window.location.href = "edit_rdv.php?rdv_id=<?= urlencode($rdv['rdv_id']) ?>";
    </script>
<?php endif; ?>


    <aside> <!-- Sidebar -->
        <nav>
            <ul>
                <li><a href="dashboard.php">Mes rendez-vous</a></li>
                <li><a href="demande_rdv_doc.php">Ajouter un rendez-vous</a></li>
                <li><a href="logout.php">Se déconnecter</a></li>
            </ul>
        </nav>
    </aside>

    <main>
        <form action="edit_rdv.php" method="POST">
            <input type="hidden" name="rdv_id" value="<?= htmlspecialchars($rdv['rdv_id']) ?>">
            <table>
                <tr>
                    <th>Nom</th>
                    <td><input type="text" name="nom" value="<?= htmlspecialchars($rdv['patient_nom']) ?>" readonly></td>
                </tr>
                <tr>
                    <th>Prénom</th>
                    <td><input type="text" name="prenom" value="<?= htmlspecialchars($rdv['patient_prenom']) ?>" readonly></td>
                </tr>
                <tr>
                    <th>Téléphone</th>
                    <td><input type="text" name="tel" value="<?= htmlspecialchars($rdv['patient_tel']) ?>" readonly></td>
                </tr>
                <tr>
                    <th>Numéro de sécurité sociale</th>
                    <td><input type="text" name="num_secu" value="<?= htmlspecialchars($rdv['num_secu']) ?>" readonly></td>
                </tr>
                <tr>
                    <th>Date du rendez-vous</th>
                    <td><input type="datetime-local" name="date" value="<?= date('Y-m-d\TH:i', strtotime($rdv['date'])) ?>" required></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <button type="submit" onclick="return confirm('Confirmer la modification du rendez-vous ?');">Enregistrer les modifications</button>
                        <a href="dashboard.php" style="margin-left:10px;">Annuler</a>
                    </td>
                </tr>
            </table>
        </form>
    </main>
</body>

    </html>
