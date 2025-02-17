<?php
session_start();
require_once '../config/config.php';

// Vérifier si le panier est vide
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Votre panier est vide.";
    exit;
}

// Calcul du total du panier
$total = 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Panier</title>
</head>
<body>
    <h1>Votre Panier</h1>

    <?php 
    foreach ($_SESSION['cart'] as $article_id => $article) {
        // Récupérer les informations de l'article dans la base de données
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$article_id]);
        $article_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier si l'article existe
        if ($article_data) {
            ?>
            <div>
                <h3>
                    <?php echo htmlspecialchars($article_data['title']); ?>
                </h3>

                <!-- Afficher l'image de l'article -->
                <?php if (!empty($article_data['image'])): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($article_data['image']); ?>" alt="Image de l'article" width="100">
                <?php else: ?>
                    <p>Aucune image disponible</p>
                <?php endif; ?>

                <p>
                    <strong>Prix :</strong> 
                    <?php echo htmlspecialchars($article_data['price']); ?> €
                </p>
                <p><strong>Quantité :</strong> <?php echo htmlspecialchars($article['quantity']); ?></p>
            </div>
            <hr>

            <?php
            // Calculer le total
            $total += $article_data['price'] * $article['quantity'];
        } else {
            echo "Article non trouvé.";
        }
    }
    ?>

    <h3>Total : <?php echo $total; ?> €</h3>

    <form action="checkout.php" method="POST">
        <button type="submit">Procéder au paiement</button>
    </form>

    <br>
    <a href="index.php">Retour à la boutique</a>
</body>
</html>
