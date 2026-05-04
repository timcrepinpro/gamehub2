<?php
// delete_game.php — Suppression d'un jeu (Règle 4)
session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

include 'db.php';

$id = intval($_GET['id'] ?? 0);

// Vérifier que le jeu appartient bien à l'utilisateur connecté avant de supprimer
$stmt = $pdo->prepare("DELETE FROM games WHERE id = :id AND user_id = :user_id");
$stmt->execute([
    ':id'      => $id,
    ':user_id' => $_SESSION['user_id'],
]);

// Si aucune ligne supprimée, le jeu n'existe pas ou n'appartient pas à l'utilisateur
if ($stmt->rowCount() === 0) {
    header("Location: favorites.php?erreur=interdit");
    exit;
}

header("Location: favorites.php?succes=suppr");
exit;
?>
