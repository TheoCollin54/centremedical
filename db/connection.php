<?php
$dbConn = array(
    'user' => 'root',
    'pass' => '',
    'name' => 'centremedical',
    'host' => 'localhost'
);

// Connexion à la base de données
$conn = new mysqli($dbConn['host'], $dbConn['user'], $dbConn['pass'], $dbConn['name']);
//Vérification de la connexion
if ($conn->connect_error) {
    die('Erreur de connexion');
}
?>