<?php
session_start(); // Pour gérer les sessions utilisateur

// Connexion à la base de données
require_once ('./db/connection.php'); 

    
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

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);

    // Recherche de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Connexion réussie : on enregistre l'utilisateur dans la session
        $_SESSION['users_id'] = $user['users_id'];
        $_SESSION['username'] = $user['username'];
        echo "Connexion réussie ! Bienvenue, " . htmlspecialchars($user['username']);
        header("Location: dashboard.php");
    } else {
        echo "Identifiants incorrects.";
    }
}
?>