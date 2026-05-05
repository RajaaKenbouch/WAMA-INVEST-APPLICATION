<?php require_once 'inc/auth.php'; ?>
<?php require_once 'inc/header.php'; ?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-primary-container px-8 py-6">
            <h1 class="text-2xl font-bold text-white">📂 Importer votre CV</h1>
            <p class="text-slate-300 text-sm mt-1">Importez votre CV et convertissez-le au format WAMA</p>
        </div>
        
        <form action="import.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            <!-- Zone d'upload -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Fichier CV</label>
                <label class="upload-box flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-secondary transition-all bg-slate-50">
                    <span class="material-symbols-outlined text-4xl text-slate-400">cloud_upload</span>
                    <span class="mt-2 text-sm text-slate-500">Cliquez ou glissez votre fichier ici</span>
                    <span class="text-xs text-slate-400 mt-1">PDF, DOCX, TXT</span>
                    <input type="file" name="cv_file" accept=".pdf,.doc,.docx,.txt" onchange="handleFile(this)" class="hidden" required>
                </label>
                <div id="fileName" class="text-sm text-slate-500 mt-2 text-center"></div>
            </div>

            <!-- Choix du logo -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Choisir le logo</label>
                <select name="logo_type" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all">
                    <option value="invest">Logo WAMA INVEST</option>
                    <option value="link">Logo WAMA LINK</option>
                </select>
            </div>

            <!-- Bouton de soumission -->
            <button type="submit" class="w-full bg-primary-container text-white py-3 rounded-xl font-button shadow-lg hover:shadow-xl transition-all active:scale-95">
                🔄 Convertir en CV WAMA
            </button>
        </form>
    </div>
</div>

<script>
function handleFile(input) {
    const fileName = input.files[0]?.name;
    document.getElementById("fileName").innerHTML = fileName ? `📄 ${fileName}` : '';
}
</script>

<style>
.upload-box {
    transition: all 0.2s ease;
}
.upload-box:hover {
    border-color: #2b6197;
    background-color: #f0f7ff;
}
</style>

<?php require_once 'inc/footer.php'; ?>