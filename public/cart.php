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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
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
            color: #4CAF50;
        }
        h1 {
            margin-bottom: 30px;
        }
        /* Cart Items en cartes */
        .cart-items {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .cart-item {
            background: #f4f4f4;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 15px;
            width: 300px;
            text-align: center;
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .cart-item h3 {
            font-size: 1.2em;
            margin-bottom: 10px;
        }
        .cart-item p {
            margin: 5px 0;
        }
        .btn-delete {
            background-color: #dc3545;
            border: none;
            color: #fff;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-delete:hover {
            background-color: #b02a37;
        }
        /* Cart Summary */
        .cart-summary {
            text-align: right;
            margin-top: 20px;
            font-size: 1.2em;
        }
        /* Payment Form */
        .payment-form {
            margin-top: 40px;
            padding: 20px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .payment-form form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .payment-form label {
            font-weight: 600;
        }
        .payment-form input[type="text"],
        .payment-form input[type="number"],
        .payment-form input[type="email"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .payment-form input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(0,123,255,0.5);
        }
        .payment-form .radio-group {
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        .payment-form button {
            padding: 12px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.3s;
        }
        .payment-form button:hover {
            background-color: #0056b3;
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
        /* Responsive */
        @media (max-width: 768px) {
            .cart-items {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</body>
</html>
