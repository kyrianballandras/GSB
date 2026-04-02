<?php
session_start();
require_once "bdd.php";

// VÉRIFICATION D'AUTHENTIFICATION - Très direct
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

// GESTION DE LA RECHERCHE ET RÉSULTATS
$praticiens = [];
$search = trim($_GET['search'] ?? '');

if ($search != "") {
    $stmt = $pdo->prepare("SELECT * FROM praticien 
                         WHERE nom LIKE ? 
                         OR prenom LIKE ?
                         OR ville LIKE ?
                         ORDER BY nom ASC");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
    $praticiens = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $praticiens = $pdo->query("SELECT * FROM praticien ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
}
 
$selectedPraticien = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $detailStmt = $pdo->prepare("SELECT * FROM praticien WHERE id = ?");
    $detailStmt->execute([$id]);
    $selectedPraticien = $detailStmt->fetch(PDO::FETCH_ASSOC);
    if ($selectedPraticien) {
        $search = ''; //  efface la recherche , affiche les détails
    }
}
?>

</style>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste Praticiens GSB</title>
</head>
<body>

<div class="divee">
    <h1>Praticiens (GSB)</h1>
</div>

<h2 style="">Recherche</h2>
<form method="GET">
    <input type="text" name="search" placeholder="Nom, Prénom ou Ville..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <button style="background-color: #3498db; color: white; border: none; border-radius: 8px; cursor: pointer; transition: 0.2s;"  >Chercher</button>
    <a href="praticiens.php"><button style=" background: #3498db;
     color: white;
     border: none;
     border-radius: 8px;
     cursor: pointer;
     transition: 0.2s;">Réinitialiser</button></a>
</form>

<?php if ($selectedPraticien): ?>
<div style="border: 1px solid #ccc; padding: 15px; margin: 15px 0;">
    <h3><?= htmlspecialchars($selectedPraticien["nom"]) ?></h3>
    <p><strong>Prénom:</strong> <?= htmlspecialchars($selectedPraticien["prenom"]) ?></p>
    <p><strong>Adresse:</strong> <?= htmlspecialchars($selectedPraticien["adresse"]) ?>, <?= htmlspecialchars($selectedPraticien["cp"]) ?> <?= htmlspecialchars($selectedPraticien["ville"]) ?></p>
    <p><strong>Notoriété:</strong> <?= htmlspecialchars($selectedPraticien["coef_notoriete"]) ?></p>
    <a href="praticiens.php"><button style=" background: #3498db;
     color: white;
     border: none;
     border-radius: 8px;
     cursor: pointer;
     transition: 0.2s;">Fermer</button></a>
</div>
<?php endif ?>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Ville</th>
            <th>Notoriété</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        if (empty($praticiens)) {
            echo '<tr><td colspan="5">Pas de résultats.</td></tr>';
        } else {
            foreach ($praticiens as $p) {
                ?>
                <tr>
                    <td><?= htmlspecialchars($p["nom"]) ?></td>
                    <td><?= htmlspecialchars($p["prenom"]) ?></td>
                    <td><?= htmlspecialchars($p["ville"]) ?></td>
                    <td><?= htmlspecialchars($p["coef_notoriete"]) ?></td>
                    <td><a href="praticiens.php?id=<?= $p["id"] ?>"style="
     color: 3498db;
     border: none;
     border-radius: 8px;
     cursor: pointer
     transition: 0.2s;">Détails</a></td>
                </tr>
                <?php
            }
        }
        ?>
    </tbody>
</table>
<a href="welcome.php"><button style="display: flex;
      margin: 15px auto;
     padding: 12px 24px;
     font-size: 25px;
     background: #3498db;
     color: white;
     border: none;
     border-radius: 8px;
     cursor: pointer;
     transition: 0.2s;">Retour</button></a>

 <style>
    .divee
    {
        "display: flex; justify-content: space-between; align-items: center;
    }
        body 
        { font-family: Arial; margin: 20px; }

        input, button
         { padding: 8px; font-size: 14px; }

        h1{
           
    color:#fff;
    background-color: #3498db;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    margin-bottom:15px;
    text-align: center;

        }
        button
         { cursor: pointer; }

        table
         { border-collapse: collapse; width: 100%; margin-top: 20px; }

        th, td 
        { border: 1px solid #ccc; padding: 10px; text-align: left; }

        th 
        { background-color: #f0f0f0; }

        a
         { text-decoration: none; color: blue; }
        a:hover
         { text-decoration: underline; }
    </style>
</body>
</html>