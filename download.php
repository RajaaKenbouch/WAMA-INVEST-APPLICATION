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

if (file_exists($logo_path)) {
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
} else {
    $logo_base64 = ''; 
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
        margin: 22mm 18mm;
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
    .header {
        border-bottom: 2px solid #365F91;
        margin-bottom: 18px;
        padding-bottom: 12px;
        width: 100%;
    }
    .header-table {
        border-collapse: collapse;
        width: 100%;
    }
    .logo-cell {
        text-align: left;
        vertical-align: middle;
        width: 45%;
    }
    .contact-cell {
        text-align: right;
        vertical-align: middle;
        width: 55%;
    }
    .logo {
        height: auto;
        max-width: 115px;
    }
    .contact-info {
        color: #4b5563;
        font-size: 11px;
        line-height: 1.5;
        text-align: right;
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
        margin: 0 0 12px;
    }
    .section div {
        page-break-inside: avoid;
    }
    p {
        margin: 0 0 7px;
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
    <div class='header'>
        <table class='header-table'>
            <tr>
                <td class='logo-cell'>
                    " . ($logo_base64 ? "<img src='$logo_base64' class='logo' alt='WAMA'>" : "") . "
                </td>
                <td class='contact-cell'>
                    <div class='contact-info'>
                        +(212) 520 673 877<br>
                        info@wama-invest.com
                    </div>
                </td>
            </tr>
        </table>
    </div>

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

if (ob_get_length()) {
    ob_end_clean();
}
$dompdf->stream("CV_" . $nom . "_" . $prenom . ".pdf", ["Attachment" => true]);
exit();
?>
