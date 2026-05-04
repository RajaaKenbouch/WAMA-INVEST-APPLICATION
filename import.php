<?php
echo "<pre>";
print_r($_FILES);
echo "</pre>";
exit;

require_once 'vendor/autoload.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cv_file'])) {
    
    $file = $_FILES['cv_file'];
    $filetype = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filepath = $file['tmp_name'];
    

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }
    

    $unique_name = uniqid() . '_' . basename($file['name']);
    $target_path = 'uploads/' . $unique_name;
    move_uploaded_file($filepath, $target_path);
    

    $python_script = __DIR__ . '/python/extract_cv.py';
    $command = 'py ' . escapeshellarg($python_script) . ' ' . escapeshellarg($target_path);
    $output = shell_exec($command . ' 2>&1');
    

    unlink($target_path);
    

    $data = json_decode($output, true);
    
    if (!$data || isset($data['error'])) {
        $error_msg = $data['error'] ?? 'Erreur inconnue';
        header("Location: importationCV.php?error=extraction&msg=" . urlencode($error_msg));
        exit;
    }
    

    $logo_type = $_POST['logo_type'] ?? 'invest';
    
    header("Location: creationCV.php?" . http_build_query([
        'nom' => $data['nom'] ?? '',
        'prenom' => $data['prenom'] ?? '',
        'email' => $data['email'] ?? '',
        'telephone' => $data['telephone'] ?? '',
        'competences' => $data['competences'] ?? '',
        'langues' => $data['langues'] ?? '',
        'diplomes' => $data['diplomes'] ?? '',
        'experiences' => $data['experiences'] ?? '',
        'certifications' => $data['certifications'] ?? '',
        'texte_brut' => $data['texte_brut'] ?? '',
        'logo_type' => $logo_type
    ]));
    exit;
} else {
    header("Location: importationCV.php?error=nofile");
    exit;
}
?>