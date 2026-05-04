<?php
// login.php — Traitement du formulaire de connexion
session_start();

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$login    = trim($_POST['identifier'] ?? '');
$password = trim($_POST['password']   ?? '');

if (empty($login) || empty($password)) {
    die("Erreur : Tous les champs sont obligatoires.");
}

include 'db.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE login = :login");
    $stmt->execute([':login' => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Stocker l'id ET le login en session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['login']   = $user['login'];
        header("Location: index.php");
        exit;
    } else {
        echo "Erreur : Identifiants incorrects. <a href='login.html'>Réessayer</a>";
    }
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
