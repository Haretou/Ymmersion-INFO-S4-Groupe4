<?php
session_start();
require '../config/config.php'; // Fichier de connexion 脿 la base de donn茅es

// V茅rification de l'authentification et du r么le administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ..//index.php');
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

// R茅cup茅ration des articles
$articles = $pdo->query("SELECT * FROM articles")->fetchAll(PDO::FETCH_ASSOC);

// R茅cup茅ration des utilisateurs
$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panneau Administrateur</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            color: #333;
            padding: 30px;
        }

        h1, h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 15px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .table th {
            background-color: #2980b9;
            color: white;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .actions a {
            margin-right: 10px;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            color: white;
        }

        .actions .btn-edit {
            background-color: #3498db;
        }

        .actions .btn-edit:hover {
            background-color: #2980b9;
        }

        .actions .btn-delete {
            background-color: #e74c3c;
        }

        .actions .btn-delete:hover {
            background-color: #c0392b;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #34495e;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        .btn-back {
            padding: 12px 20px;
            background-color: #2980b9;
            color: white;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
        }

        .btn-back:hover {
            background-color: #3498db;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="../public/index.php" class="btn-back"> Retour à l'accueil</a>
        <h1>Panneau Administrateur</h1>
        <a href="../public/logout.php" class="btn-back"> Se Déconnecter</a>
    </div>

    <div class="container">
        <h2>Gestion des Articles</h2>
        <table class="table">
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($articles as $article) : ?>
                <tr>
                    <td><?= htmlspecialchars($article['id']) ?></td>
                    <td><?= htmlspecialchars($article['title']) ?></td>
                    <td class="actions">
                        <a href="edit.php?id=<?= $article['id'] ?>" class="btn-edit">Modifier</a>
                        <a href="admin.php?delete_article=<?= $article['id'] ?>" class="btn-delete" onclick="return confirm('Etes-vous sur de vouloir supprimer cet article ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Gestion des Utilisateurs</h2>
        <table class="table">
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>R么le</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $user) : ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td class="actions">
                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn-edit">Modifier</a>
                        <a href="admin.php?delete_user=<?= $user['id'] ?>" class="btn-delete" onclick="return confirm('Etes-vous sur de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
