<?php
require_once '../config/config.php';
session_start();

// Vérifier si le panier contient des articles
if (empty($_SESSION['cart'])) {
    echo "<h1>Votre panier est vide.</h1>";
    exit;
}

// Calculer le total du panier
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
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

    <table border="1">
        <thead>
            <tr>
                <th>Image</th>
                <th>Article</th>
                <th>Prix</th>
                <th>Quantité</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <tr>
                    <td><img src="<?php echo htmlspecialchars($item['image']); ?>" width="50" alt="Image produit"></td>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td><?php echo htmlspecialchars($item['price']); ?> €</td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo $item['price'] * $item['quantity']; ?> €</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Total : <?php echo $total; ?> €</h2>

    <!-- Formulaire d'informations de livraison et facturation -->
    <h2>Informations de Livraison & Facturation</h2>
    <form action="payment.php" method="POST">
        <h3>Informations de Livraison</h3>
        <label for="shipping_name">Nom :</label>
        <input type="text" name="shipping_name" required><br><br>

        <label for="shipping_address">Adresse :</label>
        <input type="text" name="shipping_address" required><br><br>

        <label for="shipping_city">Ville :</label>
        <input type="text" name="shipping_city" required><br><br>

        <label for="shipping_zip">Code Postal :</label>
        <input type="text" name="shipping_zip" required><br><br>

        <label for="shipping_country">Pays :</label>
        <input type="text" name="shipping_country" required><br><br>

        <h3>Informations de Facturation</h3>
        <label for="billing_name">Nom :</label>
        <input type="text" name="billing_name" required><br><br>

        <label for="billing_address">Adresse :</label>
        <input type="text" name="billing_address" required><br><br>

        <label for="billing_city">Ville :</label>
        <input type="text" name="billing_city" required><br><br>

        <label for="billing_zip">Code Postal :</label>
        <input type="text" name="billing_zip" required><br><br>

        <label for="billing_country">Pays :</label>
        <input type="text" name="billing_country" required><br><br>

        <button type="submit">Procéder au paiement</button>
    </form>

    <a href="index.php">Continuer mes achats</a>
</body>
</html>
