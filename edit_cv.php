<?php
require_once 'inc/auth.php';
require_once 'db.php';

$cv_id = $_GET['id'] ?? 0;

if (!$cv_id) {
    die("CV non spécifié");
}

// Récupérer le CV
$stmt = $pdo->prepare("SELECT * FROM cv WHERE id = ?");
$stmt->execute([$cv_id]);
$cv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cv) {
    die("CV introuvable");
}

// Récupérer les diplômes
$stmtDiplomes = $pdo->prepare("SELECT * FROM diplomes WHERE cv_id = ?");
$stmtDiplomes->execute([$cv_id]);
$diplomes = $stmtDiplomes->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les expériences
$stmtExperiences = $pdo->prepare("SELECT * FROM experiences WHERE cv_id = ?");
$stmtExperiences->execute([$cv_id]);
$experiences = $stmtExperiences->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les langues
$stmtLangues = $pdo->prepare("SELECT * FROM langues WHERE cv_id = ?");
$stmtLangues->execute([$cv_id]);
$langues = $stmtLangues->fetchAll(PDO::FETCH_ASSOC);

// Certifications (depuis cv.certifications)
$certifications = $cv['certifications'] ?? '';
$certifications_list = !empty($certifications) ? explode("\n", $certifications) : [];

// Construction des données pour le formulaire
$editData = [
    'username' => $cv['username'] ?? '',
    'nom' => $cv['nom'] ?? '',
    'prenom' => $cv['prenom'] ?? '',
    'poste' => $cv['poste'] ?? '',
    'email' => $cv['email'] ?? '',
    'telephone' => $cv['telephone'] ?? '',
    'competences' => $cv['competences'] ?? '',
    'langues' => implode("\n", array_column($langues, 'langue')),
    'certifications' => $certifications_list,
    'annees_experience' => $cv['annees_experience'] ?? '0 an',
    'logo_type' => $cv['logo_type'] ?? 'invest',
    'fichier_original' => $cv['fichier_original'] ?? '',
    'diplomes' => $diplomes,
    'experiences' => $experiences
];

// Stocker en session
$_SESSION['import_data'] = $editData;
$_SESSION['import_logo_type'] = $editData['logo_type'];
$_SESSION['import_texte_brut'] = '';

header("Location: creationCV.php");
exit;
?>