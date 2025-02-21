<?php
require_once '../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Vérifier si l'email ou le username existe déjà
    $checkUser = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $checkUser->execute([$email, $username]);

    if ($checkUser->rowCount() > 0) {
        echo "Erreur : Cet email ou nom d'utilisateur existe déjà.";
    } else {
        // Insérer le nouvel utilisateur
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashedPassword])) {
            session_start();
            $_SESSION["user_id"] = $pdo->lastInsertId();
            $_SESSION["username"] = $username;
            header("Location: index.php"); // Redirection vers l'accueil
            exit;
        } else {
            echo "Erreur lors de l'inscription.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Inscription</title>
</head>
<body>
    <h2>Inscription</h2>
    <form method="post">
        <label>Nom d'utilisateur :</label>
        <input type="text" name="username" required>
        <label>Email :</label>
        <input type="email" name="email" required>
        <label>Mot de passe :</label>
        <input type="password" name="password" required>
        <button type="submit">S'inscrire</button>
    </form>
</body>
</html>
remove.php: <?php
require_once '../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Vérifier si l'ID de l'article est passé en paramètre
if (isset($_GET['article_id'])) {
    $article_id = $_GET['article_id'];
    $user_id = $_SESSION["user_id"];

    // Supprimer l'article du panier
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND article_id = ?");
    $stmt->execute([$user_id, $article_id]);

    // Rediriger vers la page du panier
    header("Location: cart.php");
    exit;
} else {
    echo "Erreur: L'article n'a pas été trouvé.";
}
?>