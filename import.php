<?php
use Dotenv\Dotenv;

session_start();
require 'vendor/autoload.php';

function setImportError($message) {
    $_SESSION['import_toast'] = ['message' => $message, 'type' => 'error'];
}

function redirectImportError($message) {
    setImportError($message);
    header("Location: importationCV.php");
    exit;
}

function lowerText($value) {
    $value = trim((string)$value);
    return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
}

function upperText($value) {
    $value = trim((string)$value);
    return function_exists('mb_strtoupper') ? mb_strtoupper($value, 'UTF-8') : strtoupper($value);
}

function titleText($value) {
    $value = lowerText($value);
    return function_exists('mb_convert_case')
        ? mb_convert_case($value, MB_CASE_TITLE, 'UTF-8')
        : ucwords($value, " \t\r\n\f\v-'");
}

function flattenStrings($value) {
    if (is_array($value)) {
        $items = [];
        foreach ($value as $item) {
            $items = array_merge($items, flattenStrings($item));
        }
        return $items;
    }

    if (is_bool($value) || $value === null) {
        return [];
    }

    $value = trim((string)$value);
    return $value === '' ? [] : [$value];
}

function stringifyValue($value, $separator = ', ') {
    return implode($separator, flattenStrings($value));
}

function cleanExtractedText($text) {
    $text = str_replace(["\r\n", "\r"], "\n", (string)$text);
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace("/\n[ \t]+/", "\n", $text);
    $text = preg_replace("/\n{4,}/", "\n\n\n", $text);
    return trim($text);
}

