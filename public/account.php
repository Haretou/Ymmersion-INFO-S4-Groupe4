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
    <link rel="stylesheet" href="style.css">
    <title>Compte de <?php echo htmlspecialchars($user['username']); ?></title>
</head>
<body>
    <h1>Compte de <?php echo htmlspecialchars($user['username']); ?></h1>

    <h2>Informations du compte</h2>
    <p><strong>Nom d'utilisateur :</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>

    <?php if ($is_own_account): ?>
        <h2>Mon solde</h2>
        <p><strong>Votre solde :</strong> <?php echo number_format($user['balance'], 2); ?> €</p>

        <h2>Ajouter de l'argent à mon solde</h2>
        <form action="stripe_payment.php" method="POST">
            <label for="amount">Montant (€) :</label>
            <input type="number" name="amount" id="amount" min="1" required>
            <button type="submit">Ajouter via Stripe</button>
        </form>
    <?php endif; ?>

    <h2>Articles publiés</h2>
    <?php if (count($articles) > 0): ?>
        <ul>
            <?php foreach ($articles as $article): ?>
                <li>
                    <a href="product.php?id=<?php echo $article['id']; ?>">
                        <?php echo htmlspecialchars($article['title']); ?>
                    </a> - <?php echo htmlspecialchars($article['price']); ?> €
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun article publié.</p>
    <?php endif; ?>

    <h2>Mes favoris</h2>
    <?php if (count($favorites) > 0): ?>
        <ul>
            <?php foreach ($favorites as $favorite): ?>
                <li>
                    <a href="product.php?id=<?php echo $favorite['id']; ?>">
                        <?php echo htmlspecialchars($favorite['title']); ?>
                    </a> - <?php echo htmlspecialchars($favorite['price']); ?> €
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun favori pour l'instant.</p>
    <?php endif; ?>

    <?php if ($is_own_account): ?>
        <h2>Mes achats</h2>
        <?php if (count($purchases) > 0): ?>
            <ul>
                <?php foreach ($purchases as $purchase): ?>
                    <li>
                        Commande #<?php echo $purchase['id']; ?> - <?php echo $purchase['total_price']; ?> €
                        (Passée le <?php echo $purchase['created_at']; ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Vous n'avez encore rien acheté.</p>
        <?php endif; ?>
    <?php endif; ?>

    <br>
    <a href="index.php"><button>🏠 Revenir à l'accueil</button></a>
    <a href="logout.php"><button>🚪 Se déconnecter</button></a>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #333;
            color: white;
            padding: 1rem;
            text-align: center;
        }

        header h1 {
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
        }

        h2 {
            color: #5e5e5e;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }

        p {
            font-size: 1rem;
            margin: 0.5rem 0;
        }

        a {
            text-decoration: none;
            color: #4CAF50;
        }

        a:hover {
            text-decoration: underline;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        .content-section {
            background-color: white;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .content-section ul {
            list-style-type: none;
            padding: 0;
        }

        .content-section li {
            border-bottom: 1px solid #eee;
            padding: 0.8rem 0;
        }

        .content-section li a {
            color: #333;
        }

        .content-section li a:hover {
            color: #4CAF50;
        }

        .balance-form input {
            padding: 0.6rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 150px;
        }

        .balance-form button {
            padding: 0.6rem 1rem;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1rem;
            position: absolute;
            width: 100%;
            bottom: 0;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0.5rem;
            }

            header h1 {
                font-size: 1.5rem;
            }

            h2 {
                font-size: 1.2rem;
            }

            button {
                padding: 0.5rem 1rem;
            }
        }
    </style>
</body>
</html>