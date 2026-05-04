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

if (strpos($competences, '-') !== false) {
    $competences = str_replace('- ', '', $competences);
    $comp_array = explode("\n", $competences);
    $competences = "<ul>";
    foreach ($comp_array as $comp) {
        if (trim($comp) != '') {
            $competences .= "<li>" . nl2br(trim($comp)) . "</li>";
        }
    }
    $competences .= "</ul>";
}


if (!empty($certifications)) {
    $cert_array = explode("\n", $certifications);
    $certifications = "<ul>";
    foreach ($cert_array as $cert) {
        if (trim($cert) != '') {
            $certifications .= "<li>" . nl2br(trim($cert)) . "</li>";
        }
    }
    $certifications .= "</ul>";
}

$html = "
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

<div class='cv'>
    <div class='header'>
        " . ($logo_base64 ? "<img src='$logo_base64' class='logo' alt='logo'>" : "") . "
        <div class='contact-info'>
            +(212) 520 673 877<br>
            info@wama-invest.com
        </div>
    </div>

    <h1>" . strtoupper(htmlspecialchars($nom)) . " " . ucfirst(htmlspecialchars($prenom)) . "</h1>
    <div class='sous-titre'>" . htmlspecialchars($poste) . "</div>
    <div class='contact'>" . htmlspecialchars($email) . " | " . htmlspecialchars($telephone) . "</div>

    <div class='section'>
        <h2>COMPÉTENCES PROFESSIONNELLES</h2>
        $competences
    </div>

    <div class='section'>
        <h2>DIPLÔMES</h2>
        " . ($diplome_html ?: "<p>Aucun diplôme renseigné</p>") . "
    </div>

    <div class='section'>
        <h2>EXPÉRIENCE PROFESSIONNELLE</h2>
        " . ($experience_html ?: "<p>Aucune expérience renseignée</p>") . "
    </div>

    <div class='section'>
        <h2>CERTIFICATIONS</h2>
        " . ($certifications ?: "<p>Aucune certification</p>") . "
    </div>

    <div class='section'>
        <h2>LANGUES</h2>
        " . ($langues ?: "<p>Aucune langue renseignée</p>") . "
    </div>
</div>
";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

ob_end_clean();
$dompdf->stream("CV_WAMA.pdf", ["Attachment" => true]);
exit();
?>