function tryJsonDecode($jsonText) {
    $decoded = json_decode($jsonText, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
    return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : null;
}

function repairJsonText($jsonText) {
    $fixed = trim($jsonText);
    $fixed = preg_replace('/,\s*$/', '', $fixed);
    $fixed = preg_replace('/,\s*([\}\]])/', '$1', $fixed);

    $withoutEscapedQuotes = preg_replace('/\\\\\"/', '', $fixed);
    if (substr_count($withoutEscapedQuotes, '"') % 2 !== 0) {
        $fixed .= '"';
    }

    $openBrackets = substr_count($fixed, '[') - substr_count($fixed, ']');
    $openBraces   = substr_count($fixed, '{') - substr_count($fixed, '}');

    for ($i = 0; $i < $openBrackets; $i++) {
        $fixed .= ']';
    }

    for ($i = 0; $i < $openBraces; $i++) {
        $fixed .= '}';
    }

    return $fixed;
}

function parseJsonFromGeminiText($rawText) {
    $rawText = trim((string)$rawText);
    $rawText = preg_replace('/^\xEF\xBB\xBF/', '', $rawText);
    $rawText = preg_replace('/^```(?:json)?\s*/i', '', $rawText);
    $rawText = preg_replace('/\s*```$/i', '', $rawText);
    $rawText = trim($rawText);

    $candidates = [$rawText];

    $firstBrace = strpos($rawText, '{');
    if ($firstBrace !== false) {
        $lastBrace = strrpos($rawText, '}');
        $candidates[] = $lastBrace !== false
            ? substr($rawText, $firstBrace, $lastBrace - $firstBrace + 1)
            : substr($rawText, $firstBrace);
    }

    $firstBracket = strpos($rawText, '[');
    if ($firstBracket !== false) {
        $lastBracket = strrpos($rawText, ']');
        $candidates[] = $lastBracket !== false
            ? substr($rawText, $firstBracket, $lastBracket - $firstBracket + 1)
            : substr($rawText, $firstBracket);
    }

    $candidates = array_values(array_unique(array_filter($candidates, fn($candidate) => trim($candidate) !== '')));

    foreach ($candidates as $candidate) {
        $decoded = tryJsonDecode($candidate);
        if ($decoded !== null) {
            return $decoded;
        }
    }

    foreach ($candidates as $candidate) {
        $decoded = tryJsonDecode(repairJsonText($candidate));
        if ($decoded !== null) {
            return $decoded;
        }
    }

    return null;
}

function callGeminiJson($apiKey, $prompt, $maxOutputTokens = 4096, $label = 'Gemini') {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . urlencode($apiKey);
    $payload = json_encode([
        'contents' => [[
            'parts' => [[
                'text' => $prompt,
            ]],
        ]],
        'generationConfig' => [
            'temperature'      => 0.1,
            'maxOutputTokens'  => $maxOutputTokens,
            'responseMimeType' => 'application/json',
        ],
    ], JSON_INVALID_UTF8_SUBSTITUTE);

    if ($payload === false) {
        throw new RuntimeException("Erreur d'encodage de la requete Gemini : " . json_last_error_msg());
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 90,
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $curlError) {
        throw new RuntimeException("Impossible de contacter l'API Gemini. Verifiez votre connexion Internet.");
    }

    if ($httpCode !== 200) {
        $errorData = json_decode((string)$response, true);
        $errorMsg  = $errorData['error']['message'] ?? trim((string)$response);
        $errorStatus = $errorData['error']['status'] ?? '';
        $message = "Gemini API HTTP $httpCode";

        if ($errorStatus !== '') {
            $message .= " [$errorStatus]";
        }

        if ($errorMsg !== '') {
            $message .= "\n" . $errorMsg;
        }

        throw new RuntimeException($message);
    }

    $geminiData = json_decode((string)$response, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
    $rawText = $geminiData['candidates'][0]['content']['parts'][0]['text'] ?? null;

    if ($rawText === null) {
        $blockReason = $geminiData['promptFeedback']['blockReason'] ?? '';
        $suffix = $blockReason !== '' ? " ($blockReason)" : '';
        throw new RuntimeException("$label: reponse Gemini invalide ou vide$suffix");
    }

    $parsed = parseJsonFromGeminiText($rawText);
    if ($parsed === null) {
        throw new RuntimeException("$label: impossible de parser la reponse Gemini");
    }

    return $parsed;
}

function buildHeaderPrompt($headerText) {
    $today = date('Y-m-d');

    return <<<PROMPT
You are a world-class CV/Resume parser.

Task: Extract ONLY the stable header/profile information from the top of this CV.
Current date: {$today}

Rules:
- The name is usually the first human name at the very top, often without a label.
- If a field is missing, return an empty string "".
- Do NOT invent data.
- Do NOT extract professional experiences in this pass.
- Return ONLY one valid JSON object. No markdown, no explanation.
- Use UTF-8.

Return exactly this structure:
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
  "competences_soft": "soft skill1, soft skill2...",
  "langues": "Language1 (level), Language2 (level)",
  "formations": [
    {
      "diplome": "degree name",
      "etablissement": "school or university name",
      "annee": "year or period, or empty string if unknown"
    }
  ],
  "certifications": "cert1, cert2...",
  "projets": "project1, project2...",
  "centres_interet": "interest1, interest2..."
}

=== CV HEADER / PROFILE TEXT (first 3000 chars only) ===
{$headerText}
PROMPT;
}

function buildExperiencePrompt($chunkText, $chunkIndex, $chunkTotal) {
    return <<<PROMPT
You are a CV experience extraction specialist.

Task: Extract professional experiences from this CV experience chunk.
Chunk: {$chunkIndex} / {$chunkTotal}

Critical rules:
- Return ONLY a valid JSON object. No markdown, no explanation.
- Return this structure: { "experiences": [ ... ] }
- Include EVERY experience found in this chunk, even if it has NO date.
- If no date/period is found, use "" for "periode".
- Do NOT use N/A, Unknown, or placeholders.
- Do NOT rewrite, summarize, or regenerate experience descriptions.
- Keep descriptions verbatim from the CV and in the original order.
- Extract tools/technologies from the description or bullets into "outils" as a comma-separated list.
- If no professional experience is found, return { "experiences": [] }.
- Do not include education entries unless they are explicitly internships, apprenticeships, freelance work, jobs, or professional projects.

Each experience must have:
{
  "poste": "job title, or empty string if unknown",
  "entreprise": "company name, or empty string if unknown",
  "periode": "start - end dates as written, or empty string if no date found",
  "description": "verbatim text from the CV, or empty string",
  "outils": "tool1, tool2, or empty string"
}

=== CV EXPERIENCE CHUNK ===
{$chunkText}
PROMPT;
}

function locateExperienceSection($text) {
    $normalized = cleanExtractedText($text);

    $startPattern = '/(?:^|\n)\s*(experiences?|expériences?|experience professionnelle|expérience professionnelle|professional experience|work experience|employment history|parcours professionnel|stages?|internships?)\s*:?\s*(?:\n|$)/iu';

    if (preg_match($startPattern, $normalized, $startMatch, PREG_OFFSET_CAPTURE)) {
        $start = $startMatch[0][1] + strlen($startMatch[0][0]);
        $tail = substr($normalized, $start);

        $endPattern = '/\n\s*(formations?|education|éducation|academic background|diplomes?|diplômes?|competences?|compétences?|skills?|langues?|languages?|certifications?|projets?|projects?|centres?\s+d.interets?|centres?\s+d.intérêts?|interests?|references?)\s*:?\s*(?:\n|$)/iu';

        if (preg_match($endPattern, $tail, $endMatch, PREG_OFFSET_CAPTURE)) {
            $section = substr($tail, 0, $endMatch[0][1]);
            return trim($section) !== '' ? trim($section) : $normalized;
        }

        return trim($tail) !== '' ? trim($tail) : $normalized;
    }

    return $normalized;
}

function chunkText($text, $size = 2500, $overlap = 250) {
    $text = trim((string)$text);
    if ($text === '') {
        return [];
    }

    $chunks = [];
    $length = strlen($text);
    $offset = 0;

    while ($offset < $length) {
        $end = min($offset + $size, $length);

        if ($end < $length) {
            $slice = substr($text, $offset, $size);
            $doubleBreak = strrpos($slice, "\n\n");
            $singleBreak = strrpos($slice, "\n");
            $break = max($doubleBreak === false ? -1 : $doubleBreak, $singleBreak === false ? -1 : $singleBreak);

            if ($break > (int)($size * 0.45)) {
                $end = $offset + $break;
            }
        }

        $chunk = trim(substr($text, $offset, $end - $offset));
        if ($chunk !== '') {
            $chunks[] = $chunk;
        }

        if ($end >= $length) {
            break;
        }

        $offset = max($end - $overlap, $offset + 1);
    }

    return $chunks;
}

function normalizeExperiences($rawExperiences) {
    if (is_array($rawExperiences) && isset($rawExperiences['experiences'])) {
        $rawExperiences = $rawExperiences['experiences'];
    }

    if (is_string($rawExperiences) && trim($rawExperiences) !== '') {
        $rawExperiences = [[
            'periode'     => '',
            'poste'       => '',
            'entreprise'  => '',
            'description' => trim($rawExperiences),
            'outils'      => '',
        ]];
    }

    if (!is_array($rawExperiences)) {
        return [];
    }

    if (!array_is_list($rawExperiences) && (
        isset($rawExperiences['poste']) ||
        isset($rawExperiences['entreprise']) ||
        isset($rawExperiences['description']) ||
        isset($rawExperiences['periode'])
    )) {
        $rawExperiences = [$rawExperiences];
    }

    $experiences = [];
    foreach ($rawExperiences as $experience) {
        if (is_string($experience)) {
            $experience = ['description' => $experience];
        }

        if (!is_array($experience)) {
            continue;
        }

        $poste       = trim(stringifyValue($experience['poste'] ?? ''));
        $entreprise  = trim(stringifyValue($experience['entreprise'] ?? ''));
        $description = trim(stringifyValue($experience['description'] ?? '', "\n"));
        $periode     = trim(stringifyValue($experience['periode'] ?? ''));
        $outils      = trim(stringifyValue($experience['outils'] ?? ''));

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

    return $experiences;
}

function normalizeKeyText($value) {
    $value = lowerText($value);
    $value = preg_replace('/\s+/', ' ', $value);
    $value = preg_replace('/[^a-z0-9]+/i', '', $value);
    return trim((string)$value);
}

function mergeToolLists($left, $right) {
    $items = [];
    foreach ([$left, $right] as $value) {
        foreach (preg_split('/[,;|\n]+/', (string)$value) as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }

            $key = normalizeKeyText($item);
            if ($key !== '') {
                $items[$key] = $item;
            }
        }
    }

    return implode(', ', array_values($items));
}

function mergeExperience($existing, $incoming) {
    foreach (['periode', 'poste', 'entreprise'] as $field) {
        if (trim($existing[$field] ?? '') === '' && trim($incoming[$field] ?? '') !== '') {
            $existing[$field] = $incoming[$field];
        }
    }

    $existingDescription = trim($existing['description'] ?? '');
    $incomingDescription = trim($incoming['description'] ?? '');

    if ($existingDescription === '') {
        $existing['description'] = $incomingDescription;
    } elseif ($incomingDescription !== '' && stripos($existingDescription, $incomingDescription) === false) {
        if (stripos($incomingDescription, $existingDescription) !== false) {
            $existing['description'] = $incomingDescription;
        } else {
            $existing['description'] = $existingDescription . "\n" . $incomingDescription;
        }
    }

    $existing['outils'] = mergeToolLists($existing['outils'] ?? '', $incoming['outils'] ?? '');

    return $existing;
}

function dedupeExperiences($experiences) {
    $merged = [];

    foreach ($experiences as $experience) {
        $key = normalizeKeyText(($experience['poste'] ?? '') . '|' . ($experience['entreprise'] ?? '') . '|' . ($experience['periode'] ?? ''));

        if ($key === '') {
            $key = substr(normalizeKeyText($experience['description'] ?? ''), 0, 180);
        }

        if ($key === '') {
            continue;
        }

        if (!isset($merged[$key])) {
            $merged[$key] = $experience;
        } else {
            $merged[$key] = mergeExperience($merged[$key], $experience);
        }
    }

    return array_values($merged);
}

function parseMonthNumber($value) {
    $value = lowerText($value);
    $months = [
        'janvier' => 1, 'janv' => 1, 'jan' => 1, 'january' => 1,
        'février' => 2, 'fevrier' => 2, 'févr' => 2, 'fevr' => 2, 'feb' => 2, 'february' => 2,
        'mars' => 3, 'mar' => 3, 'march' => 3,
        'avril' => 4, 'avr' => 4, 'apr' => 4, 'april' => 4,
        'mai' => 5, 'may' => 5,
        'juin' => 6, 'jun' => 6, 'june' => 6,
        'juillet' => 7, 'juil' => 7, 'jul' => 7, 'july' => 7,
        'août' => 8, 'aout' => 8, 'aug' => 8, 'august' => 8,
        'septembre' => 9, 'sept' => 9, 'sep' => 9, 'september' => 9,
        'octobre' => 10, 'oct' => 10, 'october' => 10,
        'novembre' => 11, 'nov' => 11, 'november' => 11,
        'décembre' => 12, 'decembre' => 12, 'déc' => 12, 'dec' => 12, 'december' => 12,
    ];

    foreach ($months as $name => $number) {
        if (preg_match('/\b' . preg_quote($name, '/') . '\b/u', $value)) {
            return $number;
        }
    }

    return null;
}

function splitPeriodRange($period) {
    $period = trim(preg_replace('/\s+/', ' ', str_replace(["\r", "\n"], ' ', (string)$period)));

    if ($period === '') {
        return null;
    }

    if (preg_match('/\b(?:depuis|since)\s+(.+)$/iu', $period, $match)) {
        return [$match[1], 'present'];
    }

    if (preg_match('/^\s*((?:19|20)\d{2})\s*\/\s*((?:19|20)\d{2})\s*$/', $period, $match)) {
        return [$match[1], $match[2]];
    }

    $patterns = [
        "/^(.+?)\s+(?:-|–|—|to|au|a|à|jusqu['’]?a|jusqu['’]?à)\s+(.+)$/iu",
        '/^(.+?\d{4})\s*[-–—]\s*(.+)$/u',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $period, $match)) {
            return [trim($match[1]), trim($match[2])];
        }
    }

    return null;
}

