<?php

require 'vendor/autoload.php';

if (!isset($_FILES['cv_file'])) {
    die("Aucun fichier envoyé");
}

$file = $_FILES['cv_file'];
$type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$tmpPath = $file['tmp_name'];

$text = "";

// =====================
// EXTRACTION TEXTE BRUT (améliorée)
// =====================
if ($type == "pdf") {
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($tmpPath);
    $text = $pdf->getText();
    
    // 🔧 Nettoyage spécifique PDF
    $text = preg_replace('/[^\x20-\x7E\xC0-\xFF\n\r\t]/', ' ', $text);
    
} elseif ($type == "docx") {
    $phpWord = \PhpOffice\PhpWord\IOFactory::load($tmpPath);
    foreach ($phpWord->getSections() as $section) {
        foreach ($section->getElements() as $element) {
            if (method_exists($element, 'getText')) {
                $text .= $element->getText() . " ";
            }
        }
    }
} elseif ($type == "txt") {
    $text = file_get_contents($tmpPath);
} else {
    die("Format non supporté");
}

// 🔧 Si le texte est vide, on affiche une erreur
if (empty(trim($text))) {
    die("Aucun texte extrait du fichier. Vérifie le format.");
}

// =====================
// EXTRACTION DES INFOS (simplifiée)
// =====================
$email = "";
if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $text, $match)) {
    $email = $match[0];
}

$telephone = "";
if (preg_match('/(?:\+212|0)[0-9\s\-]{9,13}/', $text, $match)) {
    $telephone = trim($match[0]);
}

$nom = "";
$prenom = "";
$lines = explode("\n", $text);
foreach ($lines as $line) {
    $line = trim($line);
    if (preg_match('/^[A-Za-zÀ-ÿ]+\s+[A-Za-zÀ-ÿ]+$/', $line)) {
        $parts = explode(' ', $line);
        if (count($parts) >= 2) {
            $prenom = ucfirst(strtolower($parts[0]));
            $nom = strtoupper($parts[1]);
            break;
        }
    }
}

$competences = "";
if (preg_match('/(?:COMPETENCES|Compétences|SKILLS)[\s\-:]*\n?(.*?)(?=\n\s*(?:LANGUES|EXPERIENCES|FORMATION|DIPLOMES|PROFIL|$))/is', $text, $match)) {
    $competences = trim($match[1]);
    $competences = preg_replace('/[\-\•\*]\s*/', '', $competences);
    $competences = preg_replace('/\s+/', ' ', $competences);
}

$langues = "";
if (preg_match('/(?:LANGUES|Langues|LANGUAGES)[\s\-:]*\n?(.*?)(?=\n\s*(?:COMPETENCES|EXPERIENCES|FORMATION|DIPLOMES|PROFIL|$))/is', $text, $match)) {
    $langues = trim($match[1]);
    $langues = preg_replace('/[\-\•\*]\s*/', '', $langues);
}

// =====================
// FALLBACK : si aucune compétence trouvée, on prend les lignes qui commencent par -
// =====================
if (empty($competences)) {
    preg_match_all('/^-\s*(.+)$/m', $text, $matches);
    if (!empty($matches[1])) {
        $competences = implode(", ", $matches[1]);
    }
}

// =====================
// CONSTRUCTION JSON
// =====================
$json = json_encode([
    "nom" => $nom,
    "prenom" => $prenom,
    "email" => $email,
    "telephone" => $telephone,
    "competences" => $competences,
    "langues" => $langues,
    "texte_brut" => substr($text, 0, 5000)
], JSON_UNESCAPED_UNICODE);

// =====================
// REDIRECTION
// =====================
$logo_type = $_POST['logo_type'] ?? 'invest';
header("Location: creationCV.php?data=" . urlencode($json) . "&logo_type=" . $logo_type);
exit();