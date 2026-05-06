<?php
require_once 'db.php';
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM cv WHERE id = ?");
$stmt->execute([$id]);
$cv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cv) {
    die("CV introuvable");
}

$logo_type = $cv['logo_type'] ?? 'invest';
if ($logo_type === 'link') {
    $logo_path = __DIR__ . '/images/logo_link.png';
} else {
    $logo_path = __DIR__ . '/images/logo_wama.png';
}

if (file_exists($logo_path)) {
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
} else {
    $logo_base64 = '';
}

$html = "
<style>
    body { font-family: 'Segoe UI', Arial, sans-serif; margin: 30px; }
    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #1a73e8; padding-bottom: 15px; margin-bottom: 20px; }
    .logo { width: 100px; }
    .contact-info { text-align: right; font-size: 12px; }
    h1 { text-align: center; font-size: 26px; margin: 15px 0 5px; color:#365F91; }
    .sous-titre { text-align: center; font-size: 14px; color: #1a73e8; margin-bottom: 5px; }
    h2 { font-size: 16px; background-color:#365F91; color: #90E0EF; padding: 5px; text-transform: uppercase; margin-top: 25px; }
    .section { margin: 15px 0; }
    ul { margin-left: 20px; }
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