<?php
require_once 'config/config.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    echo "Connexion réussie ! Nombre d'utilisateurs : " . $userCount;
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
