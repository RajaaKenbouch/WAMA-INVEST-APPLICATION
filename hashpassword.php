<?php
require_once 'db.php';

// Récupérer tous les utilisateurs
$stmt = $pdo->query("SELECT id, password FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    // Si le mot de passe n'est pas déjà haché (moins de 60 caractères)
    if (strlen($user['password']) < 60) {
        $hashed = password_hash($user['password'], PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$hashed, $user['id']]);
        echo "Mot de passe haché pour l'utilisateur ID: " . $user['id'] . "<br>";
    }
}

echo "Terminé !";
?>