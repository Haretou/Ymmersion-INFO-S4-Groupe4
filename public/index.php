<?php
require_once '../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION["user_id"]);
$is_admin = false;

// Si l'utilisateur est connecté, récupérer son rôle
if ($is_logged_in) {
    if (!isset($_SESSION["role"])) {
        // Requête unique pour récupérer le rôle de l'utilisateur et le stocker en session
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION["user_id"]]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Stocker le rôle en session
        $_SESSION["role"] = $user["role"] ?? "user"; 
    }

    // Vérifier si l'utilisateur est admin
    $is_admin = ($_SESSION["role"] === "admin");
}

// Récupérer les articles depuis la base de données (les plus récents en premier)
$stmt = $pdo->prepare("SELECT * FROM articles ORDER BY created_at DESC");
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    
    <h1>Bienvenue au PokéStore</h1>

    <!-- Bouton "Ajouter un article" uniquement visible pour les administrateurs -->
    <?php if ($is_admin): ?>
        <h2>Ajouter un nouvel article</h2>
        <form action="../product/create.php" method="POST">
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

                    <!-- Affichage de l'image de l'article -->
                    <?php if (!empty($article['image'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($article['image']); ?>" alt="Image de l'article" width="100">
                    <?php endif; ?>

                    <!-- Lien vers la page du produit -->
                    <a href="../product/product.php?id=<?php echo $article['id']; ?>">Voir l'article</a>

                    <?php if ($is_logged_in): ?>
                        <!-- Formulaire pour ajouter au panier -->
                        <form action="cart.php" method="POST">
                            <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                            <label for="quantity">Quantité :</label>
                            <input type="number" name="quantity" value="1" min="1" max="10">
                            <button type="submit">Ajouter au panier</button>
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
