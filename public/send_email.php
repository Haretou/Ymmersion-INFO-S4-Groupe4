<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendOrderConfirmationEmail($userEmail, $orderId, $totalPrice, $cartItems, $billingAddress, $shippingAddress) {
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io'; // Exemple, change selon ton fournisseur SMTP
        $mail->SMTPAuth = true;
        $mail->Username = '58c95b2a3d8817'; // Ton nom d'utilisateur SMTP
        $mail->Password = '********2b24'; // Ton mot de passe SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Destinataire
        $mail->setFrom('no-reply@tonsite.com', 'E-Commerce');
        $mail->addAddress($userEmail); // Email de l'utilisateur connecté

        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = 'Confirmation de commande';
        
        // Construire le corps de l'email avec les détails de la commande
        $body = "<h1>Merci pour votre commande !</h1>";
        $body .= "<p>Votre commande #{$orderId} a bien été reçue. Nous vous remercions de votre achat !</p>";
        $body .= "<h2>Détails de la commande :</h2>";
        $body .= "<p><strong>Montant total :</strong> {$totalPrice}€</p>";
        
        // Liste des produits
        $body .= "<h3>Articles commandés :</h3><ul>";
        foreach ($cartItems as $item) {
            $body .= "<li>{$item['quantity']} x {$item['name']} - {$item['price']}€</li>";
        }
        $body .= "</ul>";

        // Adresse de facturation
        $body .= "<h3>Adresse de facturation :</h3><p>";
        $body .= "{$billingAddress['name']}<br>{$billingAddress['address']}<br>{$billingAddress['city']}, {$billingAddress['zip']}<br>{$billingAddress['country']}";
        $body .= "</p>";

        // Adresse de livraison
        $body .= "<h3>Adresse de livraison :</h3><p>";
        $body .= "{$shippingAddress['name']}<br>{$shippingAddress['address']}<br>{$shippingAddress['city']}, {$shippingAddress['zip']}<br>{$shippingAddress['country']}";
        $body .= "</p>";

        // Ajouter un message de remerciement
        $body .= "<p>Nous vous enverrons un email dès que votre commande sera expédiée.</p>";

        // Ajouter le corps HTML au mail
        $mail->Body = $body;

        // Envoi de l'email
        $mail->send();
    } catch (Exception $e) {
        echo "Le message n'a pas pu être envoyé. Mailer Error: {$mail->ErrorInfo}";
    }
}

?>
