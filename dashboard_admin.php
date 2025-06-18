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



$users = $pdo->query("SELECT * FROM users ")->fetchAll(PDO::FETCH_ASSOC);

$sql_user = "SELECT username FROM users WHERE users_id = :user_id";
$stmt_user = $pdo->prepare($sql_user);
$stmt_user->execute(['user_id' => $user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if ($user && $user['username'] !== 'admin') {
    header("Location: index_doc.php");
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $users_id = intval($_POST['users_id']);

    try {
        // DÉBUT DE TRANSACTION
        $pdo->beginTransaction();

        // Suppression dans les autres tables si nécessaire
        $pdo->prepare("DELETE FROM rdv2 WHERE doctor_id = ?")->execute([$users_id]);

        // Suppression de l'utilisateur
        $pdo->prepare("DELETE FROM users WHERE users_id = ?")->execute([$users_id]);

        // COMMIT
        $pdo->commit();

        //Message de succès
        header("Location: dashboard_admin.php?success=3");
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erreur lors de la suppression : " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_user'])) {
    $users_id = $_POST['users_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $speciality = $_POST['speciality'];

    // Sécurisation basique
    $users_id = intval($users_id);

    // Mise à jour en base de données
    $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, speciality = ? WHERE users_id = ?');
    $stmt->execute([$username, $email, $speciality, $users_id]);

    $stmt = $pdo->query('SELECT * FROM users');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header("Location: dashboard_admin.php?success=2");
}

$message = "";
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 1:
            $message = "L'utilisateur a bien été ajouté ✅";
            break;
        case 2:
            $message = "L'utilisateur a bien été modifié ✅";
            break;
        case 3:
            $message = "L'utilisateur a bien été supprimé ✅";
            break;
        case 4:
            $message = "L'information a bien été ajoutée ✅";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard administrateur</title>
    <link rel="stylesheet" href="./css/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body data-message="<?= htmlspecialchars($message) ?>">
    <aside> <!-- Sidebar -->
        <nav>
            <ul>
                <li><a href="#" class="inactive"><i class="fas fa-home"></i> Accueil</a></li>
                <li><a href="ajout_doc.php" ><i class="fa-solid fa-user-doctor"></i> Ajouter un médecin</a></li>
                <!-- <li><a href="edit_rdv_admin.php">Gérer les rendez-vous</a></li> -->
                <li><a href="ajout_info.php"><i class="fa-solid fa-circle-info"></i> Ajouter une information</a></li>
                <li><a href="logout.php"><i class="fas fa-right-from-bracket"></i>Déconnexion</a></li>
            </ul>
        </nav>
    </aside>
    <main>
        <?php foreach ($users as $user): ?>
            <form method="POST">
                <input type="hidden" name="users_id" value="<?= htmlspecialchars($user['users_id']) ?>">
                <table>
                    <tr>
                        <th>Username</th>
                        <td><input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>"></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"></td>
                    </tr>

                    <tr>
                        <th>Spécialité</th>
                        <td><input type="speciality" name="speciality" value="<?= htmlspecialchars($user['speciality']) ?>">
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <button type="submit" onclick="return confirm('Modifier cet utilisateur ?');">Mettre à
                                jour</button>
                            <button type="submit" name="delete_user"
                                onclick="return confirm('Supprimer cet utilisateur ?');"
                                style="background-color:red;color:white;">Supprimer</button>
                        </td>
                    </tr>
                </table>
            </form>
        <?php endforeach; ?>
    </main>
    <script src="./js/scriptMsg.js"></script>
</body>

</html>