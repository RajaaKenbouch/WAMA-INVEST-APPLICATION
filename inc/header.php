<?php
session_start();
?>
<!DOCTYPE html>
<html class="light" lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WAMA | Générateur de CV Professionnel</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
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
                    },
                    spacing: {
                        "stack-lg": "32px",
                        "container-max": "1440px",
                        "margin-page": "40px",
                        "unit": "4px",
                        "gutter": "24px",
                        "stack-sm": "8px",
                        "stack-md": "16px",
                        "section-padding": "64px"
                    },
                    fontSize: {
                        "label-caps": ["12px", { lineHeight: "1", letterSpacing: "0.05em", fontWeight: "600" }],
                        "button": ["14px", { lineHeight: "1", letterSpacing: "0.01em", fontWeight: "500" }],
                        "body-md": ["16px", { lineHeight: "1.6", letterSpacing: "0", fontWeight: "400" }],
                        "h3": ["24px", { lineHeight: "1.3", letterSpacing: "-0.01em", fontWeight: "500" }],
                        "body-lg": ["18px", { lineHeight: "1.6", letterSpacing: "0", fontWeight: "400" }],
                        "h1": ["48px", { lineHeight: "1.1", letterSpacing: "-0.02em", fontWeight: "600" }],
                        "h2": ["30px", { lineHeight: "1.2", letterSpacing: "-0.01em", fontWeight: "600" }],
                        "body-sm": ["14px", { lineHeight: "1.5", letterSpacing: "0", fontWeight: "400" }]
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
<body class="bg-surface text-on-surface">

<!-- Top Navigation Bar -->
<nav class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-md border-b border-slate-200 shadow-sm">
    <div class="flex justify-between items-center px-6 h-16 w-full max-w-7xl mx-auto">
        <div class="flex items-center gap-2">
            <a href="home.php">
                <img src="images/logo WAMA.png" alt="error" width="13%">
            </a>
        </div>
        <div class="hidden md:flex items-center gap-8">
            <nav class="hidden md:flex items-center gap-8">
                    <a href="home.php" class="text-sm font-semibold tracking-tight transition-all <?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'text-[#0B1F3A] border-b-2 border-[#0B1F3A]' : 'text-slate-400 hover:text-[#0B1F3A]' ?>">
                        Accueil
                    </a>
                    <a href="creationCV.php" class="text-sm font-semibold tracking-tight transition-all <?= basename($_SERVER['PHP_SELF']) == 'creationCV.php' ? 'text-[#0B1F3A] border-b-2 border-[#0B1F3A]' : 'text-slate-400 hover:text-[#0B1F3A]' ?>">
                        Créer un CV
                    </a>
                    <a href="importationCV.php" class="text-sm font-semibold tracking-tight transition-all <?= basename($_SERVER['PHP_SELF']) == 'importationCV.php' ? 'text-[#0B1F3A] border-b-2 border-[#0B1F3A]' : 'text-slate-400 hover:text-[#0B1F3A]' ?>">
                        Importer un CV
                    </a>
                    <a href="liste_cv.php" class="text-sm font-semibold tracking-tight transition-all <?= basename($_SERVER['PHP_SELF']) == 'liste_cv.php' ? 'text-[#0B1F3A] border-b-2 border-[#0B1F3A]' : 'text-slate-400 hover:text-[#0B1F3A]' ?>">
                        CV stockés
                    </a>
                </nav
             </div>
        <div class="flex items-center gap-4">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="text-sm text-slate-600 hidden md:block">👋 <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php" class="text-sm font-button px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-all">Déconnexion</a>
            <?php else: ?>
                <a href="login.php" class="text-sm font-button px-4 py-2 text-primary-container hover:bg-slate-50 rounded-lg transition-all">Connexion</a>
            <?php endif; ?>
        </div>
    </div>
</nav>


<!-- <a href="home.php" class="text-[#0B1F3A] font-sans text-sm font-semibold tracking-tight">Accueil</a>
            <a href="creationCV.php" class="text-slate-400 hover:bg-slate-50 text-sm font-semibold tracking-tight p-2 rounded-lg transition-all">Créer un CV</a>
            <a href="importationCV.php" class="text-slate-400 hover:bg-slate-50 text-sm font-semibold tracking-tight p-2 rounded-lg transition-all">Importer un CV</a>
            <a href="liste_cv.php" class="text-slate-400 hover:bg-slate-50 text-sm font-semibold tracking-tight p-2 rounded-lg transition-all">CV stockés</a>
       -->

<!-- Main content container avec padding top pour la navbar fixe -->
<main class="pt-24 pb-12 px-6 min-h-screen">