<?php
require '../vendor/autoload.php';
require_once '../config/config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Vérifier si le panier est vide
if (empty($_SESSION['cart'])) {
    echo "<h1>Votre panier est vide.</h1>";
    exit;
}

// Connexion à la base de données
$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT email, username, balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$email = $user['email'];
$userName = "{$user['username']}";
$balance = $user['balance'];

// Vérifier la méthode de paiement
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

function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

// Insérer les adresses
$shipping_stmt = $pdo->prepare("INSERT INTO shipping_addresses (user_id, name, address, city, zip, country) VALUES (?, ?, ?, ?, ?, ?)");
$shipping_stmt->execute([$user_id, sanitize_input($_SESSION['shipping']['name']), sanitize_input($_SESSION['shipping']['address']), sanitize_input($_SESSION['shipping']['city']), sanitize_input($_SESSION['shipping']['zip']), sanitize_input($_SESSION['shipping']['country'])]);
$shipping_id = $pdo->lastInsertId();

$billing_stmt = $pdo->prepare("INSERT INTO billing_addresses (user_id, name, address, city, zip, country) VALUES (?, ?, ?, ?, ?, ?)");
$billing_stmt->execute([$user_id, sanitize_input($_SESSION['billing']['name']), sanitize_input($_SESSION['billing']['address']), sanitize_input($_SESSION['billing']['city']), sanitize_input($_SESSION['billing']['zip']), sanitize_input($_SESSION['billing']['country'])]);
$billing_id = $pdo->lastInsertId();

// Récupérer les informations complètes des adresses
$shipping_stmt = $pdo->prepare("SELECT * FROM shipping_addresses WHERE id = ?");
$shipping_stmt->execute([$shipping_id]);
$shipping_address = $shipping_stmt->fetch(PDO::FETCH_ASSOC);

$billing_stmt = $pdo->prepare("SELECT * FROM billing_addresses WHERE id = ?");
$billing_stmt->execute([$billing_id]);
$billing_address = $billing_stmt->fetch(PDO::FETCH_ASSOC);

// Calcul du total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Gestion du paiement
if ($payment_method === "balance") {
    if ($balance >= $total) {
        $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?")->execute([$balance - $total, $user_id]);
    } else {
        echo "Solde insuffisant.";
        exit;
    }
} else {
    \Stripe\Stripe::setApiKey('sk_test_51QtVnYDIVWd9Ur2VSfC0PWmSsrFPBl4NQ1yyAYcH3B43vbW8MXgi2M22AapIN2Nge0L70yhFcHN7pD0Vun2axPAo00Hrkhz5MR');
    try {
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => array_map(fn($item) => [
                'price_data' => ['currency' => 'eur', 'product_data' => ['name' => $item['title']], 'unit_amount' => $item['price'] * 100],
                'quantity' => $item['quantity'],
            ], $_SESSION['cart']),
            'mode' => 'payment',
            'customer_email' => $email,
            'success_url' => 'http://localhost:8888/php_exam/Ymmersion-INFO-S4-Groupe4/public/payment_success.php',
            'cancel_url' => 'http://localhost:8888/php_exam/Ymmersion-INFO-S4-Groupe4/public/cart.php',
        ]);
        header("Location: " . $checkout_session->url);
        exit;
    } catch (Exception $e) {
        echo "Erreur Stripe : " . $e->getMessage();
        exit;
    }
}

// Insérer la commande
$order_stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, payment_method, shipping_id, billing_id) VALUES (?, ?, ?, ?, ?)");
$order_stmt->execute([$user_id, $total, $payment_method, $shipping_id, $billing_id]);
$order_id = $pdo->lastInsertId();

// Insérer les articles commandés
foreach ($_SESSION['cart'] as $item) {
    $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)")->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
}

// Vider le panier
$_SESSION['cart'] = [];

// Envoi de l'email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'antoinecabanes4@gmail.com';
    $mail->Password = 'vdld ttgk omzc qtxf';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom('antoinecabanes4@gmail.com', 'Pokéstore');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = "Facture de votre commande #$order_id";
    
    $mail->Body = "<h2>Merci pour votre achat, $userName !</h2>
        <p>Votre commande a été confirmée.</p>
        <h3>Détails de votre commande :</h3>
        <table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
            <tr>
                <th>Article</th>
                <th>Image</th>
                <th>Prix Unitaire</th>
                <th>Quantité</th>
                <th>Total</th>
            </tr>";
    
    foreach ($_SESSION['cart'] as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $mail->Body .= "<tr>
            <td>{$item['title']}</td>
            <td><img src='{$item['image']}' alt='{$item['title']}' style='width: 50px;'></td>
            <td>{$item['price']}€</td>
            <td>{$item['quantity']}</td>
            <td>{$item_total}€</td>
        </tr>";
    }
    
    $mail->Body .= "</table>
        <p><strong>Total :</strong> {$total}€</p>
        <h3>Adresse de facturation :</h3>
        <p>{$billing_address['name']}</p>
        <p>{$billing_address['address']}</p>
        <p>{$billing_address['city']}, {$billing_address['zip']}</p>
        <p>{$billing_address['country']}</p>
        <h3>Adresse de livraison :</h3>
        <p>{$shipping_address['name']}</p>
        <p>{$shipping_address['address']}</p>
        <p>{$shipping_address['city']}, {$shipping_address['zip']}</p>
        <p>{$shipping_address['country']}</p>
        <p>Nous espérons vous revoir bientôt !</p>";
    
    $mail->send();
} catch (Exception $e) {
    echo "Erreur d'envoi de l'email : {$mail->ErrorInfo}";
}

// Redirection
header("Location: payment_success.php");
exit;
?>
