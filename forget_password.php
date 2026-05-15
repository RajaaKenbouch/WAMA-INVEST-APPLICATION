<?php
require_once 'db.php';
require_once 'vendor/autoload.php';
require_once 'inc/mail.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    // Chercher l'utilisateur par email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?");
        $stmt->execute([$token, $expiry, $email]);
        

        $reset_link = "http://localhost/Application%20WAMA%20INVEST/reset_password.php?token=" . $token;
        
        $subject = "Reinitialisation de votre mot de passe - WAMA INVEST";
        $body = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2 style='color: #1a73e8;'>Bonjour {$user['username']}</h2>
            <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
            <p>Cliquez sur le bouton ci-dessous :</p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$reset_link}' style='background: #1a73e8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>
                    🔐 Réinitialiser mon mot de passe
                </a>
            </div>
            <p>Ce lien est valable <strong>15 minutes</strong>.</p>
            <p style='font-size: 12px; color: #666;'>Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</p>
            <hr>
            <p style='font-size: 12px; color: #666;'>WAMA INVEST - Application de génération de CV</p>
        </body>
        </html>
        ";
        
        if (sendEmail($email, $subject, $body)) {
            $message = "Un email de réinitialisation vous a été envoyé.";
        } else {
            $error = "Erreur lors de l'envoi de l'email. Vérifiez la configuration SMTP.";
        }
    } else {
        $error = "Un email de réinitialisation vous a été envoyé.";
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - WAMA</title>
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
                <span class="material-symbols-outlined text-white text-5xl">lock_reset</span>
            </div>
            <p class="text-slate-300 text-sm mt-1">Mot de passe oublié</p>
        </div>
        
        <div class="p-8">
            <?php if ($message): ?>
                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-600 text-sm">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Adresse email</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">mail</span>
                        <input type="email" name="email" placeholder="admin@wama-invest.com" required autofocus class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all">
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-primary-container text-white py-3 rounded-xl font-button shadow-lg hover:shadow-xl transition-all active:scale-95">
                    Envoyer le lien
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