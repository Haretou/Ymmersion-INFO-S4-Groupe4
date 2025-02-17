<?php
require_once '../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Vérifier si l'ID de l'article est passé en paramètre
if (!isset($_GET['id'])) {
    die("Article non trouvé.");
}

$article_id = $_GET['id'];

// Récupérer l'article depuis la base de données
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'article existe et si l'utilisateur est le propriétaire de l'article
if (!$article || $article['user_id'] != $_SESSION["user_id"]) {
    die("Vous ne pouvez pas supprimer cet article.");
}

// Supprimer l'article de la base de données
$stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
$stmt->execute([$article_id]);

// Optionnel : Ajouter un message de succès
$_SESSION['message'] = "L'article a été supprimé avec succès.";

// Rediriger vers la page de l'utilisateur (compte)
header("Location: account.php");
exit;
?>
