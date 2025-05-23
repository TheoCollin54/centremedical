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

    $sql_user = "SELECT username FROM users WHERE users_id = :user_id";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->execute(['user_id' => $user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['username'] === 'admin') {
        header("Location: dashboard_admin.php");
        exit();
    }

    $sql_doctor = "SELECT doctor FROM users WHERE users_id = :user_id";
    $stmt_doctor = $pdo->prepare($sql_doctoy);
    $stmt_doctor->exxecute(['user_id]' = > $user_id]);
    $doctor = $stmt_doctor->fetch(PDO::FETCH_ASSOC);

    if ($doctor && $doctor['username'] === 'admin') {
        header("Location: dashboard_doctor.php");
        exit();
    }

    $sql = "SELECT doctor_name, title, date, place FROM rdv WHERE patient_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
