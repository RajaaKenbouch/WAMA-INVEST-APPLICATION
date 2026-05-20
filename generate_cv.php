<?php
$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$poste = $_POST['poste'] ?? '';
$annees_experience = $_POST['annees_experience'] ?? '0 an';
$email = $_POST['email'] ?? '';
$telephone = $_POST['telephone'] ?? '';
$fichier_original = $_POST['fichier_original'] ?? '';

$competences = nl2br($_POST['competences'] ?? '');
$certifications_raw = $_POST['certifications'] ?? '';
if (is_array($certifications_raw)) {
    $certifications_list = $certifications_raw;
    $certifications = implode("\n", $certifications_list);
} else {
    $certifications = nl2br($certifications_raw);
    $certifications_list = array_filter(array_map('trim', explode("\n", $certifications_raw)), fn($c) => !empty($c));
}


$langues_raw = $_POST['langues'] ?? '';
if (is_array($langues_raw)) {
    $langues_list = $langues_raw;
    $langues = implode("\n", $langues_list);
} else {
    $langues = nl2br($langues_raw);
    $langues_list = array_filter(array_map('trim', explode("\n", $langues_raw)), fn($l) => !empty($l));
}

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
                <p><em><strong>Outils:</strong>  $outils</em></p>
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

$show_competences = !empty(trim(strip_tags($competences)));
$show_diplomes = !empty(trim(strip_tags($diplome_html)));
$show_experiences = !empty(trim(strip_tags($experience_html)));
$show_certifications = !empty($certifications_list);
$show_langues = !empty($langues_list);

require_once 'db.php';

$stmt = $pdo->prepare("INSERT INTO cv (nom, prenom, poste, email, telephone, competences, logo_type, fichier_original, certifications,annees_experience) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([
    $nom, $prenom, $poste, $email, $telephone,
    $competences, $logo_type, $fichier_original,
    $certifications,$annees_experience
]);
$cv_id = $pdo->lastInsertId();

// Insertion des diplômes
if (!empty($_POST['diplome_date']) && !empty($_POST['diplome_titre']) && !empty($_POST['diplome_ecole'])) {
    $stmtDiplome = $pdo->prepare("INSERT INTO diplomes (cv_id, annee, titre, etablissement) VALUES (?, ?, ?, ?)");
    for ($i = 0; $i < count($_POST['diplome_date']); $i++) {
        $annee = htmlspecialchars($_POST['diplome_date'][$i]);
        $titre = htmlspecialchars($_POST['diplome_titre'][$i]);
        $ecole = htmlspecialchars($_POST['diplome_ecole'][$i]);
        if (!empty($annee) || !empty($titre) || !empty($ecole)) {
            $stmtDiplome->execute([$cv_id, $annee, $titre, $ecole]);
        }
    }
}

// Insertion des expériences
if (!empty($_POST['exp_date']) && !empty($_POST['exp_poste']) && !empty($_POST['exp_entreprise'])) {
    $stmtExp = $pdo->prepare("INSERT INTO experiences (cv_id, periode, poste_exp, entreprise, description, outils) VALUES (?, ?, ?, ?, ?, ?)");
    for ($i = 0; $i < count($_POST['exp_date']); $i++) {
        $periode = htmlspecialchars($_POST['exp_date'][$i]);
        $poste_exp = htmlspecialchars($_POST['exp_poste'][$i]);
        $entreprise = htmlspecialchars($_POST['exp_entreprise'][$i]);
        $description = htmlspecialchars($_POST['exp_description'][$i] ?? '');
        $outils = htmlspecialchars($_POST['exp_outils'][$i] ?? '');
        
        if (!empty($periode) || !empty($poste_exp) || !empty($entreprise)) {
            $stmtExp->execute([$cv_id, $periode, $poste_exp, $entreprise, $description, $outils]);
        }
    }
}

