<?php
require_once '../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les articles publiés par l'utilisateur
$stmtArticles = $pdo->prepare("SELECT * FROM articles WHERE user_id = ?");
$stmtArticles->execute([$user_id]);
$articles = $stmtArticles->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte</title>
</head>
<body>
    <h1>Mon Compte</h1>

    <h2>Informations de votre compte</h2>
    <p><strong>Nom d'utilisateur :</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>

    <h2>Mes articles</h2>
    <?php if (count($articles) > 0): ?>
        <ul>
            <?php foreach ($articles as $article): ?>
                <li>
                    <a href="product.php?id=<?php echo $article['id']; ?>">
                        <?php echo htmlspecialchars($article['title']); ?>
                    </a> 
                    - <?php echo htmlspecialchars($article['price']); ?> €
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun article publié.</p>
    <?php endif; ?>

    <h2>Modifier mes informations</h2>
    <form action="update_account.php" method="post">
        <label for="new_email">Nouvel email :</label>
        <input type="email" name="new_email" id="new_email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label for="new_password">Nouveau mot de passe :</label>
        <input type="password" name="new_password" id="new_password" placeholder="Laisser vide pour ne pas changer">

        <button type="submit">Mettre à jour</button>
    </form>

    <a href="logout.php">Se déconnecter</a>
</body>
</html>
