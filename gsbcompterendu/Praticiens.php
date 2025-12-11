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

// GESTION DES DÉTAILS - Logique imbriquée
$selectedPraticien = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $detailStmt = $pdo->prepare("SELECT * FROM praticien WHERE id = ?");
    $detailStmt->execute([$id]);
    $selectedPraticien = $detailStmt->fetch(PDO::FETCH_ASSOC);
    if ($selectedPraticien) {
        $search = ''; // On efface la recherche si on affiche les détails
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste Praticiens GSB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand h1">Praticiens (GSB)</span>
        <a href="welcome.php" class="btn btn-warning float-end">Retour</a>
    </div>
</nav>

<div class="container mt-4">

    <h2 class="text-center mb-3">Recherche Praticiens</h2>

    <form method="GET" class="mb-4 p-3 border rounded bg-white">
        <div class="row g-2 align-items-center">
            <div class="col-8">
                <input type="text" name="search" class="form-control" placeholder="Rechercher (Nom/Prénom/Ville)..."
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" type="submit">Go</button>
            </div>
            <div class="col-auto">
                <a href="praticiens.php" class="btn btn-danger">Reset</a>
            </div>
        </div>
    </form>

    <?php if ($selectedPraticien): ?>
    <div class="card border-info mb-4">
        <div class="card-header bg-info text-white">
            <h5>Détails de <?= htmlspecialchars($selectedPraticien["nom"]) ?></h5>
        </div>
        <div class="card-body">
            <p><strong>Prénom:</strong> <?= htmlspecialchars($selectedPraticien["prenom"]) ?></p>
            <p><strong>Adresse Complète:</strong> <?= htmlspecialchars($selectedPraticien["adresse"]) ?>, <?= htmlspecialchars($selectedPraticien["cp"]) ?> <?= htmlspecialchars($selectedPraticien["ville"]) ?></p>
            <p><strong>Notoriété:</strong> <?= htmlspecialchars($selectedPraticien["coef_notoriete"]) ?></p>
            <a href="praticiens.php" class="btn btn-sm btn-secondary">X Fermer</a>
        </div>
    </div>
    <?php endif; ?>

    <table class="table table-bordered table-sm">
        <thead class="table-secondary">
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
            // BOUCLE D'AFFICHAGE
            if (empty($praticiens)) {
                echo '<tr><td colspan="5" class="text-center">Pas de résultats.</td></tr>';
            } else {
                foreach ($praticiens as $p) {
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($p["nom"]) ?></td>
                        <td><?= htmlspecialchars($p["prenom"]) ?></td>
                        <td><?= htmlspecialchars($p["ville"]) ?></td>
                        <td><?= htmlspecialchars($p["coef_notoriete"]) ?></td>
                        <td>
                            <a href="praticiens.php?id=<?= $p["id"] ?>" class="btn btn-sm btn-success">Détails</a>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
        </tbody>
    </table>

</div>

</body>
</html>