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
    echo '
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; text-align: center;">
        <img src="https://cdn-icons-png.flaticon.com/512/2038/2038854.png" alt="Panier vide" width="200">
        <h1 style="font-family: Arial, sans-serif; color: #333; margin-top: 20px;">Votre panier est vide 😢</h1>
        <p style="color: #666; font-size: 18px;">Ajoutez des articles à votre panier pour passer commande.</p>
        <a href="index.php" style="margin-top: 20px; padding: 12px 24px; background-color: #4CAF50; color: #fff; text-decoration: none; border-radius: 6px; font-size: 18px;">Continuer vos achats</a>
    </div>';
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
    <title>Votre Panier</title>
    <!-- Intégration de Google Fonts pour une typographie moderne -->
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
            border-color: ##4CAF50;
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
</head>
<body>
    <div class="container">
        <h1>Votre Panier</h1>
        
        <!-- Affichage des articles en cartes -->
        <div class="cart-items">
            <?php foreach ($_SESSION['cart'] as $key => $item): ?>
            <div class="cart-item">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Image produit">
                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                <p>Prix : <?php echo htmlspecialchars($item['price']); ?> €</p>
                <p>Quantité : <?php echo $item['quantity']; ?></p>
                <p>Total : <?php echo $item['price'] * $item['quantity']; ?> €</p>
                <form method="post" action="cart.php">
                    <input type="hidden" name="delete_key" value="<?php echo $key; ?>">
                    <button type="submit" name="delete" class="btn-delete">❌ Supprimer</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Récapitulatif du panier -->
        <div class="cart-summary">
            <p><strong>Total : <?php echo $total; ?> €</strong></p>
            <p>Votre solde disponible : <?php echo number_format($balance, 2); ?> €</p>
        </div>
        
        <!-- Formulaire de paiement -->
        <div class="payment-form">
            <h2>Informations de Livraison & Facturation</h2>
            <form action="payment.php" method="POST">
                <h3>Informations de Livraison</h3>
                <label for="shipping_name">Nom :</label>
                <input type="text" name="shipping_name" id="shipping_name" required>
                
                <label for="shipping_address">Adresse :</label>
                <input type="text" name="shipping_address" id="shipping_address" required>
                
                <label for="shipping_city">Ville :</label>
                <input type="text" name="shipping_city" id="shipping_city" required>
                
                <label for="shipping_zip">Code Postal :</label>
                <input type="text" name="shipping_zip" id="shipping_zip" required>
                
                <label for="shipping_country">Pays :</label>
                <input type="text" name="shipping_country" id="shipping_country" required>
                
                <h3>Informations de Facturation</h3>
                <label for="billing_name">Nom :</label>
                <input type="text" name="billing_name" id="billing_name" required>
                
                <label for="billing_address">Adresse :</label>
                <input type="text" name="billing_address" id="billing_address" required>
                
                <label for="billing_city">Ville :</label>
                <input type="text" name="billing_city" id="billing_city" required>
                
                <label for="billing_zip">Code Postal :</label>
                <input type="text" name="billing_zip" id="billing_zip" required>
                
                <label for="billing_country">Pays :</label>
                <input type="text" name="billing_country" id="billing_country" required>
                
                <h2>Méthode de paiement</h2>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="payment_method" value="stripe" checked> Payer avec Stripe
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="balance"> Payer avec mon solde
                    </label>
                </div>
                <button type="submit">Procéder au paiement</button>
            </form>
        </div>
        <a href="index.php" class="btn-secondary">Continuer mes achats</a>
    </div>
</body>
</html>
