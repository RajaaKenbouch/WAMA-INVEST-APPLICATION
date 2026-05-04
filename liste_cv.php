<?php
require_once 'db.php';

$search = $_GET['search'] ?? '';
$poste_filter = $_GET['poste_filter'] ?? '';


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

$sql .= " ORDER BY date_creation DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cvs = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmtPostes = $pdo->query("SELECT DISTINCT poste FROM cv WHERE poste IS NOT NULL AND poste != '' ORDER BY poste");
$postes = $stmtPostes->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des CV - WAMA INVEST</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background: #f5f7fa;
            color: #333;
        }

        .container {
            max-width: 1300px;
            margin: 40px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        h1 {
            color: #1a73e8;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a73e8;
            padding-bottom: 10px;
        }

        .search-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            align-items: center;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }

        .search-bar input, .search-bar select {
            padding: 10px 15px;
            border-radius: 6px;
            border: 1px solid #ddd;
            flex: 1;
            min-width: 200px;
            font-size: 14px;
        }

        .search-bar button {
            padding: 10px 25px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: 0.3s;
        }

        .search-bar button:hover {
            background: #0e5bbf;
        }

        .reset-btn {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            transition: 0.3s;
        }

        .reset-btn:hover {
            background: #5a6268;
        }

        .result-info {
            margin-bottom: 15px;
            padding: 10px;
            background: #e9f5ff;
            border-radius: 6px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background: #1a73e8;
            color: white;
            font-weight: bold;
        }

        tr:hover {
            background: #f1f5f9;
        }

        .btn-pdf {
            display: inline-block;
            padding: 6px 12px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            transition: 0.3s;
        }

        .btn-pdf:hover {
            background: #218838;
        }

        .btn-dlt{
            display: inline-block;
            padding: 6px 12px;
            background: #ea0808;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            transition: 0.3s;
        }

        .btn-dlt:hover{
            background: #c50909;
        }

    </style>
</head>

<body>

<?php require 'inc/header.php'; ?>

<div class="container">
    <h1> Tous les CV générés</h1>


    <form method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Rechercher (nom, prénom, poste, compétence...)" value="<?= htmlspecialchars($search) ?>">
        
        <select name="poste_filter">
            <option value="">Tous les postes</option>
            <?php foreach ($postes as $p): ?>
                <option value="<?= htmlspecialchars($p) ?>" <?= $poste_filter === $p ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit">Rechercher</button>
        
        <?php if (!empty($search) || !empty($poste_filter)): ?>
            <a href="liste_cv.php" class="reset-btn"> Réinitialiser</a>
        <?php endif; ?>
    </form>


    <?php if (count($cvs) === 0): ?>
        <div class="result-info" style="background:#f8d7da; color:#721c24;">
            Aucun CV trouvé.
        </div>
    <?php else: ?>
        <div class="result-info">
            <strong><?= count($cvs) ?></strong> CV trouvé(s)
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Poste</th>
                    <th>Email</th>
                    <th>Date de création</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cvs as $cv): ?>
                <tr>
                    <td><?= $cv['id'] ?></td>
                    <td><?= htmlspecialchars($cv['nom']) ?></td>
                    <td><?= htmlspecialchars($cv['prenom']) ?></td>
                    <td><?= htmlspecialchars($cv['poste'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($cv['email'] ?: '-') ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($cv['date_creation'])) ?></td>
                    <td style="text-align:center">
                        <a href="download_cv_by_id.php?id=<?= $cv['id'] ?>" class="btn-pdf">Télécharger</a>
                        <a href="delete_cv.php?id=<?= $cv['id'] ?>" class="btn-dlt" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require 'inc/footer.php'; ?>

</body>
</html>