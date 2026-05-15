<?php
$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$poste = $_POST['poste'] ?? '';
$email = $_POST['email'] ?? '';
$telephone = $_POST['telephone'] ?? '';

$competences = nl2br($_POST['competences'] ?? '');
$certifications_raw = $_POST['certifications'] ?? '';
if (is_array($certifications_raw)) {
    $certifications_list = $certifications_raw;
    $certifications = implode("\n", $certifications_list);
} else {
    $certifications = nl2br($certifications_raw);
    $certifications_list = array_filter(array_map('trim', explode("\n", $certifications_raw)), fn($c) => !empty($c));
}
$langues = nl2br($_POST['langues'] ?? '');


$diplome_html = '';
if (!empty($_POST['diplome_date']) && !empty($_POST['diplome_titre']) && !empty($_POST['diplome_ecole'])) {
    for ($i = 0; $i < count($_POST['diplome_date']); $i++) {
        $date = htmlspecialchars($_POST['diplome_date'][$i]);
        $titre = htmlspecialchars($_POST['diplome_titre'][$i]);
        $ecole = htmlspecialchars($_POST['diplome_ecole'][$i]);
        if (!empty($date) || !empty($titre) || !empty($ecole)) {
            $diplome_html .= "<p>• <strong>$date</strong> - $titre<br><em>$ecole</em></p>";
        }
    }
}


$experience_html = '';
if (!empty($_POST['exp_date']) && !empty($_POST['exp_poste']) && !empty($_POST['exp_entreprise'])) {
    for ($i = 0; $i < count($_POST['exp_date']); $i++) {
        $date = htmlspecialchars($_POST['exp_date'][$i]);
        $poste_exp = htmlspecialchars($_POST['exp_poste'][$i]);
        $entreprise = htmlspecialchars($_POST['exp_entreprise'][$i]);
        $description = htmlspecialchars($_POST['exp_description'][$i] ?? '');
        $outils = htmlspecialchars($_POST['exp_outils'][$i] ?? '');
        
        if (!empty($date) || !empty($poste_exp) || !empty($entreprise)) {
            $experience_html .= "
            <div style='margin-bottom: 15px;'>
                <p>• <strong>$date</strong> : <strong>$poste_exp</strong> - $entreprise</p>
                <p>$description</p>
                <p><em><strong>Outils</strong> : $outils</em></p>
            </div>
            ";
        }
    }
}


$logo_type = $_POST['logo_type'] ?? 'link';

if ($logo_type === 'invest') {
    $logo_path = __DIR__ . '/images/logo WAMA.png';
} else {
    $logo_path = __DIR__ . '/images/logo wama link.png';
}

if (file_exists($logo_path)) {
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
} else {
    $logo_base64 = ''; 
}


require_once 'db.php';

$stmt = $pdo->prepare("INSERT INTO cv (nom, prenom, poste, email, telephone, competences, diplomes, experiences, langues, certifications, logo_type) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->execute([
    $nom, $prenom, $poste, $email, $telephone,
    $competences, $diplome_html, $experience_html, $langues, $certifications,
    $logo_type
]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Aperçu CV WAMA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div>

    <div class="cv">
        <div class="header">
            <img src="<?= $logo_base64 ?>" class="logo" alt="logo">
            <div class="contact-info">
                ☎ +(212) 520 673 877<br>
                ✉ info@wama-invest.com
            </div>
        </div>

        <h1><?= htmlspecialchars(strtoupper($nom)) ?> <?= htmlspecialchars(ucfirst($prenom)) ?></h1>
        <div class="sous-titre"><?= htmlspecialchars($poste) ?></div>

        <div>
            <h2>COMPÉTENCES PROFESSIONNELLES</h2>
            <?php

            if (strpos($competences, '-') !== false) {
                $competences = str_replace('- ', '', $competences);
                $comp_array = explode("\n", $competences);
                echo "<ul>";
                foreach ($comp_array as $comp) {
                    if (trim($comp) != '') {
                        echo "<li>" . nl2br(trim($comp)) . "</li>";
                    }
                }
                echo "</ul>";
            } else {
                echo "<p>" . $competences . "</p>";
            }
            ?>
        </div>

        <div class="section">
            <h2>DIPLÔMES</h2>
            <?= $diplome_html ?: "<p>Aucun diplôme renseigné</p>" ?>
        </div>

        <div class="section">
            <h2>EXPÉRIENCE PROFESSIONNELLE</h2>
            <?= $experience_html ?: "<p>Aucune expérience renseignée</p>" ?>
        </div>

        <div class="section">
            <h2>CERTIFICATIONS</h2>
            <?php
            if (!empty($certifications)) {
                $cert_array = explode("\n", $certifications);
                echo "<ul>";
                foreach ($cert_array as $cert) {
                    if (trim($cert) != '') {
                        echo "<li>" . nl2br(trim($cert)) . "</li>";
                    }
                }
                echo "</ul>";
            } else {
                echo "<p>Aucune certification</p>";
            }
            ?>
        </div>
        <div class="section">
            <h2>LANGUES</h2>
            <p><?= $langues ?: "Aucune langue renseignée" ?></p>
        </div>
    </div>

    <form action="download.php" method="POST">
        <input type="hidden" name="nom" value="<?= htmlspecialchars($nom) ?>">
        <input type="hidden" name="prenom" value="<?= htmlspecialchars($prenom) ?>">
        <input type="hidden" name="poste" value="<?= htmlspecialchars($poste) ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <input type="hidden" name="telephone" value="<?= htmlspecialchars($telephone) ?>">
        <input type="hidden" name="competences" value="<?= htmlspecialchars($_POST['competences'] ?? '') ?>">
        <input type="hidden" name="certifications" value="<?= htmlspecialchars($certifications) ?>">
        <input type="hidden" name="diplome_html" value="<?= htmlspecialchars($diplome_html) ?>">
        <input type="hidden" name="experience_html" value="<?= htmlspecialchars($experience_html) ?>">
        <input type="hidden" name="langues" value="<?= htmlspecialchars($langues) ?>">
        <input type="hidden" name="logo_type" value="<?= htmlspecialchars($logo_type) ?>">

        <button type="submit">📥 Télécharger mon CV (PDF)</button>
    </form>
</div>

</body>
</html>