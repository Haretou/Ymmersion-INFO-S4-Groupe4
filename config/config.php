<?php
// Paramètres de connexion à la base de données
$host = 'localhost'; // Change selon ton serveur (ex: 127.0.0.1, localhost, ou une IP distante)
$dbname = 'php_exam_ANTOINE_RAF_GASPARD'; // Le nom de la base de données
$username = 'root'; // Ton utilisateur MySQL (ex: root)
$password = 'root'; // Mot de passe MySQL (vide par défaut sur MAMP)

// Connexion avec PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Définir le mode d'erreur PDO en exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
