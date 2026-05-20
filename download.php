<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;

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
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        margin: 30px;
    }
    .cv {
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
        color: #555;
    }
    h1 {
        color: #365F91;
        text-align: center;
        font-size: 28px;
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
   
    li {
        margin-bottom: 5px;
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

    <h1>" . strtoupper(htmlspecialchars($nom)) . " " . ucfirst(htmlspecialchars($prenom)) . "</h1>
    <div class='sous-titre'>" . htmlspecialchars($poste) . "</div>

" . ($annees_experience !== '0 an' ? "<div style='text-align:center; color:#1a73e8; margin-bottom:15px;'> Expérience : " . htmlspecialchars($annees_experience) . "</div>" : "") . "    " . ($show_competences ? "
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
";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

ob_end_clean();
$dompdf->stream("CV_" . $nom . "_" . $prenom . ".pdf", ["Attachment" => true]);
exit();
?>