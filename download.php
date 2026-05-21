<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

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

// Vérifier si les sections sont vides
$show_competences = !empty(trim(strip_tags($competences)));
$show_diplomes = !empty(trim(strip_tags($diplome_html)));
$show_experiences = !empty(trim(strip_tags($experience_html)));
$show_certifications = !empty(trim(strip_tags($certifications)));
$show_langues = !empty(trim(strip_tags($langues)));

if ($logo_type === 'invest') {
    $logo_path = __DIR__ . '/images/logo WAMA.png';
} else {
    $logo_path = __DIR__ . '/images/logo wama link.png';
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
    // Remplacer les puces par des retours à la ligne
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

$html = "
<!DOCTYPE html>
<html lang='fr'>
<head>
<meta charset='UTF-8'>
<style>
    @page {
        margin: 34mm 16mm 10mm;
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

<div class='cv'>
    <h1>" . strtoupper(htmlspecialchars($nom)) . " " . ucfirst(htmlspecialchars($prenom)) . "</h1>
    <div class='sous-titre'>" . htmlspecialchars($poste) . "</div>

" . ($annees_experience !== '0 an' ? "<div class='experience-years'>Expérience : " . htmlspecialchars($annees_experience) . "</div>" : "") . "    " . ($show_competences ? "
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
        <h2>EXPÉRIENCE PROFESSIONNELLE</h2>
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
    $lineY = 82;
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
    $contactLines = ['+(212) 520 673 877', 'info@wama-invest.com'];

    foreach ($contactLines as $index => $line) {
        $fontSize = 9;
        $textWidth = $fontMetrics->getTextWidth($line, $font, $fontSize);
        $canvas->text($pageWidth - $right - $textWidth, 35 + ($index * 13), $line, $font, $fontSize, $gray);
    }

    $canvas->line($left, $lineY, $pageWidth - $right, $lineY, $blue, 1.5);
});

if (ob_get_length()) {
    ob_end_clean();
}
$dompdf->stream("CV_" . $nom . "_" . $prenom . ".pdf", ["Attachment" => true]);
exit();
?>
