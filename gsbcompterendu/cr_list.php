<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}
require_once "bdd.php";

$id_visiteur = $_SESSION['id'];
$role = $_SESSION['role'] ?? '';

if ($role === 'visiteur') {
    $sql = $pdo->prepare(
        "SELECT cr.id, cr.date_visite, p.nom AS nom_praticien, p.prenom AS prenom_praticien
         FROM compterendu cr
         JOIN praticien p ON cr.id_praticien = p.id
         WHERE cr.id_visiteur = ?
         ORDER BY cr.date_visite DESC"
    );
    $sql->execute([$id_visiteur]);
} else {
    $sql = $pdo->prepare(
        "SELECT cr.id, cr.date_visite, p.nom AS nom_praticien, p.prenom AS prenom_praticien,
                v.nom AS visiteur_nom, v.prenom AS visiteur_prenom
         FROM compterendu cr
         JOIN praticien p ON cr.id_praticien = p.id
         JOIN visiteur v ON cr.id_visiteur = v.id
         ORDER BY cr.date_visite DESC"
    );
    $sql->execute();
}

$crs = $sql->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mes comptes rendus</title>
</head>
<body>
<h2>Mes comptes-rendus</h2>

<?php if (isset($_GET['ok'])) echo "<p>Compte-rendu ajouté ✔</p>"; ?>

<table border="1" cellpadding="8">
    <tr>
        <th>Date visite</th>
        <th>Praticien</th>
        <?php if ($role !== 'id_visiteur'): ?>
          
                  <th>Visiteur</th>
        <?php endif ?>
        <th>Voir</th>
    </tr>


    <?php foreach ($crs as $cr): ?>
    <tr>
        <td><?= htmlspecialchars($cr['date_visite']) ?></td>
        <td><?= htmlspecialchars($cr['nom_praticien'].' '.$cr['prenom_praticien']) ?></td>

        <?php if ($role !== 'id_visiteur'): ?>
           <td>
            <?= htmlspecialchars(($cr['visiteur_nom'] ?? '') . ' ' . ($cr['visiteur_prenom'] ?? '')) ?>
            </td>
        <?php endif ?>

        <td>
            <a href="cr_view.php?id=<?= urlencode($cr['id']) ?>">Voir</a>
        </td>
    </tr>
<?php endforeach ?>



</table>

<a href="welcome.php"><button>Retour</button></a>
<style>
  body{
    font-family: Arial, sans-serif;
    background:#f2f4f7;
    margin:0;
    padding:20px;
  }

  h2{
    color:#fff;
    background-color: #3498db;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    margin-bottom:15px;
    text-align: center;
  }

  p{
    margin:10px 0;
  }

  table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
    box-shadow:0 4px 10px rgba(0,0,0,0.08);
    border-radius:16px;
    overflow:hidden;
    text-align: center;
    margin:auto;
  }

  th, td{
    padding:12px 10px;
    border:1px solid #ddd;
    text-align:center;
  }

  th{
    background:#3498db;
    color:white;
    font-weight:bold;
  }

  tr:nth-child(even){
    background:#f9f9f9;
  }

  tr:hover{
    background:#eef6ff;
  }

  a{
    text-decoration:none;
    color:#3498db;
    font-weight:bold;
  }

  a:hover{
    text-decoration:underline;
  }

 button {
   display: flex;
    margin: 15px auto;
    padding: 12px 24px;
    font-size: 25px;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.2s;
}
</style>

</body>
</html>