function parsePeriodEndpoint($value, $isEnd, DateTimeImmutable $today) {
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }

    $lower = lowerText($value);
    if (preg_match('/\b(present|current|today|now|ongoing|actuel|actuelle|présent|present|aujourd|maintenant|en cours|a ce jour|à ce jour)\b/iu', $lower)) {
        return [
            'year'      => (int)$today->format('Y'),
            'month'     => (int)$today->format('n'),
            'precision' => 'month',
        ];
    }

    if (preg_match('/\b((?:19|20)\d{2})\s*[\/\.]\s*(0?[1-9]|1[0-2])\b/', $lower, $match)) {
        return ['year' => (int)$match[1], 'month' => (int)$match[2], 'precision' => 'month'];
    }

    if (preg_match('/\b(0?[1-9]|1[0-2])\s*[\/\.]\s*((?:19|20)\d{2})\b/', $lower, $match)) {
        return ['year' => (int)$match[2], 'month' => (int)$match[1], 'precision' => 'month'];
    }

    if (preg_match('/\b((?:19|20)\d{2})\s*-\s*(0?[1-9]|1[0-2])\b/', $lower, $match)) {
        return ['year' => (int)$match[1], 'month' => (int)$match[2], 'precision' => 'month'];
    }

    if (preg_match('/\b(0?[1-9]|1[0-2])\s*-\s*((?:19|20)\d{2})\b/', $lower, $match)) {
        return ['year' => (int)$match[2], 'month' => (int)$match[1], 'precision' => 'month'];
    }

    $month = parseMonthNumber($lower);
    if ($month !== null && preg_match('/\b((?:19|20)\d{2})\b/', $lower, $match)) {
        return ['year' => (int)$match[1], 'month' => $month, 'precision' => 'month'];
    }

    if (preg_match('/\b((?:19|20)\d{2})\b/', $lower, $match)) {
        return [
            'year'      => (int)$match[1],
            'month'     => $isEnd ? 12 : 1,
            'precision' => 'year',
        ];
    }

    return null;
}

