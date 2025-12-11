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
    // les produits présentés et récupérer 


    $sql = $pdo->prepare("
        INSERT INTO compterendu (id_visiteur, id_praticien, id_remplacant, date_visite, motif, bilan)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $sql->execute([$id_visiteur, $praticien_id, $remplacant_nom ?: null, $date_visite, $motif, $bilan_text]);
    $id_cr = $pdo->lastInsertId();

    
    // boucle des échantillons
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
<html>
<head>
    <meta charset="UTF-8">
    <title>CR Visite</title>
</head>
<body>

<h1>Saisir le Compte-rendu de Visite</h1>

<form method="POST">

    <label for="prat">Praticien visité :</label>
    <select name="praticien" id="prat" required>
        <?php foreach($praticiens as $p): ?>
            <option value="<?= $p['id'] ?>"><?= $p['nom'] . " " . $p['prenom'] ?></option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label for="date_v">Date :</label>
    <input type="date" name="date_visite" id="date_v" required>
    <br><br>

    <label>Motif de la visite :</label>
    <select name="motif" required>
        <option value="periodicite">Routine</option>
        <option value="nouveaute">Nouveau produit</option>
        <option value="remontage">Problème à remonter</option>
        <option value="autre">Autre chose</option>
    </select>
    <br><br>

    <label>Remplaçant (Nom complet) :</label>
    <input type="text" name="remplacant" placeholder="Si praticien absent">
    <br><br>

    <h3>Produits présentés (max 2) :</h3>
    <select name="produit1">
        <option value="">-- Produit 1 --</option>
        <?php foreach($produits as $prod): ?>
            <option value="<?= $prod['id'] ?>"><?= $prod['nom'] ?></option>
        <?php endforeach; ?>
    </select>
    <br>

    <select name="produit2">
        <option value="">-- Produit 2 --</option>
        <?php foreach($produits as $prod): ?>
            <option value="<?= $prod['id'] ?>"><?= $prod['nom'] ?></option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <h4>Distribution des Échantillons :</h4>
    <?php foreach($produits as $prod): ?>
        <label><?= $prod['nom'] ?> :</label>
        <input type="number" name="echantillons[<?= $prod['id'] ?>]" min="0" value="0" style="width: 50px;"><br>
    <?php endforeach; ?>

    <br>

    <label>Bilan de la visite :</label><br>
    <textarea name="bilan" rows="4" cols="40" required></textarea>
    <br><br>

    <button type="submit">VALIDER LA SAISIE</button>

</form>

</body>
</html>
