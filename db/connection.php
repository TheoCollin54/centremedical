<?php
$dbConn = array(
    'user' => 'if0_39263420',
    'pass' => 'pbpUyoaNxbH',
    'name' => 'if0_39263420_centremedical',
    'host' => 'sql309.infinityfree.com'
);

// Connexion à la base de données
$conn = new mysqli($dbConn['host'], $dbConn['user'], $dbConn['pass'], $dbConn['name']);
//Vérification de la connexion
if ($conn->connect_error) {
    die('Erreur de connexion');
}
?>