<?php
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

// Vérifie si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $speciality = htmlspecialchars(trim($_POST['speciality']));

    // Vérifie si l'email existe déjà
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt_check->execute([$email]);
    $emailExists = $stmt_check->fetchColumn();

    if ($emailExists) {
        echo "Un utilisateur avec cet email existe déjà.";
    } else {
        // Hash du mot de passe
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insertion dans la base
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, speciality) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $password_hash, $speciality]);

            echo "Utilisateur ajouté avec succès.";
            header("Location: dashboard_admin.php?success=1");
            exit();
        } catch (PDOException $e) {
            echo "Erreur lors de l'ajout : " . $e->getMessage();
        }
    }
}
?>