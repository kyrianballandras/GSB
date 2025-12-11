<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}require_once "bdd.php";

$id_visiteur = $_SESSION['id'];
$sql = $pdo->prepare("
    SELECT cr.id, cr.date_visite, p.nom AS nom_praticien, p.prenom AS prenom_praticien
    FROM compterendu cr
    JOIN praticien p ON cr.id_praticien = p.id
    WHERE cr.id_visiteur = ?
    ORDER BY cr.date_visite DESC
");$sql->execute([$id_visiteur]);
$crs = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Mes comptes rendus</title></head>
<body>

<h2>Mes comptes-rendus</h2>

<?php if (isset($_GET['ok'])) echo "<p>Compte-rendu ajouté ✔</p>"; ?>

<table border="1" cellpadding="8">
    <tr>
        <th>Date visite</th>
        <th>Praticien</th>
        <th>Voir</th>
    </tr>

    <?php foreach($crs as $cr): ?>
        <tr>
            <td><?= $cr['date_visite'] ?></td>
            <td><?= $cr['nom_praticien']." ".$cr['prenom_praticien'] ?></td>
            <td><a href="cr_view.php?id=<?= $cr['id'] ?>">Voir</a></td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
