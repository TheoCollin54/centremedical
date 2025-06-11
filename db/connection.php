<?php
    $dbConn = array(
        'host' => 'sql113.infinityfree.com',
        'pass' => 'l9tQBbp4zG',
        'name' => 'if0_39171546_centremedical',
        'user' => 'if0_39171546'
    );
    
    // Connexion à la base de données
    $conn = new mysqli($dbConn['host'], $dbConn['user'], $dbConn['pass'], $dbConn['name']);
    //Vérification de la connexion
    if ($conn->connect_error) {
        die('Erreur de connexion');
    }    
?>