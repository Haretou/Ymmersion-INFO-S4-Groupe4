<?php
require '../vendor/autoload.php';
require_once '../config/config.php';
session_start();
;


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Vérifier si le panier n'est pas vide
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

// Vérifier si une méthode de paiement est sélectionnée
if (!isset($_POST['payment_method'])) {
    echo "Veuillez sélectionner une méthode de paiement.";
    exit;
}

$payment_method = $_POST['payment_method'];

// Vérifier et récupérer les adresses
if (!isset($_SESSION['shipping']) || !isset($_SESSION['billing'])) {
    echo "Les informations de livraison et de facturation sont requises.";
    exit;
}

// Sécurisation des entrées
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

// Insérer l'adresse de livraison
$shipping_stmt = $pdo->prepare("INSERT INTO shipping_addresses (user_id, name, address, city, zip, country) VALUES (?, ?, ?, ?, ?, ?)");
$shipping_stmt->execute([
    $user_id,
    sanitize_input($_SESSION['shipping']['name']),
    sanitize_input($_SESSION['shipping']['address']),
    sanitize_input($_SESSION['shipping']['city']),
    sanitize_input($_SESSION['shipping']['zip']),
    sanitize_input($_SESSION['shipping']['country'])
]);
$shipping_id = $pdo->lastInsertId();

// Insérer l'adresse de facturation
$billing_stmt = $pdo->prepare("INSERT INTO billing_addresses (user_id, name, address, city, zip, country) VALUES (?, ?, ?, ?, ?, ?)");
$billing_stmt->execute([
    $user_id,
    sanitize_input($_SESSION['billing']['name']),
    sanitize_input($_SESSION['billing']['address']),
    sanitize_input($_SESSION['billing']['city']),
    sanitize_input($_SESSION['billing']['zip']),
    sanitize_input($_SESSION['billing']['country'])
]);
$billing_id = $pdo->lastInsertId();

// Calcul du total du panier
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    if (!isset($item['id'])) {
        echo "Erreur : un produit du panier ne contient pas d'ID.";
        exit;
    }
    $total += $item['price'] * $item['quantity'];
}

if ($payment_method === "balance") {
    // Vérifier si le solde est suffisant
    if ($balance >= $total) {
        // Déduire le montant du solde
        $new_balance = $balance - $total;
        $update_balance = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
        $update_balance->execute([$new_balance, $user_id]);

        // Insérer la commande avec les IDs des adresses
        $order_stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, payment_method, shipping_id, billing_id) VALUES (?, ?, 'balance', ?, ?)");
        $order_stmt->execute([$user_id, $total, $shipping_id, $billing_id]);

        // Récupérer l'ID de la commande
        $order_id = $pdo->lastInsertId();
        
        // Insérer les articles commandés
        foreach ($_SESSION['cart'] as $key => $item) {
            if (!isset($item['id'], $item['quantity'], $item['price'])) {
                echo "Erreur : un produit du panier a des données manquantes.";
                exit;
            }
        
            $insert_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $insert_item->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        }
        

        // Vider le panier
        $_SESSION['cart'] = [];

        // Redirection vers la page de succès
        header("Location: payment_success.php");
        exit;
    } else {
        echo "Solde insuffisant pour effectuer l'achat.";
        exit;
    }
} else {
    // Paiement via Stripe
    \Stripe\Stripe::setApiKey('sk_test_51QtVnYDIVWd9Ur2VSfC0PWmSsrFPBl4NQ1yyAYcH3B43vbW8MXgi2M22AapIN2Nge0L70yhFcHN7pD0Vun2axPAo00Hrkhz5MR');

    $line_items = [];
    foreach ($_SESSION['cart'] as $item) {
        $line_items[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => ['name' => $item['title']],
                'unit_amount' => $item['price'] * 100, // Stripe utilise les centimes
            ],
            'quantity' => $item['quantity'],
        ];
    }

    try {
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $line_items,
            'mode' => 'payment',
            'customer_email' => $_SESSION['email'],
            'success_url' => 'http://localhost:8888/php_exam/Ymmersion-INFO-S4-Groupe4/public/payment_success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost:8888/php_exam/Ymmersion-INFO-S4-Groupe4/public/cart.php',
        ]);

        header("Location: " . $checkout_session->url);
        exit;
    } catch (Exception $e) {
        echo "Erreur Stripe : " . $e->getMessage();
        exit;
    }
}
?>