function periodDurationMonths($period, DateTimeImmutable $today) {
    $parts = splitPeriodRange($period);
    if ($parts === null) {
        return 0;
    }

    $start = parsePeriodEndpoint($parts[0], false, $today);
    $end   = parsePeriodEndpoint($parts[1], true, $today);

    if ($start === null || $end === null) {
        return 0;
    }

    if ($start['precision'] === 'year' && $end['precision'] === 'year') {
        return max(0, ($end['year'] - $start['year']) * 12);
    }

    $months = (($end['year'] * 12) + $end['month']) - (($start['year'] * 12) + $start['month']);

    $months += 1;

    return max(0, $months);
}

function formatExperienceDuration($totalMonths) {
    $totalMonths = max(0, (int)$totalMonths);
    if ($totalMonths === 0) {
        return '0 an';
    }

    $years = intdiv($totalMonths, 12);
    $months = $totalMonths % 12;

    if ($years > 0 && $months > 0) {
        return $years . ' ' . ($years === 1 ? 'an' : 'ans') . ' et ' . $months . ' mois';
    }

    if ($years > 0) {
        return $years . ' ' . ($years === 1 ? 'an' : 'ans');
    }

    return $months . ' mois';
}

function calculateTotalExperience($experiences) {
    $today = new DateTimeImmutable('first day of this month');
    $totalMonths = 0;

    foreach ($experiences as $experience) {
        $totalMonths += periodDurationMonths($experience['periode'] ?? '', $today);
    }

    return formatExperienceDuration($totalMonths);
}

