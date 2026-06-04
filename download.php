<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$username = $_POST['username'] ?? '';
$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$poste = $_POST['poste'] ?? '';
$email = $_POST['email'] ?? '';
$telephone = $_POST['telephone'] ?? '';
$competences = nl2br($_POST['competences'] ?? '');
$certifications = nl2br($_POST['certifications'] ?? '');
$diplome_html = $_POST['diplome_html'] ?? '';
$experience_html = $_POST['experience_html'] ?? '';
$langues = $_POST['langues'] ?? '';
$logo_type = $_POST['logo_type'] ?? 'link';
$annees_experience = $_POST['annees_experience'] ?? '0 an';

if (trim($username) === '') {
    $nom_clean = trim($nom);
    $prenom_clean = trim($prenom);
    $username = '';

    if ($prenom_clean !== '') {
        $username .= strtoupper(substr($prenom_clean, 0, 1));
    }
    if ($nom_clean !== '') {
        $username .= strtoupper(substr($nom_clean, 0, 1));
        $username .= strtoupper(substr($nom_clean, -1));
    }
}

// Vérifier si les sections sont vides
$show_competences = !empty(trim(strip_tags($competences)));
$show_diplomes = !empty(trim(strip_tags($diplome_html)));
$show_experiences = !empty(trim(strip_tags($experience_html)));
$show_certifications = !empty(trim(strip_tags($certifications)));
$show_langues = !empty(trim(strip_tags($langues)));

$logo_type = $_POST['logo_type'] ?? 'invest';

if ($logo_type === 'invest') {
    $logo_path = __DIR__ . '/images/logo WAMA.png';
    $contact_nom = "WAMA INVEST";
    $contact_tel = "+(212) 520 673 877";
    $contact_email = "info@wama-invest.com";
} else {
    $logo_path = __DIR__ . '/images/y2il.png';
    $contact_nom = "Y2IL";
    $contact_tel = "+(212) 661 900 050";
    $contact_email = "+(212) 673 749 308";
}

// Formater les compétences en liste
if (!empty($competences) && strpos($competences, '-') !== false) {
    $competences = str_replace('- ', '', $competences);
    $comp_array = explode("\n", $competences);
    $competences = "<ul>";
    foreach ($comp_array as $comp) {
        if (trim($comp) != '') {
            $comp_clean = preg_replace('/^[\s]*[\-\•\*\▪]\s*/', '', trim($comp));
            if (!empty($comp_clean)) {
                $competences .= "<li>" . nl2br($comp_clean) . "</li>";
            }
        }
    }
    $competences .= "</ul>";
} elseif (!empty($competences)) {
    $competences = "<p>" . $competences . "</p>";
}

// Traitement des certifications
if (!empty($certifications)) {
    $cert_clean = str_replace(['•', '▪', '·', '-'], "\n", $certifications);
    $cert_array = explode("\n", $cert_clean);
    $cert_array = array_filter(array_map('trim', $cert_array));
    
    $certifications_html = "<ul>";
    foreach ($cert_array as $cert) {
        if (!empty($cert)) {
            $certifications_html .= "<li>" . htmlspecialchars($cert) . "</li>";
        }
    }
    $certifications_html .= "</ul>";
    $certifications = $certifications_html;
} else {
    $certifications = "<p>Aucune certification</p>";
}

// HTML du PDF
$html = "
<!DOCTYPE html>
<html lang='fr'>
<head>
<meta charset='UTF-8'>
<style>
    @page {
        margin: 55mm 16mm 10mm;
    }
    * {
        box-sizing: border-box;
    }
    body {
        color: #1f2933;
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 12px;
        line-height: 1.45;
        margin: 0;
    }
    .fixed-header {
        position: fixed;
        top: -55mm;
        left: 0;
        right: 0;
        height: 55mm;
        padding-top: 36mm; /* To sit under the 34mm header line */
    }
    .cv {
        background: #ffffff;
        width: 100%;
    }
    h1 {
        color: #365F91;
        font-size: 25px;
        line-height: 1.2;
        margin: 16px 0 4px;
        text-align: center;
        text-transform: uppercase;
    }
    .sous-titre {
        color: #1a73e8;
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 12px;
        text-align: center;
    }
    .experience-years {
        color: #1a73e8;
        font-size: 12px;
        margin-bottom: 16px;
        text-align: center;
    }
    h2 {
        background-color: #365F91;
        color: #90E0EF;
        font-size: 13px;
        letter-spacing: 0;
        margin: 18px 0 8px;
        padding: 7px 9px;
        text-transform: uppercase;
    }
    .section {
        margin: 0 0 8px;
    }
    p {
        margin: 0 0 5px;
        overflow-wrap: break-word;
        word-wrap: break-word;
    }
    ul {
        margin: 6px 0 0 18px;
        padding: 0;
    }
    li {
        margin-bottom: 5px;
        overflow-wrap: break-word;
        word-wrap: break-word;
    }
    strong {
        color: #1f2933;
    }
    em {
        color: #4b5563;
    }
