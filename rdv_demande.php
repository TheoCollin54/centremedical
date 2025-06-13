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
    $raison = htmlspecialchars(trim($_POST['raison']));
    $description = htmlspecialchars(trim($_POST['description']));
    $num_secu = htmlspecialchars(trim($_POST['num_secu']));
    $date = htmlspecialchars(trim($_POST['date']));

    // Insertion en base de données
    try {
        $stmt = $pdo->prepare("INSERT INTO demande_rdv (patient_id, doctor_id, raison, description, num_secu, date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$patient_id, $doctor_id, $raison, $description, $num_secu, $date]);
        echo "Demande réussie !";
        header("Location: demande_rdv.php");
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            echo "Erreur : La demande de rendez-vous a échouée. Veuillez réessayer ultérieurement.";
        } else {
            echo "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>