<?php require_once 'inc/auth.php'; ?>
<?php require_once 'db.php';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$poste_filter = $_GET['poste_filter'] ?? '';

// Compter le total
$countSql = "SELECT COUNT(*) as total FROM cv WHERE 1=1";
$countParams = [];
if (!empty($search)) {
    $countSql .= " AND (nom LIKE :search OR prenom LIKE :search OR poste LIKE :search OR competences LIKE :search)";
    $countParams['search'] = "%$search%";
}
if (!empty($poste_filter)) {
    $countSql .= " AND poste = :poste_filter";
    $countParams['poste_filter'] = $poste_filter;
}
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($total / $limit);

// Récupérer les CV (sans les JOIN erronés)
$sql = "SELECT * FROM cv WHERE 1=1";
$params = [];
if (!empty($search)) {
    $sql .= " AND (nom LIKE :search OR prenom LIKE :search OR poste LIKE :search OR competences LIKE :search)";
    $params['search'] = "%$search%";
}
if (!empty($poste_filter)) {
    $sql .= " AND poste = :poste_filter";
    $params['poste_filter'] = $poste_filter;
}
$sql .= " ORDER BY date_creation DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$cvs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les postes uniques pour le filtre
$stmtPostes = $pdo->query("SELECT DISTINCT poste FROM cv WHERE poste IS NOT NULL AND poste != '' ORDER BY poste");
$postes = $stmtPostes->fetchAll(PDO::FETCH_COLUMN);
?>
<?php require_once 'inc/header.php'; ?>
<link rel="stylesheet" href="style1.css">
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-primary-container px-8 py-6">
            <h1 class="text-2xl font-bold text-white">Tous les CV générés</h1>
            <p class="text-slate-300 text-sm mt-1">Gérez et téléchargez les CV stockés</p>
        </div>
        
        <div class="p-8">
            <!-- Barre de recherche -->
            <form method="GET" class="flex flex-wrap gap-4 mb-6">
                <input type="text" name="search" placeholder="Rechercher (nom, prénom, poste...)" value="<?= htmlspecialchars($search) ?>" class="flex-1 px-4 py-3 rounded-xl border border-slate-200 focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all">
                <select name="poste_filter" class="px-4 py-3 rounded-xl border border-slate-200 focus:border-secondary">
                    <option value="">Tous les postes</option>
                    <?php foreach ($postes as $p): ?>
                        <option value="<?= htmlspecialchars($p) ?>" <?= $poste_filter === $p ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="bg-primary-container text-white px-6 py-3 rounded-xl hover:bg-primary-container/90 transition-all">Rechercher</button>
                <?php if (!empty($search) || !empty($poste_filter)): ?>
                    <a href="liste_cv.php" class="bg-slate-500 text-white px-6 py-3 rounded-xl hover:bg-slate-600 transition-all">Réinitialiser</a>
                <?php endif; ?>
            </form>

            <?php if (count($cvs) === 0): ?>
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-6xl text-slate-300">folder_empty</span>
                    <p class="text-slate-500 mt-4">Aucun CV trouvé</p>
                </div>
            <?php else: ?>
                <p class="text-sm text-slate-500 mb-4"><strong><?= $total ?></strong> CV trouvé(s) - Page <strong><?= $page ?> / <?= $totalPages ?></strong></p>
                
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="px-4 py-3 text-left text-sm font-semibold">ID</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Nom</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Prénom</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Poste</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Année d'expérience</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Date</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cvs as $cv): ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3"><?= $cv['id'] ?></td>
                                <td class="px-4 py-3 font-medium"><?= htmlspecialchars($cv['nom']) ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($cv['prenom']) ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($cv['poste'] ?: '-') ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($cv['email'] ?: '-') ?></td>
                                <td class="px-4 py-3"><?= date('d/m/Y H:i', strtotime($cv['date_creation'])) ?></td>
                                <td class="px-4 py-3 text-center">
                                    <div class="dropdown">
                                        <button class="dropdown-btn">
                                            <span class="dots">•••</span>
                                        </button>
                                        <div class="dropdown-content">
                                            <a href="download_cv_by_id.php?id=<?= $cv['id'] ?>" class="dropdown-item">
                                                <span class="icon">📥</span> Télécharger
                                            </a>
                                            <a href="download_original.php?id=<?= $cv['id'] ?>" class="dropdown-item">
                                                <span class="icon">📄</span> Original
                                            </a>
                                            <a href="delete_cv.php?id=<?= $cv['id'] ?>" class="dropdown-item delete" onclick="return confirm('Supprimer définitivement ce CV ?')">
                                                <span class="icon">🗑️</span> Supprimer
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="flex justify-center gap-2 mt-8">
                    <?php if ($page > 1): ?>
                        <a href="?page=1&search=<?= urlencode($search) ?>&poste_filter=<?= urlencode($poste_filter) ?>" class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-100 transition-colors">⏮</a>
                        <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&poste_filter=<?= urlencode($poste_filter) ?>" class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-100 transition-colors">◀</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="px-3 py-2 rounded-lg bg-primary-container text-white"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&poste_filter=<?= urlencode($poste_filter) ?>" class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-100 transition-colors"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&poste_filter=<?= urlencode($poste_filter) ?>" class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-100 transition-colors">▶</a>
                        <a href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&poste_filter=<?= urlencode($poste_filter) ?>" class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-100 transition-colors">⏭</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'inc/footer.php'; ?>