<?php
require '../vendor/autoload.php'; // Charger Stripe
require_once '../config/config.php';
session_start();

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

// Clé API Stripe (Remplace par ta vraie clé en prod)
\Stripe\Stripe::setApiKey('sk_test_51QtVnYDIVWd9Ur2VSfC0PWmSsrFPBl4NQ1yyAYcH3B43vbW8MXgi2M22AapIN2Nge0L70yhFcHN7pD0Vun2axPAo00Hrkhz5MR');

// Fonction pour sécuriser les entrées utilisateur
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupérer et sécuriser les adresses
    $_SESSION['shipping'] = [
        'name' => sanitize_input($_POST['shipping_name']),
        'address' => sanitize_input($_POST['shipping_address']),
        'city' => sanitize_input($_POST['shipping_city']),
        'zip' => sanitize_input($_POST['shipping_zip']),
        'country' => sanitize_input($_POST['shipping_country'])
    ];

    $_SESSION['billing'] = [
        'name' => sanitize_input($_POST['billing_name']),
        'address' => sanitize_input($_POST['billing_address']),
        'city' => sanitize_input($_POST['billing_city']),
        'zip' => sanitize_input($_POST['billing_zip']),
        'country' => sanitize_input($_POST['billing_country'])
    ];
}

// Calcul du total du panier
$total = 0;
$line_items = [];

foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
    
    // Ajouter chaque article au paiement Stripe
    $line_items[] = [
        'price_data' => [
            'currency' => 'eur',
            'product_data' => ['name' => $item['title']],
            'unit_amount' => $item['price'] * 100, // Stripe utilise les centimes
        ],
        'quantity' => $item['quantity'],
    ];
}

// Création d'une session Stripe Checkout
try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'customer_email' => $_SESSION['email'], // Optionnel : Afficher l’email de l'utilisateur
        'success_url' => 'http://localhost/confirmation.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost/cart.php',
    ]);

    // Rediriger vers Stripe
    header("Location: " . $checkout_session->url);
    exit;

} catch (Exception $e) {
    echo "Erreur Stripe : " . $e->getMessage();
    exit;
}
?>