<?php
require_once '../config/config.php';
session_start();

// Vérifier si un ID est fourni dans l'URL
$viewing_user_id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION["user_id"];
$is_own_account = ($viewing_user_id == $_SESSION["user_id"]);

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$viewing_user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur existe
if (!$user) {
    die("Utilisateur introuvable.");
}

// Récupérer les articles publiés par cet utilisateur
$stmtArticles = $pdo->prepare("SELECT * FROM articles WHERE user_id = ?");
$stmtArticles->execute([$viewing_user_id]);
$articles = $stmtArticles->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les favoris de l'utilisateur
$stmtFavorites = $pdo->prepare("SELECT a.id, a.title, a.price FROM favorites f JOIN articles a ON f.article_id = a.id WHERE f.user_id = ?");
$stmtFavorites->execute([$viewing_user_id]);
$favorites = $stmtFavorites->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les achats si c'est l'utilisateur connecté
if ($is_own_account) {
    $stmtPurchases = $pdo->prepare("
        SELECT o.id, o.total_price, o.created_at
        FROM orders o
        WHERE o.user_id = ?
    ");
    $stmtPurchases->execute([$viewing_user_id]);
    $purchases = $stmtPurchases->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte de <?php echo htmlspecialchars($user['username']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f4f8;
            color: #333;
            padding: 30px 20px;
        }

        h1, h2, h3 {
            color: #2c3e50;
        }

        .profile-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        header {
            text-align: center;
            margin-bottom: 40px;
        }

        .user-info, .balance-section, .articles-section, .favorites-section, .purchases-section {
            margin-bottom: 40px;
        }

        .item-list {
            list-style: none;
            margin-top: 15px;
        }

        .item-list li {
            margin-bottom: 15px;
        }

        .article-link, .favorite-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }

        .article-link:hover, .favorite-link:hover {
            text-decoration: underline;
        }

        .balance-form input {
            padding: 12px;
            font-size: 1rem;
            width: 220px;
            margin-right: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .balance-form button {
            padding: 12px 18px;
            background-color: #27ae60;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .balance-form button:hover {
            background-color: #2ecc71;
        }

        .buttons-container {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        button {
            padding: 12px 20px;
            font-size: 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-main {
            background-color: #3498db;
            color: white;
        }

        .btn-main:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: #e74c3c;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #c0392b;
        }

        h2 {
            font-weight: 600;
            margin-bottom: 15px;
        }

        p {
            font-size: 1rem;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <header>
            <h1>Profil de <?php echo htmlspecialchars($user['username']); ?></h1>
        </header>

        <section class="user-info">
            <h2>Informations Personnelles</h2>
            <p><strong>Nom d'utilisateur :</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        </section>

        <section class="balance-section">
            <?php if ($is_own_account): ?>
                <h2>Mon Solde</h2>
                <p><strong>Solde actuel :</strong> <?php echo number_format($user['balance'], 2); ?> €</p>

                <h3>Ajouter de l'argent à mon solde</h3>
                <form action="stripe_payment.php" method="POST" class="balance-form">
                    <input type="number" name="amount" id="amount" placeholder="Montant (€)" min="1" required>
                    <button type="submit">Ajouter via Stripe</button>
                </form>
            <?php endif; ?>
        </section>

        <section class="articles-section">
            <h2>Articles publiés</h2>
            <?php if (count($articles) > 0): ?>
                <ul class="item-list">
                    <?php foreach ($articles as $article): ?>
                        <li>
                            <a href="product.php?id=<?php echo $article['id']; ?>" class="article-link">
                                <?php echo htmlspecialchars($article['title']); ?> - <?php echo htmlspecialchars($article['price']); ?> €
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Aucun article publié.</p>
            <?php endif; ?>
        </section>

        <section class="favorites-section">
            <h2>Mes Favoris</h2>
            <?php if (count($favorites) > 0): ?>
                <ul class="item-list">
                    <?php foreach ($favorites as $favorite): ?>
                        <li>
                            <a href="product.php?id=<?php echo $favorite['id']; ?>" class="favorite-link">
                                <?php echo htmlspecialchars($favorite['title']); ?> - <?php echo htmlspecialchars($favorite['price']); ?> €
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Aucun favori pour l'instant.</p>
            <?php endif; ?>
        </section>

        <?php if ($is_own_account): ?>
            <section class="purchases-section">
                <h2>Mes Achats</h2>
                <?php if (count($purchases) > 0): ?>
                    <ul class="item-list">
                        <?php foreach ($purchases as $purchase): ?>
                            <li>
                                Commande #<?php echo $purchase['id']; ?> - <?php echo number_format($purchase['total_price'], 2); ?> €
                                (Passée le <?php echo $purchase['created_at']; ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Vous n'avez encore rien acheté.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <div class="buttons-container">
            <a href="index.php"><button class="btn-main">🏠 Accueil</button></a>
            <a href="logout.php"><button class="btn-secondary">🚪 Se Déconnecter</button></a>
        </div>
    </div>
</body>
</html>
