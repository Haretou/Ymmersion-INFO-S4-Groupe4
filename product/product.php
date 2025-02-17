<?php
require_once '../config/config.php';
session_start();

// Vérifier si l'ID de l'article est passé en paramètre
if (!isset($_GET['id'])) {
    die("Article non trouvé");
}

$article_id = $_GET['id'];

// Récupérer les détails de l'article depuis la base de données
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'article existe
if (!$article) {
    die("Article non trouvé");
}

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION["user_id"]);

// Ajouter au panier
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['quantity'])) {
    $quantity = $_POST['quantity'];

    // Vérifier si le panier existe dans la session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Si l'article est déjà dans le panier, on met à jour la quantité
    if (isset($_SESSION['cart'][$article_id])) {
        $_SESSION['cart'][$article_id]['quantity'] += $quantity;
    } else {
        // Ajouter l'article au panier avec les informations complètes
        $_SESSION['cart'][$article_id] = [
            'title' => $article['title'],
            'price' => $article['price'],
            'image' => $article['image'], // Assurez-vous que l'image est stockée ici
            'quantity' => $quantity
        ];
    }

    // Redirection vers le panier
    header("Location: ../public/cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?></title>
</head>
<body>
    <h1><?php echo htmlspecialchars($article['title']); ?></h1>

    <!-- Affichage de l'image de l'article -->
    <?php if (!empty($article['image'])): ?>
        <img src="../uploads/<?php echo htmlspecialchars($article['image']); ?>" alt="Image de l'article" width="200">
    <?php endif; ?>

    <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
    <p><strong>Prix :</strong> <?php echo htmlspecialchars($article['price']); ?> €</p>

    <?php if ($is_logged_in): ?>
        <!-- Ajouter au panier -->
        <form action="product.php?id=<?php echo $article_id; ?>" method="POST">
            <label for="quantity">Quantité :</label>
            <input type="number" name="quantity" value="1" min="1">
            <button type="submit">Ajouter au panier</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Connectez-vous pour ajouter cet article au panier</a></p>
    <?php endif; ?>

    <!-- Lien pour revenir à la liste des articles -->
    <a href="../public/index.php">Retour à la liste des articles</a>
</body>
</html>
