    <?php
    require_once '../config/config.php';
    require_once '../vendor/autoload.php';

    \Stripe\Stripe::setApiKey('sk_test_51QtVnYDIVWd9Ur2VSfC0PWmSsrFPBl4NQ1yyAYcH3B43vbW8MXgi2M22AapIN2Nge0L70yhFcHN7pD0Vun2axPAo00Hrkhz5MR'); // Ta clé secrète Stripe

    session_start();
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }

    if (isset($_GET["session_id"])) {
        $session = \Stripe\Checkout\Session::retrieve($_GET["session_id"]);
        
        if ($session && $session->payment_status === "paid") {
            $user_id = $_SESSION["user_id"];
            $amount = $session->amount_total / 100; // Convertir en euros

            // Mettre à jour le solde de l'utilisateur
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$amount, $user_id]);

            // ✅ Redirection automatique vers account.php après paiement réussi
            header("Location: account.php");
            exit;
        } else {
            echo "<h1>Erreur de paiement</h1>";
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Paiement Réussi</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <h1>Merci pour votre achat !</h1>
        <p>Votre paiement a été approuvé et votre commande est en cours de traitement.</p>
        <p>Vous pouvez consulter vos commandes dans votre <a href="account.php">espace personnel</a>.</p>
        <a href="index.php">Retour à l'accueil</a>
    </body>
    </html>

