<?php
require_once 'db.php';

$token = $_GET['token'] ?? '';
$message = '';
$error = '';

if (empty($token)) {
    die("Token manquant");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Lien invalide ou expiré");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $stmt->execute([$password, $user['id']]);
        
        $message = "Mot de passe réinitialisé avec succès !";
        header("refresh:2;url=login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation - WAMA</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary-container": "#0b1f3a",
                        "secondary": "#2b6197",
                        "surface": "#fbf9fb",
                        "on-surface": "#1b1b1e",
                        "on-surface-variant": "#44474d"
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #fbf9fb; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex items-center justify-center">

<div class="max-w-md w-full mx-4">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-primary-container px-8 py-6 text-center">
            <div class="flex justify-center mb-4">
                <span class="material-symbols-outlined text-white text-5xl">key</span>
            </div>
            <h1 class="text-2xl font-bold text-white">WAMA</h1>
            <p class="text-slate-300 text-sm mt-1">Nouveau mot de passe</p>
        </div>
        
        <div class="p-8">
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nouveau mot de passe</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">lock</span>
                        <input type="password" name="password" placeholder="••••••••" required class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Confirmer le mot de passe</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">check_circle</span>
                        <input type="password" name="confirm_password" placeholder="••••••••" required class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all">
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-primary-container text-white py-3 rounded-xl font-button shadow-lg hover:shadow-xl transition-all active:scale-95">
                    Réinitialiser
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="login.php" class="text-sm text-secondary hover:underline">← Retour à la connexion</a>
            </div>
            
            <div class="mt-6 text-center text-xs text-slate-400">
                <p>Application interne WAMA INVEST</p>
                <p class="mt-1">© 2026 - Tous droits réservés</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>