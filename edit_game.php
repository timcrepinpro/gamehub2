<?php
// edit_game.php — Modification d'un jeu (Règle 3)
session_start();

// Vérifier que l'utilisateur est connecté (Règle 3 : doit être connecté)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

include 'db.php';

$id     = intval($_GET['id'] ?? 0);
$erreur = '';

// Récupérer le jeu depuis la base de données
$stmt = $pdo->prepare("SELECT * FROM games WHERE id = :id");
$stmt->execute([':id' => $id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier que le jeu existe ET appartient bien à l'utilisateur connecté
if (!$game || $game['user_id'] != $_SESSION['user_id']) {
    header("Location: favorites.php?erreur=interdit");
    exit;
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre       = trim($_POST['titre']       ?? '');
    $genre       = trim($_POST['genre']       ?? '');
    $description = trim($_POST['description'] ?? '');
    $image       = trim($_POST['image']       ?? '');

    if (empty($titre) || empty($genre)) {
        $erreur = "Le titre et le genre sont obligatoires.";
    } else {
        // Mise à jour — on vérifie encore user_id pour sécuriser
        $stmt = $pdo->prepare(
            "UPDATE games SET titre=:titre, genre=:genre, description=:description, image=:image
             WHERE id=:id AND user_id=:user_id"
        );
        $stmt->execute([
            ':titre'       => $titre,
            ':genre'       => $genre,
            ':description' => $description,
            ':image'       => $image,
            ':id'          => $id,
            ':user_id'     => $_SESSION['user_id'],
        ]);
        header("Location: favorites.php?succes=modif");
        exit;
    }
}

$genresFixes = ['Action', 'Aventure', 'RPG', 'Sport', 'Stratégie', 'Simulation'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameHub - Modifier un jeu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

    <?php include 'nav.php'; ?>

    <main class="container py-5" style="max-width:600px;">
        <h2 class="mb-4">✏️ Modifier un jeu</h2>

        <?php if ($erreur): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" action="edit_game.php?id=<?= $id ?>">
            <div class="mb-3">
                <label class="form-label">Titre *</label>
                <input type="text" name="titre" class="form-control bg-dark text-light border-secondary"
                       value="<?= htmlspecialchars($_POST['titre'] ?? $game['titre']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Genre *</label>
                <select name="genre" class="form-select bg-dark text-light border-secondary" required>
                    <option value="">-- Choisir un genre --</option>
                    <?php
                    $genreActuel = $_POST['genre'] ?? $game['genre'];
                    foreach ($genresFixes as $g):
                    ?>
                        <option value="<?= $g ?>" <?= $genreActuel === $g ? 'selected' : '' ?>>
                            <?= $g ?>
                        </option>
                    <?php endforeach; ?>
                    <!-- Si le genre actuel n'est pas dans la liste fixe -->
                    <?php if (!in_array($game['genre'], $genresFixes)): ?>
                        <option value="<?= htmlspecialchars($game['genre']) ?>" selected>
                            <?= htmlspecialchars($game['genre']) ?>
                        </option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control bg-dark text-light border-secondary" rows="3"><?= htmlspecialchars($_POST['description'] ?? $game['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Nom du fichier image</label>
                <input type="text" name="image" class="form-control bg-dark text-light border-secondary"
                       placeholder="ex: zelda.jpg"
                       value="<?= htmlspecialchars($_POST['image'] ?? $game['image']) ?>">
                <div class="form-text text-muted">Placez l'image dans le dossier <code>images/</code></div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning">💾 Enregistrer les modifications</button>
                <a href="favorites.php" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
