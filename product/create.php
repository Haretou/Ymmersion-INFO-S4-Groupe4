<?php
require_once '../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Récupérer les catégories
$stmt = $pdo->prepare("SELECT * FROM categories");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Vérification des champs
    if (empty($_POST['title']) || empty($_POST['description']) || empty($_POST['price']) || empty($_POST['stock']) || empty($_FILES['image']['name']) || empty($_POST['category_id'])) {
        $error = "Tous les champs sont requis, y compris la catégorie.";

    } else {
        // Récupère le  formulaire
        $title = $_POST['title'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $category_id = $_POST['category_id'];

        // Traitement de l'image
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_name = $_FILES['image']['name'];
            $image_tmp_name = $_FILES['image']['tmp_name'];
            $image_size = $_FILES['image']['size'];
            $image_type = $_FILES['image']['type'];

            // Vérifier l'extension et la taille de l'image
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5 Mo max

            if (in_array($image_type, $allowed_types) && $image_size <= $max_size) {
                // Créer un nom unique pour l'image
                $image_name_unique = uniqid('img_', true) . '.' . pathinfo($image_name, PATHINFO_EXTENSION);
                $upload_dir = '../uploads/';
                $image_path = $upload_dir . $image_name_unique;

                // Déplacer l'image vers le dossier d'upload
                if (move_uploaded_file($image_tmp_name, $image_path)) {
                    // L'image a été téléchargée avec succès
                } else {
                    die("Erreur lors du téléchargement de l'image.");
                }
            } else {
                die("L'image doit être au format JPEG, PNG ou GIF et ne pas dépasser 5 Mo.");
            }
        } else {
            die("Aucune image téléchargée ou erreur lors du téléchargement.");
        }

        // Préparer et exécuter la requête d'insertion
        $stmt = $pdo->prepare("INSERT INTO articles (title, description, price, stock, image, user_id, category_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $price, $stock, $image_path, $_SESSION['user_id'], $category_id]);

        // Rediriger vers la page d'accueil ou autre page après la création
        header("Location: http://localhost:8888/php_exam/Ymmersion-INFO-S4-Groupe4/public/index.php");
        exit;
    }
}
?>

<!-- Affichage du message d'erreur si nécessaire -->
<?php if (isset($error)): ?>
    <p style="color: red;"><?php echo $error; ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <label for="title">Titre :</label>
    <input type="text" name="title" id="title" required>

    <label for="description">Description :</label>
    <textarea name="description" id="description" required></textarea>

    <label for="price">Prix :</label>
    <input type="text" name="price" id="price" required>

    <label for="stock">Stock :</label>
    <input type="number" name="stock" id="stock" required>

    <label for="category_id">Catégorie :</label>
    <select name="category_id" id="category_id" required>
        <option value="">Sélectionner une catégorie</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
        <?php endforeach; ?>
    </select>

    <label for="image">Image de l'article :</label>
    <input type="file" name="image" id="image" accept="image/*" required>

    <button type="submit">Créer l'article</button>
</form>
