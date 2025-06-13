<?php
require_once('./db/connection.php');
session_start();

// Connexion à la base de données
$host = $dbConn['host'];
$username_db = $dbConn['user'];
$password_db = $dbConn['pass'];
$dbname = $dbConn['name'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer toutes les informations avec pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $itemsPerPage = 5;
    $offset = ($page - 1) * $itemsPerPage;

    // Obtenir le nombre total pour la pagination
    $countStmt = $pdo->query("SELECT COUNT(*) FROM infos");
    $totalItems = $countStmt->fetchColumn();
    $totalPages = ceil($totalItems / $itemsPerPage);

    // Obtenir les résultats filtrés si la recherche est active
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $query = "SELECT * FROM infos WHERE 1=1";
    $params = [];

    if ($search) {
        $query .= " AND (title LIKE ? OR description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $query .= " ORDER BY info_id DESC LIMIT ? OFFSET ?";
    $params[] = $itemsPerPage;
    $params[] = $offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $infos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Erreur de connexion : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations - Centre Médical</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <aside>
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Retour à l'accueil</a></li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <li><a href="ajout_info.php"><i class="fas fa-plus"></i> Ajouter une information</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>

    <main>
        <div class="info-container">
            <div class="filters">
                <form method="GET" action="" id="filterForm">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Rechercher une information..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                </form>
            </div>

            <?php if (isset($error)): ?>
                <div class="notification error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($infos)): ?>
                <div class="info-grid">
                    <?php foreach ($infos as $info): ?>
                        <div class="info-card">
                            <h2><?= htmlspecialchars($info['title']) ?></h2>
                            <div class="description">
                                <?= nl2br(htmlspecialchars($info['description'])) ?>
                            </div>

                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <div class="admin-actions">
                                    <button class="edit" onclick="editInfo(<?= $info['info_id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="delete" data-confirm="Êtes-vous sûr de vouloir supprimer cette information ?"
                                            onclick="deleteInfo(<?= $info['info_id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">
                                <i class="fas fa-chevron-left"></i> Précédent
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
                               class="<?= $i === $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">
                                Suivant <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-info-circle fa-3x"></i>
                    <p>Aucune information disponible.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="./js/main.js"></script>
    <script>
        // Fonction pour éditer une information
        function editInfo(id) {
            window.location.href = `edit_info.php?id=${id}`;
        }

        // Fonction pour supprimer une information
        function deleteInfo(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette information ?')) {
                fetch(`delete_info.php?id=${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Information supprimée avec succès', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showNotification(data.message || 'Erreur lors de la suppression', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Erreur lors de la suppression', 'error');
                });
            }
        }

        // Amélioration de la recherche avec debounce
        let searchTimeout;
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    document.getElementById('filterForm').submit();
                }, 500);
            });
        }
    </script>
</body>
</html>