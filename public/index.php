<?php
require_once '../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION["user_id"]);
$is_admin = false;

if ($is_logged_in) {
    if (!isset($_SESSION["role"])) {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION["user_id"]]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION["role"] = $user["role"] ?? "user"; 
    }
    $is_admin = ($_SESSION["role"] === "admin");
}

// Récupération des catégories
$stmt = $pdo->prepare("SELECT * FROM categories");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement des filtres
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

$query = "SELECT * FROM articles WHERE title LIKE :search";
$params = ['search' => '%' . $search_query . '%'];

if ($category_filter) {
    $query .= " AND category_id = :category";
    $params['category'] = $category_filter;
}

$query .= " ORDER BY created_at DESC";  
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_asc':
            $query = str_replace('ORDER BY created_at DESC', 'ORDER BY price ASC', $query);
            break;
        case 'price_desc':
            $query = str_replace('ORDER BY created_at DESC', 'ORDER BY price DESC', $query);
            break;
    }
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - E-Commerce</title>
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

        nav {
            background-color: #4CAF50;
            padding: 1rem;
            text-align: center;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            font-size: 1.1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
            margin: 0 10px;
        }

        nav a:hover {
            background-color: #45a049;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
        }

        select, input[type="text"], button {
            padding: 0.6rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 1rem;
            margin: 0.5rem 0;
            width: 100%;
            max-width: 300px;
        }

        .article-list {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: space-between;
        }

        .article-item {
            background-color: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 48%;
            margin-bottom: 1rem;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1rem;
        }
    </style>
</head>
<body>

<header>
    <h1>Bienvenue au PokéStore</h1>
</header>

<nav>
    <a href="index.php">Accueil</a>
    <?php if ($is_logged_in): ?>
        <a href="account.php">👤 Mon Compte</a>
        <a href="cart.php">🛒 Voir le panier</a>
        <a href="logout.php">Déconnexion</a>
        <?php if ($is_admin): ?>
            <a href="../product/admin.php">Panneau Admin</a>
            <a href="../product/create.php">Modifier un article</a>
        <?php endif; ?>
        <a href="favorites.php">❤️ Voir mes favoris</a>
    <?php else: ?>
        <a href="login.php">Connexion</a>
        <a href="register.php">Inscription</a>
    <?php endif; ?>
</nav>

<div class="container">
    <!-- Barre de recherche -->
    <form action="index.php" method="GET">
        <input type="text" name="search" placeholder="Rechercher un produit" value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit">Rechercher</button>
    </form>

    <!-- Filtre par catégorie sous forme de menu déroulant -->
    <form action="index.php" method="GET">
        <label for="category">Catégories :</label>
        <select name="category" id="category" onchange="this.form.submit()">
            <option value="">Toutes les catégories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>" <?php echo ($category_filter == $category['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
    </form>

    <!-- Tri des articles -->
    <form action="index.php" method="GET">
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
        <label for="sort">Trier par :</label>
        <select name="sort" id="sort" onchange="this.form.submit()">
            <option value="created_at" <?php if (!isset($_GET['sort'])) echo 'selected'; ?>>Date</option>
            <option value="price_asc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') echo 'selected'; ?>>Prix croissant</option>
            <option value="price_desc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') echo 'selected'; ?>>Prix décroissant</option>
        </select>
    </form>

    <h2>Nos articles en vente</h2>

    <div class="article-list">
        <?php if (count($articles) > 0): ?>
            <?php foreach ($articles as $article): ?>
                <div class="article-item">
                    <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                    <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
                    <p><strong>Prix :</strong> <?php echo htmlspecialchars($article['price']); ?> €</p>
                    <p><strong>Stock :</strong> <?php echo htmlspecialchars($article['stock']); ?></p>

                    <?php if (!empty($article['image'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($article['image']); ?>" alt="Image de l'article">
                    <?php endif; ?>

                    <a href="../product/product.php?id=<?php echo $article['id']; ?>">Voir l'article</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun article disponible.</p>
        <?php endif; ?>
    </div>
</div>

<footer>
    <p>&copy; 2025 PokéStore - Tous droits réservés</p>
</footer>

</body>
</html>
