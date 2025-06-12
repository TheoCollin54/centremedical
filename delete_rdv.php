<?php
session_start();
require_once('./db/connection.php');

$host = $dbConn['host'];
$username_db = $dbConn['user'];
$password_db = $dbConn['pass'];
$dbname = $dbConn['name'];

if (!isset($_SESSION['users_id'])) {
    header("Location: index_doc.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rdv_id'])) {
    $rdv_id = $_POST['rdv_id'];
    $user_id = $_SESSION['users_id'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username_db, $password_db);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Sécurisation : suppression uniquement si ce rdv appartient au médecin connecté
        $stmt = $pdo->prepare("DELETE FROM rdv2 WHERE rdv_id = :rdv_id AND doctor_id = :doctor_id");
        $stmt->execute(['rdv_id' => $rdv_id, 'doctor_id' => $user_id]);

        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de la suppression : " . $e->getMessage());
    }
} else {
    header("Location: dashboard.php");
    exit();
}
