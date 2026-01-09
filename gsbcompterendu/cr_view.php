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

// Echantillons
$sql_e = $pdo->prepare("
    SELECT e.quantite, pr.nom
    FROM echantillon e
    JOIN produit pr ON pr.id = e.id_produit
    WHERE e.id_cr = ?");
$sql_e->execute([$id]);
$ech = $sql_e->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Détail CR</title></head>
<body>
  <div class="box">
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
  </div>
 <a href="welcome.php"><button class="retor" style="padding: 12px 24px; font-size: 16px;">Retour</button></a>
</body>

<style>
  body{
    font-family: Arial, sans-serif;
    background:#f2f4f7;
    margin:0;
    padding:20px;
  }

  h2, h3{
    color:#3498db;
    margin-top:0;
  }

  .box{
    max-width:700px;
    margin:auto;
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 4px 10px rgba(0,0,0,0.08);
  }

  p{
    margin:10px 0;
  }

  ul{
    padding-left:20px;
  }

  li{
    margin-bottom:8px;
  }

  button{
    margin-top:15px;
    padding:10px 18px;
    background-color:#3498db;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-size:15px;
    transition:0.2s;
  }

  button:hover{
    background:#2c80b4;
  }
</style>



</html>
