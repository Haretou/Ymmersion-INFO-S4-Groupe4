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
            'id' => $article['id'],
            'title' => $article['title'],
            'price' => $article['price'],
            'description'=> $article['description'],
            'image' => $article['image'], // Assurez-vous que l'image est stockée ici
            'quantity' => $quantity
        ];
    }

    // Redirection vers le panier
    header("Location: ../public/cart.php");
    exit;
}

// Ajouter aux favoris
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_favorites'])) {
    if ($is_logged_in) {
        // Vérifier si l'article est déjà dans les favoris
        $stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND article_id = ?");
        $stmt->execute([$_SESSION['user_id'], $article_id]);
        $favorite = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$favorite) {
            // Si l'article n'est pas encore un favori, l'ajouter
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, article_id) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $article_id]);
            echo "Article ajouté aux favoris !";
        } else {
            echo "Vous avez déjà cet article dans vos favoris.";
        }
    } else {
        echo "<p><a href='login.php'>Connectez-vous</a> pour ajouter cet article aux favoris.</p>";
    }
}

// Ajouter une évaluation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'] ?? '';

    // Insérer l'évaluation dans la table reviews
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, article_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $article_id, $rating, $comment]);

    echo "Merci pour votre évaluation !";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/style.css">
    <title><?php echo htmlspecialchars($article['title']); ?></title>
</head>
<body>
    <h1><?php echo htmlspecialchars($article['title']); ?></h1>

    <!-- Affichage de la date de création -->
    <p><strong>Date de création :</strong> <?php echo date("d/m/Y à H:i", strtotime($article['created_at'])); ?></p>

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

        <!-- Ajouter aux favoris -->
        <form action="product.php?id=<?php echo $article_id; ?>" method="POST">
            <button type="submit" name="add_to_favorites">Ajouter aux favoris</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Connectez-vous pour ajouter cet article au panier ou aux favoris</a></p>
    <?php endif; ?>

    <!-- Affichage des évaluations -->
    <h3>Évaluations de cet article</h3>
    <?php
    $stmtReviews = $pdo->prepare("SELECT r.rating, r.comment, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.article_id = ?");
    $stmtReviews->execute([$article_id]);
    $reviews = $stmtReviews->fetchAll(PDO::FETCH_ASSOC);

    if (count($reviews) > 0) {
        foreach ($reviews as $review) {
            echo "<p><strong>" . htmlspecialchars($review['username']) . "</strong> a donné une note de " . htmlspecialchars($review['rating']) . " étoiles.</p>";
            echo "<p>" . nl2br(htmlspecialchars($review['comment'])) . "</p>";
        }
    } else {
        echo "<p>Aucune évaluation pour cet article.</p>";
    }
    ?>

    <?php if ($is_logged_in): ?>
        <!-- Formulaire pour laisser une évaluation -->
        <h3>Laissez une évaluation</h3>
        <form action="product.php?id=<?php echo $article_id; ?>" method="POST">
            <label for="rating">Note :</label>
            <select name="rating" id="rating" required>
                <option value="1">1 étoile</option>
                <option value="2">2 étoiles</option>
                <option value="3">3 étoiles</option>
                <option value="4">4 étoiles</option>
                <option value="5">5 étoiles</option>
            </select>
            <br>
            <label for="comment">Commentaire :</label>
            <textarea name="comment" id="comment" rows="4"></textarea>
            <br>
            <button type="submit" name="submit_review">Soumettre l'évaluation</button>
        </form>
    <?php endif; ?>

    <br>
    <a href="../public/index.php">Retour à la liste des articles</a>
</body>
</html>

