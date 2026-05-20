<?php
require_once 'db.php';

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT fichier_original, nom, prenom FROM cv WHERE id = ?");
$stmt->execute([$id]);
$cv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cv || empty($cv['fichier_original'])) {
    die("Fichier original introuvable.");
}

$filepath = __DIR__ . '/' . $cv['fichier_original'];

if (!file_exists($filepath)) {
    die("Le fichier n'existe plus sur le serveur.");
}

// Récupérer l'extension
$extension = pathinfo($filepath, PATHINFO_EXTENSION);

// Créer un nouveau nom
$nom = !empty($cv['nom']) ? $cv['nom'] : 'cv';
$prenom = !empty($cv['prenom']) ? $cv['prenom'] : 'original';
$nouveau_nom = $nom . '_' . $prenom . '_original.' . $extension;

// Forcer le téléchargement avec le nouveau nom
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $nouveau_nom . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
?>