<?php
// Connexion à la base de données
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

// Vérification si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération et sécurisation des données
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);

    // Hash du mot de passe
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insertion en base de données
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $passwordHash]);
        echo "Inscription réussie !";
        header("Location: index.php");
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            echo "Erreur : Ce nom d'utilisateur ou cet email est déjà utilisé.";
        } else {
            echo "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>