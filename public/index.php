<?php
require_once '../config/config.php';
session_start();

// Récupérer les articles depuis la base de données (les plus récents d'abord)
$stmt = $pdo->prepare("SELECT * FROM articles ORDER BY created_at DESC");
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION["user_id"]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Accueil - E-Commerce</title>
</head>
<body>

    <!-- Menu de navigation -->
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <?php if ($is_logged_in): ?>
                <li>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?>!</li>
                <li><a href="account.php">👤 Mon Compte</a></li>
                <li><a href="cart.php">🛒 Voir le panier</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="login.php">Connexion</a></li>
                <li><a href="register.php">Inscription</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <h1>Bienvenue sur notre site E-Commerce</h1>

    <?php if ($is_logged_in): ?>
        <h2>Ajouter un nouvel article</h2>
        <form action="../product/create.php" method="POST" enctype="multipart/form-data">
            <button type="submit">Ajouter un article</button>
        </form>
    <?php endif; ?>

    <h2>Nos articles en vente</h2>
    <?php if (count($articles) > 0): ?>
        <ul>
            <?php foreach ($articles as $article): ?>
                <li>
                    <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                    <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
                    <p><strong>Prix :</strong> <?php echo htmlspecialchars($article['price']); ?> €</p>

                    <!-- Affichage d'une image de l'article, si elle existe -->
                    <?php if (!empty($article['image'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($article['image']); ?>" alt="Image de l'article" width="100">
                    <?php endif; ?>

                    <!-- Affichage d'un lien pour consulter l'article -->
                    <a href="../product/product.php?id=<?php echo $article['id']; ?>">Voir l'article</a>

                    <?php if ($is_logged_in): ?>
                        <!-- Ajouter au panier -->
                        <form action="cart.php" method="POST">
                            <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                            <label for="quantity">Quantité :</label>
                            <input type="number" name="quantity" value="1" min="1" max="10">
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun article disponible.</p>
    <?php endif; ?>

</body>
</html>
