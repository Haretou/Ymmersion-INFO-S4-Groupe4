<?php
require_once '../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Récupérer l'id de l'utilisateur connecté
$user_id = $_SESSION["user_id"];

// Récupérer les nouvelles informations soumises via le formulaire
$new_email = isset($_POST['new_email']) ? trim($_POST['new_email']) : '';
$new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';

// Vérification si l'email est déjà pris
if (!empty($new_email)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$new_email]);
    if ($stmt->rowCount() > 0) {
        // Si l'email est déjà pris, afficher une erreur
        die("L'email est déjà utilisé. Veuillez en choisir un autre.");
    }
}

// Si un nouveau mot de passe est fourni, le hacher avant de l'enregistrer
if (!empty($new_password)) {
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
} else {
    $hashed_password = null;
}

// Préparer la requête de mise à jour
if ($hashed_password) {
    // Mettre à jour l'email et le mot de passe
    $stmt = $pdo->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
    $stmt->execute([$new_email, $hashed_password, $user_id]);
} else {
    // Mettre à jour uniquement l'email
    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
    $stmt->execute([$new_email, $user_id]);
}

// Rediriger l'utilisateur vers son compte avec un message de succès
header("Location: account.php?success=1");
exit;
?>
