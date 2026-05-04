<?php
$texte_brut = $_GET['texte_brut'] ?? '';

$logo_type_get = $_GET['logo_type'] ?? 'invest';
$nom_get = $_GET['nom'] ?? '';
$prenom_get = $_GET['prenom'] ?? '';
$poste_get = $_GET['poste'] ?? '';
$email_get = $_GET['email'] ?? '';
$telephone_get = $_GET['telephone'] ?? '';
$competences_get = $_GET['competences'] ?? '';
$certifications_get = $_GET['certifications'] ?? '';
$experiences_get = $_GET['experiences'] ?? '';
$diplomes_get = $_GET['diplomes'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création de CV - WAMA INVEST</title>
</head>
<body>
<style>
    *{
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Segoe UI", sans-serif;
    }

    body {
        background: #f5f7fa;
        color: #333;
    }


    .container {
        max-width: 900px;
        margin: auto;
        background: white;
        padding: 25px;
        border-radius: 10px;
    }

    h2 {
        border-bottom: 2px solid black;
        padding-bottom: 5px;
    }

    input, textarea {
        width: 100%;
        padding: 10px;
        margin: 5px 0 10px;
    }

    button {
        padding: 10px;
        background: black;
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 6px;
    }

</style>

<script>
function addDiplome() {
    document.getElementById("diplomes").innerHTML += `
        <input name="diplome_date[]" placeholder="Date">
        <input name="diplome_titre[]" placeholder="Diplôme">
        <input name="diplome_ecole[]" placeholder="École"><br>
    `;
}

function addExperience() {
    document.getElementById("experience").innerHTML += `
        <input name="exp_date[]" placeholder="Date">
        <input name="exp_poste[]" placeholder="Poste">
        <input name="exp_entreprise[]" placeholder="Entreprise">
        <textarea name="exp_description[]" placeholder="Description"></textarea>
        <input name="exp_outils[]" placeholder="Outils"><br>
    `;
}
</script>

</head>
<?php require 'inc/header.php'; ?>
<body><br>

<div class="container">
    <form action="generate_cv.php" method="POST">

        <h2>Choisir le logo du CV</h2>
        <select name="logo_type">
            <option value="invest" <?= $logo_type_get === 'invest' ? 'selected' : '' ?>>Logo WAMA INVEST</option>
            <option value="link" <?= $logo_type_get === 'link' ? 'selected' : '' ?>>Logo WAMA LINK</option>
        </select>
        
        <h2>Informations personnelles</h2>

        <input type="text" name="nom" placeholder="Nom" value="<?= htmlspecialchars($nom_get) ?>" required>
        <input type="text" name="prenom" placeholder="Prénom" value="<?= htmlspecialchars($prenom_get) ?>"  required>
        <input type="text" name="poste" placeholder="Poste (ex: Data Scientist)" value="<?= htmlspecialchars($poste_get) ?>"  required>
        <input type="text" name="telephone" placeholder="Téléphone" value="<?= htmlspecialchars($telephone_get) ?>"  required>
        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email_get) ?>"  required>


        <h2>Compétences professionnelles</h2>
        <textarea name="competences" rows="5" placeholder="Ex: Langages : Python, Java..."><?= htmlspecialchars($competences_get) ?></textarea>


        <h2>Diplômes</h2>
        <div id="diplomes" >
            <input name="diplome_date[]" placeholder="Date">
            <input name="diplome_titre[]" placeholder="Diplôme">
            <input name="diplome_ecole[]" placeholder="École">
        </div>

        <button type="button" onclick="addDiplome()">+ Ajouter un diplôme</button><br><br>

        <h2>Expérience professionnelle</h2>
        <div id="experience" value="<?= htmlspecialchars($experience_get) ?>" >
            <input name="exp_date[]" placeholder="Date">
            <input name="exp_poste[]" placeholder="Poste">
            <input name="exp_entreprise[]" placeholder="Entreprise">
            <textarea name="exp_description[]" placeholder="Description"></textarea>
            <input name="exp_outils[]" placeholder="Outils">
        </div>

        <button type="button" onclick="addExperience()">+ Ajouter expérience</button><br><br>

        <h2>Langues</h2>
        <textarea name="langues" rows="3" placeholder="Ex: Arabe (maternelle), Français (courant), Anglais (technique)..."><?= htmlspecialchars($_GET['langues'] ?? '') ?></textarea>

        <h2>Certifications</h2>
        <textarea name="certifications"><?= htmlspecialchars($certifications_get) ?></textarea>
        <br><br>
        <div style="text-align: center;">
            <button type="submit"  style="font-size: 18px;" >Générer mon CV</button>
        </div>

    </form>
</div>
<?php require 'inc/footer.php'; ?>
</body>
</html>