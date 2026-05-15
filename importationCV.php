<?php require_once 'inc/auth.php'; ?>
<?php require_once 'inc/header.php'; ?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-primary-container px-8 py-6">
            <h1 class="text-2xl font-bold text-white">Importer votre CV</h1>
            <p class="text-slate-300 text-sm mt-1">Importez votre CV et convertissez-le au format WAMA</p>
        </div>
        
        <form action="import.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-6" id="importForm">

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

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Choisir le logo</label>
                <select name="logo_type" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all">
                    <option value="invest">Logo WAMA INVEST</option>
                    <option value="link">Logo WAMA LINK</option>
                </select>
            </div>

            <button type="submit" id="submitBtn" class="w-full bg-primary-container text-white py-3 rounded-xl font-button shadow-lg hover:shadow-xl transition-all active:scale-95 flex items-center justify-center gap-2">
                <span class="btn-text">Convertir en CV WAMA</span>
                <span class="btn-spinner hidden">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </form>
    </div>
</div>

<script>
function handleFile(input) {
    const fileName = input.files[0]?.name;
    document.getElementById("fileName").innerHTML = fileName ? `📄 ${fileName}` : '';
}

// Gestion du spinner au submit
document.getElementById('importForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    const btnText = btn.querySelector('.btn-text');
    const btnSpinner = btn.querySelector('.btn-spinner');
    
    btn.disabled = true;
    btn.classList.add('opacity-70', 'cursor-not-allowed');
    btnText.textContent = 'Conversion en cours...';
    btnSpinner.classList.remove('hidden');
});
</script>

<style>
.upload-box { transition: all 0.2s ease; }
.upload-box:hover { border-color: #2b6197; background-color: #f0f7ff; }
@keyframes spin { to { transform: rotate(360deg); } }
.animate-spin { animation: spin 0.8s linear infinite; }

/* Toast style ShadCN */
.toast-shadcn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.02);
    max-width: 380px;
    width: 100%;
    animation: slideInRight 0.3s ease-out;
}
.toast-shadcn-content {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
}
.toast-shadcn-icon {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.toast-shadcn-icon.success { color: #10b981; }
.toast-shadcn-icon.error { color: #ef4444; }
.toast-shadcn-message {
    flex: 1;
    font-size: 14px;
    font-weight: 500;
    color: #1e293b;
    line-height: 1.4;
}
.toast-shadcn-close {
    flex-shrink: 0;
    cursor: pointer;
    color: #94a3b8;
    font-size: 16px;
    background: none;
    border: none;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s;
}
.toast-shadcn-close:hover {
    color: #1e293b;
    background: #f1f5f9;
}
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes fadeOut {
    to { opacity: 0; transform: translateX(100%); }
}
.toast-fade-out {
    animation: fadeOut 0.3s ease-in forwards;
}
</style>

<script>
function showToastShadcn(message, type = 'success') {
    // Supprimer les toasts existants
    document.querySelectorAll('.toast-shadcn').forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = 'toast-shadcn';
    
    const iconSvg = type === 'success' 
        ? '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>'
        : '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
    
    toast.innerHTML = `
        <div class="toast-shadcn-content">
            <div class="toast-shadcn-icon ${type}">${iconSvg}</div>
            <div class="toast-shadcn-message">${escapeHtml(message)}</div>
            <button class="toast-shadcn-close" onclick="this.closest('.toast-shadcn').remove()">✕</button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-suppression après 4 secondes
    setTimeout(() => {
        if (toast.parentNode) {
            toast.classList.add('toast-fade-out');
            setTimeout(() => toast.remove(), 300);
        }
    }, 20000);
}

function escapeHtml(str) {
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}
</script>

<!-- Toast d'erreur / succès -->
<?php if (isset($_SESSION['import_toast'])): ?>
<script>
    showToastShadcn('<?= addslashes($_SESSION['import_toast']['message']) ?>', '<?= $_SESSION['import_toast']['type'] ?>');
</script>
<?php unset($_SESSION['import_toast']); ?>
<?php endif; ?>

<?php require_once 'inc/footer.php'; ?>