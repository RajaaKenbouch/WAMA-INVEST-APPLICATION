<?php require_once 'inc/auth.php'; ?>
<?php require_once 'inc/header.php'; ?>

<!-- Hero Section -->
<div class="max-w-7xl mx-auto">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
        <div class="space-y-8">
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-secondary-fixed text-on-secondary-fixed-variant text-label-caps">
                ✨ STANDARDISATION WAMA
            </div>
            <h1 class="text-h1 font-h1 text-primary-container max-w-lg">
                Créez votre CV au format WAMA
            </h1>
            <p class="text-body-lg font-body-lg text-on-surface-variant max-w-md">
                Générez un CV professionnel, structuré et conforme aux standards de notre entreprise en quelques clics.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                <a href="creationCV.php" class="bg-primary-container text-white px-8 py-4 rounded-xl font-button text-button shadow-lg hover:shadow-xl transition-all active:scale-95 text-center">
                    📄 Créer mon CV
                </a>
                <a href="importationCV.php" class="bg-transparent border border-secondary text-secondary px-8 py-4 rounded-xl font-button text-button hover:bg-secondary-fixed/30 transition-all active:scale-95 text-center">
                    📂 Importer un CV
                </a>
            </div>
        </div>
        <!-- Hero Image Showcase -->
        <div class="relative group">
            <div class="absolute -inset-4 bg-gradient-to-tr from-secondary-fixed/20 to-transparent rounded-[2rem] -z-10 blur-2xl"></div>
            <div class="bg-white p-4 rounded-[2rem] border border-slate-200 shadow-2xl overflow-hidden">
                <img class="w-full h-auto rounded-2xl shadow-sm" src="images/image.png" alt="CV WAMA">
            </div>
        </div>
    </div>
</div>

<!-- Features Bento Grid 
<section class="py-section-padding mt-12">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12 space-y-4">
            <h2 class="text-h2 font-h2 text-primary-container">Pourquoi utiliser notre application ?</h2>
            <p class="text-body-md font-body-md text-on-surface-variant max-w-2xl mx-auto">Une solution complète pour standardiser vos CV</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-8 rounded-xl border border-slate-100 hover:border-secondary transition-all">
                <div class="w-12 h-12 rounded-lg bg-secondary-fixed/30 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-secondary">bolt</span>
                </div>
                <h3 class="text-xl font-bold text-primary-container mb-3">Rapide</h3>
                <p class="text-sm text-on-surface-variant">Générez votre CV en quelques secondes seulement</p>
            </div>
            <div class="bg-white p-8 rounded-xl border border-slate-100 hover:border-secondary transition-all">
                <div class="w-12 h-12 rounded-lg bg-secondary-fixed/30 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-secondary">verified</span>
                </div>
                <h3 class="text-xl font-bold text-primary-container mb-3">Standardisé</h3>
                <p class="text-sm text-on-surface-variant">Respecte le format officiel WAMA INVEST</p>
            </div>
            <div class="bg-white p-8 rounded-xl border border-slate-100 hover:border-secondary transition-all">
                <div class="w-12 h-12 rounded-lg bg-secondary-fixed/30 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-secondary">star</span>
                </div>
                <h3 class="text-xl font-bold text-primary-container mb-3">Professionnel</h3>
                <p class="text-sm text-on-surface-variant">Présentation claire et moderne</p>
            </div>
        </div>
    </div>
</section>-->

<!-- How it works 
<section class="py-section-padding">
    <div class="max-w-7xl mx-auto text-center">
        <h2 class="text-h2 font-h2 text-primary-container mb-12">Comment ça fonctionne ?</h2>
        <div class="flex flex-wrap justify-center items-center gap-6">
            <div class="bg-white rounded-xl p-8 text-center min-w-[180px] shadow-sm">
                <div class="w-12 h-12 bg-secondary-fixed rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-secondary">edit_note</span>
                </div>
                <h3 class="font-bold">1. Remplir</h3>
                <p class="text-sm text-slate-500">ou importer un fichier</p>
            </div>
            <span class="text-3xl text-secondary">→</span>
            <div class="bg-white rounded-xl p-8 text-center min-w-[180px] shadow-sm">
                <div class="w-12 h-12 bg-secondary-fixed rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-secondary">settings</span>
                </div>
                <h3 class="font-bold">2. Transformation</h3>
                <p class="text-sm text-slate-500">Traitement des données</p>
            </div>
            <span class="text-3xl text-secondary">→</span>
            <div class="bg-white rounded-xl p-8 text-center min-w-[180px] shadow-sm">
                <div class="w-12 h-12 bg-secondary-fixed rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-secondary">download</span>
                </div>
                <h3 class="font-bold">3. Télécharger PDF</h3>
                <p class="text-sm text-slate-500">Export final</p>
            </div>
        </div>
    </div>
</section>-->

<!-- Bottom CTA 
<section class="py-16 bg-primary-container text-white rounded-2xl max-w-7xl mx-auto mt-12">
    <div class="text-center">
        <h2 class="text-3xl font-bold mb-4">Prêt à créer votre CV ?</h2>
        <p class="text-lg text-slate-300 mb-8">Rejoignez les professionnels qui utilisent WAMA</p>
        <div class="flex gap-4 justify-center">
            <a href="creationCV.php" class="bg-white text-primary-container px-8 py-3 rounded-xl font-button shadow-lg hover:bg-slate-100 transition-all">Créer mon CV</a>
            <a href="importationCV.php" class="bg-transparent border border-white/30 text-white px-8 py-3 rounded-xl hover:bg-white/10 transition-all">Importer un CV</a>
        </div>
    </div>
</section>-->

<?php require_once 'inc/footer.php'; ?>