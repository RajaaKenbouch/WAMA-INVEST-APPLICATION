<?php
require_once 'db.php';
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;

$id = $_GET['id'] ?? 0;

// Récupérer les infos du CV
$stmt = $pdo->prepare("SELECT * FROM cv WHERE id = ?");
$stmt->execute([$id]);
$cv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cv) {
    die("CV introuvable");
}

// Récupérer les diplômes
$stmtDiplomes = $pdo->prepare("SELECT * FROM diplomes WHERE cv_id = ? ORDER BY id");
$stmtDiplomes->execute([$id]);
$diplomes = $stmtDiplomes->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les expériences
$stmtExperiences = $pdo->prepare("SELECT * FROM experiences WHERE cv_id = ? ORDER BY id");
$stmtExperiences->execute([$id]);
$experiences = $stmtExperiences->fetchAll(PDO::FETCH_ASSOC);

// Nettoyer les langues (supprimer les doublons)
$langues = $cv['langues'] ?? '';
$langues_arr = array_unique(array_map('trim', explode(',', $langues)));
$langues = implode(', ', $langues_arr);

// Nettoyer les certifications (supprimer les puces)
$certifications = $cv['certifications'] ?? '';
$certifications = preg_replace('/^[\s]*[\-\•\*\▪]\s*/', '', $certifications);
$certifications = nl2br(htmlspecialchars($certifications));


$annees_experience = $cv['annees_experience'] ?? '0 an';
// Logo
$logo_type = $cv['logo_type'] ?? 'invest';
if ($logo_type === 'link') {
    $logo_path = __DIR__ . '/images/logo_link.png';
    if (!file_exists($logo_path)) $logo_path = __DIR__ . '/images/logo wama link.png';
} else {
    $logo_path = __DIR__ . '/images/logo_wama.png';
    if (!file_exists($logo_path)) $logo_path = __DIR__ . '/images/logo WAMA.png';
}

if (file_exists($logo_path)) {
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
} else {
    $logo_base64 = '';
}

// Vérifier si les sections sont vides
$show_competences = !empty(trim(strip_tags($cv['competences'] ?? '')));
$show_diplomes = !empty($diplomes);
$show_experiences = !empty($experiences);
$show_certifications = !empty(trim(strip_tags($cv['certifications'] ?? '')));
$show_langues = !empty(trim($langues));

// Construction du HTML
$html = "
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        margin: 30px;
        line-height: 1.5;
    }
    .cv {
        max-width: 900px;
        margin: 0 auto;
        background: white;
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
        color: #555;
    }
    h1 {
        color: #365F91;
        text-align: center;
        font-size: 26px;
        margin: 20px 0 5px;
    }
    .sous-titre {
        text-align: center;
        font-size: 16px;
        color: #1a73e8;
        margin-bottom: 25px;
        font-weight: 500;
    }
    h2 {
        font-size: 16px;
        padding: 8px;
        margin-top: 25px;
        text-transform: uppercase;
        color: #90E0EF;
        background-color: #365F91;
        border-radius: 4px;
    }
    .section {
        margin: 15px 0;
    }
    ul {
        margin-left: 20px;
        padding-left: 0;
    }
    li {
        margin-bottom: 5px;
    }
    p {
        margin: 8px 0;
    }
</style>

<div class='cv'>
    <div class='header'>
        " . ($logo_base64 ? "<img src='$logo_base64' class='logo'>" : "") . "
        <div class='contact-info'>
            +(212) 520 673 877<br>
            info@wama-invest.com
        </div>
    </div>

    <h1>" . strtoupper(htmlspecialchars($cv['nom'])) . " " . ucfirst(htmlspecialchars($cv['prenom'])) . "</h1>
    <div class='sous-titre'>" . htmlspecialchars($cv['poste']) . "</div>
" . ($annees_experience !== '0 an' ? "<div style='text-align:center; color:#1a73e8; margin-bottom:15px;'> Expérience : " . htmlspecialchars($annees_experience) . "</div>" : "") . "
    " . ($show_competences ? "
    <div class='section'>
        <h2>COMPÉTENCES</h2>
        <p>" . nl2br(htmlspecialchars($cv['competences'])) . "</p>
    </div>" : "") . "

    " . ($show_diplomes ? "
    <div class='section'>
        <h2>DIPLÔMES</h2>
        <ul>" . implode('', array_map(function($d) {
            $date = !empty($d['annee']) ? "<strong>" . htmlspecialchars($d['annee']) . "</strong> - " : "";
            return "<li>" . $date . htmlspecialchars($d['titre']) . " - " . htmlspecialchars($d['etablissement']) . "</li>";
        }, $diplomes)) . "</ul>
    </div>" : "") . "

    " . ($show_experiences ? "
    <div class='section'>
        <h2>EXPÉRIENCES</h2>" . implode('', array_map(function($e) {
            $periode = !empty($e['periode']) ? "<strong>" . htmlspecialchars($e['periode']) . "</strong> : " : "";
            return "<div style='margin-bottom: 20px;'>
                        <p>" . $periode . "<strong>" . htmlspecialchars($e['poste_exp']) . "</strong> - " . htmlspecialchars($e['entreprise']) . "</p>
                        <p>" . nl2br(htmlspecialchars($e['description'])) . "</p>
                        <p><em><strong>Outils :</strong> " . htmlspecialchars($e['outils']) . "</em></p>
                    </div>";
        }, $experiences)) . "
    </div>" : "") . "

    " . ($show_certifications ? "
    <div class='section'>
        <h2>CERTIFICATIONS</h2>
        <p>$certifications</p>
    </div>" : "") . "

    " . ($show_langues ? "
    <div class='section'>
        <h2>LANGUES</h2>
        <p>$langues</p>
    </div>" : "") . "
</div>
";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

ob_end_clean();
$dompdf->stream("CV_" . $cv['nom'] . "_" . $cv['prenom'] . ".pdf", ["Attachment" => true]);
exit;
?>