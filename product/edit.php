<?php
require_once '../config/config.php';
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Article non trouvé");
}

$article_id = $_GET['id'];

// Récupérer les détails de l'article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die("Article non trouvé");
}

// Vérifier si l'utilisateur est bien le propriétaire de l'article
if ($article['user_id'] != $_SESSION["user_id"]) {
    die("Vous ne pouvez pas modifier cet article.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Mettre à jour l'article
    $stmt = $pdo->prepare("UPDATE articles SET title = ?, description = ?, price = ? WHERE id = ?");
    $stmt->execute([$title, $description, $price, $article_id]);

    // Rediriger vers la page de l'article
    header("Location: product.php?id=" . $article_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'article</title>
</head>
<body>
    <h1>Modifier l'article</h1>
    <form action="edit.php?id=<?php echo $article['id']; ?>" method="POST">
        <label for="title">Titre :</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($article['title']); ?>" required>

        <label for="description">Description :</label>
        <textarea name="description" id="description" required><?php echo htmlspecialchars($article['description']); ?></textarea>

        <label for="price">Prix :</label>
        <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($article['price']); ?>" required>

        <button type="submit">Mettre à jour l'article</button>
    </form>
</body>
</html>
