<?php
// Paramètres de connexion à la DB
$host = 'localhost'; 
$dbname = 'php_exam_ANTOINE_RAF_GASPARD'; // DB name
$username = 'root'; 
$password = 'root'; 

// Connexion avec PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Définir le mode d'erreur PDO en exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
