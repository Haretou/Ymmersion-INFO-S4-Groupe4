<?php
require_once '../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Vérifier si l'ID de l'article est passé en paramètre
if (isset($_GET['article_id'])) {
    $article_id = $_GET['article_id'];
    $user_id = $_SESSION["user_id"];

    // Supprimer l'article du panier
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND article_id = ?");
    $stmt->execute([$user_id, $article_id]);

    // Rediriger vers la page du panier
    header("Location: cart.php");
    exit;
} else {
    echo "Erreur: L'article n'a pas été trouvé.";
}
?>