// Insertion des langues
if (!empty($langues_list)) {
    $stmtLangue = $pdo->prepare("INSERT INTO langues (cv_id, langue, niveau) VALUES (?, ?, ?)");
    foreach ($langues_list as $langue_str) {
        // Nettoyer la langue (supprimer les puces)
        $langue_str = preg_replace('/^[\s]*[\-\•\*\▪]\s*/', '', trim($langue_str));
        if (!empty($langue_str)) {
            // Extraire langue et niveau (ex: "Français (C2)")
            if (preg_match('/(.+?)\s*\((.+?)\)/', $langue_str, $matches)) {
                $langue = trim($matches[1]);
                $niveau = trim($matches[2]);
            } else {
                $langue = $langue_str;
                $niveau = '';
            }
            $stmtLangue->execute([$cv_id, $langue, $niveau]);
        }
    }
}

// Convertir les certifications en chaîne (une par ligne)
$certifications_str = implode("\n", $certifications_list);
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
<?php if ($annees_experience !== '0 an'): ?>
    <div class="experience-years" style="text-align: center; margin-top: 5px; color: #1a73e8;">
        Expérience : <?= htmlspecialchars($annees_experience) ?>
    </div>
<?php endif; ?>

        <div>
            <?php if ($show_competences): ?>
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
            <?php endif; ?>

            <?php if ($show_diplomes): ?>
            <div class="section">
                <h2>DIPLÔMES</h2>
                <?= $diplome_html ?>
            </div>
            <?php endif; ?>

            <?php if ($show_experiences): ?>
            <div class="section">
                <h2>EXPÉRIENCE PROFESSIONNELLE</h2>
                <?= $experience_html ?>
            </div>
            <?php endif; ?>

            <div class="section">
                <h2>CERTIFICATIONS</h2>
                <?php
                // Transformer les • en retours à la ligne
                $certifications_clean = str_replace('•', "\n", $certifications);
                $cert_array = explode("\n", $certifications_clean);
                $cert_array = array_filter(array_map('trim', $cert_array));
                
                if (!empty($cert_array)) {
                    echo "<ul>";
                    foreach ($cert_array as $cert) {
                        if (!empty($cert)) {
                            echo "<li>" . htmlspecialchars($cert) . "</li>";
                        }
                    }
                    echo "</ul>";
                } else {
                    echo "<p>Aucune certification</p>";
                }
                ?>
            </div>

            <?php if ($show_langues): ?>
            <div class="section">
                <h2>LANGUES</h2>
                <ul>
                <?php foreach ($langues_list as $langue_str): ?>
                    <?php $langue_clean = preg_replace('/^[\s]*[\-\•\*\▪]\s*/', '', trim($langue_str)); ?>
                    <li><?= htmlspecialchars($langue_clean) ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <form action="download.php" method="POST">
        <input type="hidden" name="nom" value="<?= htmlspecialchars($nom) ?>">
        <input type="hidden" name="prenom" value="<?= htmlspecialchars($prenom) ?>">
        <input type="hidden" name="poste" value="<?= htmlspecialchars($poste) ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <input type="hidden" name="telephone" value="<?= htmlspecialchars($telephone) ?>">
        <input type="hidden" name="competences" value="<?= htmlspecialchars($_POST['competences'] ?? '') ?>">
        <input type="hidden" name="certifications" value="<?= htmlspecialchars(implode("\n", $certifications_list)) ?>">
        <input type="hidden" name="diplome_html" value="<?= htmlspecialchars($diplome_html) ?>">
        <input type="hidden" name="experience_html" value="<?= htmlspecialchars($experience_html) ?>">
        <input type="hidden" name="langues" value="<?= htmlspecialchars(implode("\n", $langues_list)) ?>">
        <input type="hidden" name="logo_type" value="<?= htmlspecialchars($logo_type) ?>">

        <button type="submit" style="background: #1a73e8;color: white;padding: 12px 28px;border: none;border-radius: 30px;cursor: pointer;font-size: 16px;display: block;margin: 30px auto 10px;transition: 0.3s;">
            📥 Télécharger mon CV (PDF)
        </button>
    </form>
</div>

</body>
</html>