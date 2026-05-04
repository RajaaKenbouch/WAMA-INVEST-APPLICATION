<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Importation de CV - WAMA INVEST</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: white;
    margin: 0;
}
header {
    display: flex;
    justify-content: space-between;
    padding: 20px 50px;
    align-items: center;
}
nav a{
    list-style: none;
    text-decoration: none;
    margin-left: 20px;
    color: #4cc9ff;
    font-weight:bold;
}
nav li{
    display: inline;
}
.container {
    max-width: 600px;
    margin: 80px auto;
    background: #111827;
    padding: 30px;
    border-radius: 15px;
    text-align: center;
}
h1 {
    color: #38bdf8;
    margin-bottom: 20px;
}
.upload-box {
    border: 2px dashed #38bdf8;
    padding: 30px;
    border-radius: 10px;
    cursor: pointer;
}
.upload-box:hover {
    background: rgba(56, 189, 248, 0.1);
}
input[type="file"] {
    display: none;
}
select, button {
    margin-top: 20px;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
}
select {
    background: white;
    color: black;
    width: 100%;
}
button {
    background: #38bdf8;
    border: none;
}
button:hover {
    background: #0ea5e9;
}
.file-name {
    margin-top: 10px;
    font-size: 14px;
    color: #94a3b8;
}
footer {
    text-align: center;
    padding: 20px;
    color: white;
    margin-top: 50px;
}
</style>

<script>
function handleFile(input) {
    const fileName = input.files[0].name;
    document.getElementById("fileName").innerText = fileName;
}
</script>

</head>

<body>
    <header>
        <a href="home.php"><img src="images/logo WAMA.png" alt="logo" width="15%"></a>
        <nav>
            <ul>
                <a href="home.php"><li>Accueil</li></a>
                <a href="creationCV.php"><li>Créer un CV</li></a>
                <a href="importationCV.php"><li>Importer un CV</li></a>
                <a href="liste_cv.php"><li>CV stockés</li></a>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Importer votre CV</h1>
        <p>Importez votre CV et convertissez-le en format WAMA</p><br><br>

        <form action="import.php" method="POST" enctype="multipart/form-data">
            <label class="upload-box">
                Cliquez pour choisir un fichier
                <input type="file" name="cv_file" accept=".pdf,.doc,.docx,.txt" onchange="handleFile(this)" required>
            </label>

            <div id="fileName" class="file-name"></div><br>

            <label for="logo_type" style="display:block; text-align:left; margin-bottom:5px;">Choisir le logo :</label>
            <select name="logo_type" required>
                <option value="invest">Logo WAMA INVEST</option>
                <option value="link">Logo WAMA LINK</option>
            </select>

            <div style="margin-top: 20px;">
                <button type="submit">Convertir en CV WAMA</button>
            </div>

        </form>
    </div>
    <footer>
        <p>&copy;2026 - WAMA INVEST</p>
    </footer>
</body>
</html>