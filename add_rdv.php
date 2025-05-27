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
    $patient_id = htmlspecialchars(trim($_POST['users_id']));
    $doctor_id = htmlspecialchars(trim($_POST['doctor_id']));
    $title = htmlspecialchars(trim($_POST['title']));
    $date = trim($_POST['date']);
    $place = trim($_POST['place']);

    // Insertion en base de données
    try {
        $stmt = $pdo->prepare("INSERT INTO rdv2 (patient_id, doctor_id, title, date, place) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$patient_id, $doctor_id, $title, $date, $place]);
        echo "Ajout réussie !";
        header("Location: dashboard_doctor.php");
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            echo "Erreur";
        } else {
            echo "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>