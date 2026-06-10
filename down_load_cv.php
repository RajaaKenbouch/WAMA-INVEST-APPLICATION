<?php
require_once 'db.php';

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT pdf_path, username FROM cv WHERE id = ?");
$stmt->execute([$id]);
$cv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cv || empty($cv['pdf_path']) || !file_exists($cv['pdf_path'])) {
    die("PDF non trouvé. Veuillez générer le CV à nouveau.");
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="CV_' . $cv['username'] . '.pdf"');
readfile($cv['pdf_path']);
exit;
?>