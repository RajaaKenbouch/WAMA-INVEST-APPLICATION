<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: home.php");
        exit;
    } else {
        $error = "Identifiants incorrects";
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - WAMA</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "secondary-fixed": "#d2e4ff",
                        "surface-container-highest": "#e4e2e5",
                        "error": "#ba1a1a",
                        "tertiary-fixed": "#ffdcc1",
                        "on-surface-variant": "#44474d",
                        "surface-bright": "#fbf9fb",
                        "on-tertiary": "#ffffff",
                        "error-container": "#ffdad6",
                        "on-error": "#ffffff",
                        "inverse-surface": "#303033",
                        "on-primary": "#ffffff",
                        "background": "#fbf9fb",
                        "on-primary-fixed-variant": "#364764",
                        "primary-fixed": "#d6e3ff",
                        "secondary": "#2b6197",
                        "surface-tint": "#4d5f7d",
                        "inverse-primary": "#b5c7ea",
                        "on-primary-container": "#7587a7",
                        "tertiary": "#0d0400",
                        "surface-container": "#efedf0",
                        "surface-variant": "#e4e2e5",
                        "on-error-container": "#93000a",
                        "primary-container": "#0b1f3a",
                        "on-tertiary-container": "#a87e59",
                        "on-surface": "#1b1b1e",
                        "on-secondary-fixed": "#001c37",
                        "tertiary-fixed-dim": "#eebd94",
                        "surface": "#fbf9fb",
                        "tertiary-container": "#321800",
                        "on-primary-fixed": "#071c36",
                        "surface-container-high": "#e9e7ea",
                        "on-secondary-fixed-variant": "#02497e",
                        "outline-variant": "#c4c6ce",
                        "surface-container-low": "#f5f3f6",
                        "primary-fixed-dim": "#b5c7ea",
                        "on-secondary": "#ffffff",
                        "inverse-on-surface": "#f2f0f3",
                        "secondary-container": "#90c2fe",
                        "on-tertiary-fixed-variant": "#613f20",
                        "on-background": "#1b1b1e",
                        "primary": "#000615",
                        "secondary-fixed-dim": "#9fcaff",
                        "on-tertiary-fixed": "#2e1500",
                        "outline": "#75777e",
                        "surface-container-lowest": "#ffffff",
                        "surface-dim": "#dbd9dc",
                        "on-secondary-container": "#104f84"
                    },
                    borderRadius: {
                        DEFAULT: "0.25rem",
                        lg: "0.5rem",
                        xl: "0.75rem",
                        full: "9999px"
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
                <img src="images/logo WAMA.png" alt="error" width="20%">
            </div>
            <p class="text-slate-300 text-sm mt-1">Générateur de CV professionnel</p>
        </div>
        

        <div class="p-8">
            <h2 class="text-xl font-bold text-primary-container mb-6 text-center">Connexion</h2>
            
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm text-center">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nom d'utilisateur</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">person</span>
                        <input type="text" name="username" placeholder="admin" required autofocus class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Mot de passe</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">lock</span>
                        <input type="password" name="password" placeholder="••••••••" required class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all">
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-primary-container text-white py-3 rounded-xl font-button shadow-lg hover:shadow-xl transition-all active:scale-95 mt-6">
                    Se connecter
                </button>
            </form>
            
            <div class="mt-6 text-center text-xs text-slate-400">
                <p>Application interne WAMA INVEST</p>
                <p class="mt-1">© 2026 - Tous droits réservés</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>