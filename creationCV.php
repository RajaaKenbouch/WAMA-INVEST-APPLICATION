<?php require_once 'inc/auth.php'; ?>
<?php
$texte_brut = $_GET['texte_brut'] ?? '';
$logo_type_get = $_GET['logo_type'] ?? 'invest';
$data = [];

if (!empty($_GET['data'])) {
    $json_data = urldecode($_GET['data']);
    $data = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $data = [];
    }
} elseif (!empty($_SESSION['import_data'])) {
    $data = is_array($_SESSION['import_data']) ? $_SESSION['import_data'] : [];
    $logo_type_get = $_SESSION['import_logo_type'] ?? $logo_type_get;
    $texte_brut = $_SESSION['import_texte_brut'] ?? $texte_brut;
    unset($_SESSION['import_data'], $_SESSION['import_logo_type'], $_SESSION['import_texte_brut']);
}

$nom_get = $data['nom'] ?? '';
$prenom_get = $data['prenom'] ?? '';
$poste_get = $data['poste'] ?? '';
$email_get = $data['email'] ?? '';
$telephone_get = $data['telephone'] ?? '';
$competences_get = $data['competences'] ?? '';
$langues_get = $data['langues'] ?? '';
$certifications_get = is_array($data['certifications'] ?? null) ? implode(", ", $data['certifications']) : ($data['certifications'] ?? '');
$diplomes_get = $data['diplomes'] ?? [];
$experiences_get = $data['experiences'] ?? [];
?>
<?php require_once 'inc/header.php'; ?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-primary-container px-8 py-6">
            <h1 class="text-2xl font-bold text-white">📝 Créer votre CV au format WAMA</h1>
            <p class="text-slate-300 text-sm mt-1">Remplissez le formulaire ci-dessous pour générer votre CV professionnel</p>
        </div>
        
        <form action="generate_cv.php" method="POST" class="p-8 space-y-8">
            <!-- Choix du logo -->
            <div>
                <h2 class="text-lg font-bold text-primary-container mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">image</span>
                    Choisir le logo du CV
                </h2>
                <select name="logo_type" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all">
                    <option value="invest" <?= $logo_type_get === 'invest' ? 'selected' : '' ?>>Logo WAMA INVEST</option>
                    <option value="link" <?= $logo_type_get === 'link' ? 'selected' : '' ?>>Logo WAMA LINK</option>
                </select>
            </div>

            <!-- Informations personnelles -->
            <div>
                <h2 class="text-lg font-bold text-primary-container mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">person</span>
                    Informations personnelles
                </h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <input type="text" name="nom" placeholder="Nom" value="<?= htmlspecialchars($nom_get) ?>" class="px-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all" required>
                    <input type="text" name="prenom" placeholder="Prénom" value="<?= htmlspecialchars($prenom_get) ?>" class="px-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all" required>
                    <input type="text" name="poste" placeholder="Poste (ex: Data Scientist)" value="<?= htmlspecialchars($poste_get) ?>" class="px-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all">
                    <input type="tel" name="telephone" placeholder="Téléphone" value="<?= htmlspecialchars($telephone_get) ?>" class="px-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all">
                    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email_get) ?>" class="px-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all">
                </div>
            </div>

            <!-- Compétences -->
            <div>
                <h2 class="text-lg font-bold text-primary-container mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">star</span>
                    Compétences professionnelles
                </h2>
                <textarea name="competences" rows="5" placeholder="Ex: Langages : Python, Java, PHP..." class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all"><?= htmlspecialchars($competences_get) ?></textarea>
            </div>

            <!-- Diplômes -->
            <div>
                <h2 class="text-lg font-bold text-primary-container mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">school</span>
                    Diplômes
                </h2>
                <div id="diplomes" class="space-y-3">
                    <?php if (!empty($diplomes_get) && is_array($diplomes_get)): ?>
                        <?php foreach ($diplomes_get as $d): ?>
                            <div class="diplome-item grid md:grid-cols-3 gap-3 p-4 bg-slate-50 rounded-xl">
                                <input name="diplome_date[]" value="<?= htmlspecialchars($d['annee'] ?? '') ?>" placeholder="Date (ex: 2021-2024)" class="px-3 py-2 rounded-lg border border-slate-200">
                                <input name="diplome_titre[]" value="<?= htmlspecialchars($d['titre'] ?? '') ?>" placeholder="Diplôme" class="px-3 py-2 rounded-lg border border-slate-200">
                                <input name="diplome_ecole[]" value="<?= htmlspecialchars($d['etablissement'] ?? '') ?>" placeholder="École" class="px-3 py-2 rounded-lg border border-slate-200">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="diplome-item grid md:grid-cols-3 gap-3 p-4 bg-slate-50 rounded-xl">
                            <input name="diplome_date[]" placeholder="Date (ex: 2021-2024)" class="px-3 py-2 rounded-lg border border-slate-200">
                            <input name="diplome_titre[]" placeholder="Diplôme" class="px-3 py-2 rounded-lg border border-slate-200">
                            <input name="diplome_ecole[]" placeholder="École" class="px-3 py-2 rounded-lg border border-slate-200">
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" onclick="addDiplome()" class="mt-3 text-secondary hover:text-secondary/80 text-sm font-medium flex items-center gap-1 transition-colors">
                    <span class="material-symbols-outlined text-sm">add_circle</span>
                    + Ajouter un diplôme
                </button>
            </div>

            <!-- Expériences -->
            <div>
                <h2 class="text-lg font-bold text-primary-container mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">work</span>
                    Expérience professionnelle
                </h2>
                <div id="experiences" class="space-y-3">
                    <?php if (!empty($experiences_get) && is_array($experiences_get)): ?>
                        <?php foreach ($experiences_get as $e): ?>
                            <div class="exp-item space-y-3 p-4 bg-slate-50 rounded-xl">
                                <div class="grid md:grid-cols-3 gap-3">
                                    <input name="exp_date[]" value="<?= htmlspecialchars($e['periode'] ?? '') ?>" placeholder="Date" class="px-3 py-2 rounded-lg border border-slate-200">
                                    <input name="exp_poste[]" value="<?= htmlspecialchars($e['poste'] ?? '') ?>" placeholder="Poste" class="px-3 py-2 rounded-lg border border-slate-200">
                                    <input name="exp_entreprise[]" value="<?= htmlspecialchars($e['entreprise'] ?? '') ?>" placeholder="Entreprise" class="px-3 py-2 rounded-lg border border-slate-200">
                                </div>
                                <textarea name="exp_description[]" placeholder="Description" class="w-full px-3 py-2 rounded-lg border border-slate-200"><?= htmlspecialchars($e['description'] ?? '') ?></textarea>
                                <input name="exp_outils[]" value="<?= htmlspecialchars($e['outils'] ?? '') ?>" placeholder="Outils (ex: PHP, MySQL)" class="w-full px-3 py-2 rounded-lg border border-slate-200">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="exp-item space-y-3 p-4 bg-slate-50 rounded-xl">
                            <div class="grid md:grid-cols-3 gap-3">
                                <input name="exp_date[]" placeholder="Date" class="px-3 py-2 rounded-lg border border-slate-200">
                                <input name="exp_poste[]" placeholder="Poste" class="px-3 py-2 rounded-lg border border-slate-200">
                                <input name="exp_entreprise[]" placeholder="Entreprise" class="px-3 py-2 rounded-lg border border-slate-200">
                            </div>
                            <textarea name="exp_description[]" placeholder="Description" class="w-full px-3 py-2 rounded-lg border border-slate-200"></textarea>
                            <input name="exp_outils[]" placeholder="Outils (ex: PHP, MySQL)" class="w-full px-3 py-2 rounded-lg border border-slate-200">
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" onclick="addExperience()" class="mt-3 text-secondary hover:text-secondary/80 text-sm font-medium flex items-center gap-1 transition-colors">
                    <span class="material-symbols-outlined text-sm">add_circle</span>
                    + Ajouter une expérience
                </button>
            </div>

            <!-- Langues -->
            <div>
                <h2 class="text-lg font-bold text-primary-container mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">language</span>
                    Langues
                </h2>
                <textarea name="langues" rows="3" placeholder="Ex: Arabe (maternelle), Français (courant), Anglais (technique)..." class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all"><?= htmlspecialchars($langues_get) ?></textarea>
            </div>

            <!-- Certifications -->
            <div>
                <h2 class="text-lg font-bold text-primary-container mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">verified</span>
                    Certifications
                </h2>
                <textarea name="certifications" rows="3" placeholder="Ex: Certification AWS, Scrum Master..." class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all"><?= htmlspecialchars($certifications_get) ?></textarea>
            </div>

            <!-- Texte brut (si import) -->
            <?php if (!empty($texte_brut)): ?>
            <div>
                <h2 class="text-lg font-bold text-primary-container mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">description</span>
                    Texte extrait du CV importé
                </h2>
                <textarea rows="8" readonly class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 font-mono text-sm"><?= htmlspecialchars($texte_brut) ?></textarea>
                <p class="text-xs text-slate-400 mt-2">👆 Copiez les informations ci-dessus et collez-les dans les champs</p>
            </div>
            <?php endif; ?>

            <!-- Bouton de soumission -->
            <div class="text-center pt-4">
                <button type="submit" class="bg-primary-container text-white px-8 py-3 rounded-xl font-button text-button shadow-lg hover:shadow-xl transition-all active:scale-95">
                    🚀 Générer mon CV
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function addDiplome() {
    const div = document.createElement('div');
    div.className = 'diplome-item grid md:grid-cols-3 gap-3 p-4 bg-slate-50 rounded-xl';
    div.innerHTML = `
        <input name="diplome_date[]" placeholder="Date (ex: 2021-2024)" class="px-3 py-2 rounded-lg border border-slate-200">
        <input name="diplome_titre[]" placeholder="Diplôme" class="px-3 py-2 rounded-lg border border-slate-200">
        <input name="diplome_ecole[]" placeholder="École" class="px-3 py-2 rounded-lg border border-slate-200">
    `;
    document.getElementById('diplomes').appendChild(div);
}

function addExperience() {
    const div = document.createElement('div');
    div.className = 'exp-item space-y-3 p-4 bg-slate-50 rounded-xl';
    div.innerHTML = `
        <div class="grid md:grid-cols-3 gap-3">
            <input name="exp_date[]" placeholder="Date" class="px-3 py-2 rounded-lg border border-slate-200">
            <input name="exp_poste[]" placeholder="Poste" class="px-3 py-2 rounded-lg border border-slate-200">
            <input name="exp_entreprise[]" placeholder="Entreprise" class="px-3 py-2 rounded-lg border border-slate-200">
        </div>
        <textarea name="exp_description[]" placeholder="Description" class="w-full px-3 py-2 rounded-lg border border-slate-200"></textarea>
        <input name="exp_outils[]" placeholder="Outils (ex: PHP, MySQL)" class="w-full px-3 py-2 rounded-lg border border-slate-200">
    `;
    document.getElementById('experiences').appendChild(div);
}
</script>

<?php require_once 'inc/footer.php'; ?>