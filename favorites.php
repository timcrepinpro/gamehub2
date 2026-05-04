<?php
// favorites.php — "Mes jeux" : protégé, avec boutons modifier et supprimer
session_start();

// Règle 2 : Protection de la page — redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?erreur=connexion_requise");
    exit;
}

include 'db.php';

// Message de succès après une action
$succes = $_GET['succes'] ?? '';

// Récupérer uniquement les jeux de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM games WHERE user_id = :uid ORDER BY id DESC");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameHub - Mes jeux</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

    <?php include 'nav.php'; ?>

    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🎮 Mes jeux</h2>
            <a href="add_game.php" class="btn btn-primary">➕ Ajouter un jeu</a>
        </div>

        <!-- Messages de retour après action -->
        <?php if ($succes === 'ajout'): ?>
            <div class="alert alert-success">✅ Jeu ajouté avec succès !</div>
        <?php elseif ($succes === 'modif'): ?>
            <div class="alert alert-success">✅ Jeu modifié avec succès !</div>
        <?php elseif ($succes === 'suppr'): ?>
            <div class="alert alert-success">✅ Jeu supprimé avec succès !</div>
        <?php endif; ?>

        <?php if (empty($games)): ?>
            <!-- Règle 8 : message si aucun jeu -->
            <div class="alert alert-warning text-center">
                😕 Vous n'avez encore ajouté aucun jeu.
                <a href="add_game.php" class="alert-link">Ajouter votre premier jeu !</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($games as $game): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 bg-secondary text-light border-0 shadow">
                            <?php if ($game['image']): ?>
                                <img src="images/<?= htmlspecialchars($game['image']) ?>"
                                     class="card-img-top"
                                     alt="<?= htmlspecialchars($game['titre']) ?>"
                                     style="height:160px; object-fit:cover;"
                                     onerror="this.style.display='none'">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($game['titre']) ?></h5>
                                <span class="badge bg-warning text-dark mb-2"><?= htmlspecialchars($game['genre']) ?></span>
                                <p class="card-text small"><?= htmlspecialchars($game['description']) ?></p>
                            </div>
                            <div class="card-footer d-flex gap-2">
                                <!-- Bouton Modifier (Règle 3) -->
                                <a href="edit_game.php?id=<?= $game['id'] ?>"
                                   class="btn btn-warning btn-sm flex-fill">✏️ Modifier</a>

                                <!-- Bouton Supprimer (Règle 4) avec confirmation -->
                                <button
                                    class="btn btn-danger btn-sm flex-fill"
                                    onclick="confirmerSuppression(<?= $game['id'] ?>, '<?= addslashes(htmlspecialchars($game['titre'])) ?>')">
                                    🗑️ Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-black text-center py-3 border-top border-secondary mt-5">
        <p class="mb-0 text-muted">GameHub — Projet fil rouge BTS SIO SLAM</p>
    </footer>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="modalSuppr" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Voulez-vous vraiment supprimer le jeu <strong id="nomJeu"></strong> ?
                    <br><small class="text-muted">Cette action est irréversible.</small>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <a id="lienSuppr" href="#" class="btn btn-danger">Oui, supprimer</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmerSuppression(id, titre) {
            document.getElementById('nomJeu').textContent = titre;
            document.getElementById('lienSuppr').href = 'delete_game.php?id=' + id;
            new bootstrap.Modal(document.getElementById('modalSuppr')).show();
        }
    </script>
</body>
</html>
