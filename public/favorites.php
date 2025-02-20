<?php
require_once '../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Vous devez être connecté pour accéder à vos favoris.");
}

$user_id = $_SESSION['user_id'];

// Récupérer les articles favoris de l'utilisateur
$stmt = $pdo->prepare("
    SELECT a.id, a.title, a.price, a.description, a.image
    FROM favorites f
    JOIN articles a ON f.article_id = a.id
    WHERE f.user_id = ?
");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/style.css">
    <title>Mes favoris</title>
</head>
<body>
    <h1>Vos articles favoris</h1>

    <?php if (count($favorites) > 0): ?>
        <ul>
            <?php foreach ($favorites as $favorite): ?>
                <li>
                    <h3><?php echo htmlspecialchars($favorite['title']); ?></h3>
                    <!-- Affichage de l'image de l'article -->
                    <?php if (!empty($favorite['image'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($favorite['image']); ?>" alt="Image de l'article" width="200">
                    <?php endif; ?>
                    <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($favorite['description'])); ?></p>
                    <p><strong>Prix :</strong> <?php echo htmlspecialchars($favorite['price']); ?> €</p>
                    <form action="../product/product.php?id=<?php echo $favorite['id']; ?>" method="GET">
                        <button type="submit">Voir l'article</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun article dans vos favoris.</p>
    <?php endif; ?>

    <br>
    <a href="../public/index.php">Retour à la liste des articles</a>
</body>
</html>
