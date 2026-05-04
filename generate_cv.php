<?php
$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$poste = $_POST['poste'] ?? '';
$email = $_POST['email'] ?? '';
$telephone = $_POST['telephone'] ?? '';

$competences = nl2br($_POST['competences'] ?? '');
$certifications = nl2br($_POST['certifications'] ?? '');
$langues = nl2br($_POST['langues'] ?? '');


$diplome_html = '';
if (!empty($_POST['diplome_date']) && !empty($_POST['diplome_titre']) && !empty($_POST['diplome_ecole'])) {
    for ($i = 0; $i < count($_POST['diplome_date']); $i++) {
        $date = htmlspecialchars($_POST['diplome_date'][$i]);
        $titre = htmlspecialchars($_POST['diplome_titre'][$i]);
        $ecole = htmlspecialchars($_POST['diplome_ecole'][$i]);
        if (!empty($date) || !empty($titre) || !empty($ecole)) {
            $diplome_html .= "<p><strong>$date</strong> - $titre<br><em>$ecole</em></p>";
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
                <p><strong>$date</strong> : <strong>$poste_exp</strong> - $entreprise</p>
                <p>$description</p>
                <p><em>Outils : $outils</em></p>
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
    <style>
        body {
            background: #eef2f5;
            font-family: 'Segoe UI', Arial, sans-serif;
            padding: 40px;
            display: flex;
            justify-content: center;
        }
        .cv {
            width: 900px;
            background: white;
            padding: 35px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo {
            width: 100px;
        }
        .contact-info {
            text-align: right;
            font-size: 12px;
            line-height: 1.4;
        }
        h1 {
            color:#365F91;
            text-align: center;
            font-size: 26px;
            margin: 15px 0 5px;
        }
        .sous-titre {
            text-align: center;
            font-size: 14px;
            color: #1a73e8;
            margin-bottom: 5px;
        }
        .contact {
            text-align: center;
            font-size: 12px;
            margin-bottom: 25px;
        }
        h2 {
            font-size: 16px;
            padding-bottom: 5px;
            margin-top: 25px;
            text-transform: uppercase;
            color: #90E0EF;
            background-color:#365F91;
        }
        .section {
            margin: 15px 0;
        }
        button {
            background: #1a73e8;
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            display: block;
            margin: 30px auto 10px;
            transition: 0.3s;
        }
        button:hover {
            background: #0e5bbf;
        }
        ul, li {
            margin-left: 20px;
        }
    </style>
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
        <div class="contact"><?= htmlspecialchars($email) ?> | <?= htmlspecialchars($telephone) ?></div>

        <div class="section">
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
        <input type="hidden" name="certifications" value="<?= htmlspecialchars($_POST['certifications'] ?? '') ?>">
        <input type="hidden" name="diplome_html" value="<?= htmlspecialchars($diplome_html) ?>">
        <input type="hidden" name="experience_html" value="<?= htmlspecialchars($experience_html) ?>">

        <button type="submit">📥 Télécharger mon CV (PDF)</button>
    </form>
</div>

</body>
</html>