function normalizeDiplomes($formations) {
    if (is_string($formations) && trim($formations) !== '') {
        $formations = [[
            'diplome'       => trim($formations),
            'etablissement' => '',
            'annee'         => '',
        ]];
    }

    if (!is_array($formations)) {
        return [];
    }

    if (!array_is_list($formations) && (
        isset($formations['diplome']) ||
        isset($formations['titre']) ||
        isset($formations['etablissement']) ||
        isset($formations['annee'])
    )) {
        $formations = [$formations];
    }

    $diplomes = [];
    foreach ($formations as $formation) {
        if (is_string($formation)) {
            $formation = ['diplome' => $formation];
        }

        if (!is_array($formation)) {
            continue;
        }

        $titre = trim(stringifyValue($formation['diplome'] ?? $formation['titre'] ?? ''));
        $etablissement = trim(stringifyValue($formation['etablissement'] ?? $formation['ecole'] ?? ''));
        $annee = trim(stringifyValue($formation['annee'] ?? $formation['periode'] ?? ''));

        if ($titre === '' && $etablissement === '') {
            continue;
        }

        $diplomes[] = [
            'annee'         => $annee,
            'titre'         => $titre,
            'etablissement' => $etablissement,
        ];
    }

    return $diplomes;
}

function normalizeCertifications($certs) {
    $items = flattenStrings($certs);
    $normalized = [];

    foreach ($items as $item) {
        foreach (preg_split('/\r\n|\r|\n|,|;|•|▪|·/u', $item) as $cert) {
            $cert = trim($cert, " \t\n\r\0\x0B-*");
            if ($cert !== '') {
                $normalized[] = $cert;
            }
        }
    }

    return array_values(array_unique($normalized));
}