</style>
</head>
<body>

<header class='fixed-header'>
    <h1>" . htmlspecialchars($username) . "</h1>
    <div class='sous-titre'>" . htmlspecialchars($poste) . "</div>
" . ($annees_experience !== '0 an' ? "<div class='experience-years'>Expérience : " . htmlspecialchars($annees_experience) . "</div>" : "") . "
</header>

<div class='cv'>
    " . ($show_competences ? "
    <div class='section'>
        <h2>COMPÉTENCES PROFESSIONNELLES</h2>
        $competences
    </div>" : "") . "

    " . ($show_diplomes ? "
    <div class='section'>
        <h2>DIPLÔMES</h2>
        $diplome_html
    </div>" : "") . "

    " . ($show_experiences ? "
    <div class='section'>
        <h2>EXPÉRIENCES PROFESSIONNELLES</h2>
        $experience_html
    </div>" : "") . "

    " . ($show_certifications ? "
    <div class='section'>
        <h2>CERTIFICATIONS</h2>
        $certifications
    </div>" : "") . "

    " . ($show_langues ? "
    <div class='section'>
        <h2>LANGUES</h2>
        <p>$langues</p>
    </div>" : "") . "
</div>
</body>
</html>
";

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$canvas = $dompdf->getCanvas();
$canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($logo_path) {
    $pageWidth = $canvas->get_width();
    $left = 45;
    $right = 45;
    $top = 18;
    $lineY = 95;
    $blue = [54 / 255, 95 / 255, 145 / 255];
    $gray = [75 / 255, 85 / 255, 99 / 255];

    if (file_exists($logo_path)) {
        $logoWidth = 86;
        $logoHeight = 58;
        $imageSize = @getimagesize($logo_path);

        if ($imageSize && !empty($imageSize[0])) {
            $logoHeight = $logoWidth * ($imageSize[1] / $imageSize[0]);
        }

        $canvas->image($logo_path, $left, $top, $logoWidth, $logoHeight);
    }

    $font = $fontMetrics->getFont('DejaVu Sans', 'normal') ?: $fontMetrics->getFont('Helvetica', 'normal');
    if ($logo_type === 'invest') {
        $contactLines = ['+(212) 520 673 877', 'info@wama-invest.com'];
    } else {
        $contactLines = ['+(212) 661 900 050','+(212) 673 749 308'];
    }
    foreach ($contactLines as $index => $line) {
        $fontSize = 9;
        $textWidth = $fontMetrics->getTextWidth($line, $font, $fontSize);
        $canvas->text($pageWidth - $right - $textWidth, 35 + ($index * 13), $line, $font, $fontSize, $gray);
    }

    $canvas->line($left, $lineY, $pageWidth - $right, $lineY, $blue, 1.5);
    // Numéro de page (centré en bas)
    $pageText =  $pageNumber . " / " . $pageCount;
    $fontNormal = $fontMetrics->getFont('DejaVu Sans', 'normal') ?: $fontMetrics->getFont('Helvetica', 'normal');
    $textWidth = $fontMetrics->getTextWidth($pageText, $fontNormal, 9);
    $canvas->text(($pageWidth - $textWidth) / 2, $canvas->get_height() - 15, $pageText, $fontNormal, 9, $gray);
});

if (ob_get_length()) {
    ob_end_clean();
}
// =====================
// SAUVEGARDE DU PDF SUR LE SERVEUR
// =====================
$cv_id = $_POST['cv_id'] ?? 0;
if ($cv_id) {
    // Récupérer le contenu du PDF généré
    $pdf_output = $dompdf->output();
    
    // Créer le dossier si nécessaire
    $pdf_dir = __DIR__ . '/uploads/pdfs/';
    if (!is_dir($pdf_dir)) {
        mkdir($pdf_dir, 0777, true);
    }
    
    // Nom du fichier
    $pdf_filename = 'cv_' . $cv_id . '.pdf';
    $pdf_path = 'uploads/pdfs/' . $pdf_filename;
    
    // Sauvegarder le fichier
    file_put_contents($pdf_dir . $pdf_filename, $pdf_output);
    
    // Mettre à jour la base de données
    require_once 'db.php';
    $stmt = $pdo->prepare("UPDATE cv SET pdf_path = ? WHERE id = ?");
    $stmt->execute([$pdf_path, $cv_id]);
}
$dompdf->stream("CV_" . $username . ".pdf", ["Attachment" => true]);
exit();
?>
