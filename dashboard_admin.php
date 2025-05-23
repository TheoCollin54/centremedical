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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['users_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];

        // Mise à jour des données
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE users_id = ?");
        $stmt->execute([$username, $email, $id]);
    }

    $users = $pdo->query("SELECT users_id, username, email, doctor FROM users")->fetchAll(PDO::FETCH_ASSOC);

    $sql_user = "SELECT username FROM users WHERE users_id = :user_id";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->execute(['user_id' => $user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['username'] !== 'admin') {
        header("Location: dashboard.php");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard administrateur</title>
</head>
<body>
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
                    <th>Docteur</th>
                    <td><input type="tinyint" name="doctor" value="<?= htmlspecialchars($user['doctor']) ?>" placeholder="1 = médecin, 0 = patient"></td>
                </tr>
                <tr>
                    <td colspan="2"><button type="submit">Mettre à jour</button></td>
                </tr>
            </table>
        </form>
    <?php endforeach; ?>
</body>
</html>