function applyNameFallback(&$parsed, $text) {
    if (!empty($parsed['nom'])) {
        return;
    }

    $lines = explode("\n", $text);
    foreach ($lines as $line) {
        $line = trim($line);
        if (
            strlen($line) <= 3 ||
            strlen($line) >= 60 ||
            str_contains($line, '@') ||
            stripos($line, 'http') !== false ||
            preg_match('/^\+?[0-9\s\-().]{7,}$/', $line)
        ) {
            continue;
        }

        $parts = preg_split('/\s+/', $line);
        if (count($parts) < 2 || count($parts) > 5) {
            continue;
        }

        $upperParts = [];
        $firstNameParts = [];

        foreach ($parts as $part) {
            if ($part === upperText($part) && strlen($part) > 1) {
                $upperParts[] = $part;
            } else {
                $firstNameParts[] = $part;
            }
        }

        if (!empty($upperParts)) {
            $parsed['nom'] = upperText(implode(' ', $upperParts));
            $parsed['prenom'] = titleText(implode(' ', $firstNameParts));
        } else {
            $parsed['prenom'] = titleText($parts[0]);
            $parsed['nom'] = upperText(implode(' ', array_slice($parts, 1)));
        }

        return;
    }
}

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$geminiApiKey = $_ENV['API_KEY'] ?? $_SERVER['API_KEY'] ?? getenv('API_KEY');
if (!$geminiApiKey) {
    redirectImportError("Clé API Gemini manquante dans le fichier .env");
}

