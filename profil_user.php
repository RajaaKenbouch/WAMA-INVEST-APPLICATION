<?php
require_once 'inc/auth.php';
require_once 'db.php';

$candidat_id = $_GET['id'] ?? 0;

if (!$candidat_id) {
    die("Candidat non spécifié");
}

$stmt = $pdo->prepare("SELECT * FROM candidats WHERE id = ?");
$stmt->execute([$candidat_id]);
$candidat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidat) {
    die("Candidat introuvable");
}

$stmtCv = $pdo->prepare("SELECT * FROM cv WHERE candidat_id = ? ORDER BY date_creation DESC");
$stmtCv->execute([$candidat_id]);
$cvs = $stmtCv->fetchAll(PDO::FETCH_ASSOC);

$stmtComments = $pdo->prepare("SELECT * FROM commentaires WHERE candidat_id = ? ORDER BY date_commentaire DESC");
$stmtComments->execute([$candidat_id]);
$comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commentaire'])) {
    $commentaire = trim($_POST['commentaire']);
    $rh_nom = $_SESSION['username'] ?? 'RH';
    if (!empty($commentaire)) {
        $stmtInsert = $pdo->prepare("INSERT INTO commentaires (candidat_id, commentaire, rh_nom) VALUES (?, ?, ?)");
        $stmtInsert->execute([$candidat_id, $commentaire, $rh_nom]);
        header("Location: profil_candidat.php?id=" . $candidat_id);
        exit;
    }
}
?>
<?php require_once 'inc/header.php'; ?>
<style>
    .profil-container { max-width: 1000px; margin: 0 auto; }
    .info-card { background: white; border-radius: 16px; padding: 24px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .status-en_cours { background: #fef3c7; color: #d97706; }
    .status-publie { background: #d1fae5; color: #059669; }
    .status-archive { background: #fee2e2; color: #dc2626; }
    .comment-item { background: #f8fafc; border-radius: 12px; padding: 12px; margin-bottom: 12px; }
    .comment-header { display: flex; justify-content: space-between; font-size: 12px; color: #64748b; margin-bottom: 8px; }
</style>

<div class="profil-container">
    <div class="info-card">
        <h2 class="text-xl font-bold text-primary-container mb-4">👤 Profil de <?= htmlspecialchars($candidat['username']) ?></h2>
        <div class="grid md:grid-cols-2 gap-4">
            <div><strong>Nom complet :</strong> <?= htmlspecialchars($candidat['prenom'] ?? '') ?> <?= htmlspecialchars($candidat['nom'] ?? '') ?></div>
            <div><strong>Email :</strong> <?= htmlspecialchars($candidat['email'] ?? '-') ?></div>
            <div><strong>Téléphone :</strong> <?= htmlspecialchars($candidat['telephone'] ?? '-') ?></div>
            <div><strong>Poste actuel :</strong> <?= htmlspecialchars($candidat['poste_actuel'] ?? '-') ?></div>
            <div><strong>Disponibilité :</strong> 
                <span class="status-badge status-en_cours"><?= htmlspecialchars($candidat['disponibilite'] ?? 'Immédiate') ?></span>
            </div>
            <div><strong>Statut CV :</strong>
                <span class="status-badge status-<?= $candidat['statut_cv'] ?? 'en_cours' ?>">
                    <?= $candidat['statut_cv'] ?? 'en_cours' ?>
                </span>
            </div>
        </div>
    </div>

    <div class="info-card">
        <h2 class="text-xl font-bold text-primary-container mb-4">📄 CV de <?= htmlspecialchars($candidat['username']) ?></h2>
        <?php if (empty($cvs)): ?>
            <p>Aucun CV généré pour ce candidat.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($cvs as $cv): ?>
                <div class="border rounded-lg p-3 flex justify-between items-center">
                    <div>
                        <strong><?= htmlspecialchars($cv['poste'] ?: 'Sans titre') ?></strong><br>
                        <span class="text-sm text-slate-500">Créé le <?= date('d/m/Y', strtotime($cv['date_creation'])) ?></span>
                    </div>
                    <a href="down_load_cv.php?id=<?= $cv['id'] ?>" class="bg-primary-container text-white px-3 py-1 rounded-lg text-sm">📥 Télécharger</a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="info-card">
        <h2 class="text-xl font-bold text-primary-container mb-4">💬 Commentaires RH</h2>
        
        <form method="POST" class="mb-6">
            <textarea name="commentaire" rows="3" placeholder="Ajouter un commentaire..." class="w-full px-4 py-3 rounded-xl border border-slate-200"></textarea>
            <button type="submit" class="mt-2 bg-primary-container text-white px-4 py-2 rounded-lg">➕ Ajouter un commentaire</button>
        </form>
        
        <?php if (empty($comments)): ?>
            <p class="text-slate-500">Aucun commentaire pour le moment.</p>
        <?php else: ?>
            <?php foreach ($comments as $c): ?>
            <div class="comment-item">
                <div class="comment-header">
                    <span>👤 <?= htmlspecialchars($c['rh_nom']) ?></span>
                    <span>📅 <?= date('d/m/Y H:i', strtotime($c['date_commentaire'])) ?></span>
                </div>
                <p class="text-slate-700"><?= nl2br(htmlspecialchars($c['commentaire'])) ?></p>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'inc/footer.php'; ?>