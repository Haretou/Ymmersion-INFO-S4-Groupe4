<?php
require_once '../config/config.php';
require_once '../vendor/autoload.php'; // Assure-toi que Stripe est installé via Composer

\Stripe\Stripe::setApiKey('sk_test_51QtVnYDIVWd9Ur2VSfC0PWmSsrFPBl4NQ1yyAYcH3B43vbW8MXgi2M22AapIN2Nge0L70yhFcHN7pD0Vun2axPAo00Hrkhz5MR'); // Remplace par ta clé secrète Stripe

session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $amount = intval($_POST["amount"]) * 100; // Convertir en centimes

    try {
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Ajout de fonds',
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'http://localhost:8888/php_exam/Ymmersion-INFO-S4-Groupe4/public/payment_success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost:8888/php_exam/Ymmersion-INFO-S4-Groupe4/public/account.php',
            'metadata' => [
                'user_id' => $_SESSION["user_id"],
            ],
        ]);

        header("Location: " . $checkout_session->url);
        exit;
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
