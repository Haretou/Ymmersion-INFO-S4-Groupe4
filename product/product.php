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
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Ajouter au panier
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['quantity'])) {
    $quantity = $_POST['quantity'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$article_id])) {
        $_SESSION['cart'][$article_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$article_id] = [
            'id' => $article['id'],
            'title' => $article['title'],
            'price' => $article['price'],
            'description'=> $article['description'],
            'image' => $article['image'],
            'quantity' => $quantity
        ];
    }

    header("Location: ../public/cart.php");
    exit;
}

// Ajouter aux favoris
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_favorites'])) {
    if ($is_logged_in) {
        $stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND article_id = ?");
        $stmt->execute([$_SESSION['user_id'], $article_id]);
        $favorite = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$favorite) {
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, article_id) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $article_id]);
        }
    } else {
        echo "<p><a href='login.php'>Connectez-vous</a> pour ajouter cet article aux favoris.</p>";
    }
}

// Ajouter une évaluation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, article_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $article_id, $rating, $comment]);
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

    <?php if ($is_admin): ?>
        <a href="../product/edit.php?id=<?php echo $article_id; ?>" class="btn-edit">Modifier l'article</a>
    <?php endif; ?>

    <p><strong>Date de création :</strong> <?php echo date("d/m/Y à H:i", strtotime($article['created_at'])); ?></p>
    
    <?php if (!empty($article['image'])): ?>
        <img src="../uploads/<?php echo htmlspecialchars($article['image']); ?>" alt="Image de l'article" width="200">
    <?php endif; ?>

    <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
    <p><strong>Prix :</strong> <?php echo htmlspecialchars($article['price']); ?> €</p>

    <?php if ($is_logged_in): ?>
        <form action="product.php?id=<?php echo $article_id; ?>" method="POST">
            <label for="quantity">Quantité :</label>
            <input type="number" name="quantity" value="1" min="1">
            <button type="submit">Ajouter au panier</button>
        </form>
        <form action="product.php?id=<?php echo $article_id; ?>" method="POST">
            <button type="submit" name="add_to_favorites">Ajouter aux favoris</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Connectez-vous pour ajouter cet article au panier ou aux favoris</a></p>
    <?php endif; ?>

    <h3>Évaluations</h3>
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
        <h3>Laissez une évaluation</h3>
        <form action="product.php?id=<?php echo $article_id; ?>" method="POST">
            <label for="rating">Note :</label>
            <select name="rating" required>
                <option value="1">1 étoile</option>
                <option value="2">2 étoiles</option>
                <option value="3">3 étoiles</option>
                <option value="4">4 étoiles</option>
                <option value="5">5 étoiles</option>
            </select>
            <label for="comment">Commentaire :</label>
            <textarea name="comment" rows="4"></textarea>
            <button type="submit" name="submit_review">Soumettre</button>
        </form>
    <?php endif; ?>

    <a href="../public/index.php">Retour</a>
</body>
</html>
