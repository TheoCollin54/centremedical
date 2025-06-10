<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'centremedical';
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
    
    $patient_nom = htmlspecialchars(trim($_POST['name']));
    $patient_prenom = htmlspecialchars(trim($_POST['firstname']));
    $patient_tel = htmlspecialchars(trim($_POST['tel']));
    $num_secu = htmlspecialchars(trim($_POST['numsecu']));
    $doctor_id = htmlspecialchars(trim($_POST['users_id']));
    $date = trim($_POST['date']);

    // Insertion en base de données
    try {
        $stmt = $pdo->prepare("INSERT INTO rdv2 (rdv_id, patient_nom, patient_prenom, patient_tel, num_secu, doctor_id, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$rdv_id, $patient_nom, $patient_prenom, $patient_tel, $num_secu, $doctor_id, $date]);
        echo "Ajout réussi !";
        header("Location: index.php?success=1");
        exit();
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            echo "Erreur";
        } else {
            echo "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>