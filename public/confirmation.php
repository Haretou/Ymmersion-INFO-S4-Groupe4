<?php
require_once '../config/config.php';
require_once '../vendor/autoload.php'; // Charger Stripe SDK
require_once 'send_email.php'; // Inclure le fichier d'envoi d'email
session_start();

// Clé secrète Stripe (remplace par ta clé réelle)
\Stripe\Stripe::setApiKey('sk_test_51QtVnYDIVWd9Ur2VSfC0PWmSsrFPBl4NQ1yyAYcH3B43vbW8MXgi2M22AapIN2Nge0L70yhFcHN7pD0Vun2axPAo00Hrkhz5MR');

// Vérifier si une session de paiement existe
if (!isset($_GET['session_id'])) {
    header("Location: index.php");
    exit;
}

$session_id = $_GET['session_id'];

try {
    // Récupérer les infos de la session de paiement Stripe
    $checkout_session = \Stripe\Checkout\Session::retrieve($session_id);
    
    if ($checkout_session->payment_status === 'paid') {
        // Récupérer l'utilisateur
        $user_id = $_SESSION["user_id"];
        $user_email = $_SESSION["user_email"];  // Récupérer l'email de l'utilisateur
        
        // Insérer la commande dans la base de données
        $pdo->beginTransaction();
        $query = $pdo->prepare("INSERT INTO orders (user_id, total_price, payment_status, stripe_session_id) VALUES (?, ?, 'paid', ?)");
        $query->execute([$user_id, $checkout_session->amount_total / 100, $session_id]);
        $order_id = $pdo->lastInsertId();
        
        // Insérer les articles du panier dans order_items
        foreach ($_SESSION['cart'] as $item) {
            $query = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $query->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        }
        
        // Sauvegarde des adresses
        $query = $pdo->prepare("INSERT INTO billing_addresses (user_id, order_id, name, address, city, zip, country) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $query->execute([$user_id, $order_id, $_SESSION['billing']['name'], $_SESSION['billing']['address'], $_SESSION['billing']['city'], $_SESSION['billing']['zip'], $_SESSION['billing']['country']]);

        $query = $pdo->prepare("INSERT INTO shipping_addresses (user_id, order_id, name, address, city, zip, country) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $query->execute([$user_id, $order_id, $_SESSION['shipping']['name'], $_SESSION['shipping']['address'], $_SESSION['shipping']['city'], $_SESSION['shipping']['zip'], $_SESSION['shipping']['country']]);
        
        $pdo->commit();

        // Vider le panier après confirmation
        unset($_SESSION['cart']);

        // Envoyer l'email de confirmation de commande
        sendOrderConfirmationEmail($user_email, $order_id);
    } else {
        throw new Exception("Le paiement n'a pas été validé.");
    }
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Confirmation de paiement</title>
</head>
<body>
    <h1>Merci pour votre commande !</h1>
    <p>Votre paiement a bien été pris en compte.</p>
    <p>Nous vous enverrons un e-mail de confirmation avec les détails de votre commande.</p>
    <a href="index.php">Retour à l'accueil</a>
</body>
</html>
