<?php
// index.php — Page d'accueil avec recherche, filtre par genre et affichage du créateur
session_start();
include 'db.php';

// Récupérer la recherche et le filtre depuis l'URL
$search = trim($_GET['search'] ?? '');
$genre  = trim($_GET['genre']  ?? '');

// Construction de la requête SQL avec jointure pour récupérer le login du créateur
$sql    = "SELECT games.*, utilisateurs.login AS createur FROM games JOIN utilisateurs ON games.user_id = utilisateurs.id WHERE 1=1";
$params = [];

// Filtre par titre (Règle 5)
if ($search !== '') {
    $sql .= " AND games.titre LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

// Filtre par genre (Règle 6)
if ($genre !== '') {
    $sql .= " AND games.genre = :genre";
    $params[':genre'] = $genre;
}

$sql .= " ORDER BY games.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les genres disponibles pour la liste déroulante
$stmtGenres = $pdo->query("SELECT DISTINCT genre FROM games ORDER BY genre");
$genres = $stmtGenres->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameHub - Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

    <?php include 'nav.php'; ?>

    <header class="py-4 bg-secondary-subtle text-dark">
        <div class="container text-center">
            <h1 class="display-5 fw-bold">🎮 Bienvenue sur GameHub</h1>
            <p class="lead">Découvrez et partagez vos jeux vidéo préférés.</p>
        </div>
    </header>

    <main class="container py-4">

        <!-- Barre de recherche + filtre par genre (Règles 5 et 6) -->
        <form method="GET" action="index.php" class="row g-2 mb-4">
            <div class="col-md-6">
                <input
                    type="text"
                    name="search"
                    class="form-control bg-dark text-light border-secondary"
                    placeholder="🔍 Rechercher un jeu par titre..."
                    value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <select name="genre" class="form-select bg-dark text-light border-secondary">
                    <option value="">-- Tous les genres --</option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?= htmlspecialchars($g) ?>" <?= $genre === $g ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g) ?>
                        </option>
                    <?php endforeach; ?>
                    <!-- Genres fixes du cahier des charges -->
                    <?php
                    $genresFixes = ['Action', 'Aventure', 'RPG', 'Sport', 'Stratégie', 'Simulation'];
                    foreach ($genresFixes as $gf):
                        if (!in_array($gf, $genres)): // Éviter les doublons
                    ?>
                        <option value="<?= htmlspecialchars($gf) ?>" <?= $genre === $gf ? 'selected' : '' ?>>
                            <?= htmlspecialchars($gf) ?>
                        </option>
                    <?php endif; endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
            <?php if ($search !== '' || $genre !== ''): ?>
                <div class="col-12">
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">✖ Réinitialiser les filtres</a>
                </div>
            <?php endif; ?>
        </form>

        <!-- Résumé de la recherche -->
        <?php if ($search !== '' || $genre !== ''): ?>
            <p class="text-secondary mb-3">
                <?= count($games) ?> résultat(s) trouvé(s)
                <?= $search !== '' ? 'pour "<strong>' . htmlspecialchars($search) . '</strong>"' : '' ?>
                <?= $genre !== '' ? 'dans le genre <strong>' . htmlspecialchars($genre) . '</strong>' : '' ?>
            </p>
        <?php endif; ?>

        <!-- Affichage des jeux -->
        <div class="row g-4">
            <?php if (empty($games)): ?>
                <!-- Message si aucun résultat (Règle 8) -->
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <?php if ($search !== '' || $genre !== ''): ?>
                            😕 Aucun jeu ne correspond à votre recherche.
                            <a href="index.php" class="alert-link">Voir tous les jeux</a>
                        <?php else: ?>
                            🎮 Aucun jeu disponible pour le moment.
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="add_game.php" class="alert-link">Soyez le premier à en ajouter un !</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($games as $game): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 bg-secondary text-light border-0 shadow">
                            <?php if ($game['image']): ?>
                                <img src="images/<?= htmlspecialchars($game['image']) ?>"
                                     class="card-img-top"
                                     alt="<?= htmlspecialchars($game['titre']) ?>"
                                     style="height:180px; object-fit:cover;"
                                     onerror="this.style.display='none'">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($game['titre']) ?></h5>
                                <p class="card-text small"><?= htmlspecialchars($game['description']) ?></p>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <small class="text-warning">🎯 <?= htmlspecialchars($game['genre']) ?></small>
                                <!-- Affichage du login du créateur (Règle 7) -->
                                <small class="text-muted">👤 <?= htmlspecialchars($game['createur']) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>

    <footer class="bg-black text-center py-3 border-top border-secondary mt-5">
        <p class="mb-0 text-muted">GameHub — Projet fil rouge BTS SIO SLAM</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
