<?php
session_start();
require '../config/config.php'; // Fichier de connexion à la base de données

// Vérification de l'authentification et du rôle administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Suppression d'un article
if (isset($_GET['delete_article'])) {
    $article_id = intval($_GET['delete_article']);
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
    header('Location: admin.php');
    exit();
}

// Suppression d'un utilisateur
if (isset($_GET['delete_user'])) {
    $user_id = intval($_GET['delete_user']);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    header('Location: admin.php');
    exit();
}

// Récupération des articles
$articles = $pdo->query("SELECT * FROM articles")->fetchAll(PDO::FETCH_ASSOC);

// Récupération des utilisateurs
$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>Tableau de Bord Administrateur</h1>
    
    <h2>Articles</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($articles as $article) : ?>
            <tr>
                <td><?= htmlspecialchars($article['id']) ?></td>
                <td><?= htmlspecialchars($article['title']) ?></td>
                <td>
                    <a href="edit.php?id=<?= $article['id'] ?>">Modifier</a>
                    <a href="admin.php?delete_article=<?= $article['id'] ?>" onclick="return confirm('Supprimer cet article ?');">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    
    <h2>Utilisateurs</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user) : ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $user['id'] ?>">Modifier</a>
                    <a href="admin.php?delete_user=<?= $user['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
