<?php
// logout.php — Déconnexion (Règle 1 du cahier des charges)
session_start();
session_destroy();
header("Location: index.php");
exit;
?>
