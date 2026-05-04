<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV WAMA INVEST</title>
</head>
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
    h2{
        text-align: center;
    }

    .main1{
        display: flex;
        align-items: center;
        text-align: center;
        justify-content: space-around;
        margin-top: 5%;
    }

    button{
        padding: 12px 25px;
        border: none;
        background: #1a73e8;
        color: white;
        text-decoration: none;
        font-weight: bold;
        margin-right: 10px;
        border-radius: 6px;
        cursor: pointer;
    }
    button:hover{
        background: #155cc0;
    }


    .main2{
        display: flex;
        justify-content: space-around;
        text-align: center;
    }

    .cards {
        display: flex;
        justify-content: center;
        gap: 30px;
    }

    .card {
        background: white;
        padding: 30px;
        width: 250px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .card h3 {
        margin-bottom: 10px;
    }

    .steps {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .step {
        background: white;
        border-radius: 20px;
        padding: 25px 35px;
        text-align: center;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        min-width: 180px;
    }

    .step:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.2);
    }

    .step-number {
        width: 40px;
        height: 40px;
        background: #155cc0;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        margin: 0 auto 15px auto;
        box-shadow: 0 4px 8px rgba(79, 70, 229, 0.3);
    }

    .step-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1E293B;
        margin-bottom: 8px;
    }

    .step-sub {
        font-size: 0.9rem;
        color: #64748B;
    }

    .arrow {
        font-size: 2.5rem;
        color: #4F46E5;
        font-weight: 300;
        animation: pulse 1.5s infinite;
    }
</style>
<body>

    <?php require 'inc/header.php'; ?>
    
    <section class="main1">
        <div>
            <h2>Créer votre CV au format WAMA</h2><br>
            <p>Générez un CV professionnel, structuré et conforme aux standards de notre entreprise en quelques clics.</p><br>
            <a href="CreationCV.php"><button>Créer mon CV</button></a>
            <a href="importationCV.php"><button>Importer mon CV</button></a>
        </div>
        <img src="images/image.png" alt="image" width="30%">
    </section><br><br>

    <h2>Pourquoi utiliser notre application?</h2><br>
    <section class="main2">
        <div class="card">
            <img src="images/rapide.png" alt="erreur" width="20%">
            <h3>Rapide</h3>
            <p>Réduit la perte du temps et génére le CV en quelques moments</p>
        </div>
        <div class="card">
            <img src="images/standard.png" alt="erreur" width="20%">
            <h3>Standarisée</h3>
            <p>Respect le format officiel WAMA INVEST</p>
        </div>
        <div class="card">
            <img src="images/pro.png" alt="erreur" width="20%">
            <h3>Professionnel</h3>
            <p>Présentation claire et moderne</p>
        </div>
    </section><br><br>

    <h2>Comment ça fonctionne?</h2><br>
    <section class="main3">
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-title">📄 Remplir</div>
                <div class="step-sub">ou importer un fichier</div>
            </div>
            <div class="arrow">→</div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-title">⚙️ Transformation</div>
                <div class="step-sub">Traitement des données</div>
            </div>
            <div class="arrow">→</div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-title">📥 Télécharger PDF</div>
                <div class="step-sub">Export final</div>
            </div>
        </div>
    </section><br><br>
    <?php require 'inc/footer.php';?>
</body>
</html>