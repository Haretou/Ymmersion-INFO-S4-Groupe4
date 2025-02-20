<?php
require_once '../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Vous devez être connecté pour accéder à vos favoris.");
}

$user_id = $_SESSION['user_id'];

// Récupérer les articles favoris de l'utilisateur
$stmt = $pdo->prepare("
    SELECT a.id, a.title, a.price, a.description, a.image
    FROM favorites f
    JOIN articles a ON f.article_id = a.id
    WHERE f.user_id = ?
");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes favoris</title>
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

        h1 {
            margin: 0;
        }

        nav {
            background-color: #4CAF50;
            padding: 1rem;
        }

        nav ul {
            list-style-type: none;
            padding: 0;
            text-align: center;
        }

        nav ul li {
            display: inline-block;
            margin-right: 15px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            font-size: 1.1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        nav ul li a:hover {
            background-color: #45a049;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
        }

        .favorite-list {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: space-between;
        }

        .favorite-item {
            background-color: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 48%;
            margin-bottom: 1rem;
        }

        .favorite-item img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .favorite-item h3 {
            color: #333;
        }

        .favorite-item p {
            margin: 0.5rem 0;
        }

        .favorite-item form {
            display: flex;
            justify-content: center;
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

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1rem;
        }

        @media (max-width: 768px) {
            .favorite-item {
                width: 100%;
            }

            nav ul li {
                display: block;
                margin-right: 0;
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <header>
        <h1>Vos articles favoris</h1>
    </header>

    <nav>
        <ul>
            <li><a href="../public/index.php">Accueil</a></li>
            <li><a href="account.php">👤 Mon Compte</a></li>
            <li><a href="cart.php">🛒 Panier</a></li>
            <li><a href="logout.php">Déconnexion</a></li>
        </ul>
    </nav>

    <div class="container">
        <?php if (count($favorites) > 0): ?>
            <div class="favorite-list">
                <?php foreach ($favorites as $favorite): ?>
                    <div class="favorite-item">
                        <h3><?php echo htmlspecialchars($favorite['title']); ?></h3>
                        <?php if (!empty($favorite['image'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($favorite['image']); ?>" alt="Image de l'article">
                        <?php endif; ?>
                        <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($favorite['description'])); ?></p>
                        <p><strong>Prix :</strong> <?php echo htmlspecialchars($favorite['price']); ?> €</p>

                        <form action="../product/product.php?id=<?php echo $favorite['id']; ?>" method="GET">
                            <button type="submit">Voir l'article</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Aucun article dans vos favoris.</p>
        <?php endif; ?>

        <br>
        <a href="../public/index.php">Retour à la liste des articles</a>
    </div>

    <footer>
        <p>&copy; 2025 PokéStore - Tous droits réservés</p>
    </footer>

</body>
</html>
