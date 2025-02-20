<?php
session_start();
require '../config/config.php';

// Vérification de l'authentification et du rôle administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Vérifier si un ID utilisateur est fourni
if (!isset($_GET['id'])) {
    header('Location: admin.php');
    exit();
}

$user_id = intval($_GET['id']);

// Récupération des informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: admin.php');
    exit();
}

// Mise à jour des informations utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $role = $_POST['role'];

    $stmt = $pdo->prepare("UPDATE users SET email = ?, role = ? WHERE id = ?");
    $stmt->execute([$email, $role, $user_id]);

    header('Location: admin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>Modifier Utilisateur</h1>
    <form method="post">
        <label>Email :</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        
        <label>Rôle :</label>
        <select name="role">
            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
        </select>
        
        <button type="submit">Mettre à jour</button>
    </form>
    <a href="admin.php">Retour</a>
</body>
</html>
