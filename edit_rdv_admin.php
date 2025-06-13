<?php
session_start();

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['users_id'])) {
    header("Location: index_doc.php"); // redirection vers la page de connexion si non connecté
    exit();
}

$user_id = $_SESSION['users_id'];

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



$rdv = $pdo->query("SELECT * FROM rdv2 ")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT users_id, username, speciality FROM users WHERE username != 'admin'");
$medecins = $stmt->fetchAll();

$sql_user = "SELECT username FROM users WHERE users_id = :user_id";
$stmt_user = $pdo->prepare($sql_user);
$stmt_user->execute(['user_id' => $user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if ($user && $user['username'] !== 'admin') {
    header("Location: index_doc.php");
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_rdv'])) {
    $rdv_id = intval($_POST['rdv_id']);

    try {
        // DÉBUT DE TRANSACTION
        $pdo->beginTransaction();

        // Suppression du rendez-vous
        $pdo->prepare("DELETE FROM rdv2 WHERE rdv_id = ?")->execute([$rdv_id]);

        // COMMIT
        $pdo->commit();

        //Message de succès
        header("Location: edit_rdv_admin.php?success=3");
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erreur lors de la suppression : " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_rdv'])) {
    $rdv_id = $_POST['rdv_id'];
    $patient_nom = $_POST['name'];
    $patient_prenom = $_POST['firstname'];
    $patient_tel = $_POST['tel'];
    $num_secu = $_POST['numsecu'];
    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['date'];

    // Sécurisation basique
    $rdv_id = intval($rdv_id);

    // Mise à jour en base de données
    $stmt = $pdo->prepare('UPDATE rdv2 SET doctor_id = ?, patient_nom = ?, patient_prenom = ?, patient_tel = ?, num_secu = ?, date = ? WHERE rdv_id = ?');
    $stmt->execute([$doctor_id, $patient_nom, $patient_prenom, $patient_tel, $num_secu, $date, $rdv_id]);

    $stmt = $pdo->query('SELECT * FROM rdv2');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header("Location: edit_rdv_admin.php?success=2");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard administrateur</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body>
    <!-- Message de succès après redirection vers le dashboard si l'user a bien été ajouté-->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <script>
            alert("Le rendez-vous a bien été ajouté ✅");
        </script>
    <?php endif; ?>

    <!-- Message de succès après redirection vers le dashboard si l'user a bien été modifié-->
    <?php if (isset($_GET['success']) && $_GET['success'] == 2): ?>
        <script>
            alert("Le rendez-vous a bien été modifié ✅");
        </script>
    <?php endif; ?>

    <!-- Message de succès après redirection vers le dashboard si l'user a bien été supprimé-->
    <?php if (isset($_GET['success']) && $_GET['success'] == 3): ?>
        <script>
            alert("Le rendez-vous a bien été supprimé ✅");
        </script>
    <?php endif; ?>

    <aside> <!-- Sidebar -->
        <nav>
            <ul>
                <li><a href="ajout_doc.php">Ajouter un médecin</a></li>
                <li><a href="edit_rdv_admin.php" class="inactive">Gérer les rendez-vous</a></li>
                <li><a href="ajout_info.php">Ajouter une information</a></li>
                <li><a href="dashboard_admin.php">Retour à l'accueil</a></li>
                <li><a href="logout.php">Se déconnecter</a></li>
            </ul>
        </nav>
    </aside>
    <main>
        <button onclick="window.location.href = 'ajout_rdv_admin.php'" class='btn' id='centrer'>Ajouter un
            rendez-vous</button>
        <?php foreach ($rdv as $rdv): ?>
            <form method="POST">
                <input type="hidden" name="rdv_id" value="<?= htmlspecialchars($rdv['rdv_id']) ?>">
                <table>
                    <tr>
                        <th>Médecin</th>
                        <td>
                            <select name="doctor_id" id="doctor_id" required>
                                <option value="">-- Sélectionner --</option>
                                <?php foreach ($medecins as $user): ?>
                                    <option value="<?= htmlspecialchars($user['users_id']) ?>"
                                        <?= ($user['users_id'] == $rdv['doctor_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['username']) ?>
                                        (<?= htmlspecialchars($user['speciality']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        </td>
                    </tr>

                    <tr>
                        <th>Nom</th>
                        <td><input type="text" id="name" name="name" required
                                value="<?= htmlspecialchars($rdv['patient_nom']) ?>"></td>
                    </tr>

                    <tr>
                        <th>Prénom</th>
                        <td><input type="text" id="firstname" name="firstname" required
                                value="<?= htmlspecialchars($rdv['patient_prenom']) ?>"></td>
                    </tr>

                    <tr>
                        <th>Numéro de téléphone</th>
                        <td><input type="number" name="tel" value="<?= htmlspecialchars($rdv['patient_tel']) ?>"></td>
                    </tr>

                    <tr>
                        <th>Numéro de sécurité sociale</th>
                        <td><input type="number" name="numsecu" value="<?= htmlspecialchars($rdv['num_secu']) ?>"></td>
                    </tr>

                    <tr>
                        <th>Date</th>

                        <td>
                            <input type="text" id="date" name="date" value="<?= htmlspecialchars($rdv['date']) ?>">
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <button type="submit" onclick="return confirm('Modifier ce rendez-vous ?');">Mettre à
                                jour</button>
                            <button type="submit" name="delete_rdv" onclick="return confirm('Supprimer ce rendez-vous ?');"
                                style="background-color:red;color:white;">Supprimer</button>
                        </td>
                    </tr>
                </table>
            </form>
        <?php endforeach; ?>
    </main>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script src="./js/scriptCalendar.js"></script>
</body>

</html>