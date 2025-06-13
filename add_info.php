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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars(trim($_POST['title']));
    $description = htmlspecialchars(trim($_POST['description']));
}

// Insertion en base de données
try {
    $stmt = $pdo->prepare("INSERT INTO infos (title, description) VALUES (?, ?)");
    $stmt->execute([$title, $description]);

    header("Location: dashboard_admin.php?success=4");
    exit();
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        echo "❌ Erreur : entrée en double (conflit de clé unique).";
    } else {
        echo "❌ Erreur lors de l'insertion : " . $e->getMessage();
    }
}

?>