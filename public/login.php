<?php
require_once '../config/config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        header("Location: index.php"); // Redirection vers l'accueil
        exit;
    } else {
        $error_message = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Connexion</h2>

            <?php if (isset($error_message)): ?>
                <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="input-group">
                    <label for="email">Email :</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="input-group">
                    <label for="password">Mot de passe :</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit">Se connecter</button>
            </form>

            <div class="register-link">
                <p>Pas encore de compte ? <a href="register.php">Créer un compte</a></p>
            </div>
        </div>
    </div>

    <style>
        /* Style global */
        body {
            font-family: 'Arial', sans-serif;
            background: url('background.jpg') no-repeat center center/cover;
            backdrop-filter: blur(5px);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }

        /* Conteneur principal */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
        }

        /* Boîte de connexion */
        .login-box {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        /* Titre */
        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        /* Champs de formulaire */
        .input-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .input-group label {
            font-size: 0.9em;
            color: #333;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            background-color: #f9f9f9;
        }

        .input-group input:focus {
            border-color: #007bff;
            background-color: #f1f1f1;
            outline: none;
        }

        /* Bouton de connexion */
        button {
            background-color: #007bff;
            color: white;
            padding: 12px;
            border: none;
            width: 100%;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Message d'erreur */
        .error {
            color: red;
            font-size: 0.9em;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
        }

        /* Lien d'inscription */
        .register-link {
            margin-top: 20px;
            font-size: 0.9em;
        }

        .register-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .login-box {
                padding: 20px;
            }

            .input-group input, button {
                font-size: 0.9em;
            }
        }
    </style>
</body>
</html>
