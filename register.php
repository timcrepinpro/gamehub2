<?php
// register.php — Inscription d'un utilisateur
session_start();

$login            = trim($_POST['login']            ?? '');
$email            = trim($_POST['email']            ?? '');
$password         = trim($_POST['password']         ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');

if (empty($login) || empty($email) || empty($password) || empty($confirm_password)) {
    die("Erreur : Tous les champs sont obligatoires.");
}

if (!preg_match("/^[a-zA-Z0-9]{4,}$/", $login)) {
    die("Erreur : Le login doit contenir uniquement des lettres/chiffres (min. 4 caractères).");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Erreur : L'email n'est pas valide.");
}

if (!preg_match("/^(?=.*[A-Z])(?=.*\d).{8,}$/", $password)) {
    die("Erreur : Le mot de passe doit faire 8 caractères minimum, avec 1 majuscule et 1 chiffre.");
}

if ($password !== $confirm_password) {
    die("Erreur : Les mots de passe ne correspondent pas.");
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

include 'db.php';

try {
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, email, password) VALUES (:login, :email, :password)");
    $stmt->execute([':login' => $login, ':email' => $email, ':password' => $password_hash]);

    echo "Inscription réussie ! Bienvenue, " . htmlspecialchars($login) . ". Vous pouvez maintenant <a href='login.html'>vous connecter</a>.";
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        die("Erreur : Ce login ou cet email est déjà utilisé.");
    }
    die("Erreur : " . $e->getMessage());
}
?>
