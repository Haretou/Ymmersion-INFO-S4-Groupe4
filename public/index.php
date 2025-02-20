<?php
require_once '../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION["user_id"]);
$is_admin = false;

// Si l'utilisateur est connecté, récupérer son rôle
if ($is_logged_in) {
    if (!isset($_SESSION["role"])) {
        // Récup le rôle de la session 
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION["user_id"]]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Stocker le rôle en session
        $_SESSION["role"] = $user["role"] ?? "user"; 
    }

    // Vérifier si l'utilisateur est admin
    $is_admin = ($_SESSION["role"] === "admin");
}

<<<<<<< HEAD
// Requête pour récupérer les catégories
$stmt = $pdo->prepare("SELECT * FROM categories");
=======
// On récup les articles des plus recents au plus anciens 
$stmt = $pdo->prepare("SELECT * FROM articles ORDER BY created_at DESC");
>>>>>>> origin/main
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
    <link rel="stylesheet" href="style.css">
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
<<<<<<< HEAD

                <!-- Lien vers le panneau admin uniquement pour l'administrateur -->
                <?php if ($is_admin): ?>
                    <li><a href="../product/admin.php">Panneau Admin</a></li>
                    <li><a href="../product/create.php">Modifier un article</a></li>  <!-- Lien de modification d'article -->
                <?php endif; ?>

                <!-- Lien vers les favoris -->
                <li><a href="favorites.php">❤️ Voir mes favoris</a></li>
=======
                <li class="welcome-msg">Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?>!</li>
>>>>>>> origin/main
            <?php else: ?>
                <li><a href="login.php">Connexion</a></li>
                <li><a href="register.php">Inscription</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <h1>Bienvenue au PokéStore</h1>

<<<<<<< HEAD
    <!-- Barre de recherche -->
    <form action="index.php" method="GET">
        <input type="text" name="search" placeholder="Rechercher un produit" value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit">Rechercher</button>
    </form>

    <!-- Filtre par catégorie -->
    <h3>Catégories</h3>
    <ul>
        <li><a href="index.php">Toutes les catégories</a></li>
        <?php foreach ($categories as $category): ?>
            <li><a href="index.php?category=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
        <?php endforeach; ?>
    </ul>

    <!-- Tri des articles -->
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

    <h2>Nos articles en vente</h2>
    <?php if (count($articles) > 0): ?>
        <ul>
            <?php foreach ($articles as $article): ?>
                <li>
                    <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                    <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
                    <p><strong>Prix :</strong> <?php echo htmlspecialchars($article['price']); ?> €</p>
                    <p><strong>Stock :</strong> <?php echo htmlspecialchars($article['stock']); ?></p>
=======
    
    <?php if ($is_admin): ?>
        <div class="admin-section">
            <h2>Ajouter un nouvel article</h2>
            <form action="../product/create.php" method="POST">
                <button type="submit">Ajouter un article</button>
            </form>
        </div>
    <?php endif; ?>

    
    <div class="articles-container">
        <h2>Nos articles en vente</h2>
        <?php if (count($articles) > 0): ?>
            <ul>
                <?php foreach ($articles as $article): ?>
                    <li>
                        <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                        <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
                        <p><strong>Prix :</strong> <?php echo htmlspecialchars($article['price']); ?> €</p>
>>>>>>> origin/main

                        <!-- Affichage de l'image de l'article -->
                        <?php if (!empty($article['image'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($article['image']); ?>" alt="Image de l'article" width="100">
                        <?php endif; ?>

                        <!-- Lien vers la page du produit -->
                        <a href="../product/product.php?id=<?php echo $article['id']; ?>">Voir l'article</a>

<<<<<<< HEAD
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun article disponible.</p>
    <?php endif; ?>
=======
                        <?php if ($is_logged_in): ?>
                            <!-- Formulaire pour ajouter au panier -->
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
    </div>
>>>>>>> origin/main

</body>
</html>
