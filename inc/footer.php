</main>

<!-- Footer -->
<footer class="bg-white border-t border-slate-200 pt-16 pb-8 mt-12">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-12 mb-12">
            <div class="col-span-2">
                <div class="flex items-center gap-2 mb-4">
                    <img src="images/logo WAMA.png" alt="error" width="15%">
                </div>
                <p class="text-sm text-on-surface-variant max-w-xs">
                    La solution professionnelle pour la création de CV standardisés au format WAMA.
                </p>
            </div>
            <div>
                <h4 class="font-bold text-xs mb-4 uppercase tracking-widest text-slate-400">Application</h4>
                <ul class="space-y-2 text-sm text-on-surface-variant">
                    <li><a href="creationCV.php" class="hover:text-primary transition-colors">Créer un CV</a></li>
                    <li><a href="importationCV.php" class="hover:text-primary transition-colors">Importer un CV</a></li>
                    <li><a href="liste_cv.php" class="hover:text-primary transition-colors">CV stockés</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-xs mb-4 uppercase tracking-widest text-slate-400">Support</h4>
                <ul class="space-y-2 text-sm text-on-surface-variant">
                    <li><a href="https://www.wama-invest.com/" target="_blank" rel="noopener noreferrer" class="hover:text-primary transition-colors">Aide</a></li>
                    <li><a href="https://wama-invest.com/contactez-nous/" target="_blank" rel="noopener noreferrer" class="hover:text-primary transition-colors">Contact</a></li>
                </ul>
            </div>
        </div>
        <div class="flex flex-col md:flex-row justify-center pt-6 border-t border-slate-100">
            <p class="text-xs text-slate-400">© 2026 WAMA INVEST - Application de génération de CV</p>
        </div>
    </div>
</footer>

<!-- Bottom Navigation Bar (Mobile Only) -->
<nav class="md:hidden fixed bottom-0 left-0 w-full flex justify-around items-center h-14 bg-white border-t border-slate-100 z-50">
    <?php $current = basename($_SERVER['PHP_SELF']); ?>
    
    <a href="home.php" class="flex flex-col items-center justify-center pt-2 pb-2 px-4 transition-all <?= $current == 'home.php' ? 'text-[#0B1F3A] border-t-2 border-[#0B1F3A]' : 'text-slate-400' ?>">
        <span class="material-symbols-outlined text-xl">home</span>
        <span class="text-[9px] font-bold uppercase tracking-widest">Accueil</span>
    </a>
    <a href="creationCV.php" class="flex flex-col items-center justify-center pt-2 pb-2 px-4 transition-all <?= $current == 'creationCV.php' ? 'text-[#0B1F3A] border-t-2 border-[#0B1F3A]' : 'text-slate-400' ?>">
        <span class="material-symbols-outlined text-xl">add_circle</span>
        <span class="text-[9px] font-bold uppercase tracking-widest">Créer</span>
    </a>
    <a href="importationCV.php" class="flex flex-col items-center justify-center pt-2 pb-2 px-4 transition-all <?= $current == 'importationCV.php' ? 'text-[#0B1F3A] border-t-2 border-[#0B1F3A]' : 'text-slate-400' ?>">
        <span class="material-symbols-outlined text-xl">upload_file</span>
        <span class="text-[9px] font-bold uppercase tracking-widest">Importer</span>
    </a>
    <a href="liste_cv.php" class="flex flex-col items-center justify-center pt-2 pb-2 px-4 transition-all <?= $current == 'liste_cv.php' ? 'text-[#0B1F3A] border-t-2 border-[#0B1F3A]' : 'text-slate-400' ?>">
        <span class="material-symbols-outlined text-xl">folder_copy</span>
        <span class="text-[9px] font-bold uppercase tracking-widest">CV</span>
    </a>
</nav>

</body>
</html>