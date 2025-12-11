<?php
session_start();
if (!isset($_SESSION['id'])) { header("Location: index.php"); exit; }

require "bdd.php";

$id = $_GET['id'];
// Compte-rendu
$sql = $pdo->prepare("
    SELECT cr.*, p.nom AS nom_p, p.prenom AS prenom_p
    FROM compterendu cr
    JOIN praticien p ON cr.id_praticien = p.id
    WHERE cr.id = ?
");
$sql->execute([$id]);
$cr = $sql->fetch(PDO::FETCH_ASSOC);

// Échantillons
$sql_e = $pdo->prepare("
    SELECT e.quantite, pr.nom
    FROM echantillon e
    JOIN produit pr ON pr.id = e.id_produit
    WHERE e.id_cr = ?
");
$sql_e->execute([$id]);
$ech = $sql_e->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Détail CR</title></head>
<body>

<h2>Compte-rendu n°<?= $cr['id'] ?></h2>

<p><b>Praticien :</b> <?= $cr['nom_p']." ".$cr['prenom_p'] ?></p>
<p><b>Date visite :</b> <?= $cr['date_visite'] ?></p>
<p><b>Motif :</b> <?= $cr['motif'] ?></p>
<p><b>Remplaçant :</b> <?= $cr['id_remplacant'] ?: "Aucun" ?></p>
<p><b>Bilan :</b><br><?= nl2br($cr['bilan']) ?></p>

<h3>Échantillons :</h3>
<ul>
<?php foreach($ech as $e): ?>
    <li><?= $e['nom']." : ".$e['quantite'] ?></li>
<?php endforeach; ?>
</ul>

</body>
</html>
