<?php
session_start(); // ← AJOUTÉ
require 'vendor/autoload.php';
use Dotenv\Dotenv;

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
$geminiApiKey = $_ENV['API_KEY']; 

// ← AJOUTÉ : fonction pour les toasts d'erreur
function setImportError($message) {
    $_SESSION['import_toast'] = ['message' => $message, 'type' => 'error'];
}

if (!isset($_FILES['cv_file']) || $_FILES['cv_file']['error'] !== UPLOAD_ERR_OK) {
    setImportError("Aucun fichier envoyé ou erreur d'upload");
    header("Location: importationCV.php");
    exit;
}

$file    = $_FILES['cv_file'];
$type    = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$tmpPath = $file['tmp_name'];

$text = "";

if ($type === "pdf") {
    try {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf    = $parser->parseFile($tmpPath);
        $text   = $pdf->getText();
        $text   = preg_replace('/[^\x20-\x7E\xC0-\xFF\n\r\t]/', ' ', $text);
    } catch (Exception $e) {
        setImportError("Erreur lecture PDF: " . $e->getMessage());
        header("Location: importationCV.php");
        exit;
    }

} elseif ($type === "docx") {
    try {
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($tmpPath);
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . " ";
                } elseif (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $child) {
                        if (method_exists($child, 'getText')) {
                            $text .= $child->getText() . " ";
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        setImportError("Erreur lecture DOCX: " . $e->getMessage());
        header("Location: importationCV.php");
        exit;
    }

} elseif ($type === "txt") {
    $text = file_get_contents($tmpPath);
    if ($text === false) {
        setImportError("Erreur lecture fichier TXT");
        header("Location: importationCV.php");
        exit;
    }

} else {
    setImportError("Format non supporté. Utilisez PDF, DOCX ou TXT");
    header("Location: importationCV.php");
    exit;
}

$text = trim($text);
if (empty($text)) {
    setImportError("Aucun texte extrait. Le fichier est peut-être scanné ou protégé.");
    header("Location: importationCV.php");
    exit;
}

// =====================
// PROMPT (IDENTIQUE - NON MODIFIÉ)
// =====================
$prompt = <<<PROMPT
You are a world-class CV/Resume parser with 20 years of experience reading resumes from all countries, cultures, and formats.

Your task: Extract ALL useful information from the CV below.

=== CRITICAL RULES ===
1. NAME DETECTION (most important):
   - The name is almost ALWAYS at the very top of the CV
   - It has NO label like "Name:" or "Nom:" — it's just written directly
   - It can be: "BENALI Mohamed" / "Sarah Martin" / "EL FASSI Ahmed" / "Jean-Pierre DUPONT"
   - ALL CAPS word = usually last name / Mixed case = usually first name
   - Compound names: "El Fassi", "Ben Ali", "Van Der Berg", "Jean-Pierre" → keep them together
   - Arabic, Asian, African, European names → all supported
   - If you see 2-4 words at the very top that look like a human name → that's the name

2. CONTACT INFO:
   - Email: any format (gmail, yahoo, outlook, company domain...)
   - Phone: ANY country format (+212, +33, +1, 06, 07, 00212...)
   - LinkedIn / GitHub / Portfolio URLs if present
   - City / Country if mentioned

3. SECTIONS WITHOUT LABELS:
   - Skills may appear as bullet points, tags, icons, or inline text
   - Experience may have no "Experience" header — look for company names + dates
   - Education may have no header — look for school names + degrees + years
   - Languages may be listed anywhere

4. INCOMPLETE OR MISSING DATA:
   - If something is not found → use empty string ""
   - NEVER invent or guess data
   - If name has only one part → put it in "nom", leave "prenom" empty

5. EXPERIENCES MUST BE VERBATIM:
   - Do NOT rewrite, summarize, or regenerate experiences
   - Extract experience descriptions exactly as written in the CV
   - Keep original wording and order

6. TOOLS EXTRACTION:
   - For each experience, extract tools/technologies from the description or bullets
   - Return them in "outils" as a comma-separated list
   - If no tools are mentioned, use empty string ""

7. EXPERIENCES WITHOUT DATES — THIS IS MANDATORY:
   - EVERY experience found in the CV MUST be included, even if it has NO date at all
   - If no date/period is found for an experience → use "" for "periode"
   - NEVER skip or omit an experience just because it lacks a date
   - An experience with no date is still a valid experience — include it
   - Do not use "N/A", "Unknown", or any placeholder — just use ""

8. OUTPUT FORMAT:
   - Return ONLY a valid JSON object
   - No markdown, no backticks, no explanation
   - No text before or after the JSON
   - Use UTF-8 for special characters (é, à, ñ, etc.)
   - IMPORTANT: Make sure the JSON is complete and properly closed with all brackets and braces
   - IMPORTANT: Do NOT truncate the experiences array — include every single experience

=== JSON STRUCTURE ===
{
  "nom": "LAST NAME in uppercase",
  "prenom": "First name capitalized",
  "email": "email@domain.com",
  "telephone": "phone number as written",
  "ville": "city",
  "pays": "country",
  "linkedin": "linkedin URL or username",
  "github": "github URL or username",
  "portfolio": "website or portfolio URL",
  "titre": "job title or professional headline",
  "resume": "2-3 sentence professional summary",
  "competences_techniques": "tech skill1, skill2, skill3...",
  "langues": "Language1 (level), Language2 (level)",
  "formations": [
    {
      "diplome": "degree name",
      "etablissement": "school or university name",
      "annee": "year or period, or empty string if unknown"
    }
  ],
  "experiences": [
    {
      "poste": "job title, or empty string if unknown",
      "entreprise": "company name, or empty string if unknown",
      "periode": "start - end dates, or empty string if no date found",
      "description": "verbatim text from the CV (no rewriting), or empty string",
      "outils": "tool1, tool2, or empty string"
    }
  ],
  "certifications": "• cert1, 
  • cert2...",
  "projets": "project1, project2...",
  "centres_interet": "interest1, interest2..."
}

=== NAME EXAMPLES ===
Top of CV says "BENALI Mohamed"        → nom: "BENALI",       prenom: "Mohamed"
Top of CV says "Sarah MARTIN"          → nom: "MARTIN",       prenom: "Sarah"
Top of CV says "Ahmed El Fassi"        → nom: "EL FASSI",     prenom: "Ahmed"
Top of CV says "jean-pierre dupont"    → nom: "DUPONT",       prenom: "Jean-Pierre"
Top of CV says "Yuki Tanaka"           → nom: "TANAKA",       prenom: "Yuki"
Top of CV says "Maria Garcia Lopez"    → nom: "GARCIA LOPEZ", prenom: "Maria"
Top of CV says "Li Wei"                → nom: "LI",           prenom: "Wei"
Top of CV says "Fatima-Zahra IDRISSI"  → nom: "IDRISSI",      prenom: "Fatima-Zahra"
Top of CV says "VAN DER BERG Johan"    → nom: "VAN DER BERG", prenom: "Johan"
Top of CV says "O'Brien Patrick"       → nom: "O'BRIEN",      prenom: "Patrick"

=== EXPERIENCE EXAMPLES (with and without dates) ===
Example WITH date:
  poste: "Développeur Web", entreprise: "Acme Corp", periode: "Jan 2022 - Déc 2023"

Example WITHOUT date (still include it!):
  poste: "Stage PFE", entreprise: "StartupXYZ", periode: ""

Example with ONLY company, no title, no date (still include it!):
  poste: "", entreprise: "FreelanceProject", periode: ""

=== CV TO ANALYZE ===

PROMPT;

$prompt .= "\n\n" . substr($text, 0, 12000);

// =====================
// APPEL GEMINI API (IDENTIQUE - NON MODIFIÉ)
// =====================
$url     = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $geminiApiKey;
$payload = json_encode([
    'contents' => [[
        'parts' => [[
            'text' => $prompt
        ]]
    ]],
    'generationConfig' => [
        'temperature'     => 0.1,
        'maxOutputTokens' => 8192,
    ]
], JSON_INVALID_UTF8_SUBSTITUTE);

if ($payload === false) {
    setImportError("Erreur d'encodage de la requête : " . json_last_error_msg());
    header("Location: importationCV.php");
    exit;
}

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_TIMEOUT        => 60,
]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// =====================
// GESTION ERREURS CURL (AVEC TOAST)
// =====================
if ($curlError) {
    setImportError("Impossible de contacter l'API Gemini. Vérifiez votre connexion Internet.");
    header("Location: importationCV.php");
    exit;
}

