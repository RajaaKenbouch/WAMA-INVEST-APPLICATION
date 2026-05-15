<?php
require_once 'db.php';
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;

$nom = $_GET['nom'] ?? '';
$prenom = $_GET['prenom'] ?? '';

if (empty($nom) || empty($prenom)) {
    die("Nom ou prénom manquant");
}

// Récupérer le CV correspondant (le plus récent si plusieurs homonymes)
$stmt = $pdo->prepare("SELECT * FROM cv WHERE nom = ? AND prenom = ? ORDER BY date_creation DESC LIMIT 1");
$stmt->execute([$nom, $prenom]);
$cv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cv) {
    die("CV introuvable");
}

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

// Génération du HTML (identique à download_cv_by_id.php)
$html = "
<style>
    body { font-family: 'Segoe UI', Arial, sans-serif; margin: 30px; }
    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #1a73e8; padding-bottom: 15px; margin-bottom: 20px; }
    .logo { width: 100px; }
    .contact-info { text-align: right; font-size: 12px; }
    h1 { text-align: center; font-size: 26px; margin: 15px 0 5px; color:#365F91; }
    .sous-titre { text-align: center; font-size: 14px; color: #1a73e8; margin-bottom: 5px; }
    h2 { font-size: 16px; background-color:#365F91; color: #90E0EF; padding: 8px; text-transform: uppercase; margin-top: 25px; }
    .section { margin: 15px 0; }
</style>

<div class='header'>
    <img src='$logo_base64' class='logo'>
    <div class='contact-info'>
        +(212) 520 673 877<br>
        info@wama-invest.com
    </div>
</div>

<h1>" . strtoupper(htmlspecialchars($cv['nom'])) . " " . ucfirst(htmlspecialchars($cv['prenom'])) . "</h1>
<div class='sous-titre'>" . htmlspecialchars($cv['poste']) . "</div>

<div class='section'>
    <h2>COMPÉTENCES</h2>
    <p>" . nl2br(htmlspecialchars($cv['competences'])) . "</p>
</div>

<div class='section'>
    <h2>DIPLÔMES</h2>
    <p>" . nl2br(htmlspecialchars($cv['diplomes'])) . "</p>
</div>

<div class='section'>
    <h2>EXPÉRIENCES</h2>
    <p>" . nl2br(htmlspecialchars($cv['experiences'])) . "</p>
</div>

<div class='section'>
    <h2>LANGUES</h2>
    <p>" . nl2br(htmlspecialchars($cv['langues'])) . "</p>
</div>

<div class='section'>
    <h2>CERTIFICATIONS</h2>
    <p>" . nl2br(htmlspecialchars($cv['certifications'])) . "</p>
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