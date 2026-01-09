<?php
session_start();
require_once "bdd.php"; 

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
} 

$praticiens = $pdo->query("SELECT id, nom, prenom FROM praticien ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$produits = $pdo->query("SELECT id, nom FROM produit ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_visiteur = $_SESSION['id'];
    $praticien_id = $_POST['praticien'];
    $date_visite = $_POST['date_visite'];
    $motif = $_POST['motif'];
    $bilan_text = $_POST['bilan'];
    $remplacant_nom = $_POST['remplacant'];

    $sql = $pdo->prepare("
        INSERT INTO compterendu (id_visiteur, id_praticien, id_remplacant, date_visite, motif, bilan)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $sql->execute([$id_visiteur, $praticien_id, $remplacant_nom, $date_visite, $motif, $bilan_text]);
    $id_cr = $pdo->lastInsertId();

    foreach ($_POST['echantillons'] as $idProduit => $qte) {
        if ($qte > 0) {
            $sql_ech = $pdo->prepare("INSERT INTO echantillon (id_cr, id_produit, quantite) VALUES (?, ?, ?)");
            $sql_ech->execute([$id_cr, $idProduit, $qte]);
        }
    }

    header("Location: cr_list.php?ok");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Compte-rendu de Visite</title>

</head>
<body>
    <div class="container">
        <a href="welcome.php" class="back-link">← Retour</a>
        <h1>Saisir le Compte-rendu de Visite</h1>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="prat">Praticien visité *</label>
                    <select name="praticien" id="prat" required>
                        <option value=""> Sélectionnez un praticien </option>
                        <?php foreach($praticiens as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom'] . " " . $p['prenom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_v">Date de visite *</label>
                    <input type="date" name="date_visite" id="date_v" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="motif">Motif de la visite *</label>
                    <select name="motif" id="motif" required>
                        <option value="">Sélectionnez un motif</option>
                        <option value="periodicite">Routine</option>
                        <option value="nouveaute">Nouveau produit</option>
                        <option value="remontage">Problème à remonter</option>
                        <option value="autre">Autre chose</option>
                    </select>
                </div>
                <div class="formuuu">
                    <label for="remplacant">Remplaçant (si absent)</label>
                    <input type="text" name="remplacant" id="remplacant" placeholder="Nom complet">
                </div>
            </div>
            <h3>Produits présentés (max 2)</h3>
            <div class="form-row">
                <div class="formuuu">
                    <label for="prod1">Produit 1</label>
                    <select name="produit1" id="prod1">
                        <option value=""> Aucun </option>
                        <?php foreach($produits as $prod): ?>
                            <option value="<?= $prod['id'] ?>"><?= htmlspecialchars($prod['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="formuuu">
                    <label for="prod2">Produit 2</label>
                    <select name="produit2" id="prod2">
                        <option value=""> Aucun </option>
                        <?php foreach($produits as $prod): ?>
                            <option value="<?= $prod['id'] ?>"><?= htmlspecialchars($prod['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <h3>Distribution des Échantillons</h3>
            <?php foreach($produits as $prod): ?>
                <div class="produit-group">
                    <label><?= htmlspecialchars($prod['nom']) ?></label>
                    <input type="number" name="echantillons[<?= $prod['id'] ?>]" min="0" value="0">
                </div>
            <?php endforeach; ?>

            <div class="formuuu">
                <label for="bilan">Bilan de la visite </label>
                <textarea name="bilan" id="bilan" rows="5" required placeholder="Résumé de la visite, observations..."></textarea>
            </div>

            <button type="submit"> VALIDER LA SAISIE</button>
        </form
    </div>
    <style>
        *{box-sizing:border-box}
        html,body{margin:0;padding:0;font-family:"Segoe UI",Roboto,Arial,sans-serif;background:#f5f5f5;color:#333}
        .container{max-width:700px;margin:40px auto;background:#fff;padding:40px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.1)}
        h1{margin:0 0 30px 0;font-size:28px;color:#333;border-bottom:3px solid #3498db;padding-bottom:15px}
        .formuuu{margin-bottom:20px}
        label{display:block;margin-bottom:6px;font-weight:600;color:#333}
        input[type="text"],input[type="date"],select,textarea{width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px;font-family:inherit}
        input[type="text"]:focus,input[type="date"]:focus,select:focus,textarea:focus{outline:none;border-color:#3498db;box-shadow:0 0 4px rgba(52,152,219,0.2)}
        textarea{resize:vertical}

        h3,h4{margin:25px 0 15px 0;font-size:16px;color:#333}
        .produit-group{display:grid;grid-template-columns:1fr 80px;gap:12px;align-items:flex-end;margin-bottom:12px}
        .produit-group label{margin:0}

        .produit-group input{margin:0}

        button{width:100%;padding:12px;background:linear-gradient(90deg,#3498db,#2980b9);color:#fff;border:0;border-radius:6px;font-weight:700;font-size:16px;cursor:pointer;transition:transform .2s,box-shadow .2s;margin-top:20px}
        button:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(52,152,219,0.3)}

        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:15px}
        .form-row .form-group{margin-bottom:0}
        .back-link{display:inline-block;margin-bottom:20px;color:#3498db;text-decoration:none;font-weight:600}
        .back-link:hover{text-decoration:underline;color:#2980b9}
    </style>
</body>
</html>
