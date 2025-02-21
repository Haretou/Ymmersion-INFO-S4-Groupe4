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
        $error_message = "Erreur : Cet email ou nom d'utilisateur existe déjà.";
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
            $error_message = "Erreur lors de l'inscription.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Style global */
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(120deg, #f06, #48c6ef);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }

        /* Conteneur principal */
        .register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
        }

        /* Boîte d'inscription */
        .register-box {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        /* Titre */
        h2 {
            margin-bottom: 25px;
            color: #333;
            font-weight: 600;
            font-size: 1.8em;
        }

        /* Champs de formulaire */
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            font-size: 1em;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        .input-group input {
            width: 100%;
            padding: 14px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            background-color: #f9f9f9;
            transition: border 0.3s ease;
        }

        .input-group input:focus {
            border-color: #48c6ef;
            background-color: #f1f1f1;
            outline: none;
        }

        /* Bouton d'inscription */
        button {
            background-color: #48c6ef;
            color: white;
            padding: 14px;
            border: none;
            width: 100%;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease;
            margin-top: 20px; /* Espacement du bouton */
        }

        button:hover {
            background-color: #36a9cc;
            transform: translateY(-2px);
        }

        /* Message d'erreur */
        .error {
            color: red;
            font-size: 1em;
            margin-bottom: 15px;
            padding: 12px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
        }

        /* Lien de connexion */
        .login-link {
            margin-top: 20px;
            font-size: 1em;
            text-align: center;
        }

        .login-link a {
            color: #48c6ef;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Animation du gradient */
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Responsive */
        @media (max-width: 600px) {
            .register-box {
                padding: 30px;
                width: 90%;
            }

            .input-group input, button {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <h2>Inscription</h2>

            <?php if (isset($error_message)): ?>
                <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="input-group">
                    <label for="username">Nom d'utilisateur :</label>
                    <input type="text" name="username" id="username" required>
                </div>
                <div class="input-group">
                    <label for="email">Email :</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="input-group">
                    <label for="password">Mot de passe :</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit">S'inscrire</button>
            </form>

            <div class="login-link">
                <p>Vous avez déjà un compte ? <a href="login.php">Se connecter</a></p>
            </div>
        </div>
    </div>
</body>
</html>
