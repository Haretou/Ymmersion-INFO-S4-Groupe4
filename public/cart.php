<?php
require_once '../config/config.php';
session_start();

// Gérer la suppression d'un article du panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $delete_key = $_POST['delete_key'];
    
    if (isset($_SESSION['cart'][$delete_key])) {
        unset($_SESSION['cart'][$delete_key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Réindexer le tableau
    }
    
    header("Location: cart.php"); // Rafraîchir la page
    exit;
}

// Vérifier si le panier est vide
if (empty($_SESSION['cart'])) {
    echo "<h1>Votre panier est vide.</h1>";
    exit;
}

// Connexion à la base de données
$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$balance = $user['balance'];

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
    <link rel="stylesheet" href="style.css">
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
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['cart'] as $key => $item): ?>
                <tr>
                    <td><img src="<?php echo htmlspecialchars($item['image']); ?>" width="50" alt="Image produit"></td>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td><?php echo htmlspecialchars($item['price']); ?> €</td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo $item['price'] * $item['quantity']; ?> €</td>
                    <td>
                        <form method="post" action="cart.php">
                            <input type="hidden" name="delete_key" value="<?php echo $key; ?>">
                            <button type="submit" name="delete">🗑️ Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Total : <?php echo $total; ?> €</h2>
    <h3>Votre solde disponible : <?php echo number_format($balance, 2); ?> €</h3>

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
        <h2>Méthode de paiement</h2>
        <label>
            <input type="radio" name="payment_method" value="stripe" checked> Payer avec Stripe
        </label>
        <br>
        <label>
            <input type="radio" name="payment_method" value="balance"> Payer avec mon solde
        </label>
        <br><br>
        <button type="submit">Procéder au paiement</button>
    </form>
    <a href="index.php">Continuer mes achats</a>
</body>
</html>
