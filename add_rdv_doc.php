<?php
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

// Vérification si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Récupération et sécurisation des données
    
    $patient_nom = htmlspecialchars(trim($_POST['name']));
    $patient_prenom = htmlspecialchars(trim($_POST['firstname']));
    $patient_tel = htmlspecialchars(trim($_POST['tel']));
    $num_secu = htmlspecialchars(trim($_POST['numsecu']));
    $doctor_id = htmlspecialchars(trim($_POST['doctor_id']));
    $date = trim($_POST['date']);

    
    if (!preg_match('/^\d{10}$/', $patient_tel) ||empty($patient_tel)) {
        header("Location: demande_rdv_doc.php?fail=1");
        exit();
    }


    
    if (!preg_match('/^\d{15}$/', $num_secu) || empty($num_secu)) {
        header("Location: demande_rdv_doc.php?fail=2");
        exit();
    }

    // Insertion en base de données
    try {
        $stmt = $pdo->prepare("INSERT INTO rdv2 (rdv_id, patient_nom, patient_prenom, patient_tel, num_secu, doctor_id, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$rdv_id, $patient_nom, $patient_prenom, $patient_tel, $num_secu, $doctor_id, $date]);
        echo "Ajout réussi !";
        header("Location: dashboard.php?success=1");
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