if ($httpCode !== 200) {
    $errorData = json_decode($response, true);
    $errorMsg  = $errorData['error']['message'] ?? "Erreur API HTTP $httpCode";
    setImportError("Gemini API: " . $errorMsg);
    header("Location: importationCV.php");
    exit;
}

// =====================
// PARSING RÉPONSE GEMINI (IDENTIQUE - NON MODIFIÉ)
// =====================
$geminiData = json_decode($response, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);

if (!isset($geminiData['candidates'][0]['content']['parts'][0]['text'])) {
    setImportError("Réponse Gemini invalide ou vide");
    header("Location: importationCV.php");
    exit;
}

$rawText = $geminiData['candidates'][0]['content']['parts'][0]['text'];

// Nettoyage markdown
$rawText = preg_replace('/^```(?:json)?\s*/i', '', trim($rawText));
$rawText = preg_replace('/\s*```$/i', '', $rawText);
$rawText = trim($rawText);

// ----------------------
// Tentative 1: parser directement
// ----------------------
$parsed = json_decode($rawText, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);

// ----------------------
// Tentative 2: extraire JSON avec regex
// ----------------------
if (!$parsed || !is_array($parsed)) {
    if (preg_match('/\{.*\}/s', $rawText, $match)) {
        $parsed = json_decode($match[0], true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
    }
}

// ----------------------
// Tentative 3: réparer JSON tronqué
// ----------------------
if (!$parsed || !is_array($parsed)) {
    $fixed = $rawText;

    // Remove trailing comma before closing bracket/brace (common truncation artifact)
    $fixed = preg_replace('/,\s*$/', '', $fixed);
    $fixed = preg_replace('/,\s*([\}\]])/', '$1', $fixed);

    // Close open strings
    $cleanFixed = preg_replace('/\\\\\"/', '', $fixed);
    if (substr_count($cleanFixed, '"') % 2 !== 0) {
        $fixed .= '"';
    }

    // Close open arrays and objects
    $openBrackets = substr_count($fixed, '[') - substr_count($fixed, ']');
    $openBraces   = substr_count($fixed, '{') - substr_count($fixed, '}');

    for ($i = 0; $i < $openBrackets; $i++) $fixed .= ']';
    for ($i = 0; $i < $openBraces; $i++)   $fixed .= '}';

    $parsed = json_decode($fixed, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
}

if (!$parsed || !is_array($parsed)) {
    setImportError("Impossible de parser la réponse Gemini");
    header("Location: importationCV.php");
    exit;
}

// =====================
// FALLBACK NOM (IDENTIQUE - NON MODIFIÉ)
// =====================
if (empty($parsed['nom'])) {
    $lines = explode("\n", $text);
    foreach ($lines as $line) {
        $line = trim($line);
        if (
            strlen($line) > 3 &&
            strlen($line) < 60 &&
            !str_contains($line, '@') &&
            !str_contains($line, 'http') &&
            !preg_match('/^\+?[0-9\s\-]{7,}$/', $line)
        ) {
            $parts = explode(' ', $line);
            if (count($parts) >= 2) {
                foreach ($parts as $part) {
                    if ($part === strtoupper($part) && strlen($part) > 2) {
                        $parsed['nom'] = $part;
                    } else {
                        $parsed['prenom'] = ucfirst(strtolower($part));
                    }
                }
                if (empty($parsed['nom'])) {
                    $parsed['prenom'] = ucfirst(strtolower($parts[0]));
                    $parsed['nom']    = strtoupper($parts[1]);
                }
                break;
            }
        }
    }
}

// =====================
// NORMALISATION EXPÉRIENCES (IDENTIQUE - NON MODIFIÉ)
// =====================
$experiences = [];
if (!empty($parsed['experiences']) && is_array($parsed['experiences'])) {
    foreach ($parsed['experiences'] as $e) {
        $poste       = trim((string)($e['poste']       ?? ''));
        $entreprise  = trim((string)($e['entreprise']  ?? ''));
        $description = trim((string)($e['description'] ?? ''));
        $periode     = trim((string)($e['periode']     ?? ''));
        $outils      = trim((string)($e['outils']      ?? ''));

        if ($poste === '' && $entreprise === '' && $description === '') {
            continue;
        }

        $experiences[] = [
            'periode'     => $periode,
            'poste'       => $poste,
            'entreprise'  => $entreprise,
            'description' => $description,
            'outils'      => $outils,
        ];
    }
} elseif (!empty($parsed['experiences']) && is_string($parsed['experiences'])) {
    $experiences[] = [
        'periode'     => '',
        'poste'       => '',
        'entreprise'  => '',
        'description' => trim($parsed['experiences']),
        'outils'      => '',
    ];
}

// =====================
// NORMALISATION FORMATIONS (IDENTIQUE - NON MODIFIÉ)
// =====================
$diplomes = [];
if (!empty($parsed['formations']) && is_array($parsed['formations'])) {
    foreach ($parsed['formations'] as $f) {
        $titre          = trim((string)($f['diplome']        ?? ''));
        $etablissement  = trim((string)($f['etablissement']  ?? ''));
        $annee          = trim((string)($f['annee']          ?? ''));

        if ($titre === '' && $etablissement === '') continue;

        $diplomes[] = [
            'annee'         => $annee,
            'titre'         => $titre,
            'etablissement' => $etablissement,
        ];
    }
} elseif (!empty($parsed['formations']) && is_string($parsed['formations'])) {
    $diplomes[] = [
        'annee'         => '',
        'titre'         => trim($parsed['formations']),
        'etablissement' => '',
    ];
}

// =====================
// NORMALISATION COMPÉTENCES (IDENTIQUE - NON MODIFIÉ)
// =====================
$competences = trim(implode(' | ', array_filter([
    trim((string)($parsed['competences_techniques'] ?? '')),
    trim((string)($parsed['competences_soft']       ?? '')),
])));

// =====================
// NORMALISATION CERTIFICATIONS (AJOUTÉ POUR TABLEAU)
// =====================
function normalizeCertifications($certs) {
    if (empty($certs)) return [];
    if (is_array($certs)) return $certs;
    if (strpos($certs, ',') !== false) return array_map('trim', explode(',', $certs));
    if (strpos($certs, "\n") !== false) return array_map('trim', explode("\n", $certs));
    if (strpos($certs, '•') !== false) return array_map('trim', explode('•', $certs));
    return [trim($certs)];
}

// =====================
// CONSTRUCTION JSON FINAL (AVEC NORMALISATION CERTIFS)
// =====================
$finalData = [
    'nom'            => strtoupper(trim((string)($parsed['nom']            ?? ''))),
    'prenom'         => ucfirst(strtolower(trim((string)($parsed['prenom'] ?? '')))),
    'poste'          => trim((string)($parsed['titre']          ?? '')),
    'email'          => strtolower(trim((string)($parsed['email']          ?? ''))),
    'telephone'      => trim((string)($parsed['telephone']      ?? '')),
    'competences'    => $competences,
    'langues'        => trim((string)($parsed['langues']        ?? '')),
    'certifications' => normalizeCertifications($parsed['certifications'] ?? ''),
    'diplomes'       => $diplomes,
    'experiences'    => $experiences,
];

// =====================
// REDIRECTION (AVEC TOAST SUCCÈS)
// =====================
$logo_type   = $_POST['logo_type'] ?? 'invest';
$texte_brut  = substr($text, 0, 3000);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$_SESSION['import_data']       = $finalData;
$_SESSION['import_logo_type']  = $logo_type;
$_SESSION['import_texte_brut'] = $texte_brut;

$_SESSION['import_toast'] = ['message' => "CV importé avec succès !", 'type' => 'success'];
header("Location: creationCV.php");
exit();