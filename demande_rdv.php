<?php
    session_start();


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

    $stmt = $pdo->query("SELECT users_id, username FROM users WHERE username != 'admin'");
    $medecins = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter rendez-vous</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <aside> <!-- Sidebar -->
        <nav>
            <ul>
                <li><a href="index.php">Retour à l'accueil</a></li>
            </ul>
        </nav>
    </aside>
    <main>
        <div class="container">
            <form  action="add_rdv.php" method="POST">

                <label for="users_id">Choisissez votre médecin :</label>
                <select name="users_id" id="users" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($medecins as $user): ?>
                        <option value="<?= htmlspecialchars($user['users_id']) ?>">
                            <?= htmlspecialchars($user['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                

                <label for="name"><strong>Nom :</strong></label>
                <input type="text" id="name" name="name" required>

                <label for="firstname"><strong>Prénom :</strong></label>
                <input type="text" id="firstname" name="firstname" required>

                <label for="tel"><strong>Numéro de téléphone :</strong></label>
                <input type="number" id="tel" name="tel" required>
                
                <label for="numsecu"><strong>Numéro de sécurité sociale :</strong></label>
                <input type="number" id="numsecu" name="numsecu" required>

                <label for="date"><strong>Date du rendez-vous :</strong></label>
                <input type="date" id="date" name="date" required>

                <button class="btn" type="submit" class="login-btn">Ajouter</button>
            </form>
        </div>  
    </main>
</body>
</html>