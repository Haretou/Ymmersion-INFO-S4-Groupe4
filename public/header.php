<?php
session_start();
?>
<nav>
    <a href="index.php">Accueil</a>
    <?php if (isset($_SESSION["user_id"])): ?>
        <a href="account.php">Mon Compte</a>
        <a href="cart.php">Panier</a>
        <a href="logout.php">Déconnexion</a>
    <?php else: ?>
        <a href="login.php">Connexion</a>
        <a href="register.php">Inscription</a>
    <?php endif; ?>
</nav>