if (!isset($_FILES['cv_file']) || $_FILES['cv_file']['error'] !== UPLOAD_ERR_OK) {
    redirectImportError("Aucun fichier envoyé ou erreur d'upload");
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
        redirectImportError("Erreur lecture PDF: " . $e->getMessage());
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
        redirectImportError("Erreur lecture DOCX: " . $e->getMessage());
    }
} elseif ($type === "txt") {
    $text = file_get_contents($tmpPath);
    if ($text === false) {
        redirectImportError("Erreur lecture fichier TXT");
    }
} else {
    redirectImportError("Format non supporté. Utilisez PDF, DOCX ou TXT");
}

$text = cleanExtractedText($text);
if ($text === '') {
    redirectImportError("Aucun texte extrait. Le fichier est peut-être scanné ou protégé.");
}

try {
    // Pass 1: header/profile extraction from the top of the CV only.
    $headerText = substr($text, 0, 3000);
    $parsed = callGeminiJson($geminiApiKey, buildHeaderPrompt($headerText), 4096, 'Pass 1 header');

    // Pass 2: locate the experience section, chunk it, and extract experiences per chunk.
    $experienceSection = locateExperienceSection($text);
    $chunks = chunkText($experienceSection, 2500, 250);

    $allExperiences = [];
    $chunkTotal = max(1, count($chunks));

    foreach ($chunks as $index => $chunk) {
        $chunkParsed = callGeminiJson(
            $geminiApiKey,
            buildExperiencePrompt($chunk, $index + 1, $chunkTotal),
            4096,
            'Experience chunk ' . ($index + 1)
        );

        $allExperiences = array_merge($allExperiences, normalizeExperiences($chunkParsed));
    }

    $experiences = dedupeExperiences($allExperiences);
    $parsed['experiences'] = $experiences;
    $parsed['annees_experience'] = calculateTotalExperience($experiences);
} catch (Throwable $e) {
    echo '<pre style="margin:24px;padding:16px;border:1px solid #ef4444;background:#fef2f2;color:#991b1b;border-radius:8px;white-space:pre-wrap;">';
    echo htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo '</pre>';
    exit;
}

applyNameFallback($parsed, $text);

$diplomes = normalizeDiplomes($parsed['formations'] ?? []);

$competences = trim(implode(' | ', array_filter([
    trim(stringifyValue($parsed['competences_techniques'] ?? '')),
    trim(stringifyValue($parsed['competences_soft'] ?? '')),
])));

$finalData = [
    'nom'                => upperText($parsed['nom'] ?? ''),
    'prenom'             => titleText($parsed['prenom'] ?? ''),
    'poste'              => trim(stringifyValue($parsed['titre'] ?? '')),
    'email'              => lowerText($parsed['email'] ?? ''),
    'telephone'          => trim(stringifyValue($parsed['telephone'] ?? '')),
    'competences'        => $competences,
    'langues'            => trim(stringifyValue($parsed['langues'] ?? '')),
    'certifications'     => normalizeCertifications($parsed['certifications'] ?? ''),
    'diplomes'           => $diplomes,
    'experiences'        => $parsed['experiences'] ?? [],
    'annees_experience'  => $parsed['annees_experience'] ?? '0 an',
];

$logo_type  = $_POST['logo_type'] ?? 'invest';
$texte_brut = substr($text, 0, 3000);

$upload_dir = __DIR__ . '/uploads/originals/';
if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
    redirectImportError("Impossible de créer le dossier d'upload");
}

$original_filename = uniqid() . '_' . basename($_FILES['cv_file']['name']);
$original_path = $upload_dir . $original_filename;

if (!copy($tmpPath, $original_path)) {
    redirectImportError("Impossible de sauvegarder le fichier original");
}

$finalData['fichier_original'] = 'uploads/originals/' . $original_filename;

$_SESSION['import_data']       = $finalData;
$_SESSION['import_logo_type']  = $logo_type;
$_SESSION['import_texte_brut'] = $texte_brut;
$_SESSION['import_toast']      = ['message' => "CV importé avec succès !", 'type' => 'success'];

header("Location: creationCV.php");
exit;
