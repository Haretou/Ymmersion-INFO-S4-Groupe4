<?php
require_once '../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION["user_id"]);
$is_admin = false;

// Si l'utilisateur est connecté, récupérer son rôle
if ($is_logged_in) {
    if (!isset($_SESSION["role"])) {
        // Récupération du rôle de la session 
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION["user_id"]]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Stocker le rôle en session
        $_SESSION["role"] = $user["role"] ?? "user"; 
    }

    // Vérifier si l'utilisateur est admin
    $is_admin = ($_SESSION["role"] === "admin");
}

// Requête pour récupérer les catégories
$stmt = $pdo->prepare("SELECT * FROM categories");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement de la recherche
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Requête pour récupérer les articles avec filtrage par catégorie et recherche
$query = "SELECT * FROM articles WHERE title LIKE :search";
$params = ['search' => '%' . $search_query . '%'];

if ($category_filter) {
    $query .= " AND category_id = :category";
    $params['category'] = $category_filter;
}

$query .= " ORDER BY created_at DESC";  // Tri par date par défaut
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
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <title>Accueil - E-Commerce</title>
</head>
<body>

    
    <nav class="nav-container">
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <?php if ($is_logged_in): ?>
                <li><a href="account.php">👤 Mon Compte</a></li>
                <li><a href="cart.php">🛒 Voir le panier</a></li>
                <li><a href="logout.php">Déconnexion</a></li>

                <!-- Lien vers le panneau admin uniquement pour l'administrateur -->
                <?php if ($is_admin): ?>
                    <li><a href="../product/admin.php">Panneau Admin</a></li>
                    <li><a href="../product/create.php">Modifier un article</a></li>  <!-- Lien de modification d'article -->
                <?php endif; ?>

                <!-- Lien vers les favoris -->
                <li><a href="favorites.php">❤️ Voir mes favoris</a></li>
            <?php else: ?>
                <li><a href="login.php">Connexion</a></li>
                <li><a href="register.php">Inscription</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <header>
        <h1>Bienvenue au PokéStore</h1>
    </header>

    <!-- Barre de recherche -->
    <div class="search-bar">
        <form action="index.php" method="GET">
            <input type="text" name="search" placeholder="Rechercher un produit" value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <!-- Filtre par catégorie -->
    <div class="filter-container">
        <h3>Catégories</h3>
        <ul>
            <li><a href="index.php">Toutes les catégories</a></li>
            <?php foreach ($categories as $category): ?>
                <li><a href="index.php?category=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Tri des articles -->
    <div class="sort-container">
        <h3>Trier par</h3>
        <form action="index.php" method="GET">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
            <select name="sort" onchange="this.form.submit()">
                <option value="created_at" <?php if (!isset($_GET['sort'])) echo 'selected'; ?>>Date</option>
                <option value="price_asc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') echo 'selected'; ?>>Prix croissant</option>
                <option value="price_desc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') echo 'selected'; ?>>Prix décroissant</option>
            </select>
        </form>
    </div>

    <h2>Nos articles en vente</h2>

    <div class="articles-list">
        <?php if (count($articles) > 0): ?>
            <?php foreach ($articles as $article): ?>
                <div class="article-item">
                    <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                    <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
                    <p><strong>Prix :</strong> <?php echo htmlspecialchars($article['price']); ?> €</p>
                    <p><strong>Stock :</strong> <?php echo htmlspecialchars($article['stock']); ?></p>

                    <!-- Affichage de l'image de l'article -->
                    <?php if (!empty($article['image'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($article['image']); ?>" alt="Image de l'article">
                    <?php endif; ?>

                    <!-- Lien vers la page du produit -->
                    <a href="../product/product.php?id=<?php echo $article['id']; ?>">Voir l'article</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun article disponible.</p>
        <?php endif; ?>
    </div>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: transparent; /* Retire la couleur de fond noire */
            color: #333; /* La couleur du texte reste foncée */
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

        .search-bar, .filter-container, .sort-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .search-bar input, .search-bar button {
            padding: 0.6rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 1rem;
            width: 80%;
            max-width: 500px;
        }

        .filter-container, .sort-container {
            display: inline-block;
            margin-bottom: 2rem;
        }

        .articles-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem;
        }

        .article-item {
            background-color: white;
            padding: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        .article-item img {
            width: 100%;
            height: auto;
            max-height: 250px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .article-item a {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .article-item a:hover {
            background-color: #45a049;
        }
    </style>

</body>
</html>
