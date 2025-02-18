<?php
require_once '../config/config.php';
session_start();

// Vérifier si un ID est fourni dans l'URL
$viewing_user_id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION["user_id"];
$is_own_account = ($viewing_user_id == $_SESSION["user_id"]);

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$viewing_user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur existe
if (!$user) {
    die("Utilisateur introuvable.");
}

// Récupérer les articles publiés par cet utilisateur
$stmtArticles = $pdo->prepare("SELECT * FROM articles WHERE user_id = ?");
$stmtArticles->execute([$viewing_user_id]);
$articles = $stmtArticles->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les achats si c'est l'utilisateur connecté
if ($is_own_account) {
    $stmtPurchases = $pdo->prepare("
        SELECT o.id, o.total_price, o.created_at
        FROM orders o
        WHERE o.user_id = ?
    ");
    $stmtPurchases->execute([$viewing_user_id]);
    $purchases = $stmtPurchases->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Compte de <?php echo htmlspecialchars($user['username']); ?></title>
</head>
<body>
    <h1>Compte de <?php echo htmlspecialchars($user['username']); ?></h1>

    <h2>Informations du compte</h2>
    <p><strong>Nom d'utilisateur :</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>

    <?php if ($is_own_account): ?>
        <h2>Mon solde</h2>
        <p><strong>Votre solde :</strong> <?php echo number_format($user['balance'], 2); ?> €</p>

        <h2>Ajouter de l'argent à mon solde</h2>
        <form action="stripe_payment.php" method="POST">
            <label for="amount">Montant (€) :</label>
            <input type="number" name="amount" id="amount" min="1" required>
            <button type="submit">Ajouter via Stripe</button>
        </form>
    <?php endif; ?>

    <h2>Articles publiés</h2>
    <?php if (count($articles) > 0): ?>
        <ul>
            <?php foreach ($articles as $article): ?>
                <li>
                    <a href="product.php?id=<?php echo $article['id']; ?>">
                        <?php echo htmlspecialchars($article['title']); ?>
                    </a> - <?php echo htmlspecialchars($article['price']); ?> €
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun article publié.</p>
    <?php endif; ?>

    <?php if ($is_own_account): ?>
        <h2>Mes achats</h2>
        <?php if (count($purchases) > 0): ?>
            <ul>
                <?php foreach ($purchases as $purchase): ?>
                    <li>
                        Commande #<?php echo $purchase['id']; ?> - <?php echo $purchase['total_price']; ?> €
                        (Passée le <?php echo $purchase['created_at']; ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Vous n'avez encore rien acheté.</p>
        <?php endif; ?>

        <h2>Modifier mes informations</h2>
        <form action="update_account.php" method="post">
            <label for="new_email">Nouvel email :</label>
            <input type="email" name="new_email" id="new_email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="new_password">Nouveau mot de passe :</label>
            <input type="password" name="new_password" id="new_password" placeholder="Laisser vide pour ne pas changer">

            <button type="submit">Mettre à jour</button>
        </form>
    <?php endif; ?>

    <br>
    <a href="index.php"><button>🏠 Revenir à l'accueil</button></a>
    <a href="logout.php"><button>🚪 Se déconnecter</button></a>

</body>
</html>
