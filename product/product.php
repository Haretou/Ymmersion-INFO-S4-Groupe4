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
    <style>
        /* Global */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 90%;
            max-width: 1000px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        h1, h2, h3 {
            text-align: center;
            color: #28a745; /* Vert utilisé précédemment */
        }
        .product-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            text-align: center;
        }
        .product-item {
            background: #f4f4f4;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 15px;
            width: 300px;
            text-align: center;
        }
        .product-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .product-item h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        .product-item p {
            margin: 5px 0;
        }
        .btn-add {
            background-color: #28a745; /* Vert utilisé précédemment */
            border: none;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-add:hover {
            background-color: #218838; /* Plus foncé au survol */
        }
        .btn-secondary {
            display: block;
            text-align: center;
            margin-top: 20px;
            padding: 12px 20px;
            background-color: #6c757d;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .btn-secondary:hover {
            background-color: #565e64;
        }
        .reviews-section {
            margin-top: 40px;
            text-align: center;
        }
        .review-form {
            margin-top: 20px;
        }
        .review-form input, .review-form textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            width: 100%;
            margin-bottom: 10px;
        }
        .review-form button {
            padding: 12px 20px;
            background-color: #28a745; /* Vert utilisé précédemment */
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .review-form button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($article['title']); ?></h1>
        <p><strong>Date de création :</strong> <?php echo date("d/m/Y à H:i", strtotime($article['created_at'])); ?></p>

        <div class="product-details">
            <div class="product-item">
                <?php if (!empty($article['image'])): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($article['image']); ?>" alt="Image de l'article">
                <?php endif; ?>

                <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
                <p><strong>Prix :</strong> <?php echo htmlspecialchars($article['price']); ?> €</p>

                <?php if ($is_logged_in): ?>
                    <form action="product.php?id=<?php echo $article_id; ?>" method="POST">
                        <label for="quantity">Quantité :</label>
                        <input type="number" name="quantity" value="1" min="1">
                        <button type="submit" class="btn-add">Ajouter au panier</button>
                    </form>

                    <form action="product.php?id=<?php echo $article_id; ?>" method="POST">
                        <button type="submit" name="add_to_favorites" class="btn-add">Ajouter aux favoris</button>
                    </form>
                <?php else: ?>
                    <p><a href="login.php">Connectez-vous</a> pour ajouter cet article au panier ou aux favoris</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section des évaluations -->
        <div class="reviews-section">
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
                <div class="review-form">
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
                </div>
            <?php endif; ?>
        </div>
        
        <a href="../public/index.php" class="btn-secondary">Retour à la liste des articles</a>
    </div>
</body>
</html>
